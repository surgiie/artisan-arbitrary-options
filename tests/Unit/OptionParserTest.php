<?php

use Surgiie\ArtisanArbitraryOptions\Support\OptionsParser;
use Symfony\Component\Console\Input\InputOption;

it('parses options into --name=value format', function () {
    $parser = new OptionsParser([
        '--option',
        'value',
        '--option2',
        'value2',
        '--option3',
        'value3',
        '--option4',
        'value4',
    ]);
    $options = $parser->parseRawOptions();
    expect($options)->toBe([
        '--option=value',
        '--option2=value2',
        '--option3=value3',
        '--option4=value4',
    ]);
});

it('parses short options into --name=value format', function () {
    $parser = new OptionsParser([
        '-o',
        'value',
        '-o2',
        'value2',
        '-o3',
        'value3',
        '-o4',
        'value4',
    ]);
    $options = $parser->parseRawOptions();
    expect($options)->toBe([
        '-o=value',
        '-o2=value2',
        '-o3=value3',
        '-o4=value4',
    ]);
});

it('parses mixed options into --name=value format', function () {
    $parser = new OptionsParser([
        '--option',
        'value',
        '--option2=value2',
        '--option3',
        'value3',
        '-o4',
        'value4',
        '-o5=value5',
        '-o6',
    ]);
    $options = $parser->parseRawOptions();
    expect($options)->toBe([
        '--option=value',
        '--option2=value2',
        '--option3=value3',
        '-o4=value4',
        '-o5=value5',
        '-o6',
    ]);
});

it('only parses options and not arguments', function () {
    $parser = new OptionsParser([
        '--option',
        'value',
        '--option2=value2',
        '--option3',
        'value3',
        'arg1',
        'arg2',
    ]);
    $options = $parser->parseRawOptions();
    expect($options)->toBe([
        '--option=value',
        '--option2=value2',
        '--option3=value3',
    ]);
});

it('parses definitions for --name value options', function () {
    $parser = new OptionsParser([
        '--option',
        'value',
        '--option2',
        'a',
        '--option2',
        'b',
        '--option3',
    ]);
    $definition = $parser->parseDefinition();

    expect($definition)->toBe([
        'option' => [
            'mode' => InputOption::VALUE_REQUIRED,
            'value' => 'value',
        ],
        'option2' => [
            'mode' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'value' => ['a', 'b'],
        ],
        'option3' => [
            'mode' => InputOption::VALUE_NONE,
            'value' => true,
        ],
    ]);
});

it('parses definitions for --name=val options', function () {
    $parser = new OptionsParser([
        '--option=value',
        '--option2=a',
        '--option2=b',
        '--option3',
    ]);

    $definition = $parser->parseDefinition();

    expect($definition)->toBe([
        'option' => [
            'mode' => InputOption::VALUE_REQUIRED,
            'value' => 'value',
        ],
        'option2' => [
            'mode' => InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'value' => ['a', 'b'],
        ],
        'option3' => [
            'mode' => InputOption::VALUE_NONE,
            'value' => true,
        ],
    ]);
});

it('parses options into a ordered format', function () {
    $parsed = OptionsParser::parseOptionsOrdered([
        '--option',
        'value',
        '--option',
        'value2',
        '--option2',
        'value2',
        '--option3',
        'value3',
        '--option4',
        'value4',
    ]);
    expect($parsed)->toBe([
        ['option', 'value'],
        ['option', 'value2'],
        ['option2', 'value2'],
        ['option3', 'value3'],
        ['option4', 'value4'],
    ]);
});
