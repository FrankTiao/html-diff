<?php

declare(strict_types=1);

namespace htmlDiff\libraries;


class Diff
{
    protected $mode = 1;
    protected $modeCharacter = 1;
    protected $modeTag = 2;
    protected $modeWhitespace = 3;
    protected $actionEqual = 1;
    protected $actionDelete = 2;
    protected $actionInsert = 3;
    protected $actionNone = 4;
    protected $actionReplace = 5;

    /**
     * @param string $val
     * @return bool
     */
    protected function isStartOfTag(string $val): bool {
        return $val == '<';
    }

    /**
     * @param string $val
     * @return bool
     */
    protected function isEndOfTag(string $val): bool {
        return $val == '>';
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function isWhiteSpace(string $value): bool {
        return empty(preg_match('/\s/',$value)) ? false : true;
    }

    /**
     * @param string $str
     * @return bool
     */
    protected function isHanz(string $str): bool {
        return empty(preg_match("/[\\x{7F}-\\x{FF}]/",$str)) ? false : true;
    }

    /**
     * @param string $str
     * @return array
     */
    protected function strSplitUtf8(string $str): array {
        // place each character of the string into and array
        $split = 1;
        $array = array(); $len = strlen($str);
        for ( $i = 0; $i < $len; ){
            $value = ord($str[$i]);
            if($value > 127){
                if($value >= 192 && $value <= 223)
                    $split = 2;
                elseif($value >= 224 && $value <= 239)
                    $split = 3;
                elseif($value >= 240 && $value <= 247)
                    $split = 4;
            } else {
                $split = 1;
            }
            $key = NULL;
            for ( $j = 0; $j < $split; ++$j, ++$i ) {
                $key .= $str[$i];
            }
            $array[] = $key;
            //array_push( $array, $key );
        }
        return $array;
    }


    /**
     * 从某索引取到另外一个索引 不包含end_offset
     * @param array $arr
     * @param int $start_offset
     * @param int $end_offset
     * @param bool $preserve_keys
     * @return array
     */
    protected function arraySliceByOffset(array &$arr,int $start_offset, int $end_offset=0, bool $preserve_keys = false): array {
        return array_slice($arr,$start_offset,$end_offset - $start_offset, $preserve_keys);
    }

    /**
     * @param array $pattern_array
     * @param string $subject
     * @return false|int
     */
    protected function pregMatchArray(array $pattern_array, string $subject) {
        $result = 0;
        foreach($pattern_array as $v) {
            $result |= preg_match('/'.$v.'/', $subject);
        }
        return $result;
    }
}