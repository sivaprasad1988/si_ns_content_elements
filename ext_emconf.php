<?php

/**
 * Metadata for the TYPO3 Extension Repository (TER).
 * TYPO3 itself (Composer mode) reads composer.json; this file is kept for TER and tailor.
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'News content elements in the page module',
    'description' => 'Edit the content elements of an EXT:news record in the page module instead of the inline field of the news record. Composer mode only.',
    'category' => 'be',
    'state' => 'beta',
    'author' => 'Siva',
    'author_email' => 'siva@webofficeit.com',
    'author_company' => '',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.99.99',
            'php' => '8.2.0-8.5.99',
            'news' => '14.1.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
