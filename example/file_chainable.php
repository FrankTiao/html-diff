<?php

/**
 * 链式调用示例
 * Example of chained calls
 */


use htmlDiff\exceptions\InputException;
use htmlDiff\exceptions\MissingParameterException;
use htmlDiff\HtmlDiff;

require __DIR__.'/../vendor/autoload.php';

$old = __DIR__ . "/html/old_file.html";
$new = __DIR__."/html/new_file.html";

try {
    $diff = new HtmlDiff();
    $res = $diff->loadOldFile($old)->loadNewFile($new)->diff();
} catch (MissingParameterException $e) {
    print_r($e->getMessage());die;
} catch (InputException $e) {
    print_r($e->getMessage());die;
}

print_r($res);

