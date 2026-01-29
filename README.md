# Breathe - PHP-CS-Fixer Configuration

Custom PHP-CS-Fixer configuration package with custom fixers and rulesets for FullSmack coding standards.

## Features

This package provides:

- **Custom Fixers**: Custom fixers mainly for specific brace positioning
- **Custom RuleSet**: Pre-configured `@fullsmack/breathe` ruleset based on PSR-12
- **Easy Integration**: Simple to use in your projects

## Installation

Install via Composer:

```bash
composer require --dev fullsmack/php-cs-fixer-breathe
```

## Usage

### Quick Start

Create a `.php-cs-fixer.dist.php` file in your project root:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
    ->exclude('node_modules');

$config = \FullSmack\PhpCsFixerBreathe\Config::create();

return $config->setFinder($finder);
```

### Manual Configuration

For more control, you can manually configure:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use FullSmack\PhpCsFixerBreathe\Fixer;
use FullSmack\PhpCsFixerBreathe\RuleSet;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor');

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
        '@fullsmack/breathe' => true,
        // Override or add additional rules here
    ])
    ->setFinder($finder);
```

### Extending the RuleSet

You can use the `@Breathe` ruleset as a base and override specific rules:

```php
<?php

$config = \FullSmack\PhpCsFixerBreathe\Config::create();

return $config
    ->setRules([
        '@fullsmack/breathe' => true,
        'no_unused_imports' => true, // Override default
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
        ],
    ])
    ->setFinder($finder);
```

## Custom Fixers

This package includes the following custom fixers:

### fullsmack/match_brace_same_line
Ensures `match` expression opening braces are on the same line.

### fullsmack/try_brace_same_line
Ensures `try` block opening braces are on the same line.

### fullsmack/catch_on_new_line
Places `catch` statements on a new line after the closing brace of the try block.

### fullsmack/else_on_new_line
Places `else` statements on a new line after the closing brace of the if block.

### fullsmack/empty_catch_body_same_line
Handles formatting of empty catch blocks.

### fullsmack/method_return_type_brace_regex
Applies regex-based formatting for method return type braces.

## The @fullsmack/breathe RuleSet

The `@fullsmack/breathe` ruleset extends `@PSR12` with additional rules:

- Custom brace positioning for control structures and functions
- Specific import handling
- Trailing comma requirements in multiline arrays and parameters
- And more...

See [src/RuleSet/Breathe.php](src/RuleSet/Breathe.php) for the complete list of rules.

## Running PHP-CS-Fixer

After creating your configuration file:

```bash
# Dry run (show what would be fixed)
vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix files
vendor/bin/php-cs-fixer fix

# Fix specific directory
vendor/bin/php-cs-fixer fix src/
```

## Requirements

- PHP 8.1 or higher
- PHP-CS-Fixer 3.92 or higher

## License

MIT

## Contributing

Contributions are welcome! Please submit pull requests or open issues on GitHub.
