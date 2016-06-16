<?php
header("Content-type:text/html;charset=utf-8");
/*
 * Copyright(c)2016 All rights reserved.
 * @Licenced  http://www.w3.org
 * @Author  liutian<1538731090@qq.com> liutian_jiayi
 * @Create on 2016-6-14 11:00:59
 * @Version 1.0
 */

/**
 * 查询列表显示的功能:
 * 1、查询获取所有的数据
 * 2、在模板中显示
 */
function __autoload($classname) {
	require_once "./db/{$classname}.class.php";
}

$lamp142 = new Lamp142("localhost", "root", "", "test");
$rows = $lamp142->select();

/**
 * 使用模板引擎 驱动显示模板:
 * 1、引入smarty 工具包 类库
 * 2、在使用模板驱动的页面引入Smarty.class.php文件 并且实例化对象
 * 3、设置配置选项
 * 4、发送模板数据
 * 5、驱动模板显示
 */
require_once "./libs/Smarty.class.php";
$smarty = new Smarty();

//设置选项值
$smarty->left_delimiter = "{";		//设置smarty语句标签的限定符 不能使用尖括号 因为会和html标签冲突
$smarty->right_delimiter = "}";
$smarty->template_dir = "./tpl";	//指定模板存放目录
$smarty->compile_dir = "./tmp";		//设置编译文件存放目录

//关于缓存设置的
$smarty->caching = TRUE;			//开启缓存
$smarty->cache_lifetime = 10;		//缓存有效期
$smarty->cache_dir = "./cache";		//设置缓存目录

//发送要在模板中使用的数据
$smarty->assign("rows", $rows);

//驱动模板显示 解析模板
try {
	$smarty->display("1.html");
} catch (SmartyCompilerException $ex) {
//	var_dump($ex);
	echo "解析模板发生错误：".$ex->getMessage();
}