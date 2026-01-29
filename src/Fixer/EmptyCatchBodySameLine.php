<?php
declare(strict_types=1);

namespace FullSmack\PhpCsFixerBreathe\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class EmptyCatchBodySameLine extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Empty catch blocks should have braces on the same line.',
            [
                new CodeSample(
                    '<?php
try {
    // some code
} catch (Exception $e) {
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_CATCH);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index >= 0; --$index)
        {
            if (!$tokens[$index]->isGivenKind(T_CATCH))
            {
                continue;
            }

            $this->fixEmptyCatchBody($tokens, $index);
        }
    }

    private function fixEmptyCatchBody(Tokens $tokens, int $catchIndex): void
    {
        // Find the opening parenthesis of the catch parameters
        $catchParenIndex = $tokens->getNextTokenOfKind($catchIndex, ['(']);
        if (null === $catchParenIndex)
        {
            return;
        }

        // Find the corresponding closing parenthesis
        $catchParenCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $catchParenIndex);

        // Find the opening brace of the catch block
        $catchOpenBraceIndex = $tokens->getNextMeaningfulToken($catchParenCloseIndex);
        if (null === $catchOpenBraceIndex || !$tokens[$catchOpenBraceIndex]->equals('{'))
        {
            return;
        }

        // Find the corresponding closing brace
        $catchCloseBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $catchOpenBraceIndex);

        // Check if the catch block is empty (only whitespace between braces, no comments or code)
        $isEmpty = true;
        $hasComments = false;
        for ($i = $catchOpenBraceIndex + 1; $i < $catchCloseBraceIndex; ++$i)
        {
            if ($tokens[$i]->isComment())
            {
                $hasComments = true;
            } elseif (!$tokens[$i]->isWhitespace())
            {
                $isEmpty = false;
                break;
            }
        }

        if (!$isEmpty || $hasComments)
        {
            return; // Not an empty catch block or has comments (preserve formatting)
        }

        // Check if the braces are already on the same line
        $hasNewlineBetween = false;
        for ($i = $catchOpenBraceIndex + 1; $i < $catchCloseBraceIndex; ++$i)
        {
            if ($tokens[$i]->isWhitespace() && str_contains($tokens[$i]->getContent(), "\n"))
            {
                $hasNewlineBetween = true;
                break;
            }
        }

        if (!$hasNewlineBetween)
        {
            return; // Already properly formatted
        }

        // Remove all tokens between the braces
        for ($i = $catchCloseBraceIndex - 1; $i > $catchOpenBraceIndex; --$i)
        {
            $tokens->clearAt($i);
        }

        // The braces are now adjacent, which is what we want: {}
        // No need to insert anything as we want them directly together
    }

    public function getPriority(): int
    {
        // Run after the else_on_new_line fixer (which has priority -33) and other brace fixers
        return -34;
    }

    public function getName(): string
    {
        return 'fullsmack/empty_catch_body_same_line';
    }
}
