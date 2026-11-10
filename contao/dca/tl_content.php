<?php

declare(strict_types=1);

use RatingStar\ContaoSealBundle\Controller\ContentElement\SealController;
use RatingStar\ContaoSealBundle\Seal\SealOptions;
use RatingStar\ContaoSealBundle\Seal\SealVariants;

/*
 * "RatingStar seal" content element. Every embed point offers a slug and
 * group override plus the variant and position choice.
 *
 * The per-embed options are grouped per variant via sub-palettes of the
 * variant select. Their basic setting is always "Default (portal)" = no
 * attribute: booleans are three-state selects, never checkboxes, and no
 * default is written into the markup — otherwise the website would overrule
 * later changes in the RatingStar portal. The fields are generated from
 * SealOptions, the mirror of seal.js' EMBED_OVERRIDES.
 */

$GLOBALS['TL_DCA']['tl_content']['palettes'][SealController::TYPE] =
    '{type_legend},type;'
    .'{ratingstar_legend},ratingstar_variant,ratingstar_slug,ratingstar_group,ratingstar_data_attributes;'
    .'{template_legend:hide},customTpl;'
    .'{protected_legend:hide},protected;'
    .'{expert_legend:hide},cssID;'
    .'{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'ratingstar_variant';

foreach (SealVariants::KEYS as $ratingstarVariant) {
    $ratingstarFields = array_map(SealOptions::column(...), SealOptions::keysForVariant($ratingstarVariant));

    if (SealVariants::hasPosition($ratingstarVariant)) {
        array_unshift($ratingstarFields, 'ratingstar_position');
    }

    // A static image exists for these variants only.
    if (null !== SealVariants::staticSvgVariant($ratingstarVariant)) {
        $ratingstarFields[] = 'ratingstar_static';
    }

    $GLOBALS['TL_DCA']['tl_content']['subpalettes']['ratingstar_variant_'.SealVariants::toStorage($ratingstarVariant)] = implode(',', $ratingstarFields);
}

$GLOBALS['TL_DCA']['tl_content']['fields']['ratingstar_variant'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => SealVariants::storageValues(),
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['ratingstar_variant_options'],
    'eval' => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 32, 'default' => SealVariants::toStorage(SealVariants::DEFAULT)],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['ratingstar_slug'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 128, 'tl_class' => 'w50 clr'],
    'sql' => ['type' => 'string', 'length' => 128, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['ratingstar_group'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 128, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 128, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['ratingstar_position'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => SealVariants::POSITIONS,
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['ratingstar_position_options'],
    'eval' => [
        'includeBlankOption' => true,
        'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_content']['ratingstar_default_option'],
        'tl_class' => 'w50',
    ],
    'sql' => ['type' => 'string', 'length' => 16, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['ratingstar_static'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 clr'],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['ratingstar_data_attributes'] = [
    'exclude' => true,
    'inputType' => 'textarea',
    // Stored as typed: the quotes and "=" of the attributes must survive.
    'eval' => ['decodeEntities' => true, 'style' => 'height:60px', 'tl_class' => 'clr'],
    'sql' => ['type' => 'text', 'notnull' => false],
];

foreach (SealOptions::nativeKeys() as $ratingstarKey) {
    $ratingstarColumn = SealOptions::column($ratingstarKey);
    $ratingstarDefinition = SealOptions::DEFINITIONS[$ratingstarKey];

    $GLOBALS['TL_DCA']['tl_content']['fields'][$ratingstarColumn] = match ($ratingstarDefinition['type']) {
        SealOptions::TYPE_BOOL => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['1', '0'],
            'reference' => &$GLOBALS['TL_LANG']['tl_content']['ratingstar_bool_options'],
            'eval' => [
                'includeBlankOption' => true,
                'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_content']['ratingstar_default_option'],
                'tl_class' => 'w50',
            ],
            'sql' => ['type' => 'string', 'length' => 1, 'default' => ''],
        ],
        SealOptions::TYPE_ENUM => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => $ratingstarDefinition['values'],
            'reference' => &$GLOBALS['TL_LANG']['tl_content'][$ratingstarColumn.'_options'],
            'eval' => [
                'includeBlankOption' => true,
                'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_content']['ratingstar_default_option'],
                'tl_class' => 'w50',
            ],
            'sql' => ['type' => 'string', 'length' => 16, 'default' => ''],
        ],
        // Stored as plain hex digits like every Contao colour field; one
        // character more is accepted so that a value pasted with "#" passes.
        SealOptions::TYPE_COLOR => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 7, 'colorpicker' => true, 'isHexColor' => true, 'decodeEntities' => true, 'tl_class' => 'w25 wizard'],
            'sql' => ['type' => 'string', 'length' => 7, 'default' => ''],
        ],
        // Numbers: seal.js clamps into the valid range (see the field description).
        default => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'natural', 'maxlength' => 3, 'tl_class' => 'w25'],
            'sql' => ['type' => 'string', 'length' => 3, 'default' => ''],
        ],
    };
}

unset($ratingstarVariant, $ratingstarFields, $ratingstarKey, $ratingstarColumn, $ratingstarDefinition);
