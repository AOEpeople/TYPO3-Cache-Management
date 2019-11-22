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

    public static function getCacheType($cacheId)
    {
        $conf = (new self())->getCacheConfiguration($cacheId);
        $frontend = $conf['frontend'];
        if (empty($frontend)) {
            return 'Default (Variable)';
        }
        return $frontend;
    }

    public static function getCacheBackendType($cacheId)
    {
        $conf = (new self())->getCacheConfiguration($cacheId);
        $backend = $conf['backend'];
        if (empty($backend)) {
            return 'Default (DbBackend)';
        }
        return $backend;
    }

    /**
     * @param string $cacheId
     * @return string
     */
    public static function getCacheOptions($cacheId): string
    {
        $conf = (new self())->getCacheConfiguration($cacheId);
        $options = '';
        if (isset($conf['options']) && !empty($conf['options'])) {
            $options = str_replace('array', '', var_export($conf['options'], true));
        }

        return $options;
    }

    private function getCacheConfiguration($cacheId)
    {
        return $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheId];
    }

}
