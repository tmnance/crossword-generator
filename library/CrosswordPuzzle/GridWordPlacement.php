<?php
namespace CrosswordPuzzle;

class GridWordPlacement
{
    private $word;
    private $x;
    private $y;
    private $orientation;

    public function __construct(Word $word, $x, $y, $orientation)
    {
        $this->word = $word;
        $this->x = $x;
        $this->y = $y;
        $this->orientation = $orientation;
    }

    // allow direct public readonly access to certain attributes
    public function __get($attribute)
    {
        $allowed_attributes = ['word', 'x', 'y', 'orientation'];
        return (in_array($attribute, $allowed_attributes) ? $this->$attribute : null);
    }

    // allow direct public write access to certain attributes
    public function __set($attribute, $value)
    {
        $allowed_attributes = ['x', 'y'];
        if (in_array($attribute, $allowed_attributes)) {
            $this->$attribute = $value;
        }
        return $this;
    }
}
