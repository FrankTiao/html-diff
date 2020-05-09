<?php

declare(strict_types=1);

namespace htmlDiff;

use htmlDiff\Exceptions\MissingParameterException;
use htmlDiff\exceptions\InputException;
use htmlDiff\libraries\HtmlDiff as HtmlDiffLib;

class HtmlDiff
{
    /**
     * @var string html文本 旧版本
     */
    private $oldText = '';

    /**
     * @var string html文本 新版本
     */
    private $newText = '';

    /**
     * @var array 输出文档的绝对路径
     */
    private $output = [
        'path'      => '',
        'encode'    => ''
    ];

    /**
     * @var array 自定义追加的单边标签
     */
    private $unilateralTags = ["img", "br", "hr", "link", "meta"];


    /**
     * @return string
     */
    public function getOldText(): string
    {
        return $this->oldText;
    }

    /**
     * @param $oldText
     * @return $this
     */
    public function setOldText(string $oldText): HtmlDiff
    {
        $this->oldText = $oldText;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewText(): string
    {
        return $this->newText;
    }

    /**
     * @param string $newText
     * @return $this
     */
    public function setNewText(string $newText): HtmlDiff
    {
        $this->newText = $newText;
        return $this;
    }

    /**
     * @return array
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    /**
     * @param string $outputPath
     * @param string $encode
     * @return $this
     */
    public function setOutput(string $outputPath, string $encode="UTF-8"): HtmlDiff
    {
        $this->output = [
            'path' => $outputPath,
            'encode' => $encode,
        ];
        return $this;
    }


    /**
     * @return array
     */
    public function getUnilateralTags(): array
    {
        return $this->unilateralTags;
    }

    /**
     * @param array $unilateralTags
     * @return $this
     */
    public function setUnilateralTags(array $unilateralTags)
    {
        // 合并->去重->去空
        $this->unilateralTags = array_filter(array_unique(array_merge($this->unilateralTags, $unilateralTags)));
        return $this;
    }


    /**
     * html文本是否不为空
     * @throws MissingParameterException
     */
    public function textIsEmpty() {
        if ($this->oldText === null || $this->newText === null){
            throw new MissingParameterException("Parameters are required");
        }
    }

    /**
     * 设置html文本
     * @param string $oldText
     * @param string $newText
     * @throws MissingParameterException
     */
    private function setParams(string $oldText, string $newText){
        if (empty($oldText) || empty($newText)){
            throw new MissingParameterException("Parameters are required");
        }
        $this->setOldText($oldText);
        $this->setNewText($newText);
    }

    /**
     * 从文件中加载html文档
     * @param string $filePath 文件路径
     * @param string $textName html文档名
     * @throws InputException
     */
    private function load(string $filePath, string $textName)
    {
        // 加载文件
        $text = Utils::instance()->autoReadFile($filePath);
        $funName = "set".$textName;
        $this->$funName($text);
    }

    /**
     * 从文件中加载旧版本html文档
     * @param string $file
     * @return HtmlDiff
     * @throws InputException
     */
    public function loadOldFile(string $file): HtmlDiff
    {
        $this->load($file, 'oldText');
        return $this;
    }

    /**
     * 从文件中加载新版本html文档
     * @param string $file
     * @return HtmlDiff
     * @throws InputException
     */
    public function loadNewFile(string $file): HtmlDiff
    {
        $this->load($file, 'newText');
        return $this;
    }

    /**
     * 从文件中加载html文档
     * @param string $oldFile
     * @param string $newFile
     * @return HtmlDiff
     * @throws InputException
     */
    public function loadFile(string $oldFile, string $newFile): HtmlDiff
    {
        // 加载文件
        $this->load($oldFile, 'oldText');
        $this->load($newFile, 'newText');
        return $this;
    }

    /**
     * HtmlDiff constructor.
     * @param string $oldText html文本1
     * @param string $newText html文本2
     */
    public function __construct(string $oldText='', string $newText='')
    {
        try {
            $this->setParams($oldText, $newText);
        } catch (MissingParameterException $e) {}
    }

    /**
     * 对比两个文本的差异
     * @param string $oldText
     * @param string $newText
     * @return string
     * @throws MissingParameterException
     * @throws exceptions\OutputException
     */
    public function diff(string $oldText='', string $newText=''): string
    {
        try {
            $this->setParams($oldText, $newText);
        } catch (MissingParameterException $e) {
            $this->textIsEmpty();
        }

        $htmlDiff = new HtmlDiffLib();

        $htmlDiff->unilateralTags = $this->unilateralTags;

        $diff = $htmlDiff->diff($this->oldText, $this->newText);

        $output = $this->getOutput();
        if (empty($output['path'])){
            return $diff;
        }

        Utils::instance()->writeFile($output['path'], $diff, $output['encode']);

        return $diff;
    }

}
