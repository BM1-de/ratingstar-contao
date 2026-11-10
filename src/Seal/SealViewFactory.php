<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Seal;

/**
 * Builds the data for the seal template — shared by every embed point (the
 * content element and the ratingstar_seal() Twig function), so that all of
 * them offer the same options: slug, group, variant, position, static image,
 * per-embed options and extra data attributes.
 *
 * The official markup is
 *
 *   <div class="rs-seal" data-slug="…" data-variant="…" […]></div>
 *
 * plus ONE seal.js per page. Everything else — HTML/CSS, the loader token,
 * the tracking pixel, the tier degrade and the client-side JSON-LD — is the
 * job of seal.js and never rebuilt here.
 */
class SealViewFactory
{
    /**
     * @param array{
     *     slug?: string|null,
     *     group?: string|null,
     *     position?: string|null,
     *     static?: bool,
     *     options?: array<string, mixed>,
     *     attributes?: array<array-key, mixed>|string|null
     * } $config
     *
     * @return array{
     *     variant: string,
     *     subject: SealSubject,
     *     origin: string,
     *     script_url: string,
     *     profile_url: string,
     *     position: string,
     *     attributes: array<string, string>,
     *     static: array{src: string, width: int}|null
     * }|null Null if neither a slug nor a group is known
     */
    public function create(SealSettings $settings, string $variant, array $config = []): array|null
    {
        // The Twig function passes whatever a template hands in.
        $text = static fn (string $key): string => \is_scalar($config[$key] ?? null) ? (string) $config[$key] : '';

        $variant = SealVariants::normalize($variant);
        $subject = $settings->subject($text('slug'), $text('group'));

        if (null === $subject) {
            return null;
        }

        $options = \is_array($config['options'] ?? null) ? $config['options'] : [];
        $position = SealVariants::hasPosition($variant) ? SealVariants::normalizePosition($text('position')) : '';

        $view = [
            'variant' => $variant,
            'subject' => $subject,
            'origin' => $settings->baseOrigin,
            'script_url' => $settings->sealJsUrl(),
            'profile_url' => $settings->url($subject->profilePath()),
            'position' => $position,
            'attributes' => [],
            'static' => null,
        ];

        // Static image instead of the script — only for the variants that
        // exist as a static SVG, never a silently different motif.
        $svgVariant = SealVariants::staticSvgVariant($variant);

        if (($config['static'] ?? false) && null !== $svgVariant) {
            $view['static'] = [
                'src' => $settings->url($subject->staticSvgPath($svgVariant)),
                'width' => $this->staticWidth($svgVariant, $options),
            ];

            return $view;
        }

        $attributes = [
            $subject->attributeName() => $subject->id,
            'data-variant' => $variant,
        ];

        if ('' !== $position) {
            $attributes['data-position'] = $position;
        }

        // Native options first; the raw attribute string of the expert field
        // overrides options of the same name, never the reserved attributes.
        $attributes += SealOptions::toAttributes($options, $subject->isGroup);

        $extra = $config['attributes'] ?? null;
        $extra = \is_array($extra) ? SealOptions::filterAttributes($extra) : SealOptions::parseAttributeString($text('attributes'));

        $attributes = array_replace($attributes, $extra);

        // One JSON-LD block per page: with the server-side block active,
        // seal.js must not inject its own rich snippet.
        if ($settings->isJsonLdActive()) {
            $attributes['data-no-richsnippet'] = '1';
        }

        $view['attributes'] = $attributes;

        return $view;
    }

    /**
     * Display width of the static image. The SVG itself is drawn much larger
     * (742 px for the banner seal), and without the script the portal
     * settings are unknown — so the size option of the element applies, or
     * the width seal.js itself falls back to without a configuration.
     *
     * @param array<string, mixed> $options
     */
    private function staticWidth(string $svgVariant, array $options): int
    {
        $key = 'card' === $svgVariant ? 'pcWidth' : 'size';
        $width = SealOptions::normalize($key, $options[$key] ?? null);

        if (null === $width) {
            return SealVariants::STATIC_FALLBACK_WIDTH[$svgVariant];
        }

        $definition = SealOptions::DEFINITIONS[$key];

        return max($definition['min'], min($definition['max'], (int) $width));
    }
}
