<?php

declare(strict_types=1);

namespace Woit\SiNsContentElements\EventListener;

use TYPO3\CMS\Backend\Context\PageContext;
use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use Woit\SiNsContentElements\Service\NewsMode;

/**
 * Page module in news mode: shows which news record is edited.
 */
#[AsEventListener('woit/si-ns-content-elements/news-mode-header')]
final class AddNewsModeHeader
{
    public function __construct(
        private readonly NewsMode $newsMode,
        private readonly UriBuilder $uriBuilder,
        private readonly ViewFactoryInterface $viewFactory,
        private readonly PageRenderer $pageRenderer,
    ) {}

    public function __invoke(ModifyPageLayoutContentEvent $event): void
    {
        $request = $event->getRequest();
        $pageContext = $request->getAttribute('pageContext');
        if (!$pageContext instanceof PageContext || $pageContext->pageId <= 0) {
            return;
        }
        $pageId = $pageContext->pageId;
        $news = $this->newsMode->getActiveNews($request, $pageId);
        if ($news === null) {
            return;
        }

        $view = $this->viewFactory->create(new ViewFactoryData(
            templateRootPaths: ['EXT:si_ns_content_elements/Resources/Private/Templates'],
            request: $request,
        ));
        $view->assignMultiple([
            'news' => $news,
            'editNewsUrl' => $this->newsMode->buildNewsEditUri($news),
            'leaveUrl' => (string)$this->uriBuilder->buildUriFromRoute('web_layout', ['id' => $pageId]),
        ]);
        $event->addHeaderContent($view->render('NewsModeHeader'));
        $this->pageRenderer->loadJavaScriptModule('@woit/si-ns-content-elements/keep-news-mode.js');
    }
}
