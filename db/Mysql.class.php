<?php

/* 
 * Copyright(c)2016 All rights reserved.
 * @Licenced  http://www.w3.org
 * @Author  liutian<1538731090@qq.com> liutian_jiayi
 * @Create on 2016-5-19 11:57:47
 * @Version 1.0
 */
//mysql数据库操作基类 数据库驱动
class Mysql {
	public $link;	//资源连接符
	public $sql;	//执行的sql
	/**
	 * 连接数据库的方法:
	 * 1、连接数据库	$host, $user, $pwd
	 * 2、选择数据库	$dbname
	 * 3、设置字符编码	$charset
	 */
	public function __construct($host, $user, $password, $dbname, $charset = "utf8") {
		$this->link = mysqli_connect($host, $user, $password) or die("连接数据库失败");
		mysqli_select_db($this->link, $dbname) or die("选择数据库失败");
		mysqli_set_charset($this->link, $charset);
	}
	
	/**
	 * 执行查询的方法 传入的sql语句一定是查询语句
	 * @param string $sql		要执行的查询sql
	 * @return array			返回查询结果集【二维】
	 */
	public function query($sql) {
		//全局化sql
		$this->sql = $sql;
		$result = mysqli_query($this->link, $this->sql);
		//查询失败 执行报错
		if ($result === FALSE) $this->showError();
		//如果查询结果条数为0 则返回空数组
		else if (mysqli_num_rows($result) == 0) return array();
		//取结果集
		else {
			$rows = array();	//接收最终结果的数组
			while (FALSE != $row = mysqli_fetch_assoc($result)) {
				//将每次取出的一行结果 放入最终结果集数组
				array_push($rows, $row);
			}
			//释放结果集
			mysqli_free_result($result);
			return $rows;
		}
	}
	
	//执行增删改的方法
	public function execute($sql) {
		//全局化sql
		$this->sql = $sql;
		$result = mysqli_query($this->link, $this->sql);
		//执行失败 进行报错
		if ($result === FALSE) $this->showError();
		else return TRUE;
	}
	
	//错误处理的方法 显示错误的语句、错误号和错误提示信息
	public function showError() {
		echo "<h4>sql语句：".$this->sql."</h4>";
		echo "<h4>错误号：".  mysqli_errno($this->link)."</h4>";
		echo "<h4>错误提示：". mysqli_error($this->link)."</h4>";
	}
	
	//析构函数 释放资源 关闭数据库连接
	public function __desctruct() {
		mysqli_close($this->link);
	}
}
