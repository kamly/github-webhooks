<?php


    // 需要创建这个文件才能使用
    function write_log($message, $filename = 'api')
    {
        $filepath = './logs/' . $filename . '.log';
        if (!file_exists($filepath)) {
            $newfile = TRUE;
        }
        if (!$fp = @fopen($filepath, 'ab')) {
            return FALSE;
        }
        $message = $message . "\n";
        
        flock($fp, LOCK_EX);
        for ($written = 0, $length = strlen($message); $written < $length; $written += $result) {
            if (($result = fwrite($fp, substr($message, $written))) === FALSE) {
                break;
            }
        }
        flock($fp, LOCK_UN);

        fclose($fp);
        if (isset($newfile) && $newfile === TRUE) {
            chmod($filepath, 0644);
        }
        return is_int($result);
    }

    function guid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = chr(123)// "{"
                    .substr($charid, 0, 8).$hyphen
                    .substr($charid, 8, 4).$hyphen
                    .substr($charid,12, 4).$hyphen
                    .substr($charid,16, 4).$hyphen
                    .substr($charid,20,12)
                    .chr(125);// "}"
            return $uuid;
        }
    }


