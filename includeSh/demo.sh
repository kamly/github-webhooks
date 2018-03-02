#!/bin/bash  

# 注意，要给 www 用户赋值权限  http://blog.csdn.net/gezehao/article/details/47317103  


cmd_begin () {
  echo "------------"
  date +%F" "%H:%M:%S
  time_begin=$(date +%s)
}

cmd_end () {
  time_end=`date +%s`
  ((time_use=${time_end}-${time_begin}))
  ((time_use_m=${time_use}/60))
  ((time_use_s=${time_use}%60))
  echo "use ${time_use_m}Min ${time_use_s}Sec"
  date +%F" "%H:%M:%S
  echo "------------"
}


demo () {
 
    cmd_begin
          
    echo 'cmd demo run'
    
    cmd_end    
}


demo  &>> /data/logs/webhooks-demoe.log
