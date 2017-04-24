<?php
//����session
session_start();
//���sessionû�зſ�վ�����ǣ�������һ��
if(!isset($_SESSION['token'])){
	$_SESSION['token'] = md5(uniqid(mt_rand(), TRUE));	//true, ʮ���ַ�������
}

/**
 * ���������������Ϣ
 */
include_once '../sys/config/db_cred.inc.php';
/**
 * Ϊ������Ϣ���峣��
 */
foreach ($C as $name => $val){
	define($name, $val);
}

/**
 * ����һ��PDO ����
 */
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
$dbo = new PDO($dsn, DB_USER, DB_PASS);

/*
 * �����Զ��������_-autoload ����
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