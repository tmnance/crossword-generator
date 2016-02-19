<?php
namespace CrosswordPuzzle\Generator;

use CrosswordPuzzle\Generator\WordMetric\Base as WordMetric;

class WordAnalysis
{
    private $word_metrics = [];
    private $word_associations = [];

    private $overall_letter_frequency = [];
    private $per_word_letter_frequency = [];
    private $total_word_length = 0;
    private $min_word_length = 0;
    private $max_word_length = 0;
    private $average_word_length = 0;

    public function __construct($answers)
    {
        foreach ($answers as $i => $answer) {
            $this->word_metrics[] = new WordMetric($i, $answer);
        }
        $this->analyzeWords();
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
        foreach ($this->word_metrics as $word_metric) {
            $word_metric->processWordAssociations($this->word_metrics);
        }
        return $this;
    }

    private function processLetterFrequency()
    {
        // compare everything to everything
        foreach ($this->word_metrics as $word_metric) {
            $word = $word_metric->word;
            // increase counter for each unique letter
            $unique_letters = count_chars($word, 1);

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
        foreach ($this->word_metrics as $word_metric) {
            $word = $word_metric->word;
            $word_length = strlen($word);
            $this->total_word_length += $word_length;
            $this->min_word_length = min($word_length, $this->min_word_length);
            $this->max_word_length = max($word_length, $this->max_word_length);
        }
        $this->average_word_length = $this->total_word_length / count($this->word_metrics);
        return $this;
    }

    public function debug()
    {
        var_dump('total_word_length', $this->total_word_length);
        var_dump('min_word_length', $this->min_word_length);
        var_dump('max_word_length', $this->max_word_length);
        var_dump('average_word_length', $this->average_word_length);
    }
}
