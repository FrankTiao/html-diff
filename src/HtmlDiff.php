<?php

declare(strict_types=1);

namespace htmlDiff;

use htmlDiff\Exceptions\MissingParameterException;
use htmlDiff\libraries\HtmlDiff as HtmlDiffLib;

class HtmlDiff
{
    /**
     * @var string html文本1
     */
    private $text1 = '';

    /**
     * @var string html文本2
     */
    private $text2 = '';

    /**
     * @var string 输出文档的绝对路径
     */
    private $output = '';

    /**
     * 忽略元素和属性的差异
     * @var array
     */
    private $ignore = [];

    /**
     * @return string
     */
    public function getText1(): string
    {
        return $this->text1;
    }

    /**
     * @param $text1
     * @return $this
     */
    public function setText1(string $text1): HtmlDiff
    {
        $this->text1 = $text1;
        return $this;
    }

    /**
     * @return string
     */
    public function getText2(): string
    {
        return $this->text2;
    }

    /**
     * @param string $text2
     * @return $this
     */
    public function setText2(string $text2): HtmlDiff
    {
        $this->text2 = $text2;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * @param string $output
     * @return $this
     */
    public function setOutput($output): HtmlDiff
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return array
     */
    public function getIgnore(): array
    {
        return $this->ignore;
    }

    /**
     * @param array $ignore
     * @return $this
     */
    public function setIgnore(array $ignore): HtmlDiff
    {
        $this->ignore = $ignore;
        return $this;
    }

    /**
     * html文本是否不为空
     * @param string $text1
     * @param string $text2
     * @throws MissingParameterException
     */
    public function textIsEmpty(string $text1='', string  $text2='')
    {
        $text1 = $text1 === '' ? $this->text1 : $text1;
        $text2 = $text2 === '' ? $this->text2 : $text2;
        if (empty($text1) || empty($text2)){
            throw new MissingParameterException("Parameters are required");
        }
    }

    /**
     * 设置html文本
     * @param string $text1
     * @param string $text2
     * @throws MissingParameterException
     */
    private function setParams(string $text1, string $text2){
        $this->textIsEmpty($text1, $text2);
        $this->setText1($text1);
        $this->setText2($text2);
    }

    /**
     * 从文件中加载html文档
     * @param string $filePath 文件路径
     * @param string $textName html文档名
     */
    private function load(string $filePath, string $textName)
    {
        // 加载文件
        $text = '';

        $funName = "set".$textName;
        $this->$funName($text);
    }

    /**
     * 从文件中加载html文档1
     * @param string $file html文档1的绝对路径
     * @return $this
     */
    public function loadHtml1(string $file): HtmlDiff
    {
        $this->load($file, 'text1');
        return $this;
    }

    /**
     * 从文件中加载html文档2
     * @param string $file html文档2的绝对路径
     * @return $this
     */
    public function loadHtml2(string $file): HtmlDiff
    {
        $this->load($file, 'text2');
        return $this;
    }

    /**
     * 从文件中加载html文档
     * @param string $file1 html文档1的绝对路径
     * @param string $file2 html文档2的绝对路径
     * @return $this
     * @throws MissingParameterException
     */
    public function loadHtml(string $file1, string $file2): HtmlDiff
    {
        // 加载文件
        $text1 = '';
        $text2 = '';

        $this->setParams($text1, $text2);
        return $this;
    }

    /**
     * HtmlDiff constructor.
     * @param string $text1 html文本1
     * @param string $text2 html文本2
     * @param array $ignore 配置
     */
    public function __construct(string $text1='', string $text2='', array $ignore=[])
    {
        try {
            $this->setIgnore($ignore);
            $this->setParams($text1, $text2);
        } catch (MissingParameterException $e) {
        }
    }

    /**
     * 对比两个文本的差异
     * @param string $text1
     * @param string $text2
     * @return string
     * @throws MissingParameterException
     */
    public function diff(string $text1='', string $text2=''): string
    {
        try {
            $this->setParams($text1, $text2);
        } catch (MissingParameterException $e) {
            $this->textIsEmpty();
        }

        return (HtmlDiffLib::instance())->diff($text1, $text2);
    }
}
