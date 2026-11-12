<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\System;

/*
 * RatingStar settings of a website, stored on its root page (editable per
 * website, multi-domain aware):
 *
 *   ratingstar_slug         profile slug of the branch (/t/<slug>)
 *   ratingstar_group        slug of a group (/g/<group>); shows the seal of
 *                           the whole group instead of a single branch
 *   ratingstar_api_key      rs_live_ + 40 alphanumerics, for the key-based
 *                           endpoints (server-side JSON-LD)
 *   ratingstar_jsonld       print the JSON-LD (Google stars)
 *   ratingstar_base_origin  optional override of the bundle's base origin
 *                           (ratingstar_seal.base_origin)
 *
 * Format checks, the slug check and the cache handling are callbacks of
 * RatingStar\ContaoSealBundle\EventListener\DataContainer\SealSettingsListener.
 */

PaletteManipulator::create()
    ->addLegend('ratingstar_legend', 'global_legend', PaletteManipulator::POSITION_AFTER)
    ->addField(
        ['ratingstar_slug', 'ratingstar_group', 'ratingstar_api_key', 'ratingstar_jsonld', 'ratingstar_base_origin'],
        'ratingstar_legend',
        PaletteManipulator::POSITION_APPEND,
    )
    ->applyToPalette('root', 'tl_page')
    ->applyToPalette('rootfallback', 'tl_page')
;

$GLOBALS['TL_DCA']['tl_page']['fields']['ratingstar_slug'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 128, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 128, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['ratingstar_group'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 128, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 128, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['ratingstar_api_key'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 48, 'tl_class' => 'w50', 'placeholder' => 'rs_live_…'],
    'sql' => ['type' => 'string', 'length' => 48, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['ratingstar_jsonld'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['ratingstar_base_origin'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => [
        'rgxp' => 'url',
        'maxlength' => 255,
        'trailingSlash' => false,
        'tl_class' => 'w50 clr',
        // The effective default comes from the bundle configuration.
        'placeholder' => System::getContainer()->getParameter('ratingstar_seal.base_origin'),
    ],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];
