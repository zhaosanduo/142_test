<?php

/* 
 * Copyright(c)2016 All rights reserved.
 * @Licenced  http://www.w3.org
 * @Author  liutian<1538731090@qq.com> liutian_jiayi
 * @Create on 2016-6-14 11:52:12
 * @Version 1.0
 */

//echo md5(basename(__FILE__, ".php")).".php";
//$arr = array(
//	"a" => "hello",
//	"b" => "world",
//);
//extract($arr);
//echo $a;
//echo $b;
//开启缓冲控制
ob_start();	
//将输出放到缓冲控制区 在脚本前如果不处理 会自动输出
include "index.php";
//获取缓冲区的内容
$cont = ob_get_contents();
//手动清理掉缓冲区内容
ob_end_clean();
//查看缓冲区内容
var_dump($cont);