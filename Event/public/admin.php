<?php
//隐藏警告信息
error_reporting(E_ALL ^ E_NOTICE);

//包含初始化页面
include_once '../sys/core/init.inc.php';

//如果用户没有登录，就把他们送回主页
if(! isset($_SESSION['user'])){
	header("Location: index.php");
}

//输出页头
$page_title = "Add/Edit Event";
$css_files = array('style.css', 'admin.css');
include_once 'assets/common/header.inc.php';
//载入日历
$cal = new Calendar($dbo);
?>

<div id="content">
	<?php echo $cal->displayForm(); ?>
</div>

<?php
//输出页尾
include_once 'assets/common/footer.inc.php';
?>