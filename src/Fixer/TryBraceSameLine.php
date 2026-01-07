<?php
declare(strict_types=1);

namespace FullSmack\PhpCsFixerBreathe\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class TryBraceSameLine extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Opening brace of try statements should be on the same line.',
            [
                new CodeSample(
                    '<?php
try
{
    // some code
} catch (Exception $e) {
    // handle exception
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_TRY);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index >= 0; --$index)
        {
            if (!$tokens[$index]->isGivenKind(T_TRY))
            {
                continue;
            }

            $this->fixTryBrace($tokens, $index);
        }
    }

    private function fixTryBrace(Tokens $tokens, int $tryIndex): void
    {
        // Find the opening brace after the try keyword
        $openBraceIndex = $tokens->getNextMeaningfulToken($tryIndex);

        if (null === $openBraceIndex || !$tokens[$openBraceIndex]->equals('{'))
        {
            return;
        }

        // Check if there are any tokens between the try keyword and opening brace
        $tokensBetween = [];

        for ($i = $tryIndex + 1; $i < $openBraceIndex; ++$i)
        {
            if ($tokens[$i]->isWhitespace() || $tokens[$i]->isComment())
            {
                $tokensBetween[] = $i;
            }
        }

        // If the brace is already on the same line, check if we need to adjust spacing
        if (empty($tokensBetween))
        {
            // Add a single space before the brace if there isn't one
            if (!$tokens[$tryIndex + 1]->isWhitespace())
            {
                $tokens->insertAt($tryIndex + 1, new Token([T_WHITESPACE, ' ']));
            }
            elseif ($tokens[$tryIndex + 1]->getContent() !== ' ')
            {
                // Ensure it's just a single space (not multiple spaces or tabs)
                $tokens[$tryIndex + 1] = new Token([T_WHITESPACE, ' ']);
            }

            return;
        }

        // Remove all tokens between try keyword and opening brace
        foreach (array_reverse($tokensBetween) as $tokenIndex)
        {
            $tokens->clearAt($tokenIndex);
        }

        // Insert a single space
        $tokens->insertAt($tryIndex + 1, new Token([T_WHITESPACE, ' ']));
    }

    public function getPriority(): int
    {
        // Run after braces_position fixer (which has priority -30)
        return -31;
    }

    public function getName(): string
    {
        return 'FullSmack/PhpCsFixerConfig/Fixer/try_brace_same_line';
    }
}
