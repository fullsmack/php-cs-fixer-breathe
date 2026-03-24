<?php
declare(strict_types=1);

namespace FullSmack\PhpCsFixerBreathe\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class ElseOnNewLine extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Else statements should be on a new line after the closing brace of the if block.',
            [
                new CodeSample(
                    '<?php
if ($condition) {
    // some code
} else {
    // other code
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_IF) && $tokens->isTokenKindFound(T_ELSE);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index >= 0; --$index) {
            if (!$tokens[$index]->isGivenKind(T_IF)) {
                continue;
            }

            $this->fixElsePosition($tokens, $index);
        }
    }

    private function fixElsePosition(Tokens $tokens, int $ifIndex): void
    {
        // Find the opening parenthesis of the if condition
        $ifOpenParenIndex = $tokens->getNextTokenOfKind($ifIndex, ['(']);
        if (null === $ifOpenParenIndex) {
            return;
        }

        // Find the corresponding closing parenthesis
        $ifCloseParenIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $ifOpenParenIndex);

        // Find the opening brace of the if block
        $ifOpenBraceIndex = $tokens->getNextMeaningfulToken($ifCloseParenIndex);
        if (null === $ifOpenBraceIndex || !$tokens[$ifOpenBraceIndex]->equals('{')) {
            return;
        }

        // Get the indentation of the if statement
        $ifIndentation = $this->getIndentationOfToken($tokens, $ifIndex);

        // Find the corresponding closing brace of the if block
        $ifCloseBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $ifOpenBraceIndex);

        // Look for else/elseif statements after the if block
        $nextMeaningfulIndex = $tokens->getNextMeaningfulToken($ifCloseBraceIndex);

        while (null !== $nextMeaningfulIndex && $tokens[$nextMeaningfulIndex]->isGivenKind([T_ELSE, T_ELSEIF])) {
            $this->moveElseToNewLine($tokens, $ifCloseBraceIndex, $nextMeaningfulIndex, $ifIndentation);

            // Find the next block if it exists
            if ($tokens[$nextMeaningfulIndex]->isGivenKind(T_ELSEIF)) {
                // Skip over elseif condition and block
                $elseifOpenParenIndex = $tokens->getNextTokenOfKind($nextMeaningfulIndex, ['(']);
                if (null !== $elseifOpenParenIndex) {
                    $elseifCloseParenIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $elseifOpenParenIndex);
                    $elseifOpenBraceIndex = $tokens->getNextMeaningfulToken($elseifCloseParenIndex);
                    if (null !== $elseifOpenBraceIndex && $tokens[$elseifOpenBraceIndex]->equals('{')) {
                        $ifCloseBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $elseifOpenBraceIndex);
                    }
                }
            } elseif ($tokens[$nextMeaningfulIndex]->isGivenKind(T_ELSE)) {
                // Skip over else block
                $elseOpenBraceIndex = $tokens->getNextMeaningfulToken($nextMeaningfulIndex);
                if (null !== $elseOpenBraceIndex && $tokens[$elseOpenBraceIndex]->equals('{')) {
                    $ifCloseBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $elseOpenBraceIndex);
                }
                // else is the last in the chain, so we can break
                break;
            }

            $nextMeaningfulIndex = $tokens->getNextMeaningfulToken($ifCloseBraceIndex);
        }
    }

    private function moveElseToNewLine(Tokens $tokens, int $closeBraceIndex, int $elseIndex, string $ifIndentation): void
    {
        // Check if else is already on a new line
        $tokensBetween = [];
        for ($i = $closeBraceIndex + 1; $i < $elseIndex; ++$i) {
            if ($tokens[$i]->isWhitespace() || $tokens[$i]->isComment()) {
                $tokensBetween[] = $i;
            }
        }

        // If there are no tokens between or only whitespace without newline, we need to fix it
        $hasNewline = false;
        foreach ($tokensBetween as $tokenIndex) {
            if ($tokens[$tokenIndex]->isWhitespace() && str_contains($tokens[$tokenIndex]->getContent(), "\n")) {
                $hasNewline = true;
                break;
            }
        }

        if ($hasNewline) {
            return; // Already properly formatted
        }

        // Remove all tokens between closing brace and else
        foreach (array_reverse($tokensBetween) as $tokenIndex) {
            $tokens->clearAt($tokenIndex);
        }

        // Insert a newline and proper indentation after the closing brace
        // Use the same indentation as the if statement
        $tokens->insertAt($closeBraceIndex + 1, new Token([T_WHITESPACE, "\n" . $ifIndentation]));
    }

    private function getIndentationOfToken(Tokens $tokens, int $tokenIndex): string
    {
        // Look backwards from the token to find the whitespace before it on the same line
        for ($i = $tokenIndex - 1; $i >= 0; --$i) {
            if ($tokens[$i]->isWhitespace()) {
                $content = $tokens[$i]->getContent();
                if (str_contains($content, "\n")) {
                    // Found newline, extract indentation after the last newline
                    $lines = explode("\n", $content);
                    return end($lines);
                }
            } elseif ($tokens[$i]->isComment()) {
                // Continue looking through comments
                continue;
            } else {
                // Hit a meaningful token, look for whitespace before it
                break;
            }
        }

        return ''; // No indentation found
    }

    public function getPriority(): int
    {
        // Run after the catch_on_new_line fixer (which has priority -32)
        return -33;
    }

    public function getName(): string
    {
        return 'Fullsmack/else_on_new_line';
    }
}
