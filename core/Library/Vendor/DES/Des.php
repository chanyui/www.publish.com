<?php

/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/9/15
 * Time: 10:53
 */
class DES
{
    var $key;  //加密密钥
    var $iv;   //偏移量

    /**
     * DES constructor.
     * @param $key
     * @param int $iv
     */
    function __construct($key, $iv)
    {
        $this->key = $key;
        if ($iv == 0) {
            $this->iv = $key;
        } else {
            $this->iv = $iv;
        }
    }
    /*function DES($key, $iv = 0)
    {
        //key长度8例如:1234abcd
        $this->key = $key;
        if ($iv == 0) {
            $this->iv = $key;
        } else {
            $this->iv = $iv;
        }
    }*/

    /**
     * 字符串加密
     * +-----------------------------------------------------------
     * @functionName : encrypt
     * +-----------------------------------------------------------
     * @param string $str 加密字符串
     * +-----------------------------------------------------------
     * @return string 返回大写十六进制字符串
     */
    public function encrypt($str)
    {
        $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
        $str = $this->pkcs5Pad($str, $size);
        return strtoupper(bin2hex(mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv)));
    }

    /**
     * 字符串解密
     * +-----------------------------------------------------------
     * @functionName : decrypt
     * +-----------------------------------------------------------
     * @param string $str
     * +-----------------------------------------------------------
     * @return bool|string
     */
    public function decrypt($str)
    {
        $strBin = $this->hex2bin(strtolower($str));
        $str = mcrypt_cbc(MCRYPT_DES, $this->key, $strBin, MCRYPT_DECRYPT, $this->iv);
        $str = $this->pkcs5Unpad($str);
        return $str;
    }

    private function hex2bin($hexData)
    {
        $binData = "";
        for ($i = 0; $i < strlen($hexData); $i += 2) {
            $binData .= chr(hexdec(substr($hexData, $i, 2)));
        }
        return $binData;
    }

    private function pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private function pkcs5Unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text))
            return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;
        return substr($text, 0, -1 * $pad);
    }
}
