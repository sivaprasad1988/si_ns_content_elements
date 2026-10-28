<?php

declare(strict_types=1);

namespace Woit\SiNsContentElements\EventListener;

use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use Woit\SiNsContentElements\Service\NewsMode;

/**
 * Page module in news mode: shows which news record is edited.
 * Registered in Services.yaml.
 */
final class AddNewsModeHeader
{
    public function __construct(
        private readonly NewsMode $newsMode,
        private readonly UriBuilder $uriBuilder,
        private readonly BackendViewFactory $backendViewFactory,
        private readonly PageRenderer $pageRenderer,
    ) {}

    public function __invoke(ModifyPageLayoutContentEvent $event): void
    {
        $request = $event->getRequest();
        $pageId = (int)($request->getQueryParams()['id'] ?? 0);
        if ($pageId <= 0) {
            return;
        }
        $news = $this->newsMode->getActiveNews($request, $pageId);
        if ($news === null) {
            return;
        }

        $view = $this->backendViewFactory->create($request, ['woit/si-ns-content-elements']);
        $view->assignMultiple([
            'news' => $news,
            'editNewsUrl' => $this->newsMode->buildNewsEditUri($news),
            'leaveUrl' => (string)$this->uriBuilder->buildUriFromRoute('web_layout', ['id' => $pageId]),
        ]);
        $event->addHeaderContent($view->render('NewsModeHeader'));
        $this->pageRenderer->loadJavaScriptModule('@woit/si-ns-content-elements/keep-news-mode.js');
    }
}
