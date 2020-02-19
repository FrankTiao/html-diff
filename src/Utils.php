<?php


namespace htmlDiff;


use htmlDiff\exceptions\InputException;
use htmlDiff\exceptions\OutputException;

class Utils
{

    /**
     * [单例]
     * @return Utils
     */
    public static function instance(): Utils  {
        static $_instance;
        if (!$_instance) {
            $_instance = new self();
        }
        return $_instance;
    }

    /**
     * 读取文件
     * @param string $filePath 文件路径
     * @param string $fileSize 默认为空，获取文件的全部内容，如果仅需要获取文件编码类型，获取前一百个字符即可，配合detectEncoding方法使用
     * @return false|string
     * @throws InputException
     */
    public function fileToString($filePath, $fileSize = '')
    {
        //判断文件路径中是否含有中文，如果有，那就对路径进行转码，如此才能识别
        if (preg_match("/[\x7f-\xff]/", $filePath)) {
            $filePath = iconv('UTF-8', 'GBK', $filePath);
        }
        if (file_exists($filePath)) {
            $fp = fopen($filePath, "r");
            if ($fileSize === '') {
                $fileSize = filesize($filePath);
            }
            return fread($fp, $fileSize);
        } else {
            throw new InputException("文件路径错误！File path error!");
        }
    }

    /**

     * 获取文件编码类型
     * @param string $filePath 文件路径
     * @param string $fileSize 需要获取的字符长度
     * @return string          字符编码
     * @throws InputException
     */
    public function detectEncoding($filePath, $fileSize = '1000')
    {
        $list = array('GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1');
        $str = $this->fileToString($filePath, $fileSize);
        foreach ($list as $item) {
            $tmp = mb_convert_encoding($str, $item, $item);
            if (md5($tmp) == md5($str)) {
                return $item;
            }
        }

        throw new InputException("识别不出的编码！Unrecognized encoding!");
    }

    /**
     * 自动解析编码读入文件
     * @param string $filePath 文件路径
     * @param string $fileSize 读取文件的长度
     * @param string $charset 读取编码
     * @return string 返回读取内容
     * @throws InputException
     */
    public function autoReadFile($filePath, $fileSize = '', $charset = 'UTF-8')
    {
        $list = array('GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1');
        $str = $this->fileToString($filePath, $fileSize);
        foreach ($list as $item) {
            $tmp = mb_convert_encoding($str, $item, $item);
            if (md5($tmp) == md5($str)) {
                return mb_convert_encoding($str, $charset, $item);
            }
        }
        return "";
    }


    /**
     * 指定编码写入文件
     * @param string $path
     * @param string $string
     * @param string $charset
     * @throws OutputException
     */
    public function writeFile(string $path, string $string, $charset='UTF-8'){
        $encode = mb_detect_encoding($string, ["ASCII","UTF-8","GB2312","GBK","BIG5"]);

        if (file_put_contents($path, iconv($encode, $charset, $string)) === false){
            throw new OutputException();
        }
    }
}