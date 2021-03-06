<?php

/**
 * 读取文件基本使用方式示例
 * Example of basic usage for reading files
 */

use htmlDiff\exceptions\InputException;
use htmlDiff\exceptions\MissingParameterException;
use htmlDiff\exceptions\OutputException;
use htmlDiff\HtmlDiff;

require __DIR__.'/../vendor/autoload.php';

$old = __DIR__ . "/html/old_file.html";
$new = __DIR__."/html/new_file.html";

try {
    $diff = new HtmlDiff();
    $res = $diff->loadFile($old, $new)->diff();
} catch (MissingParameterException $e) {
    print_r($e->getMessage());die;
} catch (InputException $e) {
    print_r($e->getMessage());die;
} catch (OutputException $e) {
    print_r($e->getMessage());die;
}

print_r($res);
