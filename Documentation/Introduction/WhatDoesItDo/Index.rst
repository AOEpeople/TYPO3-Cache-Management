

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


What does it do?
^^^^^^^^^^^^^^^^

The AOE Cache Management extensions, gives an overview of which TYPO3 Caches is in place.

You have a list of all cache registered under `$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']`,
and you will be able to look into the details of the cache type and to flush the cache individually if needed.

The cache manager also includes a re-caching hook for usage with the “Crawler” extension.
