<?php

########################################################################
# Extension Manager/Repository config file for ext "cachemgm".
#
# Auto generated 12-09-2011 21:12
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Cache Management Extension',
	'description' => 'Provides management of the page caching for high traffic websites.',
	'category' => 'module',
	'author' => 'Kasper Skaarhoj',
	'author_email' => 'kasper@typo3.com',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '2.4.1',
	'_md5_values_when_last_written' => 'a:18:{s:9:"ChangeLog";s:4:"67d8";s:25:"class.tx_cachemgm_lib.php";s:4:"282c";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"52ac";s:14:"ext_tables.php";s:4:"2882";s:16:"locallang_db.php";s:4:"c16b";s:12:"doc/TODO.txt";s:4:"95fb";s:14:"doc/manual.sxw";s:4:"c4a7";s:13:"mod/clear.gif";s:4:"cc11";s:12:"mod/conf.php";s:4:"e198";s:15:"mod/db_read.png";s:4:"528b";s:19:"mod/file_access.png";s:4:"4671";s:18:"mod/file_write.png";s:4:"00a4";s:13:"mod/index.php";s:4:"8eb5";s:15:"mod/isearch.gif";s:4:"4cbf";s:21:"mod/locallang_mod.xml";s:4:"b829";s:39:"modfunc1/class.tx_cachemgm_modfunc1.php";s:4:"3d62";s:22:"modfunc1/locallang.php";s:4:"a346";}',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);
