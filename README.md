JSON Container
==============

Usage
-----

```php
use Jsonor\JSON;

    
// returns null if it's valid json, or a ParsingException object.
JSON::lint($json);

// Call getMessage() on the exception object to get
// a well formatted error message error like this

// Parse error on line 2:
// ... "key": "value"    "numbers": [1, 2, 3]
// ----------------------^
// Expected one of: 'EOF', '}', ':', ',', ']'

// Call getDetails() on the exception to get more info.

// returns parsed json, like json_decode() does
$data = JSON::decode($json);
// sets yours callback on changes
$data->onChange(function () {
    // store in DB, e.g.
});
// Use $data as array
$data["d"][1]["name"] = "Banana";
$data["d"][1]["desc"] = "It's fruit";
$data["d"][] = [
    "name" => "Apple"
];

unset($c["d"][1]);

foreach($data as $key => $value) {
    // ...
}
```

Installation
------------

For a quick install with Composer use:

    $ composer require bzick/jsonor

Jsonor can easily be used within another app if you have a
[PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
autoloader, or it can be installed through [Composer](https://getcomposer.org/)
for use as a CLI util.


Requirements
------------

- PHP 5.4+
- [optional] PHPUnit 3.5+ to execute the test suite (phpunit --version)

Submitting bugs and feature requests
------------------------------------

Bugs and feature request are tracked on [GitHub](https://github.com/bzick/jsonor/issues)

Author
------

Ivan Shalganov - <a.cobest@gmail.com>

License
-------

Jsonor is licensed under the MIT License - see the LICENSE file for details
