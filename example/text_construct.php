<?php

/**
 * 构造方法使用示例
 * Constructor usage example
 */

use htmlDiff\Exceptions\MissingParameterException;
use htmlDiff\HtmlDiff;

require __DIR__.'/../vendor/autoload.php';

require __DIR__.'/html/html_text.php';



try {
    $diff = new HtmlDiff($old, $new);
    $res = $diff->diff();
} catch (MissingParameterException $e) {
    print_r($e->getMessage());die;
}

print_r($res);
