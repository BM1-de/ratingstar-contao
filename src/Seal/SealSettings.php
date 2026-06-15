<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Seal;

/**
 * Resolved RatingStar settings for one website (Contao root page) — the two
 * independent handles from CONTRACT.md §1 plus the base origin (§2).
 *
 *  - slug:       drives /t/<slug>, the slug-based endpoints and seal.js
 *  - apiKey:     rs_live_ + 40 alphanumerics; drives the rename-safe key-based
 *                endpoints and google-stars.js (wired up with the JSON-LD step)
 *  - baseOrigin: the single origin every URL is built from (no hardcoded host)
 */
final class SealSettings
{
    /** API key shape: rs_live_ followed by exactly 40 alphanumeric chars. */
    public const API_KEY_PATTERN = '/^rs_live_[A-Za-z0-9]{40}$/';

    public function __construct(
        public readonly string $slug,
        public readonly string $apiKey,
        public readonly string $baseOrigin,
    ) {
    }

    public function hasSlug(): bool
    {
        return '' !== $this->slug;
    }

    public function hasValidApiKey(): bool
    {
        return 1 === preg_match(self::API_KEY_PATTERN, $this->apiKey);
    }

    public static function isValidApiKey(string $key): bool
    {
        return 1 === preg_match(self::API_KEY_PATTERN, trim($key));
    }

    /**
     * Absolute URL for seal.js on the configured origin.
     */
    public function sealJsUrl(): string
    {
        return $this->baseOrigin.'/seal.js';
    }
}
