<?php
header("Content-type:text/html;charset=utf-8");
/* 
 * Copyright(c)2016 All rights reserved.
 * @Licenced  http://www.w3.org
 * @Author  liutian<1538731090@qq.com> liutian_jiayi
 * @Create on 2016-6-14 8:45:17
 * @Version 1.0
 */
//初始化连接数据库
function __autoload($classname) {
	require_once "./db/{$classname}.class.php";
}
$lamp142 = new Lamp142("localhost", "root", "", "test");

//获取GPC输入变量的值
function I($name, $default = NULL) {
	if (isset($_GET[$name])) {
		return $_GET[$name];
	} else if (isset($_POST[$name])) {
		return $_POST[$name];
	} else if (isset($_COOKIE[$name])) {
		return $_COOKIE[$name];
	} else {
		return $default;
	}
}

$action = I("action", "index");
if ($action == "add") {
	//显示添加的表单
	include "./tpl/add.html";
} else if ($action == "insert") {
//	//输入入库的 操作
//	if (FALSE == $lamp142->create()) {//创建数据对象发生错误 输出错误信息
//		exit($lamp142->getError());
//	}
	$id = $lamp142->data()->add();
	//输出提示信息
	echo "添加数据成功，编号：{$id}，<a href='index.php'>查看</a>";
} else if ($action == "delete") {
	
//	$affected_rows = $lamp142->delete("id=".$_GET['id']);
//	$affected_rows = $lamp142->where("id=".$_GET['id'])->delete();
	$affected_rows = $lamp142->delete($_GET['id']);
	
	echo "成功删除了{$affected_rows}条记录，<a href='index.php'>查看</a>";
	
}else if ($action == "edit") {
	//查询该条记录
	$row = $lamp142->find(array("id" => $_GET['id']));
//	var_dump($row);
	//引入模板显示
	include "./tpl/edit.html";
} else if ($action == "update") {
	//执行修改 使用post表单数据 经过create方法 产生有效的数据 存入模型类中
	if (FALSE == $lamp142->create()) {
		exit($lamp142->getError());
	}
	//将已经保存在模型类中的有效数据 添加入库
//	$affected_rows =  $lamp142->save(null, array("id" => $_POST['id'])); 
	$affected_rows =  $lamp142->where(array("id" => $_POST['id']))->data()->save(); 
	//输出提示信息
	echo "修改成功，影响了{$affected_rows}条记录，<a href='index.php'>查看</a>";
} else if ($action == "index") {
	//使用findAll查询所有的结果 返回的是二维数组形式
//	$rows = $lamp142->findAll(null, "id DESC", 10); 
	$rows = $lamp142->where()->order("id DESC")->limit(10)->select(); 
	//使用find查找一条 返回一维数组
//	var_dump($lamp142->find("", "id DESC"));exit;
	//引入模板显示
	include("tpl/index.html");
}
