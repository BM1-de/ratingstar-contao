<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Seal;

/**
 * Per-embed options of a seal — a mirror of EMBED_OVERRIDES in the app's
 * seal-embed.js. Each option travels as its own data attribute on the
 * .rs-seal element (camelCase key → data-kebab-case, booleans as 1/0).
 *
 * The defaults belong to the RatingStar portal: an option that is not set
 * here produces NO attribute, so a later change of the portal defaults still
 * reaches the website. seal.js validates the values itself (enums, hex
 * colours, numbers clamped into the server ranges).
 */
final class SealOptions
{
    public const TYPE_INT = 'int';
    public const TYPE_BOOL = 'bool';
    public const TYPE_ENUM = 'enum';
    public const TYPE_COLOR = 'color';

    /**
     * Option key => definition. "min"/"max" are informational (shown in the
     * field descriptions), seal.js clamps. "accept" lists legacy enum values
     * that are passed through but never offered.
     *
     * @var array<string, array{type: string, values?: list<string>, accept?: list<string>, min?: int, max?: int}>
     */
    public const DEFINITIONS = [
        'size' => ['type' => self::TYPE_INT, 'min' => 120, 'max' => 320],
        'pcPosition' => ['type' => self::TYPE_ENUM, 'values' => SealVariants::POSITIONS],
        'pcWidth' => ['type' => self::TYPE_INT, 'min' => 100, 'max' => 215],
        'pcShowCount' => ['type' => self::TYPE_BOOL],
        'pcColor' => ['type' => self::TYPE_ENUM, 'values' => ['gold', 'silber', 'weiss']],
        'pcMobile' => ['type' => self::TYPE_ENUM, 'values' => ['compact', 'hide', 'full']],
        'pcClick' => ['type' => self::TYPE_ENUM, 'values' => ['overlay', 'profile']],
        'barShowCount' => ['type' => self::TYPE_BOOL],
        'barShowVerified' => ['type' => self::TYPE_BOOL],
        'heroShowCount' => ['type' => self::TYPE_BOOL],
        'heroShowVerified' => ['type' => self::TYPE_BOOL],
        'heroShowBranches' => ['type' => self::TYPE_BOOL],
        'quotePick' => ['type' => self::TYPE_ENUM, 'values' => ['best', 'newest', 'rotate', 'manual']],
        'quoteWidth' => ['type' => self::TYPE_ENUM, 'values' => ['s', 'm', 'l', 'full']],
        'carCount' => ['type' => self::TYPE_INT, 'min' => 3, 'max' => 20],
        'carRotate' => ['type' => self::TYPE_INT, 'min' => 0, 'max' => 30],
        'carMin' => ['type' => self::TYPE_INT, 'min' => 1, 'max' => 5],
        'carWidth' => ['type' => self::TYPE_ENUM, 'values' => ['s', 'm', 'l', 'full']],
        'wallCols' => ['type' => self::TYPE_INT, 'min' => 2, 'max' => 4],
        'wallTotal' => ['type' => self::TYPE_INT, 'min' => 2, 'max' => 50],
        'wallMin' => ['type' => self::TYPE_INT, 'min' => 1, 'max' => 5],
        'wallSort' => ['type' => self::TYPE_ENUM, 'values' => ['newest', 'best', 'mix']],
        'wallWidth' => ['type' => self::TYPE_ENUM, 'values' => ['m', 'l', 'xl', 'full']],
        'footerBarBg' => ['type' => self::TYPE_COLOR],
        'footerBarText' => ['type' => self::TYPE_COLOR],
        'footerBarLinkColor' => ['type' => self::TYPE_COLOR],
        'footerBarLinkHover' => ['type' => self::TYPE_COLOR],
        'footerBarPosition' => ['type' => self::TYPE_ENUM, 'values' => ['fixed', 'inline'], 'accept' => ['absolute']],
        'reviewsOnlyNamed' => ['type' => self::TYPE_BOOL],
        'reviewsOnlyWithComment' => ['type' => self::TYPE_BOOL],
        'theme' => ['type' => self::TYPE_ENUM, 'values' => ['light', 'dark']],
    ];

    /**
     * Options offered as native fields per variant — a mirror of "attrKeys"
     * in the portal's code generator. The position of the profile card
     * (pcPosition) is the element's own position field (data-position).
     *
     * @var array<string, list<string>>
     */
    public const VARIANT_KEYS = [
        'seal-circle' => ['size'],
        'seal-circle-banner' => ['size'],
        'profile-card' => ['pcWidth', 'pcColor', 'pcShowCount', 'pcMobile', 'pcClick'],
        'bar' => ['barShowCount', 'barShowVerified', 'theme'],
        'hero' => ['heroShowCount', 'heroShowVerified', 'heroShowBranches', 'theme'],
        'quote' => ['quotePick', 'quoteWidth', 'reviewsOnlyNamed', 'reviewsOnlyWithComment', 'theme'],
        'carousel' => ['carCount', 'carRotate', 'carMin', 'carWidth', 'reviewsOnlyNamed', 'reviewsOnlyWithComment', 'theme'],
        'wall' => ['wallCols', 'wallTotal', 'wallMin', 'wallSort', 'wallWidth', 'reviewsOnlyNamed', 'reviewsOnlyWithComment', 'theme'],
        'footer-bar' => ['footerBarPosition', 'footerBarBg', 'footerBarText', 'footerBarLinkColor', 'footerBarLinkHover'],
    ];

    /**
     * Options that only make sense for the seal of a group.
     *
     * @var list<string>
     */
    public const GROUP_ONLY = ['heroShowBranches'];

    /**
     * data attributes the pass-through field must never set: identity,
     * variant and position come from their own fields, data-no-richsnippet
     * enforces the one-JSON-LD-block rule and the preview attributes are
     * reserved for the RatingStar portal.
     *
     * @var list<string>
     */
    public const RESERVED_ATTRIBUTES = [
        'data-slug',
        'data-group',
        'data-variant',
        'data-position',
        'data-no-richsnippet',
        'data-preview-config',
        'data-preview-data',
        'data-preview-svg',
    ];

    /**
     * Options with a native field in the content element, i.e. everything
     * from VARIANT_KEYS (pcPosition is covered by the position field).
     *
     * @return list<string>
     */
    public static function nativeKeys(): array
    {
        return array_values(array_unique(array_merge(...array_values(self::VARIANT_KEYS))));
    }

    /**
     * @return list<string>
     */
    public static function keysForVariant(string $variant): array
    {
        return self::VARIANT_KEYS[$variant] ?? [];
    }

    /**
     * Column of the native field in tl_content: pcWidth → ratingstar_pc_width.
     */
    public static function column(string $key): string
    {
        return 'ratingstar_'.strtolower((string) preg_replace('/[A-Z]/', '_$0', $key));
    }

    /**
     * Attribute read by seal.js: pcWidth → data-pc-width.
     */
    public static function attribute(string $key): string
    {
        return 'data-'.strtolower((string) preg_replace('/[A-Z]/', '-$0', $key));
    }

    /**
     * Turns [option key => raw value] into data attributes. Empty values mean
     * "default from the portal" and are skipped, as are unknown keys and
     * values that seal.js would ignore anyway.
     *
     * @param array<string, mixed> $values
     *
     * @return array<string, string>
     */
    public static function toAttributes(array $values, bool $isGroup): array
    {
        $attributes = [];

        foreach ($values as $key => $value) {
            if (!isset(self::DEFINITIONS[$key]) || (!$isGroup && \in_array($key, self::GROUP_ONLY, true))) {
                continue;
            }

            $normalized = self::normalize($key, $value);

            if (null !== $normalized) {
                $attributes[self::attribute($key)] = $normalized;
            }
        }

        return $attributes;
    }

    /**
     * Returns the attribute value for one option, or null for "no attribute".
     */
    public static function normalize(string $key, mixed $value): string|null
    {
        $definition = self::DEFINITIONS[$key] ?? null;

        if (null === $definition || null === $value) {
            return null;
        }

        if (\is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        if (!\is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ('' === $value) {
            return null;
        }

        switch ($definition['type']) {
            case self::TYPE_BOOL:
                return match (strtolower($value)) {
                    '1', 'true' => '1',
                    '0', 'false' => '0',
                    default => null,
                };

            case self::TYPE_INT:
                return 1 === preg_match('/^\d{1,4}$/', $value) ? (string) (int) $value : null;

            case self::TYPE_ENUM:
                $allowed = [...$definition['values'] ?? [], ...$definition['accept'] ?? []];

                return \in_array($value, $allowed, true) ? $value : null;

            case self::TYPE_COLOR:
                $hex = ltrim($value, '#');

                if (1 === preg_match('/^[0-9a-fA-F]{3}$/', $hex)) {
                    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                }

                return 1 === preg_match('/^[0-9a-fA-F]{6}$/', $hex) ? '#'.strtolower($hex) : null;
        }

        return null;
    }

    /**
     * Splits a raw attribute string as copied from the portal's code
     * generator (data-size="240" data-pc-color='weiss' data-car-count=4)
     * into [attribute name => value]. Only data-* names are accepted;
     * anything else (class, onclick, …) is dropped, as are the reserved
     * attributes. The values are escaped on output.
     *
     * @return array<string, string>
     */
    public static function parseAttributeString(string $raw): array
    {
        // The back end may store the quotes and "=" as entities.
        $raw = trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ('' === $raw) {
            return [];
        }

        preg_match_all(
            '/(?<![\w-])(data-[a-z][a-z0-9-]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+))/i',
            $raw,
            $matches,
            PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL,
        );

        $attributes = [];

        foreach ($matches as $match) {
            $attributes[$match[1]] = $match[2] ?? $match[3] ?? $match[4] ?? '';
        }

        return self::filterAttributes($attributes);
    }

    /**
     * Keeps well-formed, non-reserved data-* attributes with scalar values.
     *
     * @param array<array-key, mixed> $attributes
     *
     * @return array<string, string>
     */
    public static function filterAttributes(array $attributes): array
    {
        $filtered = [];

        foreach ($attributes as $name => $value) {
            $name = strtolower(trim((string) $name));

            if (1 !== preg_match('/^data-[a-z][a-z0-9-]*$/', $name) || \in_array($name, self::RESERVED_ATTRIBUTES, true)) {
                continue;
            }

            if (\is_bool($value)) {
                $value = $value ? '1' : '0';
            }

            if (null === $value || !\is_scalar($value)) {
                continue;
            }

            $filtered[$name] = (string) $value;
        }

        return $filtered;
    }
}
