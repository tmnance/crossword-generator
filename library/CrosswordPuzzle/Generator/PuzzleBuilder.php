<?php
namespace CrosswordPuzzle\Generator;

use CrosswordPuzzle\Model\Answer;

class PuzzleBuilder
{
    private $answers = [];
    private $word_analysis = null;

    public function __construct()
    {
        $this->seedAnswers();
        $this->analyzeAnswers();
    }

    private function seedAnswers()
    {
        // sample config sourced from https://www.randomlists.com/random-words
        $puzzle_json = file_get_contents('./config/sample_puzzle.json');
        $puzzle_data = json_decode($puzzle_json);

        foreach ($puzzle_data as $word => $clue) {
            $this->answers[] = new Answer($word, $clue);
        }
        return $this;
    }

    private function analyzeAnswers()
    {
        $this->word_analysis = new WordAnalysis($this->answers);
        return $this;
    }
}
