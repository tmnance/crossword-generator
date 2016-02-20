<?php
namespace CrosswordPuzzle;

use CrosswordPuzzle\Analysis\LetterMatch;

class Word
{
    private $answer;
    private $clue;
    private $analysis = null;

    private $character_position_matching_words = [];
    private $matching_letters_count = 0;

    public function __construct($answer, $clue)
    {
        $this->answer = $this->cleanAnswer($answer);
        $this->clue = $clue;
    }

    private function cleanAnswer($answer)
    {
        // o'brien to OBRIEN
        $answer = preg_replace('/[^A-Z]/', '', strtoupper($answer));
        return $answer;
    }

    public function processWordAssociations($comparison_words)
    {
        $letter_match_count = 0;
        $matching_ids = [];

        // compare every letter of self word vs every letter of the other words
        $self_letters = str_split($this->answer);
        foreach ($self_letters as $self_letter_position => $self_letter) {
            $has_letter_match = false;
            foreach ($comparison_words as $comparison_word) {
                if ($comparison_word->answer == $this->answer) {
                    // skip comparisons against self
                    continue;
                }

                $comparison_letters = str_split($comparison_word->answer);
                $matching_letter_positions = array_keys($comparison_letters, $self_letter);
                if (count($matching_letter_positions) > 0) {
                    $has_letter_match = true;
                    if (!array_key_exists($self_letter_position, $this->character_position_matching_words)) {
                        $this->character_position_matching_words[$self_letter_position] = [];
                    }
                    foreach ($matching_letter_positions as $matching_letter_position) {
                        $this->character_position_matching_words[$self_letter_position][] = new LetterMatch(
                            $comparison_word,
                            $matching_letter_position
                        );
                        $matching_ids[] = $comparison_word->answer;
                    }
                }
            }
            if ($has_letter_match) {
                $letter_match_count++;
            }
        }


        $matching_ids = array_unique($matching_ids);
echo $this->answer . "\n";
echo 'letter_match_count = ' . $letter_match_count . "\n";
echo 'matching_ids = ' . count($matching_ids) . "\n";
echo "\n";


// $this->getLetterMatchRelationCount();

        if (empty($this->character_position_matching_words)) {
            // this word matches no others, fail
            throw new \Exception('Error generating puzzle :: orphan word "' . $this->answer . '"');
        }

        $this->matching_letters_count = count($this->character_position_matching_words);
    }

    public function getLetterMatchRelationCount()
    {
        $matching_ids = [];
        // each position has it's own potential matches, get the sum of the combined every position matches
        foreach ($this->character_position_matching_words as $word) {
            $matching_ids = array_merge($matching_ids, array_keys($word));
        }
        $matching_ids = array_unique($matching_ids);
var_dump($matching_ids);

    }

    // allow direct public readonly access to certain attributes
    public function __get($attribute)
    {
        $allowed_attributes = ['answer', 'clue', 'analysis', 'matching_letters_count'];
        return (in_array($attribute, $allowed_attributes) ? $this->$attribute : null);
    }
}
