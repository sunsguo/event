<?php
session_start();

//确保传入活动id
if(isset($_POST['event_id']) && isset($_SESSION['user'])){
	//确保传入的id是个整数
	$id = preg_replace('/[^0-9]/', '', $_POST['event_id']);
	//如果传入的id不是整数，则页面重定向到主页面
	if(empty($id)){
		header('Location: index.php');
		exit;
	}
}else {
	header('Location: index.php');
	exit;
}
//载入必须的初始化文件
include_once '../sys/core/init.inc.php';

//输出页头
$page_title = "View Event";
$css_files = array('style.css', 'admin.css');
include_once 'assets/common/header.inc.php';

//载入日历
$cal = new Calendar($dbo);
$markup = $cal->confirmDelete($id);
?>
<div id="content">
	<?php echo $markup ; ?>
</div>

<?php 
//输出页尾
include_once 'assets/common/footer.inc.php';
?>