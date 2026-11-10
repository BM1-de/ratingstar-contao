<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use RatingStar\ContaoSealBundle\JsonLd\JsonLdProvider;
use RatingStar\ContaoSealBundle\Seal\SealSettingsResolver;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Prints the server-side LocalBusiness JSON-LD (Google stars) into the
 * <head> of every regular page of a website that has it switched on — exactly
 * one block per page. The seals of such a page carry data-no-richsnippet, so
 * seal.js does not add a second block.
 */
class JsonLdListener
{
    /**
     * Fixed key: registering the block twice still prints it once.
     */
    public const HEAD_KEY = 'ratingstar_seal_jsonld';

    public function __construct(
        private readonly SealSettingsResolver $resolver,
        private readonly JsonLdProvider $provider,
    ) {
    }

    /**
     * Pages rendered by the classic page controller.
     */
    #[AsHook('generatePage')]
    public function onGeneratePage(PageModel $page, LayoutModel $layout, PageRegular $pageRegular): void
    {
        $this->addBlock($page);
    }

    /**
     * Pages with a Twig layout (Contao 5.7+). The event class is referenced
     * by name only, so the listener stays inert on older versions.
     */
    #[AsEventListener('Contao\CoreBundle\Event\LayoutEvent')]
    public function onLayout(object $event): void
    {
        if (!method_exists($event, 'getPage')) {
            return;
        }

        $page = $event->getPage();

        if ($page instanceof PageModel) {
            $this->addBlock($page);
        }
    }

    private function addBlock(PageModel $page): void
    {
        // Not on error pages (403/404/503).
        if (str_starts_with((string) $page->type, 'error_')) {
            return;
        }

        $json = $this->provider->get($this->resolver->forPage($page));

        if (null === $json) {
            return;
        }

        $GLOBALS['TL_HEAD'][self::HEAD_KEY] = '<script type="application/ld+json">'.$json.'</script>';
    }
}
