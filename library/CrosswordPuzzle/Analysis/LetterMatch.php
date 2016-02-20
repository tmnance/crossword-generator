<?
namespace CrosswordPuzzle\Analysis;

use CrosswordPuzzle\Word;

class LetterMatch
{
    private $comparison_word = null;
    private $letter_index = null;

    public function __construct(Word $comparison_word, $letter_index)
    {
        $this->comparison_word = $comparison_word;
        $this->letter_index = $letter_index;
    }
}
