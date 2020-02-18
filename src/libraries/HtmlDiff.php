<?php

declare(strict_types=1);

namespace htmlDiff\libraries;

use htmlDiff\exceptions\IllegalParameter;

class HtmlDiff extends Diff
{
    /**
     * @var array 开始标签
     */
    private $specialCaseOpeningTags = ["<strong[\\>\\s]+", "<b[\\>\\s]+", "<i[\\>\\s]+", "<big[\\>\\s]+", "<small[\\>\\s]+", "<u[\\>\\s]+", "<sub[\\>\\s]+", "<sup[\\>\\s]+", "<strike[\\>\\s]+", "<s[\\>\\s]+"];

    /**
     * @var array 闭合标签
     */
    private $specialCaseClosingTags = ["</strong>", "</b>", "</i>", "</big>", "</small>", "</u>", "</sub>", "</sup>", "</strike>", "</s>"];

    /**
     * @var array 文本内容
     */
    private $content = [];

    /**
     * @var array newWords队列对应的索引表
     */
    private $wordIndices = [];

    /**
     * @var array 单词列表 旧文本
     */
    private $oldWords = [];

    /**
     * @var array 单词列表 新文本
     */
    private $newWords = [];


    /**
     * [单例]
     * @return HtmlDiff
     */
    public static function instance(): HtmlDiff  {
        static $_instance;
        if (!$_instance) {
            $_instance = new self();
        }
        return $_instance;
    }


    public function diff(string $oldText, string $newText) {
        $this->oldWords = $this->convertHtmlToListOfWords($this->strSplitUtf8($oldText));
        $this->newWords = $this->convertHtmlToListOfWords($this->strSplitUtf8($newText));

        $this->wordIndices = $this->indexNewWords($this->newWords);

        $operations = $this->operations();

        foreach ($operations as $item) {
            $this->performOperation($item);
        }

        return implode('', $this->content);
    }

    /**
     * 将HTML解析成单词列表
     * @param array $characterString
     * @return array
     */
    private function convertHtmlToListOfWords(array $characterString): array {
        $mode = $this->mode;
        $current_word = '';
        $words = [];
        foreach($characterString as $character) {
            switch($mode) {
                case $this->modeCharacter:
                    if ($this->isStartOfTag($character)) {
                        if (!empty($current_word)) {
                            $words[] = $current_word;
                        }
                        $current_word = '<';
                        $mode = $this->modeTag;
                    } else if ($this->isWhiteSpace($character)) {
                        if (!empty($current_word)) {
                            $words[] = $current_word;
                        }
                        $current_word = $character;
                        $mode = $this->modeWhitespace;
                    } else {
                        if ($this->isHanz($current_word.$character)) {
                            if (!empty($current_word)) {
                                $words[] = $current_word;
                            }
                            $current_word = $character;
                        } else {
                            $current_word .= $character;
                        }

                    }
                    break;
                case $this->modeTag:
                    if ($this->isEndOfTag($character)) {
                        $current_word .= '>';
                        $words[] = $current_word;
                        $current_word = '';

                        if ($this->IsWhiteSpace($character)) {
                            $mode = $this->modeWhitespace;
                        } else {
                            $mode = $this->modeCharacter;
                        }
                    } else {
                        $current_word .= $character;
                    }
                    break;
                case $this->modeWhitespace:
                    if ($this->IsStartOfTag($character)) {
                        if (!empty($current_word)) {
                            $words[] = $current_word;
                        }
                        $current_word = '<';
                        $mode = $this->modeTag;
                    } else if ($this->IsWhiteSpace($character)) {
                        $current_word .= $character;
                    } else {
                        if (!empty($current_word)) {
                            $words[] = $current_word;
                        }
                        $current_word = $character;
                        $mode = $this->modeCharacter;
                    }
                    break;
                default:
                    break;
            }
        }
        if (!empty($current_word)) {
            $words[] = $current_word;
        }
        return $words;
    }

    /**
     * 构建一个newWords队列对应的索引表
     * @param array $newWords
     * @return array
     */
    private function indexNewWords(array &$newWords): array {
        $wordIndices = [];
        for ($i = 0; $i < count($newWords); $i++) {
            $word = $newWords[$i];
            if (array_key_exists($word,$wordIndices)) {
                $wordIndices[$word][] = $i;
            } else {
                $wordIndices[$word] = array($i);
            }
        }
        return $wordIndices;
    }

    /**
     * 将文档抽象为操作描述队列
     * @return array
     */
    private function operations(): array {
        $positionInOld = 0;
        $positionInNew = 0;

        $operations = [];

        $matches = $this->MatchingBlocks();

        $matches[] = new Match(count($this->oldWords), count($this->newWords), 0);

        for ($i = 0; $i < count($matches); $i++)
        {
            $match = $matches[$i];

            $matchStartsAtCurrentPositionInOld = ($positionInOld == $match->startInOld);
            $matchStartsAtCurrentPositionInNew = ($positionInNew == $match->startInNew);

            $action = $this->actionNone;

            if ($matchStartsAtCurrentPositionInOld == false && $matchStartsAtCurrentPositionInNew == false) {
                $action = $this->actionReplace;
            } else if ($matchStartsAtCurrentPositionInOld == true && $matchStartsAtCurrentPositionInNew == false) {
                $action = $this->actionInsert;
            } else if ($matchStartsAtCurrentPositionInOld == false && $matchStartsAtCurrentPositionInNew == true) {
                $action = $this->actionDelete;
            } else {
                $action = $this->actionNone;
            }

            if ($action != $this->actionNone) {
                $operations[] = new Operation($action, $positionInOld, $match->startInOld, $positionInNew, $match->startInNew);
            }

            if (!empty($match)) {
                $operations[] = new Operation($this->actionEqual, $match->startInOld, $match->endInOld(), $match->startInNew, $match->endInNew());
            }

            $positionInOld = $match->endInOld();
            $positionInNew = $match->endInNew();
        }

        return $operations;
    }

    /**
     * 获取一个用于描述新旧文档内全部相同内容的匹配描述列表
     * @return array
     */
    private function matchingBlocks(): array {
        $matchingBlocks = [];
        $this->findMatchingBlocks(0, count($this->oldWords), 0, count($this->newWords), $matchingBlocks);
        return $matchingBlocks;
    }

    /**
     * 递归查找匹配项
     * @param int $startInOld
     * @param int $endInOld
     * @param int $startInNew
     * @param int $endInNew
     * @param array $matchingBlocks
     */
    private function findMatchingBlocks(int $startInOld, int $endInOld, int $startInNew, int $endInNew, array &$matchingBlocks) {
        try {
            $match = $this->findMatch($startInOld, $endInOld, $startInNew, $endInNew);

            if ($startInOld < $match->startInOld && $startInNew < $match->startInNew) {
                $this->findMatchingBlocks($startInOld, $match->startInOld, $startInNew, $match->startInNew, $matchingBlocks);
            }

            $matchingBlocks[] = $match;

            if ($match->endInOld() < $endInOld && $match->endInNew() < $endInNew) {
                $this->findMatchingBlocks($match->endInOld(), $endInOld, $match->endInNew(), $endInNew, $matchingBlocks);
            }
        } catch (IllegalParameter $e) {
            $match = null;
        }
    }

    /**
     * 从指定位置开始查询第一块匹配的文本块
     * @param int $startInOld
     * @param int $endInOld
     * @param int $startInNew
     * @param int $endInNew
     * @return Match
     * @throws IllegalParameter
     */
    private function findMatch(int $startInOld, int $endInOld, int $startInNew, int $endInNew): Match {
        $bestMatchInOld = $startInOld;
        $bestMatchInNew = $startInNew;
        $bestMatchSize = 0;

        $matchLengthAt = [];

        for ($indexInOld = $startInOld; $indexInOld < $endInOld; $indexInOld++)
        {
            $newMatchLengthAt = [];
            $index = $this->oldWords[$indexInOld];

            if (!array_key_exists($index,$this->wordIndices)) {
                $matchLengthAt = $newMatchLengthAt;
                continue;
            }

            foreach($this->wordIndices[$index] as $indexInNew) {
                if ($indexInNew < $startInNew) {
                    continue;
                }

                if ($indexInNew >= $endInNew) {
                    break;
                }


                $newMatchLength = (array_key_exists($indexInNew - 1,$matchLengthAt) ? $matchLengthAt[$indexInNew - 1] : 0) + 1;
                $newMatchLengthAt[$indexInNew] = $newMatchLength;

                if ($newMatchLength > $bestMatchSize) {
                    $bestMatchInOld = $indexInOld - $newMatchLength + 1;
                    $bestMatchInNew = $indexInNew - $newMatchLength + 1;
                    $bestMatchSize = $newMatchLength;
                }
            }

            $matchLengthAt = $newMatchLengthAt;
        }

        if (empty($bestMatchSize)){
            throw new IllegalParameter('$bestMatchSize is '.$bestMatchSize);
        }

        return new Match($bestMatchInOld, $bestMatchInNew, $bestMatchSize);
    }

    /**
     * 执行操作
     * @param Operation $operation
     */
    private function performOperation(Operation &$operation){
        switch ($operation->action) {
            case $this->actionEqual:
                $this->processEqualOperation($operation);
                break;
            case $this->actionDelete:
                $this->processDeleteOperation($operation, 'diffdel');
                break;
            case $this->actionInsert:
                $this->processInsertOperation($operation, 'diffins');
                break;
            case $this->actionReplace:
                $this->processReplaceOperation($operation);
                break;
            case $this->actionNone:
            default: break;
        }
    }

    /**
     * 替换
     * @param Operation $operation
     */
    private function processReplaceOperation(Operation &$operation) {
        $this->processDeleteOperation($operation, 'diffmod');
        $this->processInsertOperation($operation, 'diffmod');
    }

    /**
     * 新增
     * @param Operation $operation
     * @param string $cssClass
     */
    private function processInsertOperation(Operation &$operation, string $cssClass) {
        $text = $this->arraySliceByOffset($this->newWords, $operation->startInNew, $operation->endInNew ? $operation->endInNew : 0);
        $this->insertTag("ins", $cssClass, $text);
    }

    /**
     * 删除
     * @param Operation $operation
     * @param string $cssClass
     */
    private function processDeleteOperation(Operation &$operation, string $cssClass) {
        $text = $this->arraySliceByOffset($this->oldWords, $operation->startInNew, $operation->endInNew ? $operation->endInNew : 0);
        $this->insertTag("del", $cssClass, $text);
    }

    /**
     * 无修改
     * @param Operation $operation
     */
    private function processEqualOperation(Operation &$operation) {
        $result = $this->arraySliceByOffset($this->newWords, $operation->startInNew, $operation->endInNew ? $operation->endInNew : 0);
        $this->content[] = implode('', $result);
    }

    /**
     * 添加标签
     * @param string $tag
     * @param string $cssClass
     * @param array $words
     */
    private function insertTag(string $tag, string $cssClass, array $words) {
        while (true) {
            if (count($words) == 0) {
                break;
            }

            $nonTags = $this->extractConsecutiveWords($words, false);

            $specialCaseTagInjection = '';
            $specialCaseTagInjectionIsBefore = false;

            if (count($nonTags) != 0) {
                $text = $this->wrapText(implode('', $nonTags), $tag, $cssClass);
                $this->content[] = $text;
            } else {
                if (!!$this->pregMatchArray($this->specialCaseOpeningTags, $words[0]) ) {
                    $specialCaseTagInjection = '<ins class=\'mod\'>';
                    if ($tag == 'del'){
                        array_shift($words);
                    }

                } else if (in_array($words[0],$this->specialCaseClosingTags)) {
                    $specialCaseTagInjection = '</ins>';
                    $specialCaseTagInjectionIsBefore = true;
                    if ($tag == "del") {
                        array_shift($words);
                    }
                }
            }

            if (count($words) == 0 && strlen($specialCaseTagInjection) == 0) {
                break;
            }

            if ($specialCaseTagInjectionIsBefore) {
                $this->content[] = $specialCaseTagInjection . implode('', $this->extractConsecutiveWords($words, true));
            } else {
                $this->content[] = implode('', $this->extractConsecutiveWords($words, true)) . $specialCaseTagInjection;
            }
        }
    }

    /**
     * 获取words内连续的“文本”或“标签”
     * @param array $words
     * @param bool $condition
     * @return array
     */
    private function extractConsecutiveWords(array &$words, bool $condition): array {
        $indexOfFirstTag = false;

        for ($i = 0; $i < count($words); $i++) {
            $word = $words[$i];

            if ($condition ? !$this->isTag($word) : !!$this->isTag($word)) {
                $indexOfFirstTag = $i;
                break;
            }
        }

        if ($indexOfFirstTag !== false) {
            $items = $this->arraySliceByOffset($words,0, $indexOfFirstTag);
            if ($indexOfFirstTag > 0) {
                array_splice($words,0, $indexOfFirstTag);
            }
            return $items;
        } else {
            $items = $words;
            $words = [];
            return $items;
        }
    }

    /**
     * @param string $item
     * @return bool
     */
    private function isTag(string $item): bool {
        return $this->isOpeningTag($item) || $this->isClosingTag($item);
    }

    /**
     * @param string $item
     * @return bool
     */
    private function isOpeningTag(string $item): bool {
        return preg_match("/^\\s*<[^>]+>\\s*$/",$item) > 0;
    }

    /**
     * @param $item
     * @return bool
     */
    private function isClosingTag(string $item): bool {
        return preg_match("/^\\s*<\\/[^>]+>\\s*$/",$item) > 0;
    }

    /**
     * @param string $text
     * @param string $tagName
     * @param string $cssClass
     * @return string
     */
    private function wrapText(string $text, string $tagName, string $cssClass): string {
        return sprintf("<%s class='%s'>%s</%s>", $tagName, $cssClass, $text ,$tagName);
    }
}