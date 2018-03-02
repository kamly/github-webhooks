# github webhooks

## 介绍

利用 github 的 webhooks 实现自动化部署环境

## 版本 1

### 环境配置

- nginx
- php >=5.6

### 目录结构

~~~
.
├── README.md
├── include               PHP文件目录
│   └── config.php        配置文件
├── includeSh             脚本文件目录
│   ├── demo.sh           脚本文件
│   └── ....              更多脚本文件
├── index.php             入口
└── logs                  日志目录
~~~

### 配置文档

```php
<?php

$secret = 'xxxxxxxx'; // github 设置的 webhooks 密码
$root' =  "xxxxxxxx"; // github webhooks 中 index.php 的绝对路径
$branch => 'master';  // 只是监听 master 的推送
// 仓库名字
$repository_path_array => [
    'graduation-zd-app', 
    'graduation-zd-web'
];
```

### 缺陷

由于执行脚本文件的时候需要时间，github 的回调会是 time out

因此需要将任务推进消息队列，使用定时任务定时检测消息队列


## 版本 2

### 环境配置

- nginx
- php >=5.6
- redis 

### 目录结构

~~~
.
├── README.md
├── include               PHP文件目录
│   ├── config.php        配置文件
│   └── public.php        公共函数
├── includeSh             脚本文件目录
│   ├── demo.sh           脚本文件
│   └── ....              更多脚本文件
├── index.php             入口
├── logs                  日志目录
└── worker.php            定时任务
~~~

### 配置文档

```php
<?php

$config = [
    'secret' => 'xxxxxxxx', // github 设置的 webhooks 密码
    'root' =>  "xxxxxxxx",  // github webhooks 中 index.php 的绝对路径
    'branch' => 'master',   // 只是监听master的推送

    // 仓库名字
    'repository_path_array' => [
        'graduation-zd-app', 
        'graduation-zd-web'
    ], 

    'redis' => [
        'host' => ‘xx.xx.xx.xx',    // redis 地址
        'port' => 'xxxxxxxx',       // redis 端口
        'password' => 'xxxxxxxx',   // redis 密码
    ],
    'queue_name' => 'xxxxxxxx'      // 消息队列的队名
];
```



