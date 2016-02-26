<?php
require_once '../autoloader.php';

use CrosswordPuzzle\Builder as PuzzleBuilder;

echo '<pre>';
$puzzle = (new PuzzleBuilder())->setSource('config/sample_puzzle2.json');
$best_result_grid = $puzzle->getBestScoreGrid();

echo 'Processing time -- ' . $puzzle->getElapsedTime() . "\n\n";

$best_result_grid->debug();

echo 'done';
echo '</pre>';
