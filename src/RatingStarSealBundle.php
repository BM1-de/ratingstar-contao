<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * RatingStar seal bundle for Contao. The DI extension (RatingStarSealExtension,
 * alias "rating_star_seal") is auto-discovered by Symfony from the bundle name.
 */
class RatingStarSealBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
