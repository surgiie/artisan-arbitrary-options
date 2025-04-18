<?php

namespace Surgiie\ArtisanArbitraryOptions;

use Illuminate\Console\Command as LaravelCommand;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command as LaravelZeroCommand;
use Surgiie\ArtisanArbitraryOptions\Support\OptionsParser;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

if (class_exists(LaravelZeroCommand::class)) {
    abstract class BaseCommand extends LaravelZeroCommand {}
} else {
    abstract class BaseCommand extends LaravelCommand {}
}

abstract class Command extends BaseCommand
{
    /**
     * The options that are not defined on the command.
     */
    protected Collection $arbitraryOptions;

    protected Collection $arbitraryOptionsOrdered;

    /**
     * Constuct a new Command instance.
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->shouldHaveArbitraryOptions()) {
            $this->arbitraryOptions = collect();
            $this->arbitraryOptionsOrdered = collect();
            $this->ignoreValidationErrors();
        }

    }

    /**
     * Check if arbitrary options should be allowed.
     */
    protected function shouldHaveArbitraryOptions(): bool
    {
        return true;
    }

    /**
     * Initialize the command input/ouput objects.
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {

        if (! $this->shouldHaveArbitraryOptions()) {
            parent::initialize($input, $output);

            return;
        }

        if ($input instanceof StringInput) {
            $input = new ArrayInput(explode(' ', $input));
        }

        $tokens = $input instanceof ArrayInput ? invade($input)->parameters : invade($input)->tokens;
        $definition = $this->getDefinition();

        $orderedOptions = OptionsParser::parseOptionsOrdered($tokens);
        foreach ($orderedOptions as $info) {
            if ($definition->hasOption($info[0])) {
                continue;
            }
            $this->arbitraryOptionsOrdered->push([$info[0], $info[1]]);
        }

        $parser = new OptionsParser($tokens);

        foreach ($parser->parseDefinition() as $name => $data) {
            if (! $definition->hasOption($name)) {
                $this->arbitraryOptions->put($name, $data['value']);
                $this->addOption($name, mode: $data['mode']);
            }
        }
        // rebind input definition
        $input->bind($definition);

    }
}
