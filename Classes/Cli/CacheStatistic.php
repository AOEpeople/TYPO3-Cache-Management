<?php


/**
 * Simple Cache statistic collector and outputter :-)
 * 
 * @author danielpotzinger
 */
class Tx_Cachemgm_Cli_CacheStatistic {
	private $counts = array();
	private $timeSums = array();

	/**
	 * @param array $log
	 */
	public function addLog(array $log) {
		if (empty($log['cache']) || $log['cache'] == '-') {
			return;
		}
		$this->counts[$log['cache']][$log['action']]++;
		$this->timeSums[$log['cache']][$log['action']] = $this->timeSums[$log['cache']][$log['action']]+($log['time']);
	}
	
	
	/**
	 * Echos the statistic - used as shutdown or sigterm callback
	 * 
	 * @return void
	 */
	public function printStat() {
		echo PHP_EOL.'Statistics:'.PHP_EOL.str_repeat('*',100).PHP_EOL;		
		$overallHitTime = $overallHitCount = $overallSetTime = $overallSetCount = 0;
		foreach ($this->counts as $cache => $counts) {
			echo PHP_EOL.'Cache "'.$cache.'":'.PHP_EOL.str_repeat('-',25).PHP_EOL;
			echo ' get method:'.PHP_EOL;
			echo '   Hits:'.$counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_HIT].PHP_EOL;
			echo '   Misses:'.$counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_MISS].PHP_EOL;
			if ($counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_GETSTART] > 0) {
				echo '   Hit-Rate:'.round( $counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_HIT] / $counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_GETSTART],3).PHP_EOL;
			}
			if ($counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_HIT] > 0) {
				$overallHitTime = $overallSetTime + $this->timeSums[$cache][Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_HIT];
				$overallHitCount =  $overallSetCount + $counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_HIT];								
				echo '   Average Hit time:'. $this->formatTime($this->timeSums[$cache][Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_HIT]  / $counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_HIT]  ).PHP_EOL;
			}			
			echo '   Overall Hit Time:'.   $this->formatTime($this->timeSums[$cache][Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_HIT]).PHP_EOL;
			//has method stats
			echo ' has method:'.PHP_EOL;
			echo '   Hits:'.$counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_HASHIT].PHP_EOL;
			echo '   Misses:'.$counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_HASMISS].PHP_EOL;
			echo '   Overall Time:'.   $this->formatTime($this->timeSums[$cache][Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_HASHIT]+$this->timeSums[$cache][Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_HASMISS]).PHP_EOL;
			//set method
			echo ' set method:'.PHP_EOL;
			echo '    Sucess Writes:'.$counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_SETEND].PHP_EOL;
			echo '    Failed Writes:'. ( $counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_SETSTART]-$counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_SETEND]) .PHP_EOL;
			
			if ($counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_SETEND] > 0) {
				$overallSetTime = $overallSetTime + $this->timeSums[$cache][Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_SETEND];
				$overallSetCount =  $overallSetCount + $counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_SETEND];			
				echo '    Average Write time:'. $this->formatTime( $this->timeSums[$cache][Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_SETEND]  / $counts[Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_SETEND] ).PHP_EOL;
			}
			echo '    Overall Write Time:'. $this->formatTime($this->timeSums[$cache][Tx_Cachemgm_Cache_MemoryLogWriter::ACTION_SETEND]).PHP_EOL;
			
			echo PHP_EOL;
		}
		echo PHP_EOL;
		if ($overallSetCount > 0 ) {
			echo 'Overall Average Write time:'. $this->formatTime( $overallSetTime  / $overallSetCount ).PHP_EOL;
		}
		if ($overallHitCount > 0 ) {
			echo 'Overall Average Hit time:'. $this->formatTime( $overallHitTime  / $overallHitCount  ).PHP_EOL;
		}	
		echo PHP_EOL;
	}
	
	private function formatTime($time) {
		if ($time < 0.001) {
			return round($time*1000000).'ns';
		}
		if ($time < 1) {
			return round($time*1000).'ms';
		}
		return round($time,2).'sec';
	}
	
}


?>
