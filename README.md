# artisan-arbitrary-options

![tests](https://github.com/surgiie/artisan-arbitrary-options/actions/workflows/tests.yml/badge.svg)

This package allows your artisan commands to accept arbitrary options not part of the command signature.

## Install

```bash
composer require surgiie/artisan-arbitrary-options
```

## Usage

Simply extend your command from `Surgiie\ArbitraryOptions\Command`:

```php
<?php
namespace App\Console\Commands;

use Surgiie\ArbitraryOptions\Command as ArbitraryOptionsCommand;
// ....

class MyCommand extends ArbitraryOptionsCommand
{
    protected $signature = 'my:command ...';

    public function handle()
    {
        // Access arbitrary options not defined in the signature.
        // for example if --name wasnt defined in the signature,
        // you can access it like so:
        $name = $this->arbitraryOptions->get('name');
    }
}
```

### Option Types

Like normal signature options, options can be of type `string`, `array`, `bool` (true) depending on count/repitition of the option in the command line.

When option is passed multiple times, it will be converted to an array. When no value is passed, it will be converted to a boolean, otherwise the value will be a string.

## Things To Consider:

In order to allow arbitrary options, this does make your command lose it's validation for "unknown options". If this is a problem, its encouraged that you validate anything you're not expecting in your command's `handle` method.

For example you may consider doing something like this in your command:

```php
$this->arbitraryOptions->keys()->each(function ($option)) {
    if (! in_array($option, ['name', 'age'])) {
        $this->fail("Unknown or not accepted option encountered: `{$option}`.");
    }
});
```

### Disable Arbitrary Options

If you want to disable arbitrary options for a specific command, you can do so by utilizing the `shouldHaveArbitraryOptions` method:

```php
<?php
namespace App\Console\Commands;

use Surgiie\ArbitraryOptions\Command as ArbitraryOptionsCommand;

class ExampleCommand extends ArbitraryOptionsCommand
{
    protected $signature = 'example:command ...';

    public function shouldHaveArbitraryOptions(): bool
    {
        return false;
    }
}

```

### Care about order of options?

If you care about the order of the options as they were passed, you can use the `arbritraryOptionsOrdered` property to loop through the options in the order they were passed in.
This property is formatted as an array of arrays, where each inner array will contain the option name and value:

```php

foreach ($this->arbitraryOptionsOrdered as $option) {
    $optionName = $option[0];
    $value = $option[1]; // "true" if no value was passed or "<string>" if a value was passed.
}

```
