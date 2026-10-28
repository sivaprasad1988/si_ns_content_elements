<?php

use TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordOverrideValues;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;
use Woit\SiNsContentElements\Form\FormDataProvider\NewsContentColPos;

defined('TYPO3') or die();

// After all default values are set, before the colPos items are resolved
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][NewsContentColPos::class] = [
    'depends' => [
        DatabaseRecordOverrideValues::class,
    ],
    'before' => [
        TcaSelectItems::class,
    ],
];
