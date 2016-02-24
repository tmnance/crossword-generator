<?php
namespace CrosswordPuzzle;

class GridWord
{
    private $pos_x;
    private $pos_y;
    private $orientation;
    private $word;

    public function __construct($pos_x, $pos_y, $orientation, $word)
    {
        $this->pos_x = $pos_x;
        $this->pos_y = $pos_y;
        $this->orientation = $orientation;
        $this->word = $word;
    }

    // allow direct public readonly access to certain attributes
    public function __get($attribute)
    {
        $allowed_attributes = ['pos_x', 'pos_y', 'orientation', 'word'];
        return (in_array($attribute, $allowed_attributes) ? $this->$attribute : null);
    }

    // allow direct public write access to certain attributes
    public function __set($attribute, $value)
    {
        $allowed_attributes = ['pos_x', 'pos_y'];
        if (in_array($attribute, $allowed_attributes)) {
            $this->$attribute = $value;
        }
// echo "set {$this->word->answer}->{$attribute} = {$value}!\n";
        return $this;
    }
}
