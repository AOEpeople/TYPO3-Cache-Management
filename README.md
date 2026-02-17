# Cache-Management for TYPO3

This is a TYPO3-Extension that provides management of the page caching for high-traffic websites.

## Download / Installation

You can download and install this extension from the [TER (TYPO3 Extension Repository)][1] or use composer.
```
$ composer require aoepeople/cachemgm
```

## Documentation

The documentation is available online at [docs.typo3.org][2].

If you want you can also render the documentation locally, this can be really helpful when adjusting
the documentation to check before commiting.

The local rendering requires docker and can be done like this:

```bash
docker run --rm --pull always -v $(pwd):/project -it ghcr.io/typo3-documentation/render-guides:latest --config=Documentation
``` 

And then open `Documentation-GENERATED-temp/Index.html` with your browser.


## Copyright / License

Copyright: (c) 2009 - 2026, Kasper Skaarhoj & AOE GmbH
License: GPLv3, <http://www.gnu.org/licenses/gpl-3.0.en.html>

[1]: http://typo3.org/extensions/repository/view/cachemgm
[2]: https://docs.typo3.org/p/aoepeople/cachemgm/10.0/en-us/

## Contributing

	1. Fork the repository on Github
	2. Create a named feature / bugfix branch (like `feature_add_something_new` or `bugfix\thing_which_does_not_work`)
	3. Write your change
	4. Write tests for your change (if applicable)
	5. Write documentaton for your change
	6. Run the tests, ensuring they all pass
	7. Submit a Pull Request using Github
