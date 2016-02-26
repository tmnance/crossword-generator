<?php
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

    // allow direct public readonly access to certain attributes
    public function __get($attribute)
    {
        $allowed_attributes = ['comparison_word', 'letter_index'];
        return (in_array($attribute, $allowed_attributes) ? $this->$attribute : null);
    }
}
