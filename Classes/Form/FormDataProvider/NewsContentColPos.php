<?php

declare(strict_types=1);

namespace Woit\SiNsContentElements\Form\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Backend\View\BackendLayoutView;
use Woit\SiNsContentElements\Service\NewsMode;

/**
 * Content elements of news records: colPos must be a column of the backend layout of the news folder.
 * EXT:news creates them with colPos 0 ("Normal"), which leads to 'Invalid value ("0")' in the colPos
 * field if the backend layout has no column 0.
 *
 * Column (page TSconfig of the news folder): tx_sinscontentelements.colPos = 10
 * - New records in the inline field of the news record: the configured column.
 * - Other records: the configured column, only if their colPos is not part of the backend layout.
 * Nothing is changed without this setting, or if the configured column is not part of the backend layout.
 */
final class NewsContentColPos implements FormDataProviderInterface
{
    public function __construct(private readonly BackendLayoutView $backendLayoutView) {}

    public function addData(array $result): array
    {
        if ($result['tableName'] !== 'tt_content' || !$this->isNewsContent($result)) {
            return $result;
        }
        $configuredColPos = $result['pageTsConfig'][NewsMode::PARAMETER . '.']['colPos'] ?? null;
        $pageId = (int)($result['effectivePid'] ?? 0);
        if (!is_numeric($configuredColPos) || $pageId <= 0) {
            return $result;
        }
        $configuredColPos = (int)$configuredColPos;
        $columns = array_map(intval(...), $this->backendLayoutView->getBackendLayoutForPage($pageId)->getColumnPositionNumbers());
        if (!in_array($configuredColPos, $columns, true)) {
            return $result;
        }
        // New records of the page module (wizard) keep the column the wizard was opened for
        if (($result['command'] === 'new' && !empty($result['isInlineChild']))
            || !in_array((int)($result['databaseRow']['colPos'] ?? 0), $columns, true)
        ) {
            $result['databaseRow']['colPos'] = $configuredColPos;
        }
        return $result;
    }

    /**
     * Inline child of the news field "content_elements", or a content element related to a news record
     */
    private function isNewsContent(array $result): bool
    {
        if (!empty($result['isInlineChild'])) {
            return $result['inlineParentTableName'] === NewsMode::NEWS_TABLE
                && $result['inlineParentFieldName'] === 'content_elements';
        }
        return (int)($result['databaseRow']['tx_news_related_news'] ?? 0) > 0;
    }
}
