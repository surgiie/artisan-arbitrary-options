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
        $this->arbitraryOptions->get('name');
    }
}
```



