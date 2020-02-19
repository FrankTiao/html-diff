<?php

/**
 * 读取文件基本使用方式示例
 * Example of basic usage for reading files
 */

use htmlDiff\exceptions\InputException;
use htmlDiff\Exceptions\MissingParameterException;
use htmlDiff\HtmlDiff;

require __DIR__.'/../vendor/autoload.php';

$old = __DIR__."/html/PHP_7.3.14 _changeLog.html";
$new = __DIR__."/html/PHP_7.4.0_changeLog.html";

try {
    $diff = new HtmlDiff();
    $res = $diff->loadHtml($old, $new)->diff();
} catch (MissingParameterException $e) {
    print_r($e->getMessage());die;
} catch (InputException $e) {
    print_r($e->getMessage());die;
}

print_r($res);