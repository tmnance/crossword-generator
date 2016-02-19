<?php
namespace CrosswordPuzzle\Generator;

use CrosswordPuzzle\Model\Answer;

class PuzzleAnswerCollection
{
    private $answers = null;

    public function add($word, $clue)
    {
        $this->answers[] = new Answer($word, $clue);
        return $this;
    }
}
