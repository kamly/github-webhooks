<?php
    // 引入配置文件
    require_once './include/config.php';

    // 请求头没有内容
    $requestBody = file_get_contents("php://input");
    if (empty($requestBody)) {
        $msg = [
            'date' => date('c'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'content' =>  $requestBody,
            'repository' => '',
            'branch' => '',
            'reason' => '提交空请求',
            'type' => 'fail'
        ];
        write_log(json_encode($msg), 'request');
        echo json_encode('fail');
        exit();
    }

    // 解密
    $content = json_decode($requestBody, true); // 获取请求头内容
    $hubSignature = $_SERVER['HTTP_X_HUB_SIGNATURE']; // 获取签名
    list($algo, $hash) = explode('=', $hubSignature, 2);   // 拆分 algo hash
    $payloadHash = hash_hmac($algo, $requestBody, $secret); // 加密同样的内容
    $auth = $hash === $payloadHash ? true : false; // 判断是否相同
    if (!$auth){
        $msg = [
            'date' => date('c'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'content' => $content,
            'repository' => '',
            'branch' => '',
            'reason' => '解密失败',    
            'type' => 'fail'
        ];
        write_log(json_encode($msg), 'request');
        echo json_encode('fail');
        exit();
    }


    // 判断哪个仓库改变，再判断是不是master分支改变
    $origin_repository = $content['repository']['name']; // 获取是哪个仓库
    if (!in_array($origin_repository, $repository_path_array)) {
        $msg = [
            'date' => date('c'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'content' => $content,
            'repository' => $origin_repository,
            'branch' => '',
            'reason' => '不是指定仓库提交push更新',    
            'type' => 'fail'
        ];
        write_log(json_encode($msg), 'request');
        echo json_encode('fail');
        exit();
    } else {
        $origin_branch = explode('/', $content['ref']); // 获取是哪个分支
        if ($origin_branch[2] == $branch) {
            // 执行相应的 shell 脚本
            $cmd_result = shell_exec("{$root}/include/{$origin_repository}.sh 2>&1");
            
            if ($cmd_result == FALSE) {
               // 记录发送请求
               $msg = [
                  'date' => date('c'),
                  'ip' => $_SERVER['REMOTE_ADDR'],
                  'content' => $content,
                  'repository' => $origin_repository,
                  'branch' => $origin_branch,
                  'type' => 'fail',
              ];
              write_log(json_encode($msg), 'request');
              echo json_encode('fail');
              exit();
            } else {
              // 记录发送请求
              $msg = [
                  'date' => date('c'),
                  'ip' => $_SERVER['REMOTE_ADDR'],
                  'content' => $content,
                  'repository' => $origin_repository,
                  'branch' => $origin_branch,
                  'type' => 'success',
              ];
              write_log(json_encode($msg), 'request');
              echo json_encode('success');
              exit();
            }
        } else {
            $msg = [
                'date' => date('c'),
                'ip' => $_SERVER['REMOTE_ADDR'],
                'content' => $content,
                'repository' => $origin_repository,
                'branch' => $origin_branch,
                'reason' => '不是指定分支提交push更新',    
                'type' => 'fail'
            ];
            write_log(json_encode($msg), 'request');
            echo json_encode('fail');
            exit();
        }
    }

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

