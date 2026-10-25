<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Seal;

/**
 * Canonical seal.js widget variants — a faithful mirror of the app's
 * SealConfig::VARIANT_META (CONTRACT.md §5). New plugins must offer the FULL
 * set with the canonical keys; do not silently drop variants such as
 * "footer-bar".
 *
 * The three variant namespaces (seal.js data-variant, static SVG ?variant,
 * google-stars ?v) are kept separate on purpose — this class only covers the
 * seal.js data-variant namespace used by the content element.
 */
final class SealVariants
{
    public const DEFAULT = 'seal-circle-banner';

    /** Empty = the position from the RatingStar settings (no data-position). */
    public const DEFAULT_POSITION = '';

    /**
     * Canonical variant key => [label, category]. Order is the order shown in
     * the back end select.
     *
     * @var array<string, array{label: string, category: string}>
     */
    public const META = [
        'seal-circle'        => ['label' => 'Rundsiegel',       'category' => 'static'],
        'seal-circle-banner' => ['label' => 'Banderole',        'category' => 'static'],
        'profile-card'       => ['label' => 'Profil-Karte',     'category' => 'live'],
        'bar'                => ['label' => 'Bewertungsleiste', 'category' => 'live'],
        'hero'               => ['label' => 'Gesamtnote',       'category' => 'live'],
        'quote'              => ['label' => 'Zitat',            'category' => 'live'],
        'carousel'           => ['label' => 'Karussell',        'category' => 'live'],
        'wall'               => ['label' => 'Bewertungswand',   'category' => 'live'],
        'footer-bar'         => ['label' => 'Fußleiste',        'category' => 'live'],
    ];

    /**
     * Legacy names (SealConfig::VARIANT_ALIAS) mapped to the canonical keys.
     * The former floating badge is the profile card in a screen corner.
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
     * Variants for which a data-position is meaningful: only the profile
     * card, inline or in a screen corner (CONTRACT.md §4).
     *
     * @var list<string>
     */
    public const POSITION_VARIANTS = ['profile-card'];

    /**
     * Allowed data-position values.
     *
     * @var list<string>
     */
    public const POSITIONS = ['inline', 'bottom-right', 'bottom-left', 'top-right', 'top-left'];

    /**
     * Variants with a dark look via data-theme (CONTRACT.md §17).
     *
     * @var list<string>
     */
    public const THEME_VARIANTS = ['bar', 'hero', 'quote', 'carousel', 'wall'];

    /**
     * @var list<string>
     */
    public const THEMES = ['light', 'dark'];

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::META);
    }

    public static function isValid(string $variant): bool
    {
        return isset(self::META[$variant]);
    }

    public static function normalize(string $variant): string
    {
        $variant = trim($variant);
        $variant = self::ALIASES[$variant] ?? $variant;

        return self::isValid($variant) ? $variant : self::DEFAULT;
    }

    public static function hasPosition(string $variant): bool
    {
        return \in_array($variant, self::POSITION_VARIANTS, true);
    }

    public static function normalizePosition(string $position): string
    {
        $position = trim($position);

        return \in_array($position, self::POSITIONS, true) ? $position : self::DEFAULT_POSITION;
    }

    public static function hasTheme(string $variant): bool
    {
        return \in_array($variant, self::THEME_VARIANTS, true);
    }

    /**
     * Empty = the colour scheme from the RatingStar settings (no data-theme).
     */
    public static function normalizeTheme(string $theme): string
    {
        $theme = trim($theme);

        return \in_array($theme, self::THEMES, true) ? $theme : '';
    }
}
