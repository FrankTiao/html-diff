<?php


/**
 * 基本使用方式示例
 * Example of basic usage
 */

use htmlDiff\exceptions\MissingParameterException;
use htmlDiff\HtmlDiff;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/html/html_text.php';


try {
    $diff = new HtmlDiff();
    $res = $diff->setUnilateralTags(["img", ""])->diff($old, $new);
} catch (MissingParameterException $e) {
    print_r($e->getMessage());die;
} catch (\htmlDiff\exceptions\OutputException $e) {
    print_r($e->getMessage());die;
}

print_r($res);

