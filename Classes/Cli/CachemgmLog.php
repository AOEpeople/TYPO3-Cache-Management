<?php


/**
 * CLI Controller for displaying and listenig to cache logs
 * 
 * @author danielpotzinger
 */
class Tx_Cachemgm_Cli_CachemgmLog extends t3lib_cli {
	
	public $stat;
	
	/**
	 * Constructor
	 *
	 * @return	void
	 */
	function __construct()	{

		parent::__construct();

		$this->cli_options[] = array('-h', 'Show the help', '');
		$this->cli_options[] = array('--help', 'Same as -h', '');
		$this->cli_options[] = array('--cache', 'Filter for cache', 'E.g. extbase_object will only listen to cschelogs from this cache');
		$this->cli_options[] = array('--filterUrl', 'Filter for url', 'This will only evaluate processes matching the request url');
		$this->cli_options[] = array('--filterAction', 'Filter for action', 'This will only evaluate logs mathcing the action');
	
		
		// Setting help texts:
		$this->cli_help['name'] = 'Cachemgm Shared Memory Log reader interface';
		$this->cli_help['synopsis'] = '###OPTIONS###';
		$this->cli_help['description'] = "";
		$this->cli_help['examples'] = "nice 10 /.../cli_dispatch.phpsh cachemgm_log --cache=extbase_object \nWill trigger the listener\n";
		$this->cli_help['author'] = 'Daniel Poetzinger - AOE media 2012';
		
		$this->stat = new Tx_Cachemgm_Cli_CacheStatistic();
	}
	
	/**
	 * Main action - direct STDOUT of logs read from shared memory
	 * @return void
	 */
	public function showLogAction() {
		echo "#\tCache \t\t Action \t\t\t CacheIdenifier \t\t\t\t\t Processid \t Elapsed Time".PHP_EOL;
		echo str_repeat('-',140).PHP_EOL;
		$this->startListenToLogs();		
	}
	
	/**
	 * prints cache statitic summary
	 */
	public function finalStatPrint() {
		$this->stat->printStat();
	}
	
	/**
	 * @param array $log
	 */
	protected function logListener(array $log) {
		$this->outputSingleLog($log);
		$this->stat->addLogForActionCountStat($log);
		$this->stat->addLogForActionTimeStat($log);		
	}
	
	/**
	 * listening thread - also evaluates further filters
	 * @param string $callback
	 */
	protected function startListenToLogs($callback='logListener') {
		$reader = new Tx_Cachemgm_Cache_MemoryLogReader();
		if (!$reader->isEnabled()) {	
			die('Tx_Cachemgm_Cache_MemoryLogReader not enabled! Check shm PHP functions. (Or the shared memory is not yet created?)'.PHP_EOL);
		}
		
		$filterCache = $filterUrl = NULL;
		if ( isset($this->cli_args['--cache']) ) {
			$filterCache = $this->cli_args['--cache'][0];
			echo 'Cache filter set: '.$filterCache.PHP_EOL;			
		}
		if ( isset($this->cli_args['--filterUrl']) ) {
			$filterUrl = $this->cli_args['--filterUrl'][0];
			echo 'Url filter set: '.$filterUrl.PHP_EOL;			
		}
		
		if ( isset($this->cli_args['--filterAction']) ) {
			$filterAction = $this->cli_args['--filterAction'][0];
			echo 'Action filter set: '.$filterAction.PHP_EOL;			
		}
		
		$processesMatchingUrlFilter = array();
		$nr=0;
		while(TRUE) {
			$log = $reader->getNextLog($nr, $filterCache, $filterAction);
			$nr = $log['nr'];			
			if (!empty($filterUrl)) {
				if ($log['action'] == Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_LOGINIT && $log['id'] == $filterUrl) {
					$processesMatchingUrlFilter[$log['pid']] = TRUE;
				}
				if (!isset($processesMatchingUrlFilter[$log['pid']])) {
					continue;
				}
			}			
			call_user_func_array (array($this,$callback),array($log));	
		}
	}
	

	/**
	 * @param array $log
	 * @return void
	 */
	protected function outputSingleLog(array $log) {
		echo '#'.$log['nr'].str_repeat(' ',6-strlen($log['nr'])).' '.
				 $log['cache'].str_repeat(' ',20-strlen($log['cache'])).' '.
				 $log['action'].str_repeat(' ',15-strlen($log['action'])).' '.
				 $log['id'].str_repeat(' ',70-strlen($log['id'])).' '.
				// we dont need mirotime output $log[3].str_repeat(' ',20-strlen($log[3])).
				 $log['pid'].str_repeat(' ',20-strlen($log['pid'])).' '.
				 round($log['time']*1000).'ms'.PHP_EOL;
	}
}


?>
