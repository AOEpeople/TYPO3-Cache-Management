<?php


/**
 * CLI Controller for displaying and listenig to cache logs
 * 
 * @author danielpotzinger
 */
class Tx_Cachemgm_Cli_CachemgmTop extends Tx_Cachemgm_Cli_CachemgmLog {
	
	public $stat;
	
	/**
	 * Constructor
	 *
	 * @return	void
	 */
	function __construct()	{

		parent::__construct();

			// Setting help texts:
		$this->cli_help['name'] = 'Cachemgm Shared Memory Top display';
		$this->cli_help['synopsis'] = '###OPTIONS###';
		$this->cli_help['description'] = "";
		$this->cli_help['examples'] = "nice 10 /.../cli_dispatch.phpsh cachemgm_top --cache=extbase_object \nWill trigger the listener\n";
		$this->cli_help['author'] = 'Daniel Poetzinger - AOE GmbH';
		
		$this->stat = new Tx_Cachemgm_Cli_CacheStatistic();
	}
	
	/**
	 * Main action - direct STDOUT of logs read from shared memory
	 * @return void
	 */
	public function showTopAction() {
		
		$this->startTime = time();
		$this->refreshTime = 2; //every 2 seconds
		$this->logAmount = 30; // top 30
		$this->startListenToLogs();		
	}
	
	/**
	 * prints cache statitic summary
	 */
	public function finalStatPrint() {
		echo 'done'.PHP_EOL;
	}
	
	/**
	 * @param array $log
	 */
	protected function logListener(array $log) {
		$this->stat->addLogForIdendifierStat($log);
		if ( (time() - $this->startTime) > $this->refreshTime) {
			$this->startTime = time();
			$this->printTop();
		}
	}
	
	protected function printTop() {
		if (function_exists('ncurses_clear')) {
			ncurses_clear();
		}
		else {
			echo str_repeat(PHP_EOL,140);
		}
		
		echo 'Top #'.$this->logAmount.' Cache Idendifiers: '.PHP_EOL;
		echo ' count                 cache                   idendifier '.PHP_EOL;
		echo str_repeat('-',140).PHP_EOL; 
		$idendifiers = $this->stat->getTopIdendifiers($this->logAmount);
		foreach ($idendifiers as $id) {
			echo $id['count'].str_repeat(' ',20-strlen($id['count'])).' '.
				$id['cache'].str_repeat(' ',30-strlen($id['cache'])).
				$id['id'].PHP_EOL;
		}
	}

}
