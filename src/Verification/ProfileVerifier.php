<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Verification;

use RatingStar\ContaoSealBundle\Seal\SealSubject;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Checks a profile or group slug against the public page
 * GET <origin>/t/<slug> (branch) or GET <origin>/g/<group> (group) and reads
 * the profile name from its <title>.
 *
 * The check only informs: the caller saves the value in any case, so a typo
 * stays correctable and an unreachable RatingStar never blocks the editor.
 */
class ProfileVerifier
{
    private const TIMEOUT = 5;

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function verify(string $baseOrigin, SealSubject $subject): ProfileVerification
    {
        $url = rtrim($baseOrigin, '/').$subject->profilePath();

        try {
            $response = $this->httpClient->request('GET', $url, [
                'timeout' => self::TIMEOUT,
                'max_duration' => self::TIMEOUT,
                'max_redirects' => 2,
                'headers' => ['Accept' => 'text/html'],
            ]);

            $status = $response->getStatusCode();

            if (404 === $status) {
                return new ProfileVerification(ProfileVerification::NOT_FOUND, $url);
            }

            if (200 !== $status) {
                return new ProfileVerification(ProfileVerification::UNAVAILABLE, $url, detail: 'HTTP '.$status);
            }

            $name = $this->extractName($response->getContent(false));
        } catch (ExceptionInterface) {
            return new ProfileVerification(ProfileVerification::UNAVAILABLE, $url);
        }

        return new ProfileVerification(ProfileVerification::FOUND, $url, $name ?? $subject->id);
    }

    /**
     * Profile titles read "<name> · Bewertungen · RatingStar"; the suffix is
     * dropped for the confirmation message.
     */
    private function extractName(string $html): string|null
    {
        if (1 !== preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            return null;
        }

        $title = strip_tags($matches[1]);

        // Decode until stable: an ampersand may arrive as "&amp;amp;".
        for ($round = 0; $round < 3; ++$round) {
            $decoded = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if ($decoded === $title) {
                break;
            }

            $title = $decoded;
        }

        $title = (string) preg_replace('/(\s*[·|–-]\s*Bewertungen)?\s*[·|–-]\s*RatingStar\s*$/u', '', $title);
        $title = trim((string) preg_replace('/\s+/u', ' ', $title));

        return '' !== $title ? mb_substr($title, 0, 200) : null;
    }
}
