<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Seal;

/**
 * Resolved RatingStar settings of one website (Contao root page).
 *
 *  - slug:          profile slug of a branch (/t/<slug>)
 *  - group:         slug of a group (/g/<group>); if set, the website shows
 *                   the seal of the group instead of a single branch
 *  - apiKey:        rs_live_ + 40 alphanumerics; drives the rename-safe
 *                   key-based endpoints (server-side JSON-LD). With a group
 *                   set this is the key of the group.
 *  - baseOrigin:    the one origin every RatingStar URL is built from
 *  - jsonLdEnabled: print the server-side LocalBusiness JSON-LD block
 */
final class SealSettings
{
    /** API key shape: rs_live_ followed by exactly 40 alphanumeric chars. */
    public const API_KEY_PATTERN = '/^rs_live_[A-Za-z0-9]{40}$/';

    public function __construct(
        public readonly string $slug,
        public readonly string $group,
        public readonly string $apiKey,
        public readonly string $baseOrigin,
        public readonly bool $jsonLdEnabled,
    ) {
    }

    public static function isValidApiKey(string $key): bool
    {
        return 1 === preg_match(self::API_KEY_PATTERN, $key);
    }

    /**
     * An origin is scheme + host (+ port) without path, query or fragment.
     */
    public static function isValidOrigin(string $origin): bool
    {
        return 1 === preg_match('#^https?://[^/\s?\#@]+$#i', $origin);
    }

    /**
     * The server-side JSON-LD block is switched on and has a usable key. If
     * so, every seal of the page suppresses the client-side rich snippet.
     */
    public function isJsonLdActive(): bool
    {
        return $this->jsonLdEnabled && self::isValidApiKey($this->apiKey);
    }

    /**
     * Element override beats the website setting; on each level a group
     * beats the profile slug.
     */
    public function subject(string $slugOverride = '', string $groupOverride = ''): SealSubject|null
    {
        foreach ([[$groupOverride, true], [$slugOverride, false], [$this->group, true], [$this->slug, false]] as [$id, $isGroup]) {
            $id = trim($id);

            if ('' !== $id) {
                return new SealSubject($id, $isGroup);
            }
        }

        return null;
    }

    public function url(string $path): string
    {
        return $this->baseOrigin.$path;
    }

    public function sealJsUrl(): string
    {
        return $this->url('/seal.js');
    }

    /**
     * Key-based JSON-LD endpoint; a group key returns the block of the group.
     */
    public function jsonLdUrl(): string
    {
        return $this->url('/seal/k/'.rawurlencode($this->apiKey).'.jsonld');
    }
}
