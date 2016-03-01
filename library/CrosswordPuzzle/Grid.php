<?php
namespace CrosswordPuzzle;

class Grid
{
    const ORIENTATION_HORIZONTAL = 'horizontal';
    const ORIENTATION_VERTICAL = 'vertical';
    const POSITION_OFFGRID = 'offgrid';

    private $grid_matrix = [];
    private $inserted_words = [];
    private $interception_count = 0;

    private static $all_grids = [];

    public function __construct()
    {
        self::$all_grids[] = $this;
    }

    public function __clone()
    {
        self::$all_grids[] = $this;
    }

    public static function getBestScoreGrid()
    {
        $best_grid = null;
        $best_score = -100000000000;
        foreach (self::$all_grids as $grid) {
            $score = $grid->getScore();
            if ($score > $best_score) {
                $best_grid = $grid;
                $best_score = $score;
            }
        }
        return $best_grid;
    }

    public function findValidWordPlacements(Word $insert_word)
    {
        $is_added = false;
        $valid_placements = [];

        if (empty($this->inserted_words)) {
            // first word, insert horizontally
            $valid_placements[] = new GridWordPlacement(
                $insert_word,
                0,
                0,
                self::ORIENTATION_HORIZONTAL
            );
        } else {
            // try to fit next to an existing word
            foreach ($this->inserted_words as $grid_word) {
                $comparison_word = $grid_word->word;
                $comparison_position_matches = $comparison_word->getPositionMatchesForOtherWord($insert_word);
                $insert_orientation = $this->getOppositeOrientation($grid_word->orientation);

                foreach ($comparison_position_matches as $comparison_match_pos) {
                    $base_insert_x = $this->getXOffset(
                        $grid_word->x,
                        $comparison_match_pos,
                        $grid_word->orientation
                    );
                    $base_insert_y = $this->getYOffset(
                        $grid_word->y,
                        $comparison_match_pos,
                        $grid_word->orientation
                    );

                    // get the corresponding x,y letter
                    $coordinate_letter = $this->getLetterAtCoordinates($base_insert_x, $base_insert_y);
                    // new word position matches for the coordinate letter
                    $insert_word_position_matches = $insert_word->getPositionMatchesForLetter($coordinate_letter);

                    foreach ($insert_word_position_matches as $insert_match_pos) {
                        $insert_x = $this->getXOffset(
                            $base_insert_x,
                            $insert_match_pos * -1,
                            $insert_orientation
                        );
                        $insert_y = $this->getYOffset(
                            $base_insert_y,
                            $insert_match_pos * -1,
                            $insert_orientation
                        );

                        $does_fit = $this->checkWordFitAtCoordinates(
                            $insert_word,
                            $insert_x,
                            $insert_y,
                            $insert_orientation
                        );
                        if ($does_fit) {
                            $valid_placements[] = new GridWordPlacement(
                                $insert_word,
                                $insert_x,
                                $insert_y,
                                $insert_orientation
                            );
                        }
                    }
                }
            }
        }

        return $valid_placements;
    }

    // adding new word attached to a specific existing x,y coordinate that we know has a letter match
    public function insertWordPlacement(GridWordPlacement $word_placement)
    {
        $word = $word_placement->word;
        $insert_x = $word_placement->x;
        $insert_y = $word_placement->y;
        $orientation = $word_placement->orientation;

        // we know this word fits into this position with no letter conflicts
        $grid_dim_x = $this->getDimX();
        $grid_dim_y = $this->getDimY();
        $grid_offset_x = 0;
        $grid_offset_y = 0;
        $word_length = strlen($word->answer);
        $insert_letters = str_split($word->answer);

        // grid resize checks
        if ($insert_x < 0) {
            $grid_offset_x = $insert_x * -1;
            $grid_dim_x += $grid_offset_x;
            $insert_x = 0;
        }
        if ($insert_y < 0) {
            $grid_offset_y = $insert_y * -1;
            $grid_dim_y += $grid_offset_y;
            $insert_y = 0;
        }

        $end_x = $this->getXOffset(
            $insert_x,
            $word_length,
            $orientation
        );
        $end_y = $this->getYOffset(
            $insert_y,
            $word_length,
            $orientation
        );

        $grid_dim_x = max($end_x, $grid_dim_x, 1);
        $grid_dim_y = max($end_y, $grid_dim_y, 1);

        // will attempt to insert in expanded grid that will fit the new word
        $this->expandGrid($grid_dim_x, $grid_dim_y, $grid_offset_x, $grid_offset_y);

        foreach ($insert_letters as $pos => $insert_letter) {
            $letter_x = $this->getXOffset(
                $insert_x,
                $pos,
                $orientation
            );
            $letter_y = $this->getYOffset(
                $insert_y,
                $pos,
                $orientation
            );

            $this->setLetterAtCoordinates(
                $insert_letter,
                $letter_x,
                $letter_y
            );
        }

        // update in case changed
        $word_placement->x = $insert_x;
        $word_placement->y = $insert_y;

        // add word
        $this->inserted_words[] = $word_placement;

        return $this;
    }

    private function getXOffset($x, $offset, $orientation)
    {
        if ($orientation == self::ORIENTATION_HORIZONTAL) {
            $x += $offset;
        }
        return $x;
    }

    private function getYOffset($y, $offset, $orientation)
    {
        if ($orientation == self::ORIENTATION_VERTICAL) {
            $y += $offset;
        }
        return $y;
    }

    // check if word fits at an existing x,y coordinate that we know has a letter match
    private function checkWordFitAtCoordinates($word, $base_x, $base_y, $orientation)
    {
        $does_fit = true;
        $word_letters = str_split($word->answer);
        $empty_letters = [null, self::POSITION_OFFGRID];

        foreach ($word_letters as $pos => $letter) {
            $test_x = $this->getXOffset(
                $base_x,
                $pos,
                $orientation
            );
            $test_y = $this->getYOffset(
                $base_y,
                $pos,
                $orientation
            );

            $coordinate_letter = $this->getLetterAtCoordinates($test_x, $test_y);
            $is_intersection = ($coordinate_letter == $letter);

            if (!in_array($coordinate_letter, $empty_letters) && !$is_intersection) {
                $does_fit = false;
                break;
            } elseif ($coordinate_letter != $letter) {
                // additional spacing check when not intersecting
                //   (don't want different words touching in same orientation)
                foreach ([-1, 1] as $dir) {
                    $adjacent_test_x = $this->getXOffset(
                        $test_x,
                        $dir,
                        $this->getOppositeOrientation($orientation)
                    );
                    $adjacent_test_y = $this->getYOffset(
                        $test_y,
                        $dir,
                        $this->getOppositeOrientation($orientation)
                    );

                    $adjacent_letter = $this->getLetterAtCoordinates(
                        $adjacent_test_x,
                        $adjacent_test_y
                    );
                    if (!in_array($adjacent_letter, $empty_letters)) {
                        // bad match
                        $does_fit = false;
                        break 2;
                    }
                }
            }
        }

        // lastly, verify the letters before and after the word are empty
        if ($does_fit) {
            $word_length = strlen($word->answer);
            foreach ([-1, $word_length] as $pos) {
                $test_x = $this->getXOffset(
                    $base_x,
                    $pos,
                    $orientation
                );
                $test_y = $this->getYOffset(
                    $base_y,
                    $pos,
                    $orientation
                );
                $coordinate_letter = $this->getLetterAtCoordinates(
                    $test_x,
                    $test_y
                );

                if (!in_array($coordinate_letter, $empty_letters)) {
                    // bad match
                    $does_fit = false;
                    break;
                }
            }
        }

        return $does_fit;
    }

    private function expandGrid($new_x, $new_y, $offset_x = 0, $offset_y = 0)
    {
        $dim_x = $this->getDimX();
        $dim_y = $this->getDimY();
        $grid_matrix = $this->grid_matrix;
        if ($new_x > $dim_x) {
            // dimension x resize
            $empty_col = null;
            foreach ($grid_matrix as &$row) {
                // modify each existing row with pre/post col padding
                if ($offset_x > 0) {
                    // pad before
                    $row = array_pad($row, (-1 * ($dim_x + $offset_x)), $empty_col);
                }
                // pad after
                $row = array_pad($row, $new_x, $empty_col);
            }
        }
        if ($new_y > $dim_y) {
            // dimension y resize
            $empty_row = array_pad([], $new_x, null);
            if ($offset_y > 0) {
                // pad before
                $grid_matrix = array_pad($grid_matrix, (-1 * ($dim_y + $offset_y)), $empty_row);
            }
            // pad after
            $grid_matrix = array_pad($grid_matrix, $new_y, $empty_row);
        }
        $this->grid_matrix = $grid_matrix;

        // update grid word pointers
        $this->offsetAllWords($offset_x, $offset_y);

        return $this;
    }

    private function offsetAllWords($offset_x, $offset_y)
    {
        if ($offset_x > 0 || $offset_y > 0) {
            foreach ($this->inserted_words as $grid_word) {
                if ($offset_x > 0) {
                    $grid_word->x = $grid_word->x + $offset_x;
                }
                if ($offset_y > 0) {
                    $grid_word->y = $grid_word->y + $offset_y;
                }
            }
        }
        return $this;
    }

    private function getLetterAtCoordinates($x, $y)
    {
        if ($x < 0 || $y < 0 || $x >= $this->getDimX() || $y >= $this->getDimY()) {
            return self::POSITION_OFFGRID;
        }
        return $this->grid_matrix[$y][$x] ?: null;
    }

    private function setLetterAtCoordinates($letter, $x, $y)
    {
        if ($this->grid_matrix[$y][$x] == $letter) {
            // interception count will used for scoring
            $this->interception_count++;
        } else {
            $this->grid_matrix[$y][$x] = $letter;
        }
        return $this;
    }

    private function getOppositeOrientation($orientation)
    {
        if ($orientation == self::ORIENTATION_HORIZONTAL) {
            return self::ORIENTATION_VERTICAL;
        } else {
            return self::ORIENTATION_HORIZONTAL;
        }
    }

    private function getDimX()
    {
        return (empty($this->grid_matrix) ? 0 : count($this->grid_matrix[0]));
    }

    private function getDimY()
    {
        return count($this->grid_matrix);
    }

    public function debug()
    {
        $dim_x = $this->getDimX();
        $dim_y = $this->getDimY();

        echo "Grid::debug\n";
        // has content
        if ($dim_y > 0) {
            $pos_x_list = range(0, $dim_x - 1);
            // pad each
            $pos_x_list = array_map(
                function ($value) {
                    return str_pad($value, 3);
                },
                $pos_x_list
            );

            $grid_box_top_bottom_border_row = '+' . str_repeat('---', $dim_x + 4) . "-+\n";
            $grid_legend_x_row = '|       ' . implode('', $pos_x_list) . "      |\n";
            $grid_spacer_row = '|' . str_repeat('   ', $dim_x + 4) . " |\n";

            echo $grid_box_top_bottom_border_row;
            echo $grid_legend_x_row;
            echo $grid_spacer_row;

            foreach ($this->grid_matrix as $pos_y => $row) {
                $pos_y = str_pad($pos_y, 3);
                // treat null cols as spaces
                $row = array_map(
                    function ($value) {
                        return ($value ?: ' ') . '  ';
                    },
                    $row
                );
                echo "| {$pos_y}   " . implode('', $row) . "   {$pos_y}|\n";
                echo $grid_spacer_row;
            }

            echo $grid_legend_x_row;
            echo $grid_box_top_bottom_border_row;

            echo "STATISTICS:\n";
            $stats = array_merge(
                $this->getMetrics(),
                [
                    'dimension_score' => $this->getDimensionScore(),
                    'total_score' => $this->getScore(),
                ]
            );

            foreach ($stats as $key => $value) {
                echo "-{$key}: {$value}\n";
            }
        }

        echo "\n";
    }

    private function getMetrics()
    {
        $dim_x = $this->getDimX();
        $dim_y = $this->getDimY();
        return [
            'word_count' => count($this->inserted_words),
            'dim_size' => ($dim_x * $dim_y),
            // smaller the better (minimum 1)
            'dim_aspect_ratio' => (max($dim_x, $dim_y) / (min($dim_x, $dim_y) ?: 1)),
            'interception_count' => $this->interception_count,
            'interception_bonus' => 1 + $this->interception_count - count($this->inserted_words),
        ];
    }

    public function getDimensionScore()
    {
        // smaller the better
        $metrics = $this->getMetrics();

        $dimension_score = $metrics['dim_size'] - $metrics['interception_bonus'] * 25;
        $dimension_score *= $metrics['dim_aspect_ratio'];
        return 10000 + $dimension_score;
    }

    public function getScore($output = false)
    {
        // higher is better
        // word count is by far most important
        $score = count($this->inserted_words) * 100000 - $this->getDimensionScore();

        return intval($score);
    }
}
