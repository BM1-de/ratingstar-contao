<?php

declare(strict_types=1);

use RatingStar\ContaoSealBundle\Maintenance\CachePurger;

// System maintenance: drop the cached RatingStar responses (JSON-LD), e.g.
// after a key rotation or a plan change.
$GLOBALS['TL_PURGE']['custom']['ratingstar_seal'] = [
    'callback' => [CachePurger::class, 'purge'],
];
