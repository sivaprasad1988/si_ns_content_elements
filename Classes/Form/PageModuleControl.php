<?php

declare(strict_types=1);

namespace Woit\SiNsContentElements\Form;

use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use Woit\SiNsContentElements\Service\NewsMode;

/**
 * TCA customControls of tx_news_domain_model_news.content_elements: link "Edit content in page module".
 * Not shown for news records which are not saved yet.
 */
final class PageModuleControl
{
    public function __construct(
        private readonly IconFactory $iconFactory,
        private readonly NewsMode $newsMode,
    ) {}

    public function render(array $params): string
    {
        $row = $params['row'] ?? [];
        if (!is_numeric($row['uid'] ?? null)) {
            return '';
        }
        // FormEngine delivers relation fields as arrays
        $l10nParent = $row['l10n_parent'] ?? 0;
        $l10nParent = (int)(is_array($l10nParent) ? ($l10nParent[0] ?? 0) : $l10nParent);
        $newsUid = $l10nParent > 0 ? $l10nParent : (int)$row['uid'];

        return sprintf(
            '<a href="%s" class="btn btn-default">%s %s</a>',
            htmlspecialchars($this->newsMode->buildPageModuleUri((int)$row['pid'], $newsUid)),
            $this->iconFactory->getIcon('module-page', IconSize::SMALL),
            htmlspecialchars($this->getLanguageService()->sL('LLL:EXT:si_ns_content_elements/Resources/Private/Language/locallang_be.xlf:button.editInPageModule'))
        );
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
