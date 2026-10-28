<?php

declare(strict_types=1);

namespace Woit\SiNsContentElements\EventListener;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Controller\Event\ModifyNewContentElementWizardItemsEvent;
use Woit\SiNsContentElements\Service\NewsMode;

/**
 * News mode: content elements created via the new content element wizard belong to the active news record.
 * The default values end up in the DataHandler data map (defVals) when the record is saved.
 * Registered in Services.yaml.
 */
final class RelateNewContentToNews
{
    public function __construct(private readonly NewsMode $newsMode) {}

    public function __invoke(ModifyNewContentElementWizardItemsEvent $event): void
    {
        // ModifyNewContentElementWizardItemsEvent::getRequest() does not exist in TYPO3 12
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $news = $this->newsMode->getActiveNews(
            $request instanceof ServerRequestInterface ? $request : null,
            (int)($event->getPageInfo()['uid'] ?? 0)
        );
        if ($news === null) {
            return;
        }
        $wizardItems = $event->getWizardItems();
        foreach ($wizardItems as $identifier => $item) {
            // Group headers carry no record defaults
            if (isset($item['header'])) {
                continue;
            }
            // TYPO3 13: "defaultValues", TYPO3 12: "tt_content_defValues"
            $wizardItems[$identifier]['defaultValues']['tx_news_related_news'] = (int)$news['uid'];
            $wizardItems[$identifier]['tt_content_defValues']['tx_news_related_news'] = (int)$news['uid'];
        }
        $event->setWizardItems($wizardItems);
    }
}
