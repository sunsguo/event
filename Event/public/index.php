<?php
/*
 * 锟斤拷锟斤拷锟斤拷锟铰伙拷锟侥硷拷
 */
include_once '../sys/core/init.inc.php';

$cal = new  Calendar($dbo, "2010-01-01 12:00:00");

$page_title = "Events Calendar";
$css_files = array('style.css', 'admin.css', 'ajax.css');

/*
 * 包含头文件
 */
include_once 'assets/common/header.inc.php';
?>

<div id="content">
	<?php echo $cal->buildCalendar(); ?>
</div>
<?php 
	/**
	 * 包含页脚
	 */
	include 'assets/common/footer.inc.php';
?>
