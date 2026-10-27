<?php

declare(strict_types=1);

namespace Woit\SiNsContentElements\EventListener;

use GeorgRinger\News\Event\Listener\ModifyDatabaseQueryForContentEventListener;
use TYPO3\CMS\Backend\View\Event\ModifyDatabaseQueryForContentEvent;
use TYPO3\CMS\Core\Database\Connection;
use Woit\SiNsContentElements\Service\NewsMode;

/**
 * Decorates the EXT:news listener (see Services.yaml).
 * News mode: only the content elements of the active news record. Otherwise: EXT:news behaviour.
 */
final class FilterPageModuleContent
{
    public function __construct(
        private readonly ModifyDatabaseQueryForContentEventListener $inner,
        private readonly NewsMode $newsMode,
    ) {}

    public function modify(ModifyDatabaseQueryForContentEvent $event): void
    {
        $news = $event->getTable() === 'tt_content' && $event->getPageId() > 0
            // The event carries no request
            ? $this->newsMode->getActiveNews($GLOBALS['TYPO3_REQUEST'] ?? null, $event->getPageId())
            : null;
        if ($news === null) {
            $this->inner->modify($event);
            return;
        }
        $queryBuilder = $event->getQueryBuilder();
        $queryBuilder->andWhere(
            $queryBuilder->expr()->in(
                'tx_news_related_news',
                $queryBuilder->createNamedParameter(
                    $this->newsMode->getNewsUidsWithTranslations((int)$news['uid']),
                    Connection::PARAM_INT_ARRAY
                )
            )
        );
    }
}
