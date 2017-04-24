<?php
error_reporting(E_ALL ^ E_NOTICE);
//启用session
session_start();

//包含配置文件
include_once '../../../sys/config/db_cred.inc.php';

foreach ($C as $name => $value){
	define($name, $value);
}
//为表单action创建一个查找数组
$actions = array(
    'event_view' => array(
        'object' => 'Calendar',
        'method' => 'displayEvent' 
    ),
    'edit_event' => array(
        'object' => 'Calendar',
        'method' => 'displayForm'
    ),
    'event_edit' => array(
        'object' => 'Calendar',
        'method' => 'processForm'
    ),
    'delete_event' => array(
        'object' => 'Calendar',
        'method' => 'confirmDelete'
    ),
    'confirm_delete' => array(
        'object' => 'Calendar',
        'method' => 'confirmDelete'
    )
);

if(isset($actions[$_POST['action']])){
    $use_array = $actions[$_POST['action']];
    $obj = new $use_array['object']($dbo);
    if(isset($_POST['event_id'])){
        $id = (int)$_POST['event_id'];
     //   echo $id;
    }else{
        $id = NULL;
    }
    if($_POST['action'] == 'event_view'){
        echo $obj->displayEvent($id);
    }else if($_POST['action'] == 'edit_event'){
        echo $obj->displayForm($id);
    }else if($_POST['action'] == 'event_edit'){
        echo $obj->processForm($id);
    }else if($_POST['action'] == 'delete_event'){
        echo $obj->confirmDelete($id);
    }else if($_POST['action'] == 'confirm_delete'){
        echo $obj->confirmDelete($id);
    }

}



function __autoload($class_name){
	$file_name = "../../../sys/class/class." . strtolower($class_name) . ".inc.php";
//	echo $file_name;
	if(file_exists($file_name)){
		include_once $file_name;
	}
}
?>