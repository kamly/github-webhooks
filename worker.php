<?php

    require_once __DIR__.'/include/public.php';
    require_once __DIR__.'/include/config.php';

    // 插入数据到redis队列
    $redis = new Redis();
    $redis->connect($config['redis']['host'], $config['redis']['port']);
    $redis->auth($config['redis']['password']);

    $value = $redis->lpop($config['queue_name']);

    if (!$value) {
        $msg = [
            'date' => date('c'),
            'repository' => '',
            'message' => 'no date in queue'
        ];
        echo json_encode($msg);
        exit();
    }
    
    $value = json_decode($value, true);
    $origin_repository = $value['origin_repository'];
    $root = $config['root'];

    // 执行相应的 shell 脚本
    $cmd_result = shell_exec("{$root}/includeSh/{$origin_repository}.sh 2>&1");

    $msg = [
        'date' => date('c'),
        'repository' => $origin_repository,
        'cmd' => "{$root}/includeSh/{$origin_repository}.sh 2>&1",
        'cmd_result' =>  $cmd_result,
        'message' => 'cmd exe'
    ];
    echo json_encode($msg);




