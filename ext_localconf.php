<?php

use Aoe\Cachemgm\Hooks\TypoScriptFrontendHook;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;

defined('TYPO3') or die();

if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
    && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()
) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache']['tx_cachemgm'] =
        TypoScriptFrontendHook::class . '->fe_headerNoCache';
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['procInstructions']['cachemgm'] = [
    'key' => 'tx_cachemgm_recache',
    'value' => 'Re-cache pages'
];
