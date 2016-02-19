<?php
namespace CrosswordPuzzle\Generator;

class PuzzleBuilder
{
    private $answer_collection = null;

    public function __construct()
    {
        $this->answer_collection = new PuzzleAnswerCollection();
        $this->seedAnswers();
    }

    private function seedAnswers()
    {
        // sample config sourced from https://www.randomlists.com/random-words
        $puzzle_json = file_get_contents('./config/sample_puzzle.json');
        $puzzle_data = json_decode($puzzle_json);

        foreach ($puzzle_data as $word => $clue) {
            $this->answer_collection->add($word, $clue);
        }
        return $this;
    }
}
