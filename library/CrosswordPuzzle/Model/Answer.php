<?php
namespace CrosswordPuzzle\Model;

class Answer
{
    private $word;
    private $clue;

    public function __construct($word, $clue)
    {
        $this->word = $word;
        $this->clue = $clue;
    }
}
