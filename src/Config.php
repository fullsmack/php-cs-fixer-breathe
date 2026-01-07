<?php

declare(strict_types=1);

namespace FullSmack\PhpCsFixerBreathe;

use FullSmack\PhpCsFixerBreathe\Fixer\CatchOnNewLine;
use FullSmack\PhpCsFixerBreathe\Fixer\ElseOnNewLine;
use FullSmack\PhpCsFixerBreathe\Fixer\EmptyCatchBodySameLine;
use FullSmack\PhpCsFixerBreathe\Fixer\MatchBraceSameLine;
use FullSmack\PhpCsFixerBreathe\Fixer\MethodReturnTypeBraceRegex;
use FullSmack\PhpCsFixerBreathe\Fixer\TryBraceSameLine;
use PhpCsFixer\Config as BaseConfig;

/**
 * Custom PHP-CS-Fixer configuration with FullSmack coding standards.
 */
final class Config extends BaseConfig
{
    public function __construct()
    {
        parent::__construct('Breathe');

        $this->registerCustomFixers([
            new MatchBraceSameLine(),
            new TryBraceSameLine(),
            new CatchOnNewLine(),
            new ElseOnNewLine(),
            new EmptyCatchBodySameLine(),
            new MethodReturnTypeBraceRegex(),
        ]);

        $this->registerCustomRuleSets([
            new RuleSet\Breathe(),
        ]);
    }

    /**
     * Get a configuration instance with Breathe ruleset applied.
     */
    public static function create(): self
    {
        $config = new self();

        $config->setRules([
            '@Breathe' => true,
        ]);

        return $config;
    }
}
