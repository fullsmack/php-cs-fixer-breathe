<?php
declare(strict_types=1);

namespace FullSmack\PhpCsFixerBreathe\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class MatchBraceSameLine extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Opening brace of match expressions should be on the same line.',
            [
                new CodeSample(
                    '<?php
$result = match ($value)
{
    1 => "one",
    2 => "two",
};
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_MATCH);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index >= 0; --$index)
        {
            if (!$tokens[$index]->isGivenKind(T_MATCH))
            {
                continue;
            }

            $this->fixMatchBrace($tokens, $index);
        }
    }

    private function fixMatchBrace(Tokens $tokens, int $matchIndex): void
    {
        // Find the opening parenthesis of the match condition
        $openParenIndex = $tokens->getNextTokenOfKind($matchIndex, ['(']);
        if (null === $openParenIndex)
        {
            return;
        }

        // Find the corresponding closing parenthesis
        $closeParenIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenIndex);

        // Find the opening brace after the closing parenthesis
        $openBraceIndex = $tokens->getNextMeaningfulToken($closeParenIndex);
        if (null === $openBraceIndex || !$tokens[$openBraceIndex]->equals('{'))
        {
            return;
        }

        // Check if there are any tokens between the closing parenthesis and opening brace
        $tokensBetween = [];
        for ($i = $closeParenIndex + 1; $i < $openBraceIndex; ++$i)
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
            if (!$tokens[$closeParenIndex + 1]->isWhitespace())
            {
                $tokens->insertAt($closeParenIndex + 1, new Token([T_WHITESPACE, ' ']));
            } else {
                // Ensure it's just a single space
                $tokens[$closeParenIndex + 1] = new Token([T_WHITESPACE, ' ']);
            }
            return;
        }

        // Remove all tokens between closing paren and opening brace
        foreach (array_reverse($tokensBetween) as $tokenIndex)
        {
            $tokens->clearAt($tokenIndex);
        }

        // Insert a single space
        $tokens->insertAt($closeParenIndex + 1, new Token([T_WHITESPACE, ' ']));
    }

    public function getPriority(): int
    {
        // Run after braces_position fixer (which has priority -30)
        return -31;
    }

    public function getName(): string
    {
        return 'fullsmack/match_brace_same_line';
    }
}
