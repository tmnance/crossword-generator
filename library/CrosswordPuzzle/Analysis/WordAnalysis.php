<?php
namespace CrosswordPuzzle\Analysis;

class WordAnalysis
{
    private $words = [];

    private $overall_letter_frequency = [];
    private $per_word_letter_frequency = [];
    private $total_word_length = 0;
    private $min_word_length = 0;
    private $max_word_length = 0;
    private $average_word_length = 0;

    public function __construct($words)
    {
        $this->words = $words;
        $this->analyzeWords();
// $this->debug();
    }

    private function analyzeWords()
    {
        $this->processWordAssociations()
            ->processLetterFrequency()
            ->processWordLengths();
    }


    private function processWordAssociations()
    {
        // compare everything to everything
        foreach ($this->words as $word) {
            $word->processWordAssociations($this->words);
// $word->debug();
        }
        return $this;
    }

    private function processLetterFrequency()
    {
        // compare everything to everything
        foreach ($this->words as $word) {
            $answer = $word->answer;
            // increase counter for each unique letter
            $unique_letters = count_chars($answer, 1);

            foreach ($unique_letters as $letter_ascii => $frequency) {
                $letter = chr($letter_ascii);

                if (!array_key_exists($letter, $this->overall_letter_frequency)) {
                    $this->overall_letter_frequency[$letter] = 0;
                }
                $this->overall_letter_frequency[$letter] += $frequency;

                if (!array_key_exists($letter, $this->per_word_letter_frequency)) {
                    $this->per_word_letter_frequency[$letter] = 0;
                }
                $this->per_word_letter_frequency[$letter]++;
            }
        }
        return $this;
    }

    private function processWordLengths()
    {
        // should be overridden
        $this->min_word_length = 99;
        foreach ($this->words as $word) {
            $answer = $word->answer;
            $word_length = strlen($answer);
            $this->total_word_length += $word_length;
            $this->min_word_length = min($word_length, $this->min_word_length);
            $this->max_word_length = max($word_length, $this->max_word_length);
        }
        $this->average_word_length = $this->total_word_length / count($this->words);
        return $this;
    }

    public function debug()
    {
        echo "WordAnalysis::debug\n";
        echo '-total_word_length = ' . $this->total_word_length . "\n";
        echo '-min_word_length = ' . $this->min_word_length . "\n";
        echo '-max_word_length = ' . $this->max_word_length . "\n";
        echo '-average_word_length = ' . $this->average_word_length . "\n";
        echo "\n";
    }
}
