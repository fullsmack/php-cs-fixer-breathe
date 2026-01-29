<?php
declare(strict_types=1);

namespace FullSmack\PhpCsFixerBreathe\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class CatchOnNewLine extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Catch statements should be on a new line after the closing brace of the try block.',
            [
                new CodeSample(
                    '<?php
try {
    // some code
} catch (Exception $e)
{
    // handle exception
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_TRY) && $tokens->isTokenKindFound(T_CATCH);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index >= 0; --$index)
        {
            if (!$tokens[$index]->isGivenKind(T_TRY))
            {
                continue;
            }

            $this->fixCatchPosition($tokens, $index);
        }
    }

    private function fixCatchPosition(Tokens $tokens, int $tryIndex): void
    {
        // Find the opening brace of the try block
        $tryOpenBraceIndex = $tokens->getNextMeaningfulToken($tryIndex);

        if (null === $tryOpenBraceIndex || !$tokens[$tryOpenBraceIndex]->equals('{'))
        {
            return;
        }

        // Get the indentation of the try statement
        $tryIndentation = $this->getIndentationOfToken($tokens, $tryIndex);

        // Find the corresponding closing brace of the try block
        $tryCloseBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $tryOpenBraceIndex);

        // Look for catch statements after the try block
        $nextMeaningfulIndex = $tokens->getNextMeaningfulToken($tryCloseBraceIndex);

        while (null !== $nextMeaningfulIndex && $tokens[$nextMeaningfulIndex]->isGivenKind([T_CATCH, T_FINALLY]))
        {
            $this->moveCatchToNewLine($tokens, $tryCloseBraceIndex, $nextMeaningfulIndex, $tryIndentation);

            // Find the next catch/finally block if it exists
            if ($tokens[$nextMeaningfulIndex]->isGivenKind(T_CATCH))
            {
                // Skip over catch parameters and block
                $catchParenIndex = $tokens->getNextTokenOfKind($nextMeaningfulIndex, ['(']);

                if (null !== $catchParenIndex)
                {
                    $catchParenCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $catchParenIndex);
                    $catchOpenBraceIndex = $tokens->getNextMeaningfulToken($catchParenCloseIndex);

                    if (null !== $catchOpenBraceIndex && $tokens[$catchOpenBraceIndex]->equals('{'))
                    {
                        $tryCloseBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $catchOpenBraceIndex);
                    }
                }
            }
            elseif ($tokens[$nextMeaningfulIndex]->isGivenKind(T_FINALLY))
            {
                // Skip over finally block
                $finallyOpenBraceIndex = $tokens->getNextMeaningfulToken($nextMeaningfulIndex);

                if (null !== $finallyOpenBraceIndex && $tokens[$finallyOpenBraceIndex]->equals('{'))
                {
                    $tryCloseBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $finallyOpenBraceIndex);
                }
            }

            $nextMeaningfulIndex = $tokens->getNextMeaningfulToken($tryCloseBraceIndex);
        }
    }

    private function moveCatchToNewLine(Tokens $tokens, int $closeBraceIndex, int $catchIndex, string $tryIndentation): void
    {
        // Check if catch is already on a new line
        $tokensBetween = [];

        for ($i = $closeBraceIndex + 1; $i < $catchIndex; ++$i)
        {
            if ($tokens[$i]->isWhitespace() || $tokens[$i]->isComment())
            {
                $tokensBetween[] = $i;
            }
        }

        // If there are no tokens between or only whitespace without newline, we need to fix it
        $hasNewline = false;

        foreach ($tokensBetween as $tokenIndex)
        {
            if ($tokens[$tokenIndex]->isWhitespace() && str_contains($tokens[$tokenIndex]->getContent(), "\n"))
            {
                $hasNewline = true;
                break;
            }
        }

        if ($hasNewline)
        {
            return; // Already properly formatted
        }

        // Remove all tokens between closing brace and catch
        foreach (array_reverse($tokensBetween) as $tokenIndex)
        {
            $tokens->clearAt($tokenIndex);
        }

        // Insert a newline and proper indentation after the closing brace
        // Use the same indentation as the try statement
        $tokens->insertAt($closeBraceIndex + 1, new Token([T_WHITESPACE, "\n" . $tryIndentation]));
    }

    private function getIndentationOfToken(Tokens $tokens, int $tokenIndex): string
    {
        // Look backwards from the token to find the whitespace before it on the same line
        for ($i = $tokenIndex - 1; $i >= 0; --$i)
        {
            if ($tokens[$i]->isWhitespace())
            {
                $content = $tokens[$i]->getContent();
                if (str_contains($content, "\n"))
                {
                    // Found newline, extract indentation after the last newline
                    $lines = explode("\n", $content);
                    return end($lines);
                }
            } elseif ($tokens[$i]->isComment())
            {
                // Continue looking through comments
                continue;
            }
            else
            {
                // Hit a meaningful token, look for whitespace before it
                break;
            }
        }

        return ''; // No indentation found
    }

    public function getPriority(): int
    {
        // Run after the try_brace_same_line fixer (which has priority -31)
        return -32;
    }

    public function getName(): string
    {
        return 'fullsmack/catch_on_new_line';
    }
}
