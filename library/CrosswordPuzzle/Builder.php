<?php
namespace CrosswordPuzzle;

use CrosswordPuzzle\Analysis\WordAnalysis;

class Builder
{
    private $words = [];
    private $word_analysis = null;

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
        return $this->buildGridFromWordSet($this->words);
    }

    private function buildGridFromWordSet($words)
    {
        $grid = new Grid();

        $this->addNextWordPlacementToGrid($grid, $words);

        return $this;
    }

    private function addNextWordPlacementToGrid(
        Grid $grid,
        array $remaining_words,
        array $failed_words = [],
        $is_last_fail = false
    )
    {
        $next_word = array_shift($remaining_words);
echo "attempting word {$next_word->answer}...\n";

        $valid_placements = $grid->findValidWordPlacements($next_word);
echo "--matching placement count: " . count($valid_placements) . "\n";

        if (count($valid_placements) > 0) {
            $grid->insertWordPlacement($valid_placements[0]);
            // success!
echo "--success\n";
            if ($is_last_fail) {
                $remaining_words = array_merge($remaining_words, $failed_words);
                $failed_words = [];
            }
            $is_last_fail = false;
$grid->debug();
        } else {
            // fail
echo "--fail\n";
            $is_last_fail = true;
            $failed_words[] = $next_word;
        }

        if (count($remaining_words) > 0) {
            $this->addNextWordPlacementToGrid(
                $grid,
                $remaining_words,
                $failed_words,
                $is_last_fail
            );
        }
    }
}
