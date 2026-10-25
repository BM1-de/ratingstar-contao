<?php

declare(strict_types=1);

use RatingStar\ContaoSealBundle\Seal\SealVariants;

/*
 * "RatingStar-Siegel" content element (rs_seal). Every embed point offers both
 * a slug override and a variant/position choice (CONTRACT.md §13). The position
 * is only relevant for the profile card, the colour scheme for the rating bar,
 * overall rating, quote, carousel and review wall — both via sub-palettes.
 */

$GLOBALS['TL_DCA']['tl_content']['palettes']['rs_seal'] =
    '{type_legend},type;'
    .'{rs_seal_legend},rs_variant,rs_slug;'
    .'{template_legend:hide},customTpl;'
    .'{protected_legend:hide},protected;'
    .'{expert_legend:hide},guests,cssID;'
    .'{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'rs_variant';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['rs_variant_profile-card'] = 'rs_position';

foreach (SealVariants::THEME_VARIANTS as $rsThemeVariant) {
    $GLOBALS['TL_DCA']['tl_content']['subpalettes']['rs_variant_'.$rsThemeVariant] = 'rs_theme';
}

// Build variant options + labels from the canonical VARIANT_META mirror.
$rsVariantOptions = SealVariants::keys();
$rsVariantReference = [];

foreach (SealVariants::META as $rsKey => $rsMeta) {
    $rsVariantReference[$rsKey] = $rsMeta['label'];
}

$GLOBALS['TL_DCA']['tl_content']['fields']['rs_variant'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => $rsVariantOptions,
    'reference' => $rsVariantReference,
    'eval' => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 32, 'default' => SealVariants::DEFAULT],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['rs_position'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => SealVariants::POSITIONS,
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['rs_position_options'],
    'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_content']['rs_from_settings']],
    'sql' => ['type' => 'string', 'length' => 16, 'default' => SealVariants::DEFAULT_POSITION],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['rs_theme'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => SealVariants::THEMES,
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['rs_theme_options'],
    'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_content']['rs_from_settings']],
    'sql' => ['type' => 'string', 'length' => 8, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['rs_slug'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 128, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 128, 'default' => ''],
];
