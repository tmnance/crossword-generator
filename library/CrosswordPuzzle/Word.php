<?php
namespace CrosswordPuzzle;

use CrosswordPuzzle\Analysis\LetterMatch;

class Word
{
    private $answer;
    private $clue;
    private $analysis = null;

    private $character_position_matching_words = [];
    private $matching_answers = [];
    private $matching_letters_count = 0;

    public function __construct($answer, $clue)
    {
        $this->answer = $this->cleanAnswer($answer);
        $this->clue = $clue;
    }

    public function getPositionMatchesForOtherWord(Word $other_word)
    {
        $position_matches = [];
        foreach ($this->character_position_matching_words as $pos => $letter_matches) {
            foreach ($letter_matches as $letter_match) {
                if ($letter_match->comparison_word == $other_word) {
                    $position_matches[] = $pos;
                    break;
                }
            }
        }
        return $position_matches;
    }

    public function getPositionMatchesForLetter($letter)
    {
        $self_letters = str_split($this->answer);
        return array_keys($self_letters, $letter);
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
        $matching_answers = [];

        // compare every letter of self word vs every letter of the other words
        $self_letters = str_split($this->answer);
        foreach ($self_letters as $self_letter_position => $self_letter) {
            foreach ($comparison_words as $comparison_word) {
                if ($comparison_word->answer == $this->answer) {
                    // skip comparisons against self
                    continue;
                }

                $comparison_letters = str_split($comparison_word->answer);
                $matching_letter_positions = array_keys($comparison_letters, $self_letter);
                if (count($matching_letter_positions) > 0) {
                    if (!array_key_exists($self_letter_position, $this->character_position_matching_words)) {
                        $this->character_position_matching_words[$self_letter_position] = [];
                    }
                    foreach ($matching_letter_positions as $matching_letter_position) {
                        $this->character_position_matching_words[$self_letter_position][] = new LetterMatch(
                            $comparison_word,
                            $matching_letter_position
                        );
                        $matching_answers[] = $comparison_word->answer;
                    }
                }
            }
        }

        if (empty($this->character_position_matching_words)) {
            // this word matches no others, fail
            throw new \Exception('Error generating puzzle :: orphan word "' . $this->answer . '"');
        }

        $this->matching_answers = array_unique($matching_answers);
        $this->matching_letters_count = count($this->character_position_matching_words);
    }

    public function debug()
    {
        echo "Word::debug\n";
        echo '-word = ' . $this->answer . "\n";
        echo '-matching_letters_count = ' . $this->matching_letters_count . "\n";
        echo '-matching_answers = ' . count($this->matching_answers) . "\n";
        echo "\n";
    }

    // allow direct public readonly access to certain attributes
    public function __get($attribute)
    {
        $allowed_attributes = ['answer', 'clue', 'analysis', 'matching_letters_count'];
        return (in_array($attribute, $allowed_attributes) ? $this->$attribute : null);
    }
}
