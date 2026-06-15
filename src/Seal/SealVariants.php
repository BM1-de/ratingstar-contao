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

    public const DEFAULT_POSITION = 'bottom-right';

    /**
     * Canonical variant key => [label, category]. Order is the order shown in
     * the back end select.
     *
     * @var array<string, array{label: string, category: string}>
     */
    public const META = [
        'seal-circle'        => ['label' => 'Rundsiegel',     'category' => 'static'],
        'seal-circle-banner' => ['label' => 'Banderole',      'category' => 'static'],
        'profile-card'       => ['label' => 'Profil-Karte',   'category' => 'live'],
        'bar'                => ['label' => 'Trust-Bar',       'category' => 'live'],
        'floating'           => ['label' => 'Floating Badge',  'category' => 'live'],
        'hero'               => ['label' => 'Hero-Snippet',    'category' => 'live'],
        'quote'              => ['label' => 'Featured Quote',  'category' => 'live'],
        'carousel'           => ['label' => 'Carousel',        'category' => 'live'],
        'wall'               => ['label' => 'Wall of Love',    'category' => 'live'],
        'footer-bar'         => ['label' => 'Footer-Bar',      'category' => 'live'],
    ];

    /**
     * Variants for which a data-position is meaningful. seal.js only reads
     * data-position for the floating ("pro") widget.
     *
     * @var list<string>
     */
    public const POSITION_VARIANTS = ['floating'];

    /**
     * Allowed data-position values (seal.js floating corners + inline).
     *
     * @var list<string>
     */
    public const POSITIONS = ['bottom-right', 'bottom-left', 'top-right', 'top-left', 'inline'];

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
}
