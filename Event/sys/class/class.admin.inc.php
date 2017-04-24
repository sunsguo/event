<?php
error_reporting(E_ALL ^ E_NOTICE);
/**
 * 管理添加修改行为
 * @author gy
 *
 */
class Admin extends DB_Connect{
	//用于定义散列密码的长度
	private $_saltLenght = 7;
	
	public function __construct($db=NULL, $saltLength=NULL){
		parent::__construct($db);
		//若传入一个整形，则用他来设置saltLength的值
		if(is_int($saltLength)){
			$this->_saltLenght = $saltLength;
		}
	}
	/**
	 * 检查用户登录信息是否正确
	 * @return 若成功返回TRUE, 失败则返回错误信息
	 */
	public function processLoginForm(){
		//若未提交正确的action，返回出错信息
		if($_POST['action'] != 'user_login'){
			return 'Invalid action supplied for processLoginForm.';
		}
		//转义用户输入数据
		$user_name = html_entity_decode($_POST['user_name'], ENT_QUOTES);
		$user_pass = html_entity_decode($_POST['user_pass'], ENT_QUOTES);
		//若用户存在，则返回数据库中汽配信息
		$sql = "select user_id, user_name, user_pass, user_email from users where user_name=:name limit 1";
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":name", $user_name, PDO::PARAM_STR);	
			$stmt->execute();
			//array_shift 删除数组第一个元素，并返回删除元素的值
			$user = array_shift($stmt->fetchAll());
			//$user = $stmt->fetchAll();
			$stmt->closeCursor();
		}catch (Exception $e){
			die($e->getMessage());
		}
		//若用户名在数据库中并不存在，则返回出错信息
		if(!isset($user)){
			return "Your username or password is invalid";
		}
		$hash = $this->_getSaltedHash($user_pass, $user['user_pass']);
		if($user['user_pass'] == $hash){
		
			//将用户信息以数组的形式保存到 session中
			$_SESSION['user'] = array(
				'id' => $user['user_id'],
				'name' => $user['user_name'],
				'email' => $user['user_email']
			);
			return TRUE;
		}else{
			return "Your password or username invalid ...........";
		}
	}
	/**
	 * 为给定字符串生成一个加盐散列值
	 * @param string $string 即将被散列的字符串
	 * @param string $salt从这个串中提取盐
	 * @return string 加盐之后的散列值
	 */
	private function _getSaltedHash($string, $salt=NULL){
		//如果没有传入盐，则生成一个盐
		if($salt == NULL){
			$salt = substr(md5(time()), 0, $this->_saltLenght);
		}else {
			//如果传入了盐，则从中提取真正的盐
			$salt = substr($salt, 0, $this->_saltLenght);
		}
		//将盐添加到散列值之前，并返回散列值
		return $salt . sha1($salt . $string);
	}
	public function testSaltHash(){
		return $this->_getSaltedHash($string, $salt);
	}
	/**
	* 用户登出
	* @return 成功返回 TRUE 失败返回错误信息
	*/
	public function processLogout(){
		if($_POST['action'] != 'user_logout'){
			return 'invalid action supplied for processLogout.';
		}
		//从当前会话删除用户数据
		session_destroy();
		return TRUE;
	}
}

