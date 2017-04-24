<?php

error_reporting(E_ALL ^ E_NOTICE);
//启用session
session_start();

//包含配置文件
include_once '../../../sys/config/db_cred.inc.php';

foreach ($C as $name => $value){
	define($name, $value);
}
//以表单actions为键生成一个关联数组
$actions = array('event_edit' => array(
				'object' => 'Calendar',
				'method' => 'processForm',
				'header' => 'Location: ../../index.php'
			),
				'user_login' => array(
					'object' => 'Admin',
					'method' => 'processLoginForm',
					'header' => 'Location: ../../index.php'
			),
				'user_logout' => array(
					'object' => 'Admin',
					'method' => 'processLogout',
					'header' => 'Location: ../../index.php'
			)	
		);		
//保证session中的防跨站标记和提交的一致,且请求action合法
if($_POST['token'] == $_SESSION['token'] && isset($actions[$_POST['action']])){
	$use_array = $actions[$_POST['action']];
	$obj = new $use_array['object']($dbo);
	//不能在执行是设置函数签名
	if($use_array['method'] == 'processForm' && TRUE === $msg = $obj->processForm()){
		header($use_array['header']);
		exit;
	}else if($use_array['method'] == 'processLoginForm' && TRUE === $msg = $obj->processLoginForm()){
		header($use_array['header']);
		exit;
	}else if($use_array['method'] == 'processLogout' && TRUE === $msg = $obj->processLogout()){
		header($use_array['header']);
		exit;
	}else {
		die($msg);	//如果出错则返回信息，退出程序，
	}
}else {		//token/action分发重定向到主页
	header($use_array['header']);
	exit;
}

function __autoload($class_name){
	$file_name = "../../../sys/class/class." . strtolower($class_name) . ".inc.php";
//	echo $file_name;
	if(file_exists($file_name)){
		include_once "$file_name";
	}
}













