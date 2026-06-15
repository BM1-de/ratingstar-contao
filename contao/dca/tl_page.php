<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use RatingStar\ContaoSealBundle\Seal\SealSettings;

/*
 * Per-website RatingStar settings (CONTRACT.md §1/§2). Stored on the root page
 * so they are editable per site and multi-domain aware:
 *   - rs_slug:       the /t/<slug> handle, drives seal.js and the slug endpoints
 *   - rs_apiKey:     rs_live_ + 40 alphanumerics, rename-safe key endpoints
 *   - rs_baseOrigin: optional per-site override of the global base origin;
 *                    empty = use the bundle default (rating_star_seal.base_origin)
 */

PaletteManipulator::create()
    ->addLegend('ratingstar_legend', 'global_legend', PaletteManipulator::POSITION_AFTER)
    ->addField(['rs_slug', 'rs_apiKey', 'rs_baseOrigin'], 'ratingstar_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page')
    ->applyToPalette('rootfallback', 'tl_page')
;

$GLOBALS['TL_DCA']['tl_page']['fields']['rs_slug'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 128, 'tl_class' => 'w50', 'trailingSlash' => false],
    'sql' => ['type' => 'string', 'length' => 128, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['rs_apiKey'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 48, 'tl_class' => 'w50', 'placeholder' => 'rs_live_…'],
    'save_callback' => [
        static function ($value) {
            $value = trim((string) $value);

            if ('' !== $value && !SealSettings::isValidApiKey($value)) {
                throw new \InvalidArgumentException($GLOBALS['TL_LANG']['tl_page']['rs_apiKey_invalid'] ?? 'Invalid RatingStar API key.');
            }

            return $value;
        },
    ],
    'sql' => ['type' => 'string', 'length' => 48, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['rs_baseOrigin'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50', 'placeholder' => 'https://ratingstar.de'],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];
