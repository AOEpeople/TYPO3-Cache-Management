<?php

use Aoe\Cachemgm\Hooks\TypoScriptFrontendHook;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['procInstructions']['cachemgm'] = [
    'key' => 'tx_cachemgm_recache',
    'value' => 'Re-cache pages'
];
