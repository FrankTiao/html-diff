<?php

/**
 * 输出文件链式调用示例（html text）
 * Example of chaining output files
 */

use htmlDiff\exceptions\InputException;
use htmlDiff\exceptions\MissingParameterException;
use htmlDiff\exceptions\OutputException;
use htmlDiff\HtmlDiff;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/html/html_text.php';


$output = __DIR__.'/html/'.time().'.html';

try {
    $diff = new HtmlDiff();
    $res = $diff->setOutput($output, "GBK")->diff($old, $new);
} catch (MissingParameterException $e) {
    print_r($e->getMessage());die;
} catch (InputException $e) {
    print_r($e->getMessage());die;
} catch (OutputException $e) {
    print_r($e->getMessage());die;
}
