<?php

namespace Org\Rsa;

/**
 +----------------------------------------------------------
 * 非对称加密RSA
 +----------------------------------------------------------
 * @author chenfeng
 +----------------------------------------------------------
 * @date 2016-04-19
 +----------------------------------------------------------
 */
class Rsa
{
    /**
    * 私钥
    */
    private $_privKey;
    
    /**
    * 公钥
    */
    private $_pubKey;
    
    /**
    * 密钥路径
    */
    private $_keyPath;
    
    /**
    +----------------------------------------------------------
    * @param $path 密钥路径
    +----------------------------------------------------------
    */
    public function __construct($path)
    {
        if(empty($path) || !is_dir(dirname(__FILE__) . DIRECTORY_SEPARATOR .$path)){
            throw new Exception('Must set the keys save path');
        }
        
        $this->_keyPath = $path;
    }
    
    /**
    +----------------------------------------------------------
    * 生成私钥和公钥
    +----------------------------------------------------------
    */
    public function createKey()
    {
        $r = openssl_pkey_new();
        openssl_pkey_export($r, $privKey);
        file_put_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->_keyPath . DIRECTORY_SEPARATOR . 'rsa_private_key.pem', $privKey);
        $this->_privKey = openssl_pkey_get_private($privKey);
        
        $rp = openssl_pkey_get_details($r);
        $pubKey = $rp['key'];
        file_put_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->_keyPath . DIRECTORY_SEPARATOR .  'rsa_public_key.pem', $pubKey);
        $this->_pubKey = openssl_pkey_get_public($pubKey);
    }
    
    /**
    +----------------------------------------------------------
    * 生成私钥
    +----------------------------------------------------------
    */
    public function setupPrivKey()
    {
        if(is_resource($this->_privKey)){
            return true;
        }
        $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->_keyPath . DIRECTORY_SEPARATOR . 'rsa_private_key.pem';
        $prk = file_get_contents($file);
        $this->_privKey = openssl_pkey_get_private($prk);
        return $this->_privKey;
    }
    
    /**
    +----------------------------------------------------------
    * 生成公钥
    +----------------------------------------------------------
    */
    public function setupPubKey()
    {
        if(is_resource($this->_pubKey)){
            return true;
        }
        $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->_keyPath . DIRECTORY_SEPARATOR .  'rsa_public_key.pem';
        $puk = file_get_contents($file);
        $this->_pubKey = openssl_pkey_get_public($puk);
        return true;
    }
    
    /**
    +----------------------------------------------------------
    * 私钥加密
    +----------------------------------------------------------
    */
    public function privEncrypt($data)
    {
        if(!is_string($data)){
            return null;
        }
        
        $this->setupPrivKey();
        
        $r = openssl_private_encrypt($data, $encrypted, $this->_privKey);
        if($r){
            return base64_encode($encrypted);
        }
        return null;
    }
    
    /**
    +----------------------------------------------------------
    * 私钥解密
    +----------------------------------------------------------
    */
    public function privDecrypt($encrypted)
    {
        if(!is_string($encrypted)){
            return null;
        }
        
        $this->setupPrivKey();
        
        $encrypted = base64_decode($encrypted);
        
        $r = openssl_private_decrypt($encrypted, $decrypted, $this->_privKey);
        if($r){
            return $decrypted;
        }
        return null;
    }
    
    /**
    +----------------------------------------------------------
    * 公钥加密
    +----------------------------------------------------------
    */
    public function pubEncrypt($data)
    {
        if(!is_string($data)){
            return null;
        }
        
        $this->setupPubKey();
        
        $r = openssl_public_encrypt($data, $encrypted, $this->_pubKey);
        if($r){
            return base64_encode($encrypted);
        }
        return null;
    }
    
    /**
    +----------------------------------------------------------
    * 公钥解密
    +----------------------------------------------------------
    */
    public function pubDecrypt($crypted)
    {
        if(!is_string($crypted)){
            return null;
        }
        
        $this->setupPubKey();
        
        $crypted = base64_decode($crypted);
        
        $r = openssl_public_decrypt($crypted, $decrypted, $this->_pubKey);
        if($r){
            return $decrypted;
        }
        return null;
    }
    
    public function __destruct()
    {
        @ fclose($this->_privKey);
        @ fclose($this->_pubKey);
    }

}
