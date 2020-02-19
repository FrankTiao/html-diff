<?php

/**
 * 输出文件链式调用示例（files）
 * Example of chaining output files
 */

use htmlDiff\exceptions\InputException;
use htmlDiff\Exceptions\MissingParameterException;
use htmlDiff\exceptions\OutputException;
use htmlDiff\HtmlDiff;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/html/html_text.php';


$old = __DIR__ . "/html/old_file.html";
$new = __DIR__."/html/new_file.html";

$output = __DIR__.'/html/'.time().'.html';

try {
    $diff = new HtmlDiff();
    $res = $diff->setOutput($output, "UTF-8")
        ->loadOldFile($old)
        ->loadNewFile($new)
        ->diff();

} catch (MissingParameterException $e) {
    print_r($e->getMessage());die;
} catch (InputException $e) {
    print_r($e->getMessage());die;
} catch (OutputException $e) {
    print_r($e->getMessage());die;
}
