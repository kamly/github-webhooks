<?php

    require_once __DIR__.'/include/public.php';
    require_once __DIR__.'/include/config.php';

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
    $payloadHash = hash_hmac($algo, $requestBody, $config['secret']); // 加密同样的内容
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
    if (!in_array($origin_repository, $config['repository_path_array'])) {
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
        if ($origin_branch[2] == $config['branch']) {

            // 插入数据到redis队列
            $redis = new Redis();
            $redis->connect($config['redis']['host'], $config['redis']['port']);
            $redis->auth($config['redis']['password']);
           
            $msg = [
                'time' => date('c'), // time
                'uuid' => guid(), // uuid
                'origin_repository' => $origin_repository, // 哪个仓库
            ];
            $redis->rpush($config['queue_name'], json_encode(msg));

            // 记录发送请求
            $msg = [
                'date' => date('c'),
                'ip' => $_SERVER['REMOTE_ADDR'],
                'content' => $content,
                'repository' => $origin_repository,
                'branch' => $origin_branch,
                'type' => 'get',
            ];
            write_log(json_encode($msg), 'request');
            echo json_encode('get');
            exit();

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
