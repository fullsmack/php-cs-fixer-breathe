<?php
declare(strict_types=1);

namespace FullSmack\PhpCsFixerBreathe\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

final class MethodReturnTypeBraceRegex extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Method opening braces should be on a new line when the method has a return type (regex-based).',
            [
                new CodeSample(
                    '<?php
class Example
{
    public function myMethod(
        string $value,
    ): string {
        return $value;
    }
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return true; // We'll check the content directly
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $content = $tokens->generateCode();

        // Pattern explanation:
        // ^(\s{4}\):\s*) - Match exactly 4 spaces, closing paren, colon, optional whitespace (capture group 1)
        // ([^{}\r\n]+?) - Match return type - any characters except braces or newlines, non-greedy (capture group 2)
        // (\s*\{) - Match optional whitespace and opening brace (capture group 3)
        // The pattern must be strict: 4 spaces + ) + : + return type + {
        $pattern = '/^(\s{4}\):\s*)([^{}\r\n]+?)(\s*\{)/m';

        $replacement = function ($matches) {
            $prefix = $matches[1]; // "    ): "
            $returnType = trim($matches[2]); // The return type without extra whitespace

            // Only apply if we actually have a return type (not empty)
            if (empty($returnType))
            {
                return $matches[0]; // Return original if no return type
            }

            // Return with the return type on same line, but brace on new line with proper indentation
            return $prefix . $returnType . "\n    {";
        };

        $newContent = preg_replace_callback($pattern, $replacement, $content);

        if ($newContent !== $content)
        {
            $tokens->setCode($newContent);
        }
    }

    public function getPriority(): int
    {
        // Run after all other custom fixers
        return -37;
    }

    public function getName(): string
    {
        return 'fullsmack/method_return_type_brace_regex';
    }
}
