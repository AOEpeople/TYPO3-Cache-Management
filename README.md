# Cache-Management for TYPO3

This is a TYPO3-Extension, that provides management of the page caching for high traffic websites.

## Download / Installation

You can download and install this extension from the [TER (TYPO3 Extension Repository)][1] or use composer.
```
$ composer require aoepeople/cachemgm
```

## Documentation

The documentation is available online at [docs.typo3.org][2].

If you want you can also render the documentation locally, this can be really helpful when adjusting
the documentation, to check before commiting.

The local rendering requires docker, and can be done like this:

```
$ source <(docker run --rm t3docs/render-documentation:latest show-shell-commands)
$ dockrun_t3rd makehtml
``` 

And then open `Documentation-GENERATED-temp/Result/project/0.0.0/Index.html` with your browser.


## Copyright / License

Copyright: (c) 2009 - 2019, Kasper Skaarhoj & AOE GmbH
License: GPLv3, <http://www.gnu.org/licenses/gpl-3.0.en.html>

[1]: http://typo3.org/extensions/repository/view/cachemgm
[2]: http://docs.typo3.org/typo3cms/extensions/cachemgm/

## Contributing

	1. Fork the repository on Github
	2. Create a named feature / bugfix branch (like `feature_add_something_new` or `bugfix\thing_which_does_not_work`)
	3. Write your change
	4. Write tests for your change (if applicable)
	5. Write documentaton for your change
	6. Run the tests, ensuring they all pass
	7. Submit a Pull Request using Github
