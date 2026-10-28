<?php

declare(strict_types=1);

namespace Woit\SiNsContentElements\EventListener;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use Woit\SiNsContentElements\Service\NewsMode;

/**
 * Page module in news mode: doc header button "Back to news" (first button on the left).
 * Registered in Services.yaml.
 */
final class AddBackToNewsButton
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
        $pageId = (int)($request->getQueryParams()['id'] ?? 0);
        if (!$route instanceof Route || $route->getOption('_identifier') !== 'web_layout' || $pageId <= 0) {
            return;
        }
        $news = $this->newsMode->getActiveNews($request, $pageId);
        if ($news === null) {
            return;
        }
        $button = $event->getButtonBar()->makeLinkButton()
            ->setHref($this->newsMode->buildNewsEditUri($news))
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:si_ns_content_elements/Resources/Private/Language/locallang_be.xlf:button.backToNews'))
            ->setIcon($this->iconFactory->getIcon('actions-view-go-back', Icon::SIZE_SMALL))
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
