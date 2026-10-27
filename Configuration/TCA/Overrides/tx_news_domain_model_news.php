<?php

defined('TYPO3') or die();

// Replaces the EXT:news teaser for its "news_content_elements" add-on below the inline field
$GLOBALS['TCA']['tx_news_domain_model_news']['columns']['content_elements']['config']['customControls']['ce'] = [
    'userFunc' => \Woit\SiNsContentElements\Form\PageModuleControl::class . '->render',
];
