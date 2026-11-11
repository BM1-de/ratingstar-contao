<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Seal;

/**
 * Canonical seal.js widget variants — a mirror of the app's
 * SealConfig::VARIANT_META. The full set is offered with the canonical keys;
 * the visible names live in the language files
 * (tl_content.ratingstar_variant_options.*).
 *
 * The three variant namespaces (seal.js data-variant, static SVG ?variant,
 * google-stars ?v) are separate on purpose; this class covers the seal.js
 * namespace and its mapping onto the static SVG namespace.
 */
final class SealVariants
{
    public const DEFAULT = 'seal-circle-banner';

    /**
     * Canonical variant keys in the order shown in the back end.
     *
     * @var list<string>
     */
    public const KEYS = [
        'seal-circle',
        'seal-circle-banner',
        'profile-card',
        'bar',
        'hero',
        'quote',
        'carousel',
        'wall',
        'footer-bar',
    ];

    /**
     * Legacy names (SealConfig::VARIANT_ALIAS) mapped to the canonical keys.
     * They are still accepted but never offered. The former floating badge
     * is the profile card placed in a screen corner.
     *
     * @var array<string, string>
     */
    public const ALIASES = [
        'circle' => 'seal-circle',
        'banner' => 'seal-circle-banner',
        'card' => 'profile-card',
        'floating' => 'profile-card',
        'pro' => 'profile-card',
    ];

    /**
     * Variants that exist as a static SVG, mapped to the ?variant value of
     * /seal/<slug>.svg. Live-only variants have no static motif, so a static
     * mode is never offered for them.
     *
     * @var array<string, string>
     */
    public const STATIC_SVG = [
        'seal-circle' => 'circle',
        'seal-circle-banner' => 'banner',
        'profile-card' => 'card',
    ];

    /**
     * Display width in px of a static image without a size option — the
     * widths seal.js falls back to when it has no configuration.
     *
     * @var array<string, int>
     */
    public const STATIC_FALLBACK_WIDTH = [
        'circle' => 200,
        'banner' => 200,
        'card' => 120,
    ];

    /**
     * Allowed data-position values (profile card only).
     *
     * @var list<string>
     */
    public const POSITIONS = ['inline', 'bottom-right', 'bottom-left', 'top-right', 'top-left'];

    /**
     * Prefix of the value stored in tl_content.ratingstar_variant. Contao
     * probes every selector value as a palette name BEFORE the element type
     * ("bar", "hero" or "quote" would pick up a palette of that name from
     * another bundle), so the stored value must not be a generic word.
     */
    public const STORAGE_PREFIX = 'ratingstar_';

    public static function isValid(string $variant): bool
    {
        return \in_array($variant, self::KEYS, true);
    }

    /**
     * Maps a stored, legacy or user-supplied value onto a canonical key.
     * Unknown values fall back to the default (the render fallback of seal.js).
     */
    public static function normalize(string $variant): string
    {
        $variant = strtolower(trim($variant));

        if (str_starts_with($variant, self::STORAGE_PREFIX)) {
            $variant = substr($variant, \strlen(self::STORAGE_PREFIX));
        }

        $variant = self::ALIASES[$variant] ?? $variant;

        return self::isValid($variant) ? $variant : self::DEFAULT;
    }

    public static function toStorage(string $variant): string
    {
        return self::STORAGE_PREFIX.$variant;
    }

    /**
     * @return list<string>
     */
    public static function storageValues(): array
    {
        return array_map(self::toStorage(...), self::KEYS);
    }

    public static function hasPosition(string $variant): bool
    {
        return 'profile-card' === $variant;
    }

    /**
     * Empty = no data-position, the position from the RatingStar portal applies.
     */
    public static function normalizePosition(string $position): string
    {
        $position = strtolower(trim($position));

        return \in_array($position, self::POSITIONS, true) ? $position : '';
    }

    public static function staticSvgVariant(string $variant): string|null
    {
        return self::STATIC_SVG[$variant] ?? null;
    }
}
