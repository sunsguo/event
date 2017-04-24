<?php
//启用session
session_start();
//如果session没有放跨站请求标记，则生成一个
if(!isset($_SESSION['token'])){
	$_SESSION['token'] = md5(uniqid(mt_rand(), TRUE));	//true, 十六字符二进制
}

/**
 * 包含必须的配置信息
 */
include_once '../sys/config/db_cred.inc.php';
/**
 * 为配置信息定义常量
 */
foreach ($C as $name => $val){
	define($name, $val);
}

/**
 * 生成一个PDO 对象
 */
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
$dbo = new PDO($dsn, DB_USER, DB_PASS);

/*
 * 定义自动载入类的_-autoload 函数
 */
function __autoload($class){
	$filename = "../sys/class/class." . $class . ".inc.php";
	if(file_exists($filename)){
		include_once $filename;
	}else {
		throw new Exception("fsdlf");
	}
}
?>