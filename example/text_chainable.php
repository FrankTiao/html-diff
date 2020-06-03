<?php

/**
 * 链式调用示例
 * Example of chained calls
 */

use htmlDiff\exceptions\MissingParameterException;
use htmlDiff\HtmlDiff;

require __DIR__.'/../vendor/autoload.php';

require __DIR__.'/html/html_text.php';


try {
    $diff = new HtmlDiff();
    $res = $diff->setOldText($old)->setNewText($new)->diff();
} catch (MissingParameterException $e) {
    print_r($e->getMessage());die;
}

print_r($res);

