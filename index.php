<?php
require_once 'autoloader.php';

use CrosswordPuzzle\Generator\PuzzleBuilder;

$puzzle = new PuzzleBuilder();

echo '<pre>';
var_dump($puzzle);
