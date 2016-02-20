<?php
namespace CrosswordPuzzle;

use CrosswordPuzzle\Analysis\WordAnalysis;

class Builder
{
    private $words = [];
    private $word_analysis = null;

    public function __construct()
    {
        $this->seedWords();
        $this->analyzeWords();
    }

    private function seedWords()
    {
        // sample config sourced from https://www.randomlists.com/random-words
        $puzzle_json = file_get_contents('./config/sample_puzzle2.json');
        $puzzle_data = json_decode($puzzle_json);

        foreach ($puzzle_data as $answer => $clue) {
            $this->words[] = new Word($answer, $clue);
        }
        return $this;
    }

    private function analyzeWords()
    {
        $this->word_analysis = new WordAnalysis($this->words);
        return $this;
    }
}
