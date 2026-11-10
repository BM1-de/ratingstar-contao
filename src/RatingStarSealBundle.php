<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle;

use RatingStar\ContaoSealBundle\DependencyInjection\RatingStarSealExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * RatingStar seal bundle for Contao.
 */
class RatingStarSealBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    /**
     * Returned explicitly: the configuration key is "ratingstar_seal", like
     * every other name of the bundle, not the "rating_star_seal" Symfony
     * would derive from the class name.
     */
    public function getContainerExtension(): RatingStarSealExtension
    {
        return new RatingStarSealExtension();
    }
}
