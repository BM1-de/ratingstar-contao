<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\JsonLd;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use RatingStar\ContaoSealBundle\Seal\SealSettings;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fetches the ready-made LocalBusiness JSON-LD of a website from the
 * key-based endpoint /seal/k/<key>.jsonld — on the server, so the visitor's
 * browser needs no connection to RatingStar for the Google stars.
 *
 * Fail-soft: any error (timeout, 403/404, invalid body) yields "no block" and
 * never breaks the page. A success is cached for 6 hours, a failure for 15
 * minutes so that a wrong key or an outage does not slow down every request.
 *
 * The same goes for the cache: if it fails (pool folder not writable, Redis
 * not reachable or read-only), there is no block and no request. A response
 * that cannot be stored would be fetched again on every page view.
 */
class JsonLdProvider
{
    public const TTL_SUCCESS = 21600;
    public const TTL_FAILURE = 900;

    /**
     * < > & ' " are written as \uXXXX: a company name or review text
     * containing "</script>" cannot end the surrounding script block.
     */
    public const ENCODE_FLAGS = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    private const TIMEOUT = 5;
    private const MAX_BYTES = 262144;

    /**
     * @var array<string, string|null>
     */
    private array $resolved = [];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheItemPoolInterface $cache,
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

    /**
     * Returns the JSON for the <script type="application/ld+json"> block,
     * already encoded for safe output, or null if there is nothing to print.
     */
    public function get(SealSettings $settings): string|null
    {
        if (!$settings->isJsonLdActive()) {
            return null;
        }

        $cacheKey = 'jsonld.'.hash('sha256', $settings->baseOrigin.'|'.$settings->apiKey);

        if (\array_key_exists($cacheKey, $this->resolved)) {
            return $this->resolved[$cacheKey];
        }

        $json = $this->load($cacheKey, $settings);

        return $this->resolved[$cacheKey] = \is_string($json) && '' !== $json ? $json : null;
    }

    /**
     * Drops all cached RatingStar responses, e.g. after a key rotation or a
     * plan change.
     */
    public function clear(): bool
    {
        $this->resolved = [];

        return $this->cache->clear();
    }

    /**
     * Decodes a remote body, accepts it only as a LocalBusiness object and
     * re-encodes it for output inside a script block. The remote response is
     * never printed as raw text.
     */
    public function sanitize(string $body): string|null
    {
        if ('' === $body || \strlen($body) > self::MAX_BYTES) {
            return null;
        }

        try {
            $data = json_decode($body, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!\is_array($data) || 'LocalBusiness' !== ($data['@type'] ?? null)) {
            return null;
        }

        try {
            return json_encode($data, self::ENCODE_FLAGS | JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
    }

    /**
     * Reads the response from the cache or fetches it. Before the request the
     * entry is saved as a remembered failure — only a cache that takes this
     * very entry gets a request.
     */
    private function load(string $cacheKey, SealSettings $settings): string|null
    {
        try {
            $item = $this->cache->getItem($cacheKey);

            if ($item->isHit()) {
                $cached = $item->get();

                return \is_string($cached) ? $cached : null;
            }

            // An empty string marks a remembered failure.
            if (!$this->store($item, '', self::TTL_FAILURE)) {
                $this->logCacheFailure();

                return null;
            }
        } catch (\Throwable $exception) {
            // A broken cache back end must not take the page down either.
            $this->logCacheFailure($exception);

            return null;
        }

        $json = $this->fetch($settings);

        if (null === $json) {
            // The entry saved above already remembers the failure.
            return null;
        }

        try {
            if (!$this->store($item, $json, self::TTL_SUCCESS)) {
                $this->logCacheFailure();
            }
        } catch (\Throwable $exception) {
            $this->logCacheFailure($exception);
        }

        return $json;
    }

    private function store(CacheItemInterface $item, string $value, int $ttl): bool
    {
        $item->set($value);
        $item->expiresAfter($ttl);

        return $this->cache->save($item);
    }

    /**
     * Level "error", so the line is also written in the production
     * environment, where lower levels only reach the log together with an
     * error.
     */
    private function logCacheFailure(\Throwable|null $exception = null): void
    {
        $this->logger?->error(
            'RatingStar: the cache pool "ratingstar_seal.cache" did not store the JSON-LD entry. Without a working cache there is no JSON-LD block (Google stars) and RatingStar is not asked. Check that the cache folder is writable.',
            null === $exception ? [] : ['exception' => $exception],
        );
    }

    private function fetch(SealSettings $settings): string|null
    {
        try {
            $response = $this->httpClient->request('GET', $settings->jsonLdUrl(), [
                'timeout' => self::TIMEOUT,
                'max_duration' => self::TIMEOUT,
                'max_redirects' => 2,
                'headers' => ['Accept' => 'application/ld+json, application/json'],
            ]);

            $status = $response->getStatusCode();

            if (200 !== $status) {
                // 403 = plan or domain not authorised, 404 = unknown or rotated key.
                $this->logger?->notice(\sprintf('RatingStar: JSON-LD request to %s answered with HTTP %d.', $settings->baseOrigin, $status));

                return null;
            }

            $json = $this->sanitize($response->getContent(false));
        } catch (ExceptionInterface $exception) {
            $this->logger?->notice(\sprintf('RatingStar: JSON-LD request to %s failed: %s', $settings->baseOrigin, $exception::class));

            return null;
        }

        if (null === $json) {
            $this->logger?->notice(\sprintf('RatingStar: JSON-LD response of %s is not a LocalBusiness object.', $settings->baseOrigin));
        }

        return $json;
    }
}
