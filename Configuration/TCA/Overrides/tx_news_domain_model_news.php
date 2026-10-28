<?php

defined('TYPO3') or die();

$customControls = &$GLOBALS['TCA']['tx_news_domain_model_news']['columns']['content_elements']['config']['customControls'];

// Link "Edit content in page module" below the inline field
$customControls['si_ns_content_elements'] = [
    'userFunc' => \Woit\SiNsContentElements\Form\PageModuleControl::class . '->render',
];

// The notice of EXT:news (control "ce") is kept, unless removed in the extension configuration
if ((bool)($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['si_ns_content_elements']['hideNewsNotice'] ?? false)) {
    unset($customControls['ce']);
}
unset($customControls);
