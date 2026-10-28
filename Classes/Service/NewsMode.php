<?php

declare(strict_types=1);

namespace Woit\SiNsContentElements\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

/**
 * "News mode" of the page module: the news record whose content elements are shown.
 * Only active while the URL contains ?tx_sinscontentelements[news]=<uid>. The page module uses its own URL
 * as returnUrl for edit and new content links, so the mode survives editing; opening the page via the
 * page tree shows the regular page module.
 */
final class NewsMode
{
    public const PARAMETER = 'tx_sinscontentelements';
    public const NEWS_TABLE = 'tx_news_domain_model_news';

    /** @var array<string, array|null> news record per "newsUid:pageId" (runtime cache) */
    private array $news = [];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly UriBuilder $uriBuilder,
    ) {}

    public function buildPageModuleUri(int $pageId, int $newsUid): string
    {
        return (string)$this->uriBuilder->buildUriFromRoute('web_layout', [
            'id' => $pageId,
            self::PARAMETER => ['news' => $newsUid],
        ]);
    }

    /**
     * Edit form of the news record; closing it leads to the list module of the news folder.
     */
    public function buildNewsEditUri(array $news): string
    {
        return (string)$this->uriBuilder->buildUriFromRoute('record_edit', [
            'edit' => [self::NEWS_TABLE => [(int)$news['uid'] => 'edit']],
            'returnUrl' => (string)$this->uriBuilder->buildUriFromRoute('web_list', ['id' => (int)$news['pid']]),
        ]);
    }

    /**
     * News record (default language) whose content elements are shown on this page, or null.
     * Read from the request, or from its returnUrl (new content element wizard opened from the page module).
     */
    public function getActiveNews(?ServerRequestInterface $request, int $pageId): ?array
    {
        if ($request === null || $pageId <= 0) {
            return null;
        }
        $queryParams = $request->getQueryParams();
        $newsUid = (int)($queryParams[self::PARAMETER]['news'] ?? 0);
        if ($newsUid === 0 && is_string($queryParams['returnUrl'] ?? null)) {
            parse_str((string)parse_url($queryParams['returnUrl'], PHP_URL_QUERY), $returnUrlParams);
            $newsUid = (int)($returnUrlParams[self::PARAMETER]['news'] ?? 0);
        }
        if ($newsUid <= 0) {
            return null;
        }
        return $this->news[$newsUid . ':' . $pageId] ??= $this->findNews($newsUid, $pageId);
    }

    /**
     * Uid of the news record plus the uids of its translations: translated content elements
     * may point to the translated news record (tt_content.tx_news_related_news).
     *
     * @return int[]
     */
    public function getNewsUidsWithTranslations(int $newsUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::NEWS_TABLE);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());
        $translations = $queryBuilder
            ->select('uid')
            ->from(self::NEWS_TABLE)
            ->where($queryBuilder->expr()->eq('l10n_parent', $queryBuilder->createNamedParameter($newsUid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchFirstColumn();
        return array_merge([$newsUid], array_map(intval(...), $translations));
    }

    private function findNews(int $newsUid, int $pageId): ?array
    {
        $news = BackendUtility::getRecord(self::NEWS_TABLE, $newsUid, 'uid,pid,title,sys_language_uid');
        if (!is_array($news) || (int)$news['pid'] !== $pageId || (int)$news['sys_language_uid'] !== 0) {
            return null;
        }
        return $news;
    }
}
