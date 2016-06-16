<?php

/* 
 * Copyright(c)2016 All rights reserved.
 * @Licenced  http://www.w3.org
 * @Author  liutian<1538731090@qq.com> liutian_jiayi
 * @Create on 2016-5-19 11:58:15
 * @Version 2.0
 */
class Model extends Mysql{
	public $tbname;
	private $error;	//存放错误信息
	private $data = array();//存放有效数据
	private $option = array(
		"FIELD" => "*",
		"WHERE" => "",
		"ORDER" => "",
		"LIMIT" => "",
	);
	//对要入库的数据进行创建
	public function create($data = NULL) {
		//1、类型约束
		if (!is_array($data) && $data) {
			$this->error = "非法数据对象";
			return FALSE;
		}
		//2、默认POST值
		empty($data) && $data = $_POST;
		
		//3、字段映射 去除多余的数据值
		$data = $this->keyMap($data);
		
		//4、数据值过滤
		foreach ($data as $field=>$val) {
			$data[$field] = $this->escape($val);//对字段值进行转义 再重新赋给该字段
		}
		//5、全句化数据
		$this->data = $data;
		return TRUE;
	}
	
	public function getError() {
		return $this->error;
	}
	
	//字段映射的方法
	private function keyMap($data) {//array("name" => "xiao'xiao\xiao", "p" => 2);
		//获取所有的数据库中的字段值信息
		$rows = $this->query("DESC {$this->tbname}");
		$cols = array();	//存放所有字段的数组
		foreach ($rows as $row) {
			$cols[$row['Field']] = $row['Field'];//$cols["id"] = "id" $cols["name"] = "name";
		}
		//交叉取相同索引
		return array_intersect_key($data, $cols);
	}
	
	//对值进行转移 返回转移后的值
	public function escape($val) {
		if (get_magic_quotes_gpc()) {//如果开启了魔术引用 则反转移回原来的状态
			$val = stripslashes($val);
		}
		//统一使用mysqli_real_escape_string转移
		return mysqli_real_escape_string($this->link, $val);
	}
	
	//连贯操作 数据部分的组装
	public function data($data = NULL) {
		$this->create($data);
		return $this;
	}
	
	/**
	 * 执行插入的方法
	 * @param string $tbname	要操作的表明
	 * @param array $data		要入库的数据 以关联索引数组形式存在
	 * @return int			返回最近插入的行ID 
	 */
	public function add($data = NULL) {//array("uname" => "xiaoming", "password" => "12332")
		!empty($data) && $this->create($data);
		//$sql = "INSERT INTO %TAB% (%FIELDS%) VALUES (%VALS%);"
		//1、设置插入操作的sql模板
		$sql = "INSERT INTO {$this->tbname} (%FIELDS%) VALUES (%VALS%)";
		//2、重组数据 提取出字段和值
		$fields = array();	//存放所有的字段
		$vals = array();	//存放所有的值
		foreach ($this->data as $field=>$val) {
			$fields[] = $field;	//$fields = array("uname", "password");
			$vals[] = "'".$val."'";//$vals = array("xiaomoing", "123321");
		}
		//3、替换掉模板中占位符
		$sql = str_replace(array("%FIELDS%", "%VALS%"), array(implode(",", $fields), implode(",", $vals)), $sql);
		//4、执行该sql
//		echo $sql;
		$this->execute($sql);
		//5、返回结果
		return mysqli_insert_id($this->link);
	}
	
	/**
	 * 执行删除的方法
	 * @param string $tbname	要操作的表明
	 * @param mixed $where	数组或字符串形式的条件
	 * @return int			返回删除的行数
	 */
	public function delete($id = NULL) {//"uid=3 AND uname='dddd'", array("uid" => 3, "uname"=>"ha") ====:> array("uid=3", "uname='ha'") ====> implode(" AND ", )
		//设置模板
		$sql = "DELETE FROM {$this->tbname} %WHERE%";
		 
		//手动的组装where条件
		!empty($id) && $this->where(array($this->pk => $id));
		
		//替换模板占位符
		$sql = str_replace("%WHERE%", $this->option["WHERE"], $sql);
		
		//执行sql
		$this->execute($sql);
		//返回结果
		return mysqli_affected_rows($this->link);
	}
	
	/**
	 * 修改数据的方法
	 * @param string $tbname	要操作的表
	 * @param array $data		要更新的数据值 以字段=>值 的关联索引数组形式传参
	 * @param mixed $where	字符串或者数组形式的where条件
	 * @return int			返回修改的行数
	 */
	public function save($data = NULL) {
		!empty($data) && $this->create($data);
		
		//设置修改语句的sql模板
		$sql  = "UPDATE {$this->tbname} SET %DATA% %WHERE% ";
		//重组data数据值 array("uname" => "ddsd", "password" => "dksjdj") => array("uname='ddsd'", "password='dksjd'") => implode(",", $arr);
		$join = array();	//存放一个个临时字段对应值形式的元素
		foreach ($this->data as $field=>$val) {
			$join[] = $field."='".$val."'"; //$join = array("uname='3'", "password='dksjd'")
		}
		$data = implode(",", $join);
		
		//替换模板中的占位符
		$sql = str_replace(array("%DATA%","%WHERE%"),array($data, $this->option['WHERE']), $sql);
		
		//执行sql语句
		$this->execute($sql);
		//返回结果
		return mysqli_affected_rows($this->link);
	}
	
	/**
	 * 查询操作的封装
	 * @param string $tbname	要查询的表明
	 * @param mixed $where	字符串或者数组形式的where条件
	 * @param string $order	排序规则
	 * @param string $limit	显示的限制条数
	 * @param string $field	查询显示的字段
	 * @return array			查询结果集【二维】
	 */
	public function findAll($where = NULL, $order = NULL, $limit = NULL,$field = NULL) {
		//设置查询的sql模板
		$field == NULL && $field = "*";//优雅
		$sql = "SELECT {$field} FROM {$this->tbname} %WHERE% %ORDER% %LIMIT%";
		//重组where条件
		if ($where != NULL) {
			if (is_string($where)) {
				$where = " WHERE ".$where;
			} else if (is_array($where)) {
				$join = array();
				foreach ($where as $field=>$val) {
					$join[] = $field."='".$val."'";
				}
				$where = " WHERE ".implode(" AND ", $join);
			}
		} else {
			$where = "";
		}
		//重组排序条件
		if ($order == NULL) {
			$order = "";
		} else {
			$order = " ORDER BY ".$order;	// uid DESC|uname ASC
		}
		//重组limit条件
//		$limit != NULL && $limit = " LIMIT ".$limit;
		$limit = ($limit == NULL ? "" : " LIMIT ".$limit);
		
		//替换模板中的占位符
		$sql = str_replace(array("%WHERE%", "%ORDER%", "%LIMIT%"), array($where, $order, $limit), $sql);
		//执行sql
		return $this->query($sql);
	}
	
	/**
	 * 返回一条结果集的方法 
	 * @param string $tbname	查询操作的表明
	 * @param mixed $where	where条件
	 * @param string $order	排序条件
	 * @return array			一维数组形式的结果
	 */
	public function find($where = NULL, $order = NULL) {
		$rowsets = $this->findAll($where, $order, 1);
		if (!empty($rowsets)) {
			return $rowsets[0];
		} else {
			return array();
		}
	}
	
	/**
	 * 查询结果集总数的方法
	 * @param string $tbname	要操作的表明
	 * @param mixed $where	where条件
	 * @return int			结果集总数
	 */
	public function count($where = NULL) {
		//$sql = "SELECT COUNT(*) FROM $tbname %WHERE%";
		$rowsets = $this->findAll($where, NULL, 1, "COUNT(*) as ILU");
		return $rowsets[0]['ILU'];
	}
	
	//字段值自增的方法
	public function setInc($field, $where = NULL, $step = 1) {
		//设置模板
		$sql = "UPDATE {$this->tbname} SET {$field} = {$field} + {$step} %WHERE%";
		//重组where条件
		if ($where != NULL) {
			if (is_string($where)) {
				$where = " WHERE ".$where;
			} else if (is_array($where)) {
				$join = array();
				foreach ($where as $field=>$val) {
					$join[] = $field."='".$val."'";
				}
				$where = " WHERE ".implode(" AND ", $join);
			}
		} else {
			$where = "";
		}
		//替换占位符
		$sql = str_replace("%WHERE%", $where, $sql);
		//执行该sql
		$this->execute($sql);
		//返回结果
		return mysqli_affected_rows($this->link);
	}
	
	//字段值自减的方法
	public function setDec($field, $where = NULL, $step = 1) {
		return $this->setInc($field, $where, -$step);
	}
	
 
	//组装字段部分的数据
	public function field($field = NULL) {
		!empty($field) && $this->option["FIELD"] = $field;
		//返回模型对象
		return $this;
	}
	
	//组装where条件部分
	public function where($where = NULL) {
		if ($where != NULL) {
			if (is_string($where)) {
				$this->option["WHERE"] = " WHERE ".$where;
			} else if (is_array($where)) {
				$join = array();
				foreach ($where as $field=>$val) {
					$join[] = $field."='".$val."'";
				}
				$this->option["WHERE"] = " WHERE ".implode(" AND ", $join);
			}
		}
		//返回模型对象
		return $this;
	}
	
	//设置排序规则
	public function order($order = NULL) {
		!empty($order) && $this->option["ORDER"] = " ORDER BY ".$order;
		//返回模型对象
		return $this;
	}
	
	//限制条数 10 "10", "5,10", array(5,10)
	public function limit($limit = NULL) {
		if (is_array($limit)) $limit = implode(",", $limit);
		!empty($limit) && $this->option["LIMIT"] = " LIMIT ".$limit;
		return $this;
	}
	
	//连贯操作的执行方法
	public function select() {
		//设置sql模板
		$sql = "SELECT %FIELD% FROM {$this->tbname} %WHERE% %ORDER% %LIMIT%";
		//替换模板占位符
		$sql = str_replace(array("%FIELD%", "%WHERE%", "%ORDER%", "%LIMIT%"), $this->option, $sql);
		//执行该sql
		return $this->query($sql);
	}
}
