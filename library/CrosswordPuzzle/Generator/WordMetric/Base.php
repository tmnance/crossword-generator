<?
namespace CrosswordPuzzle\Generator\WordMetric;

use CrosswordPuzzle\Model\Answer;

class Base
{
    private $id = null;
    private $answer = null;
    private $word = null;
    private $character_position_matching_metrics = [];
    private $matching_letters_count = 0;

    public function __construct($id, Answer $answer)
    {
        $this->id = $id;
        $this->answer = $answer;
        $this->word = $answer->word;
    }

    public function processWordAssociations($comparison_word_metrics)
    {
        // compare every letter of self word vs every letter of the other words
        $self_letters = str_split($this->word);
        foreach ($self_letters as $self_letter_position => $self_letter) {
            foreach ($comparison_word_metrics as $comparison_word_metric) {
                if ($comparison_word_metric->id == $this->id) {
                    // skip comparisons against self
                    continue;
                }

                $comparison_letters = str_split($comparison_word_metric->word);
                $matching_letter_positions = array_keys($comparison_letters, $self_letter);
                if (count($matching_letter_positions) > 0) {
                    if (!array_key_exists($self_letter_position, $this->character_position_matching_metrics)) {
                        $this->character_position_matching_metrics[$self_letter_position] = [];
                    }
                    foreach ($matching_letter_positions as $matching_letter_position) {
                        $this->character_position_matching_metrics[$self_letter_position][] = new LetterMatch(
                            $comparison_word_metric,
                            $matching_letter_position
                        );
                    }
                }
            }
        }

        if (empty($this->character_position_matching_metrics)) {
            // this word matches no others, fail
            throw new \Exception('Error generating puzzle :: orphan word "' . $this->word . '"');
        }

        $this->matching_letters_count = count($this->character_position_matching_metrics);
    }

    // allow direct public readonly access to certain attributes
    public function __get($attribute)
    {
        $allowed_attributes = ['id', 'word', 'matching_letters_count'];
        return (in_array($attribute, $allowed_attributes) ? $this->$attribute : null);
    }
}
