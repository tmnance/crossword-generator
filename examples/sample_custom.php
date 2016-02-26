<?php
require_once '../library/autoloader.php';

use CrosswordPuzzle\Builder as PuzzleBuilder;


$words_raw = (array_key_exists('words', $_POST) ? $_POST['words'] : '');
if (!empty($words_raw)) {
    // process form words
    echo '<pre>';
    $words_raw = $_POST['words'];
    // remove garbage chars
    $words_raw = str_replace("\r", '', $words_raw);
    $words_list = explode("\n", $words_raw);
    $words_list = array_flip($words_list);
    // assign filler clues
    foreach ($words_list as $key => $value) {
        $words_list[$key] = "{$key} definition";
    }

    try {

        $puzzle = (new PuzzleBuilder())->setSource($words_list);
        $best_result_grid = $puzzle->getBestScoreGrid();

        echo 'Processing time -- ' . $puzzle->getElapsedTime() . "\n\n";

        $best_result_grid->debug();
    } catch (Exception $e) {
        echo "{$e->getMessage()}\n";
    }

    echo "done\n\n";
    echo '</pre>';
}


echo <<<END
<h3>Words to process</h3>
<form action="sample_custom.php" method="post">
    <p>
        <textarea name="words" style="width: 250px; height: 250px;">{$words_raw}</textarea>
    </p>
    <input type="submit" />
</form>
END;


