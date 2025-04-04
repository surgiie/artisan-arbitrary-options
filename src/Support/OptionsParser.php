<?php

namespace Surgiie\ArtisanArbitraryOptions\Support;

use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputOption;

class OptionsParser
{
    /**
     * The options being parsed.
     */
    protected array $options = [];

    /**
     * Construct new OptionsParser instance.
     */
    public function __construct(array $options)
    {
        $this->setOptions($options);
    }

    /**
     * Set the options to parse.
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Tests use ArrayInput objects, so we need to handle the tokens differently.
     */
    protected function parseTestOptions(?array $options = null): array
    {
        $iterable = $options ?? $this->options;
        $result = [];
        foreach ($iterable as $token => $v) {
            if (str_starts_with($token, '--')) {
                if ($v === false) {
                    continue;
                }
                if (is_array($v)) {
                    foreach ($v as $item) {
                        $result[] = "$token=$item";

                    }
                } else {
                    $token = $token.($v ? "=$v" : '');
                    $result[] = $token;
                }
            }
        }

        return $result;
    }

    /**
     * Parse the options into a format where they can be looped in order that they were passed.
     */
    public static function parseOptionsOrdered(array $options): array
    {
        $result = [];
        $parser = new static($options);

        if (app()->runningUnitTests()) {
            $options = $parser->parseTestOptions();
            $iterable = $parser->parseRawOptions($options);
        } else {
            $iterable = $parser->parseRawOptions();
        }

        foreach ($iterable as $option) {
            if (str_contains($option, '=')) {
                $parts = explode('=', $option);
                $result[] = [ltrim($parts[0], '-'), $parts[1]];
            } else {
                $result[] = [ltrim($option, '-'), null];
            }
        }

        return $result;
    }

    /**
     * Parse raw options into format that can be parsed for definition.
     */
    public function parseRawOptions(?array $options = null): array
    {
        $parsed = [];
        $options = $options ?? $this->options;
        $indexes = array_keys($options);
        foreach ($indexes as $index) {

            $current = $options[$index] ?? null;
            $next = $options[$index + 1] ?? null;

            if (is_null($current)) {
                continue;
            }

            preg_match('/--([^=]+)(=)(.*)/', $current, $match);

            if (! $match && str_starts_with($current, '-') && (is_null($next) || str_starts_with($next, '-'))) {
                $parsed[] = $current;

                continue;
            }

            if (! $match && str_starts_with($current, '-') && ! str_starts_with($next, '-')) {
                $parsed[] = "$current=$next";

                continue;
            }

            if ($match) {
                $parsed[] = "--{$match[1]}={$match[3]}";

                continue;
            }
        }

        return $parsed;
    }

    /**
     * Parse the set options.
     */
    public function parseDefinition(): array
    {
        $definition = [];

        if (app()->runningUnitTests()) {
            $options = $this->parseTestOptions();
            $iterable = $this->parseRawOptions($options);
        } else {
            $iterable = $this->parseRawOptions();
        }

        foreach ($iterable as $token) {
            preg_match('/--([^=]+)(=)?(.*)/', $token, $match);

            if (! $match) {
                continue;
            }

            $name = $match[1];
            $equals = $match[2] ?? false;
            $value = $match[3] ?? false;

            $optionExists = array_key_exists($name, $definition);

            if ($optionExists && ($value || $equals)) {
                $definition[$name] = [
                    'mode' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    'value' => $definition[$name]['value'] ?? [],
                ];
                $definition[$name]['value'] = Arr::wrap($definition[$name]['value']);
                $definition[$name]['value'][] = $value;
            } elseif ($value) {
                $definition[$name] = [
                    'mode' => InputOption::VALUE_REQUIRED,
                    'value' => $value,
                ];
            } elseif (! $optionExists) {
                $definition[$name] = [
                    'mode' => ($value == '' && $equals) ? InputOption::VALUE_OPTIONAL : InputOption::VALUE_NONE,
                    'value' => ($value == '' && $equals) ? '' : true,
                ];
            }
        }

        return $definition;
    }
}
