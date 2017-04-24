<?php 
//载入必须的初始化文件
include_once '../sys/core/init.inc.php';

//输出页头
$page_title = "Please Log In";
$css_files = array('style.css', 'admin.css');
include_once 'assets/common/header.inc.php';

?>
<div id="content">
	<form action="assets/inc/process.inc.php" method="post">
		<fieldset>
			<legend>Please Log In</legend>
			<label for="user_name">Username</label>
			<input type="text" name="user_name" id="user_name" value=""/>
			<label for="user_pass">Password</label>
			<input type="password" name="user_pass" id="user_pass" value=""/>
			<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
			<input type="hidden" name="action" value="user_login" />
			<input type="submit" name="login_submit" value="Log in" />
			or <a href="index.php">cancel</a>
		</fieldset>
	</form>
</div>
<?php 
//输出页尾
include_once 'assets/common/footer.inc.php';
?>