<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\EventListener\DataContainer;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\Message;
use RatingStar\ContaoSealBundle\Controller\ContentElement\SealController;
use RatingStar\ContaoSealBundle\JsonLd\JsonLdProvider;
use RatingStar\ContaoSealBundle\Seal\SealSettings;
use RatingStar\ContaoSealBundle\Seal\SealSettingsResolver;
use RatingStar\ContaoSealBundle\Seal\SealSubject;
use RatingStar\ContaoSealBundle\Verification\ProfileVerification;
use RatingStar\ContaoSealBundle\Verification\ProfileVerifier;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Back end behaviour of the RatingStar fields on the root page (tl_page) and
 * the content element (tl_content): format checks, the non-blocking slug
 * check and dropping the cache when the website settings change.
 */
class SealSettingsListener
{
    private const PAGE_FIELDS = [
        'ratingstar_slug',
        'ratingstar_group',
        'ratingstar_api_key',
        'ratingstar_base_origin',
        'ratingstar_jsonld',
    ];

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly SealSettingsResolver $resolver,
        private readonly ProfileVerifier $verifier,
        private readonly JsonLdProvider $jsonLdProvider,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * The key field takes rs_live_ + 40 alphanumerics, no free text.
     */
    #[AsCallback(table: 'tl_page', target: 'fields.ratingstar_api_key.save')]
    public function validateApiKey(mixed $value): string
    {
        $value = trim((string) $value);

        if ('' !== $value && !SealSettings::isValidApiKey($value)) {
            throw new \InvalidArgumentException($this->translator->trans('tl_page.ratingstar_api_key_invalid', [], 'contao_tl_page'));
        }

        return $value;
    }

    /**
     * Every URL is built as <origin><path>, so the value must be an origin
     * without a path.
     */
    #[AsCallback(table: 'tl_page', target: 'fields.ratingstar_base_origin.save')]
    public function validateBaseOrigin(mixed $value): string
    {
        $value = rtrim(trim((string) $value), '/');

        if ('' !== $value && !SealSettings::isValidOrigin($value)) {
            throw new \InvalidArgumentException($this->translator->trans('tl_page.ratingstar_base_origin_invalid', [], 'contao_tl_page'));
        }

        return $value;
    }

    /**
     * Root page: check a changed slug or group against the public profile
     * and drop the cached JSON-LD so that new settings apply at once.
     *
     * @param array<string, mixed> $values Changed values only
     *
     * @return array<string, mixed>
     */
    #[AsCallback(table: 'tl_page', target: 'config.onbeforesubmit')]
    public function onSubmitPage(array $values, DataContainer $dc): array
    {
        if ([] === array_intersect(self::PAGE_FIELDS, array_keys($values))) {
            return $values;
        }

        try {
            $this->jsonLdProvider->clear();

            $settings = $this->resolver->fromRow([...$dc->getCurrentRecord() ?? [], ...$values]);
            $originChanged = \array_key_exists('ratingstar_base_origin', $values);

            // Switched on without a usable key, the block would silently stay away.
            if ($settings->jsonLdEnabled && !$settings->isJsonLdActive() && (\array_key_exists('ratingstar_jsonld', $values) || \array_key_exists('ratingstar_api_key', $values))) {
                $this->framework->getAdapter(Message::class)->addInfo($this->translator->trans('tl_page.ratingstar_jsonld_needs_key', [], 'contao_tl_page'));
            }

            if ($originChanged || \array_key_exists('ratingstar_slug', $values)) {
                $this->check($settings->baseOrigin, $settings->slug, false);
            }

            if ($originChanged || \array_key_exists('ratingstar_group', $values)) {
                $this->check($settings->baseOrigin, $settings->group, true);
            }
        } catch (\Throwable) {
            // The check must never keep the record from being saved.
        }

        return $values;
    }

    /**
     * Content element: check a changed slug or group override.
     *
     * @param array<string, mixed> $values Changed values only
     *
     * @return array<string, mixed>
     */
    #[AsCallback(table: 'tl_content', target: 'config.onbeforesubmit')]
    public function onSubmitContent(array $values, DataContainer $dc): array
    {
        $checkSlug = \array_key_exists('ratingstar_slug', $values);
        $checkGroup = \array_key_exists('ratingstar_group', $values);

        if (!$checkSlug && !$checkGroup) {
            return $values;
        }

        try {
            $record = [...$dc->getCurrentRecord() ?? [], ...$values];

            if (SealController::TYPE !== ($record['type'] ?? null)) {
                return $values;
            }

            $this->framework->initialize();

            $element = $this->framework->getAdapter(ContentModel::class)->findByPk((int) $dc->id);
            $origin = $element instanceof ContentModel
                ? $this->resolver->forContentElement($element)->baseOrigin
                : $this->resolver->getDefaultBaseOrigin();

            if ($checkSlug) {
                $this->check($origin, (string) $values['ratingstar_slug'], false);
            }

            if ($checkGroup) {
                $this->check($origin, (string) $values['ratingstar_group'], true);
            }
        } catch (\Throwable) {
            // The check must never keep the record from being saved.
        }

        return $values;
    }

    /**
     * Runs the check and reports the outcome as a back end message. The
     * value is saved in every case.
     */
    private function check(string $origin, string $id, bool $isGroup): void
    {
        $id = trim($id);

        if ('' === $id) {
            return;
        }

        $result = $this->verifier->verify($origin, new SealSubject($id, $isGroup));
        $kind = $isGroup ? 'group' : 'slug';

        // The profile name is remote content and the messages are printed as HTML.
        $escape = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        $message = fn (string $key, string ...$arguments): string => $this->translator->trans(
            'tl_page.ratingstar_verify_'.$kind.'_'.$key,
            array_map($escape, $arguments),
            'contao_tl_page',
        );

        $messages = $this->framework->getAdapter(Message::class);

        match ($result->status) {
            ProfileVerification::FOUND => $messages->addConfirmation($message('found', (string) $result->name)),
            ProfileVerification::NOT_FOUND => $messages->addInfo($message('not_found', $result->url)),
            default => $messages->addInfo($message('unavailable', $result->url)),
        };
    }
}
