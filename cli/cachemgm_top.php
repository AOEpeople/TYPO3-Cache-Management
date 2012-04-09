<?php

if (!defined('TYPO3_cliMode'))	die('You cannot run this script directly!');
declare(ticks = 1);

 pcntl_signal(SIGTERM, "signal_handler");
 pcntl_signal(SIGINT, "signal_handler");

 $cliObj = t3lib_div::makeInstance('Tx_Cachemgm_Cli_CachemgmTop');
 
 
 function signal_handler($signal) {
      global  $cliObj;
      $cliObj->finalStatPrint();
      exit();
 }
    


if (isset($cliObj->cli_args['-h']) || isset($cliObj->cli_args['--help']))	{
	$cliObj->cli_validateArgs();
	$cliObj->cli_help();
	exit;
}

$cliObj->showTopAction();


?>
