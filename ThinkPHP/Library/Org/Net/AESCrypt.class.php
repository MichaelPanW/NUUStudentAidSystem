<?php

class AESCrypt extends Think
{
    protected $iv;
    protected $key;
    protected $blockSize;

    public function __construct($key, $iv = null, $mode = MCRYPT_MODE_CBC)
    {
        if($iv === null){
           $size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, $mode);
           $iv = mcrypt_create_iv($size, MCRYPT_DEV_RANDOM);
        }
        $this->key = $key;
        $this->iv = $iv;
        $this->blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, $mode);
    }

    protected function addPadding($data)
    {
        $paddingSize = $this->blockSize - (strlen($data) % $this->blockSize);
        $data .= str_repeat(chr($paddingSize), $paddingSize);
        return $data;
    }

    protected function removePadding($data)
    {
        $len = strlen($data);
        $paddingSize = ord($data[$len-1]);
        return substr($data, 0, $len - $paddingSize);
    }

    public function encrypt($plainData)
    {
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key,
            $this->addPadding($plainData), MCRYPT_MODE_CBC, $this->iv);
    }

    public function decrypt($encryptData)
    {
        $decryptData = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key,
            $encryptData, MCRYPT_MODE_CBC, $this->iv);
        return $this->removePadding($decryptData);
    }
}

?>