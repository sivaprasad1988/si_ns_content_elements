<?php

declare(strict_types=1);

namespace Woit\SiNsContentElements\EventListener;

use TYPO3\CMS\Backend\Context\PageContext;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ComponentFactory;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use Woit\SiNsContentElements\Service\NewsMode;

/**
 * Page module in news mode: doc header button "Back to news" (first button on the left).
 */
#[AsEventListener('woit/si-ns-content-elements/back-to-news-button')]
final class AddBackToNewsButton
{
    public function __construct(
        private readonly ComponentFactory $componentFactory,
        private readonly IconFactory $iconFactory,
        private readonly NewsMode $newsMode,
    ) {}

    public function __invoke(ModifyButtonBarEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->getAttribute('route');
        $pageContext = $request->getAttribute('pageContext');
        if (!$route instanceof Route || $route->getOption('_identifier') !== 'web_layout'
            || !$pageContext instanceof PageContext || $pageContext->pageId <= 0
        ) {
            return;
        }
        $news = $this->newsMode->getActiveNews($request, $pageContext->pageId);
        if ($news === null) {
            return;
        }
        $button = $this->componentFactory->createLinkButton()
            ->setHref($this->newsMode->buildNewsEditUri($news))
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:si_ns_content_elements/Resources/Private/Language/locallang_be.xlf:button.backToNews'))
            ->setIcon($this->iconFactory->getIcon('actions-view-go-back', IconSize::SMALL))
            ->setShowLabelText(true);

        $buttons = $event->getButtons();
        $buttons[ButtonBar::BUTTON_POSITION_LEFT][0] ??= [];
        array_unshift($buttons[ButtonBar::BUTTON_POSITION_LEFT][0], $button);
        ksort($buttons[ButtonBar::BUTTON_POSITION_LEFT]);
        $event->setButtons($buttons);
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
