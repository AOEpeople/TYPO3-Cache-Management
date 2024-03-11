<?php

declare(strict_types=1);

namespace Aoe\Cachemgm\Utility;

class CacheUtility
{
    /**
     * @return array with cache keys
     */
    public static function getAvailableCaches(): array
    {
        return array_keys($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
    }

    public static function getCacheType(string $cacheId): string
    {
        $conf = (new self())->getCacheConfiguration($cacheId);
        if (!isset($conf['frontend'])) {
            return 'Default (Variable)';
        }

        return $conf['frontend'];
    }

    public static function getCacheBackendType(string $cacheId): string
    {
        $conf = (new self())->getCacheConfiguration($cacheId);
        if (!isset($conf['backend'])) {
            return 'Default (DbBackend)';
        }

        return $conf['backend'];
    }

    public static function getCacheOptions(string $cacheId): string
    {
        $conf = (new self())->getCacheConfiguration($cacheId);
        if (isset($conf['options']) && !empty($conf['options'])) {
            return str_replace('array', '', var_export($conf['options'], true));
        }

        return '';
    }

    /**
     * @return mixed
     */
    private function getCacheConfiguration(string $cacheId)
    {
        return $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheId];
    }
}
