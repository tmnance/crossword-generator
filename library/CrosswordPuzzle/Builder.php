<?php
namespace CrosswordPuzzle;

use CrosswordPuzzle\Analysis\WordAnalysis;

class Builder
{
    private $words = [];
    private $word_analysis = null;
    private $start_time = null;
    private $min_grid_dimension_score = null;

    public function __construct()
    {
        $this->start_time = microtime(true);
        $this->seedWords();
echo 'seedWords -- ' . $this->getElapsedTime() . "\n";
        $this->analyzeWords();
echo 'analyzeWords -- ' . $this->getElapsedTime() . "\n";
        $this->buildGrid();
echo 'buildGrid -- ' . $this->getElapsedTime() . "\n";

        $best_grid = Grid::getBestScoreGrid();
        $best_grid->debug();
    }

    private function getElapsedTime()
    {
        return microtime(true) - $this->start_time;
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
        $words = $this->words;
        // shuffle($words);
        // // sort by word score
        // usort(
        //     $words,
        //     function ($a, $b) {
        //         $a_score = $a->getScore();
        //         $b_score = $b->getScore();
        //         if ($a_score == $b_score) {
        //             return 0;
        //         }
        //         return ($a_score > $b_score) ? -1 : 1;
        //     }
        // );
        $this->buildGridFromWordSet($words);

        return $this;
    }

    private function buildGridFromWordSet($words)
    {
        $grid = new Grid();
        $this->processNextGridWordPlacement($grid, $words);
        return $this;
    }

    private function updateMinGridDimensionScore(Grid $grid)
    {
        $dimension_score = $grid->getDimensionScore();
        if (empty($this->min_grid_dimension_score) || $dimension_score < $this->min_grid_dimension_score) {
            $this->min_grid_dimension_score = $dimension_score;
        }
    }

    private function processNextGridWordPlacement(
        Grid $grid,
        array $remaining_words,
        array $failed_words = [],
        $is_last_fail = false
    )
    {
        if (count($remaining_words) == 0) {
            if (count($failed_words) == 0) {
                $this->updateMinGridDimensionScore($grid);
            }
            return;
        }
        if (!empty($this->min_grid_dimension_score) && $grid->getDimensionScore() > $this->min_grid_dimension_score) {
            return;
        }

        $next_word = array_shift($remaining_words);
        $valid_placements = $grid->findValidWordPlacements($next_word);
        if (count($valid_placements) > 0) {
            // success!
            if ($is_last_fail) {
                $remaining_words = array_merge($remaining_words, $failed_words);
                $failed_words = [];
            }
            $is_last_fail = false;
        } else {
            // fail
            $is_last_fail = true;
            $failed_words[] = $next_word;
        }

        if (!$is_last_fail) {
            // prep placement grid copies (if necessary) before we alter them
            $placement_grids = [];
            foreach ($valid_placements as $i => $valid_placement) {
                $placement_grids[] = ($i == 0 ? $grid : clone $grid);
            }

            foreach ($valid_placements as $i => $valid_placement) {
                // each alternate placement choice has its own grid
                $this_grid = $placement_grids[$i];
                $this_grid->insertWordPlacement($valid_placement);

                $this->processNextGridWordPlacement(
                    $this_grid,
                    $remaining_words,
                    $failed_words,
                    $is_last_fail
                );
            }
        } else {
            // not adding a word but keep going anyway just in case there are more words to try
            $this->processNextGridWordPlacement(
                $grid,
                $remaining_words,
                $failed_words,
                $is_last_fail
            );
        }
    }
}
