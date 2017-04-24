<?php

/**
 * 连接数据库操作
 */
class DB_Connect{
	//保存数据库连接对象
	protected $db;
	
	/**
	 * 查找数据库连接对象，若不存在久生成一个
	 */
	protected function __construct($dbo = NULL){
		if(is_object($dbo)){
			$this->db = $dbo;
		}else{
			//定义常量，连接数据库
			$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
			try{
				$this->db = new PDO($dsn, DB_USER, DB_PASS);
			}catch(Exception $e){
				//如果数据库连接失败，输出错误信息
				die($e->getMessage());
			}
		}
	}
}