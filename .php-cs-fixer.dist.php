<?php

/**
 * Example PHP-CS-Fixer configuration using the Breathe package.
 *
 * Copy this file to your project root as `.php-cs-fixer.dist.php`
 */

require_once __DIR__ . '/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
    ->exclude('node_modules')
    ->exclude('storage')
    ->exclude('bootstrap/cache')
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

// Option 1: Use the quick setup with @Breathe ruleset
$config = \FullSmack\PhpCsFixerBreathe\Config::create();

return $config->setFinder($finder);

// Option 2: Customize by overriding rules
/*
$config = \FullSmack\PhpCsFixerBreathe\Config::create();

return $config
    ->setRules([
        '@Breathe' => true,
        // Add or override rules here
        'no_unused_imports' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
*/

// Option 3: Manual setup with full control
/*
use FullSmack\PhpCsFixerBreathe\Fixer;
use FullSmack\PhpCsFixerBreathe\RuleSet;

$config = new PhpCsFixer\Config();

return $config
    ->registerCustomFixers([
        new Fixer\MatchBraceSameLine(),
        new Fixer\TryBraceSameLine(),
        new Fixer\CatchOnNewLine(),
        new Fixer\ElseOnNewLine(),
        new Fixer\EmptyCatchBodySameLine(),
        new Fixer\MethodReturnTypeBraceRegex(),
    ])
    ->registerCustomRuleSets([
        new RuleSet\Breathe(),
    ])
    ->setRules([
        '@Breathe' => true,
        // Additional rules
    ])
    ->setFinder($finder);
*/
