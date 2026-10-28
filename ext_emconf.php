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
            'typo3' => '12.4.0-13.4.99',
            'php' => '8.1.0-8.4.99',
            'news' => '11.0.0-13.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
