<?php
namespace CrosswordPuzzle;

use CrosswordPuzzle\Analysis\WordAnalysis;

class Builder
{
    private $words = [];
    private $word_analysis = null;
    private $grid = null;

    public function __construct()
    {
        $this->seedWords()
            ->analyzeWords()
            ->buildGrid();
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

    private function buildGrid()
    {
        $this->grid = new Grid();
        $failed_words = [];
        $is_last_fail = false;
        $remaining_words = $this->words;

        while (count($remaining_words) > 0) {
            $word = array_shift($remaining_words);
            echo "attempting word {$word->answer}...\n";
            if ($this->grid->addWord($word)) {
                // success!
                echo "--success\n";
                if ($is_last_fail) {
                    $remaining_words = array_merge($remaining_words, $failed_words);
                    $failed_words = [];
                }
                $is_last_fail = false;
                $this->grid->debug();
            } else {
                // fail
                echo "--fail\n";
                $is_last_fail = true;
                $failed_words[] = $word;
            }
        }

        echo "failed word count = " . count($failed_words) . "\n";

        return $this;
    }
}
