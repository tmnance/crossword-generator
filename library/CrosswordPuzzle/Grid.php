<?php
namespace CrosswordPuzzle;

class Grid
{
    const ORIENTATION_HORIZONTAL = 'horizontal';
    const ORIENTATION_VERTICAL = 'vertical';
    const POSITION_OFFGRID = 'offgrid';

    private $grid_content = [];
    private $grid_words = [];

    public function __construct()
    {
    }

    public function addWord(Word $insert_word)
    {
        $is_added = false;

        if (empty($this->grid_words)) {
            // first word, insert horizontally
            $is_added = $this->addWordAtCoordinates(
                $insert_word,
                0,
                0,
                self::ORIENTATION_HORIZONTAL
            );
        } else {
            // try to fit next to an existing word

            foreach ($this->grid_words as $grid_word) {
                $comparison_word = $grid_word->word;
                $comparison_position_matches = $comparison_word->getPositionMatchesForOtherWord($insert_word);
                $insert_orientation = $this->getOppositeOrientation($grid_word->orientation);

                foreach ($comparison_position_matches as $comparison_match_pos) {
                    $base_insert_x = $this->getXOffset(
                        $grid_word->pos_x,
                        $comparison_match_pos,
                        $grid_word->orientation
                    );
                    $base_insert_y = $this->getYOffset(
                        $grid_word->pos_y,
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

                        $is_added = $this->addWordAtCoordinates(
                            $insert_word,
                            $insert_x,
                            $insert_y,
                            $insert_orientation
                        );
                        if ($is_added) {
                            // word successfully inserted
                            break 3;
                        }
                    }
                }
            }
        }

        return $is_added;
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
            $bad_letters = array_merge($empty_letters, [$letter]);

            if (!in_array($coordinate_letter, $bad_letters)) {
                $does_fit = false;
                break;
            } else {
                // additional spacing check (don't want different words touching in same orientation)
                foreach ([-1, 1] as $dir) {
                    $adjacent_filled_num = 0;
                    foreach ([0, 1] as $incr) {
                        $adjacent_test_x = $test_x;
                        $adjacent_test_y = $test_x;
                        switch ($orientation) {
                            case self::ORIENTATION_VERTICAL:
                                $adjacent_test_x += $dir;
                                $adjacent_test_y += $incr;
                                break;
                            default:
                                $adjacent_test_x += $incr;
                                $adjacent_test_y += $dir;
                                break;
                        }

                        $adjacent_letter = $this->getLetterAtCoordinates(
                            $adjacent_test_x,
                            $adjacent_test_y
                        );
                        if (!in_array($adjacent_letter, $empty_letters)) {
                            $adjacent_filled_num++;
                        }
                    }
                    if ($adjacent_filled_num == 2) {
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

    // adding new word attached to a specific existing x,y coordinate that we know has a letter match
    private function addWordAtCoordinates($word, $insert_x, $insert_y, $orientation = self::ORIENTATION_HORIZONTAL)
    {
        $does_fit = $this->checkWordFitAtCoordinates($word, $insert_x, $insert_y, $orientation);
        if ($does_fit) {
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

            // add word
            $this->grid_words[] = new GridWord(
                $insert_x,
                $insert_y,
                $orientation,
                $word
            );
            return true;
        } else {
            return false;
        }
    }

    private function expandGrid($new_x, $new_y, $offset_x = 0, $offset_y = 0)
    {
        $dim_x = $this->getDimX();
        $dim_y = $this->getDimY();
        $grid_content = $this->grid_content;
        if ($new_x > $dim_x) {
            // dimension x resize
            $empty_col = null;
            foreach ($grid_content as &$row) {
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
                $grid_content = array_pad($grid_content, (-1 * ($dim_y + $offset_y)), $empty_row);
            }
            // pad after
            $grid_content = array_pad($grid_content, $new_y, $empty_row);
        }
        $this->grid_content = $grid_content;

        // update grid word pointers
        $this->offsetAllWords($offset_x, $offset_y);

        return $this;
    }

    private function offsetAllWords($offset_x, $offset_y)
    {
        if ($offset_x > 0 || $offset_y) {
            foreach ($this->grid_words as $grid_word) {
                if ($offset_x > 0) {
                    $grid_word->pos_x = $grid_word->pos_x + $offset_x;
                }
                if ($offset_y > 0) {
                    $grid_word->pos_y = $grid_word->pos_y + $offset_y;
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
        return $this->grid_content[$y][$x] ?: null;
    }

    private function setLetterAtCoordinates($letter, $x, $y)
    {
        $this->grid_content[$y][$x] = $letter;
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
        return (empty($this->grid_content) ? 0 : count($this->grid_content[0]));
    }

    private function getDimY()
    {
        return count($this->grid_content);
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
                    return str_pad($value, 2);
                },
                $pos_x_list
            );

            $grid_box_top_bottom_border_row = '+' . str_repeat('--', $dim_x + 4) . "-+\n";
            $grid_legend_x_row = '|     ' . implode('', $pos_x_list) . "    |\n";
            $grid_spacer_row = '|' . str_repeat('  ', $dim_x + 4) . " |\n";

            echo $grid_box_top_bottom_border_row;
            echo $grid_legend_x_row;
            echo $grid_spacer_row;

            foreach ($this->grid_content as $pos_y => $row) {
                $pos_y = str_pad($pos_y, 2);
                // treat null cols as spaces
                $row = array_map(
                    function ($value) {
                        return ($value ?: ' ') . ' ';
                    },
                    $row
                );
                echo "| {$pos_y}  " . implode('', $row) . "  {$pos_y}|\n";
                echo $grid_spacer_row;
            }

            echo $grid_legend_x_row;
            echo $grid_box_top_bottom_border_row;
        }
        echo "\n";
    }
}