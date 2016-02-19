<?
namespace CrosswordPuzzle\Generator\WordMetric;

class LetterMatch
{
    private $comparison_word_metric = null;
    private $letter_index = null;

    public function __construct(Base $comparison_word_metric, $letter_index)
    {
        $this->comparison_word_metric = $comparison_word_metric;
        $this->letter_index = $letter_index;
    }
}
