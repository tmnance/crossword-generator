<?php
namespace CrosswordPuzzle\Model;

class Answer
{
    private $word;
    private $clue;

    public function __construct($word, $clue)
    {
        $this->word = $this->cleanWord($word);
        $this->clue = $clue;
    }

    private function cleanWord($word)
    {
        // o'brien to OBRIEN
        $word = preg_replace('/[^A-Z]/', '', strtoupper($word));
        return $word;
    }

    // allow direct public readonly access to certain attributes
    public function __get($attribute)
    {
        $allowed_attributes = ['word', 'clue'];
        return (in_array($attribute, $allowed_attributes) ? $this->$attribute : null);
    }
}
