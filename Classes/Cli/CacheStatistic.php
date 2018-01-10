<?php
namespace Aoe\Cachemgm\Cli;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 AOE GmbH <dev@aoe.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Aoe\Cachemgm\Cache\MemoryLogWriter;

/**
 * Simple Cache statistic collector and outputter :-)
 *
 * @author danielpotzinger
 */
class CacheStatistic
{
    private $counts = array();
    private $timeSums = array();
    private $idendifiers = array();

    const CACHE_ID_SEPERATOR = '   |   ';

    /**
     * @param array $log
     */
    public function addLogForActionTimeStat(array $log)
    {
        if (empty($log['cache']) || $log['cache'] == '-') {
            return;
        }
        $this->timeSums[$log['cache']][$log['action']] = $this->timeSums[$log['cache']][$log['action']] + ($log['time']);
    }

    /**
     * @param array $log
     */
    public function addLogForIdendifierStat(array $log)
    {
        if (empty($log['cache']) || $log['cache'] == '-') {
            return;
        }
        $this->idendifiers[$log['cache'] . self::CACHE_ID_SEPERATOR . $log['id']]++;
    }

    /**
     * @param array $log
     */
    public function addLogForActionCountStat(array $log)
    {
        if (empty($log['cache']) || $log['cache'] == '-') {
            return;
        }
        $this->counts[$log['cache']][$log['action']]++;
    }

    /**
     * returns the top n identifiers
     * @param integer $n
     * @return array
     */
    public function getTopIdendifiers($n = 100)
    {
        arsort($this->idendifiers);
        $result = array();
        $i = 0;
        foreach ($this->idendifiers as $id => $count) {
            $i++;
            if ($i > $n) {
                return $result;
            }
            $parts = explode(self::CACHE_ID_SEPERATOR, $id);
            $result[] = array('cache' => $parts[0], 'id' => $parts[1], 'count' => $count);
        }
        return $result;
    }

    /**
     * Echos the statistic - used as shutdown or sigterm callback
     *
     * @return void
     */
    public function printStat()
    {
        echo PHP_EOL . 'Statistics:' . PHP_EOL . str_repeat('*', 100) . PHP_EOL;
        $overallHitTime = $overallHitCount = $overallSetTime = $overallSetCount = 0;
        foreach ($this->counts as $cache => $counts) {
            echo PHP_EOL . 'Cache "' . $cache . '":' . PHP_EOL . str_repeat('-', 25) . PHP_EOL;
            echo ' get method:' . PHP_EOL;
            echo '   Hits:' . $counts[MemoryLogWriter::ACTION_HIT] . PHP_EOL;
            echo '   Misses:' . $counts[MemoryLogWriter::ACTION_MISS] . PHP_EOL;
            if ($counts[MemoryLogWriter::ACTION_GETSTART] > 0) {
                echo '   Hit-Rate:' . round($counts[MemoryLogWriter::ACTION_HIT] / $counts[MemoryLogWriter::ACTION_GETSTART],
                        3) . PHP_EOL;
            }
            if ($counts[MemoryLogWriter::ACTION_HIT] > 0) {
                $overallHitTime = $overallSetTime + $this->timeSums[$cache][MemoryLogWriter::ACTION_HIT];
                $overallHitCount = $overallSetCount + $counts[MemoryLogWriter::ACTION_HIT];
                echo '   Average Hit time:' . $this->formatTime($this->timeSums[$cache][MemoryLogWriter::ACTION_HIT] / $counts[MemoryLogWriter::ACTION_HIT]) . PHP_EOL;
            }
            echo '   Overall Hit Time:' . $this->formatTime($this->timeSums[$cache][MemoryLogWriter::ACTION_HIT]) . PHP_EOL;
            //has method stats
            echo ' has method:' . PHP_EOL;
            echo '   Hits:' . $counts[MemoryLogWriter::ACTION_HASHIT] . PHP_EOL;
            echo '   Misses:' . $counts[MemoryLogWriter::ACTION_HASMISS] . PHP_EOL;
            echo '   Overall Time:' . $this->formatTime($this->timeSums[$cache][MemoryLogWriter::ACTION_HASHIT] + $this->timeSums[$cache][MemoryLogWriter::ACTION_HASMISS]) . PHP_EOL;
            //set method
            echo ' set method:' . PHP_EOL;
            echo '    Sucess Writes:' . $counts[MemoryLogWriter::ACTION_SETEND] . PHP_EOL;
            echo '    Failed Writes:' . ($counts[MemoryLogWriter::ACTION_SETSTART] - $counts[MemoryLogWriter::ACTION_SETEND]) . PHP_EOL;

            if ($counts[MemoryLogWriter::ACTION_SETEND] > 0) {
                $overallSetTime = $overallSetTime + $this->timeSums[$cache][MemoryLogWriter::ACTION_SETEND];
                $overallSetCount = $overallSetCount + $counts[MemoryLogWriter::ACTION_SETEND];
                echo '    Average Write time:' . $this->formatTime($this->timeSums[$cache][MemoryLogWriter::ACTION_SETEND] / $counts[MemoryLogWriter::ACTION_SETEND]) . PHP_EOL;
            }
            echo '    Overall Write Time:' . $this->formatTime($this->timeSums[$cache][MemoryLogWriter::ACTION_SETEND]) . PHP_EOL;

            echo PHP_EOL;
        }
        echo PHP_EOL;
        if ($overallSetCount > 0) {
            echo 'Overall Average Write time:' . $this->formatTime($overallSetTime / $overallSetCount) . PHP_EOL;
        }
        if ($overallHitCount > 0) {
            echo 'Overall Average Hit time:' . $this->formatTime($overallHitTime / $overallHitCount) . PHP_EOL;
        }
        echo PHP_EOL;
    }

    private function formatTime($time)
    {
        if ($time < 0.001) {
            return round($time * 1000000) . 'ns';
        }
        if ($time < 1) {
            return round($time * 1000) . 'ms';
        }
        return round($time, 2) . 'sec';
    }
}
