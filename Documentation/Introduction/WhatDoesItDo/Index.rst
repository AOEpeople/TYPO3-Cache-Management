

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

Gives an overview of cached instances of page ids from the page tree and thus allows you to analyze if the size of your cache_page is within expected limits. This can reveal poor usages of &cHash links and non-optimal usage of TypoScript template conditions.

Also it includes a suite of Database and file system performance tools, as well as over view of the cache_hash content. More tools could be included in the future.

Also includes a re-caching hook for usage with the “crawler” extension.
