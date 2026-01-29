<?php

declare(strict_types=1);

namespace FullSmack\PhpCsFixerBreathe\RuleSet;

use PhpCsFixer\RuleSet\RuleSetDefinitionInterface;

final class Breathe implements RuleSetDefinitionInterface
{
    public function getName(): string
    {
        return '@fullsmack/breathe';
    }

    public function getRules(): array
    {
        return [
            '@PSR12' => true,
            'braces_position' => [
                'control_structures_opening_brace' => 'next_line_unless_newline_at_signature_end',
                'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
                'anonymous_functions_opening_brace' => 'same_line',
                'classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
                'anonymous_classes_opening_brace' => 'same_line',
                'allow_single_line_empty_anonymous_classes' => false,
                'allow_single_line_anonymous_functions' => true,
            ],
            'single_line_empty_body' => true,
            'blank_line_after_opening_tag' => false,
            'blank_line_between_import_groups' => true,
            'ordered_imports' => false,
            'single_import_per_statement' => true,
            'group_import' => false,
            // Keep false. Removes same namespace imports which is undesired
            'no_unused_imports' => false,
            'no_leading_import_slash' => true,
            'global_namespace_import' => true,
            'no_extra_blank_lines' => false,
            'trailing_comma_in_multiline' => [
                'elements' => [
                    'arguments',
                    'arrays',
                    'match',
                    'parameters',
                ],
            ],
            'method_argument_space' => [
                'on_multiline' => 'ensure_fully_multiline',
            ],
            // Custom fixers
            'fullsmack/match_brace_same_line' => true,
            'fullsmack/try_brace_same_line' => true,
            'fullsmack/catch_on_new_line' => true,
            'fullsmack/else_on_new_line' => true,
            'fullsmack/empty_catch_body_same_line' => true,
            'fullsmack/method_return_type_brace_regex' => true,
        ];
    }

    public function getDescription(): string
    {
        return 'Breathe - FullSmack coding standards with custom brace positioning and formatting rules.';
    }

    public function isRisky(): bool
    {
        return false;
    }
}
