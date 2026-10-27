<?php

declare(strict_types=1);

namespace Woit\SiNsContentElements\EventListener;

use TYPO3\CMS\Backend\Controller\Event\ModifyNewContentElementWizardItemsEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use Woit\SiNsContentElements\Service\NewsMode;

/**
 * News mode: content elements created via the new content element wizard belong to the active news record.
 * The default values end up in the DataHandler data map (defVals) when the record is saved.
 */
#[AsEventListener('woit/si-ns-content-elements/relate-new-content')]
final class RelateNewContentToNews
{
    public function __construct(private readonly NewsMode $newsMode) {}

    public function __invoke(ModifyNewContentElementWizardItemsEvent $event): void
    {
        $news = $this->newsMode->getActiveNews($event->getRequest(), (int)($event->getPageInfo()['uid'] ?? 0));
        if ($news === null) {
            return;
        }
        $wizardItems = $event->getWizardItems();
        foreach ($wizardItems as $identifier => $item) {
            // Group headers carry no record defaults
            if (isset($item['header'])) {
                continue;
            }
            $wizardItems[$identifier]['defaultValues']['tx_news_related_news'] = (int)$news['uid'];
        }
        $event->setWizardItems($wizardItems);
    }
}
