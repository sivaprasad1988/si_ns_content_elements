<?php

declare(strict_types=1);

namespace Woit\SiNsContentElements\EventListener;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use Woit\SiNsContentElements\Service\NewsMode;

/**
 * Record edit form of a single, saved news record: button "Edit content in page module".
 * Registered in Services.yaml.
 */
final class AddPageModuleButton
{
    public function __construct(
        private readonly IconFactory $iconFactory,
        private readonly NewsMode $newsMode,
    ) {}

    public function __invoke(ModifyButtonBarEvent $event): void
    {
        // The event carries no request
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return;
        }
        $route = $request->getAttribute('route');
        if (!$route instanceof Route || $route->getOption('_identifier') !== 'record_edit') {
            return;
        }
        $editConfiguration = $request->getQueryParams()['edit'] ?? null;
        $newsConfiguration = $editConfiguration[NewsMode::NEWS_TABLE] ?? null;
        if (!is_array($editConfiguration) || count($editConfiguration) !== 1
            || !is_array($newsConfiguration) || count($newsConfiguration) !== 1
        ) {
            return;
        }
        $uid = array_key_first($newsConfiguration);
        if (!is_numeric($uid) || $newsConfiguration[$uid] !== 'edit') {
            return;
        }
        $news = BackendUtility::getRecord(NewsMode::NEWS_TABLE, (int)$uid, 'uid,pid,l10n_parent');
        if (!is_array($news)) {
            return;
        }
        // Content elements are managed on the record in the default language
        $newsUid = (int)$news['l10n_parent'] > 0 ? (int)$news['l10n_parent'] : (int)$news['uid'];

        $button = $event->getButtonBar()->makeLinkButton()
            ->setHref($this->newsMode->buildPageModuleUri((int)$news['pid'], $newsUid))
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:si_ns_content_elements/Resources/Private/Language/locallang_be.xlf:button.editInPageModule'))
            ->setIcon($this->iconFactory->getIcon('module-page', Icon::SIZE_SMALL))
            ->setShowLabelText(true);

        $buttons = $event->getButtons();
        $buttons[ButtonBar::BUTTON_POSITION_LEFT][5][] = $button;
        $event->setButtons($buttons);
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
