<?php

/**
 * 创建并维护活动日程表
 * 为了能够访问数据库对象，该类继承自DB_Connect
 */

class Calendar extends DB_Connect{
	/**
	 * 日历根据此日期构建
	 * 格式为 YYYY-MM-DD HH:MM:SS
	 * @var string 日历显示日期
	 */
	private $_useDate;
	
	/**
	 * 日历显示月份
	 * @var int 月份
	 */
	private $_m;
	
	/**
	 * 当前显示月份是哪一年
	 * 
	 * @var int 当前年份
	 */
	private $_y;
	
	/**
	 * 这个月有多少天
	 * @var int 这个月份的天数
	 */
	private $_daysInMonth;
	
	/**
	 * 这个月起始日周几的索引
	 * @var int 这个月从周几开始
	 */
	private $_startDay;
	
	private function _validate($date){
		$pattern = '/^(\d{4}(-\d{2}){2} (\d{2})(:\d{2}){2})$/';
		return preg_match($pattern, $date) == 1 ? TRUE : FALSE;
	}
	
	/**
	 * 创建一个数据库存储有关的数据，
	 * @param object $dbo 数据库连接对象 
	 * @param string $useDate 生成日历使用的日期
	 */
	public function __construct($dbo=NULL, $useDate=NULL){
		//调用父类构造函数
		parent::__construct($dbo);
		/**
		 * 收集并存储该月有关数据
		 */
		if(isset($useDate)){
			$this->_useDate = $useDate;
		}else {
			$this->_useDate = date('Y-m-d H:i:s');
		}
		//把日期转换成时间戳，确定日历要显示的年和月
		$ts = strtotime($this->_useDate);
		$this->_m = date('m', $ts);
		$this->_y = date('Y', $ts);
		//确定这个月有多少天
		$this->_daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->_m, $this->_y);
		//数字表示星期中的第几天
		$ts = mktime(0, 0 , 0, $this->_m, 1, $this->_y);
		$this->_startDay = date('w', $ts);
	}
	
	private function _loadEventData($id=NULL){
		$sql = "select event_id, event_title, event_desc, event_start, event_end from events";
		//如果提供了活动id，则添加一个where子句
		if(!empty($id)){
			$sql .= " where event_id=:id limit 1";
		}else {		//否则找出该月所有活动
			$start_ts = mktime(0, 0, 0, $this->_m, 1, $this->_y);
			$end_ts = mktime(23, 59, 59, $this->_m + 1, 0, $this->_y);
			$start_date = date('Y-m-d H:i:s', $start_ts);
			$end_date = date('Y-m-d H:i:s', $end_ts);
			//找出当前月份的活动
			$sql .= " where event_start between '$start_date' and '$end_date' order by event_start";
		}
		try{
			$stmt = $this->db->prepare($sql);
			//如果id有效则绑定此参数
			if(!empty($id)){
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			}
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			return $result;
		}catch(Exception $e){
			die($e->getMessage());
		}
		
	}
	
	/**
	 * 载入该月全部活动信息到一个数组
	 * @return array 活动信息
	 */
	private function _createEventObj(){
		//载入活动数组
		$arr = $this->_loadEventData();
		$events = array();
		foreach ($arr as $event){
			$day = date('j', strtotime($event['event_start']));
			try{
				$events[$day][] = new Event($event);
			}catch (Exception $e){
				die($e->getMessage());
			}
		}
		return $events;
	}
	
	/**
	 * 用于显示日历和活动的HTML标记
	 * 
	 * 使用存储在类中的数据，载入给定月份的活动数据，生成并返回完整的日历HTML标记
	 * @return string html标记
	 */
	public function buildCalendar(){
		//当前日期yyyy-mm
		$cal_id = date('Y-m', strtotime($this->_useDate));

		//确定日历显示月份，并创建一个用于表示每列星期几的缩写数组
		$cal_month = date('F Y', strtotime($this->_useDate));
		$weekdays = array('Sun', 'Mon', 'Tue', 'Web', 'Thu', 'Fri', 'Sat');
		//给日历标记添加一个标题
		$html = "\n\t<h2 id='month-$cal_id'>$cal_month</h2>";
		for($d=0, $labels=NULL; $d<7; ++$d){
			$labels .= "\n\t\t<li>" . $weekdays[$d] . "</li>";
		}
		//返回用于输出的标记
		$html .= "\n\t<ul class='weekdays'>" .$labels . "\n\t</ul>";
		//生成日历html标记
		$html .= "\n\t<ul>";
		//载入数据
		$events = $this->_createEventObj();
		
		//$t=11当前月份的第几天，$m=12 数字表示当前月份，有前导0  $y=2016,当前年份的四位表示
		for($i=1, $c=1, $t=date('j'), $m=date('m'), $y=date('Y'); $c<=$this->_daysInMonth; $i++){
			//为起始日之前的几天添加class fill 
			$class = $i <= $this->_startDay ? 'fill' : NULL;
			//如果当前处理的日期是今天则，为他添加class today
			if($c==$t && $m==$this->_m && $y==$this->_y){
				$class = 'today';
			}
			//生成列表<li> 的开始和结束
			$ls = sprintf("\n\t\t<li class='%s'>", $class);
			$le = "\n\t\t</li>";
			
			$event_info = NULL; //clear the variable
			//添加日历盒的主体，内容的该月的每一天
			if($this->_startDay < $i && $this->_daysInMonth >= $c){
				//格式化活动数据	
				if(isset($events[$c])){
					foreach ($events[$c] as $event){
						$link = '<a href="view.php?event_id=' . $event->id . '">' . $event->title . '</a>';
						$event_info .= "\n\t\t\t$link";
					}
				}
				$date = sprintf("\n\t\t\t<strong>%02d</strong>", $c++);
			}else {
				$date = "&nbsp;";
			}
			//如果赶上周六就新起一行，
			$wrap = $i != 0 && $i%7 == 0 ? "\n\t</ul>\n\t<ul>" : NULL;
			//将碎片组成一个完整的东西
			$html .= $ls . $date . $event_info . $le . $wrap;			
		}
		//为最后的几天添加填充项
		while ($i%7 != 1){
			$html .= "\n\t\t<li class='fill'>&nbsp;</li>";
			$i++;
		}
		//关闭最后一个ul标签
		$html .= "\n\t</ul>\n\n";
		//若用户已经登陆，显示管理选项
		$admin = $this->_adminGeneralOptions();
		
		return $html . $admin;
	}	
	/**
	 * 根据event_id 得到活动对象
	 * @param int 活动id
	 * @return object 活动对象
	 */
	private function _loadEventById($id){
		//如果id为空，返回空
		if(empty($id)){
			return NULL;
		}
		//载入活动信息
		$event = $this->_loadEventData($id);
		//返回event对象
		if(isset($event[0])){
			return new Event($event[0]);
		}else{
			return NULL;
		}
	}
	/**
	 * 获得活动信息html
	 * @param int $id 活动id
	 * @param String 用于显示活动信息的基本html
	 */
	public function displayEvent($id){
		//确保传入有效id
		if(empty($id)){
			return NULL;
		}
		$id = preg_replace('/[^0-9]/', '', $id);		//确保id是整数
		//从数据库载入活动
		$event = $this->_loadEventById($id);
		//为date, start , end 生成相应的字符串
		$ts = strtotime($event->start);
		$date = date('F d, Y', $ts);
		$start = date('g:ia', $ts);
		$end = date('g:ia', strtotime($event->end));
		
		//若用户已经登陆，载入管理选选
		$admin = $this->_adminEntryOptions($id);
				//生成并返回html标记
		return "<center><h2>$event->title</h2>"
				. "\n\t<p class='dates'>$date, $start&mdash;$end</p>"
				. "\n\t<p>$event->description</p>$admin</center>" ;
	}
	/**
	 * 生成一个修改或创建活动的表单
	 * @return 表单标记字符串
	 */
	public function displayForm(){
		//检查是否传如活动id
		if(isset($_POST['event_id'])){
		//	echo $_POST['event_id'];
			$id = (int)$_POST['event_id'];	//强制类型转换，确保输入数据安全
		}else {
			$id = NULL;
		}
		//标题/提交按钮
		$submit = "Create a New Event";
		//若传入活动id，则载入相应的活动数据
		if(!empty($id)){
			$event = $this->_loadEventById($id);
			//若未找到相应的活动，则返回空
			if(!is_object($event)){
				return NULL;
			}
			$submit = "Edit This Event";
		}
		//生成标记
		return "
			<form action='assets/inc/process.inc.php' method='post'>
				<fieldset>
					<legend>{$submit}</legend>
					<label for='event_title'>Event Title</label>
					<input type='text' name='event_title' id='event_title' value='$event->title' />
					<label for='event_start'>Start Time</label>
					<input type='text' name='event_start' id='event_start' value='$event->start' />
					<label for='event_end'>End Time</label>
					<input type='text' name='event_end' id='event_end' value='$event->end' />
					<label for='event_description'>Event Description</label>
					<textarea name='event_description' id='event_description'>$event->description</textarea>
					<input type='hidden' name='event_id' value='$event->id' />
					<input type='hidden' name='token' value='$_SESSION[token]' />	<!--用来方法伪造跨站请求  -->
					<input type='hidden' name='action' value='event_edit' />
					<input type='submit' name='event_submit' value='$submit' />
				or <a href='index.php'>cancel</a>
				</fieldset>
			</form> "	;
	}
	/**
	 * 验证表单保存跟新活动
	 * @return 成功返回TRUE, 失败返回错误信息
	 */
	public function processForm(){
		//action设置不正确，退出
		if($_POST['action'] != 'event_edit'){
			return "The Method processForm was accessed incorrect";
		}
		$title = html_entity_decode($_POST['event_title'], ENT_QUOTES);	//解码单引号，双引号
		$desc = html_entity_decode($_POST['event_description'], ENT_QUOTES);
		$start = html_entity_decode($_POST['event_start'], ENT_QUOTES);
		$end = html_entity_decode($_POST['event_end'], ENT_QUOTES);

		if(!$this->_validate($start) || !$this->_validate($end)){
			alert("格式不对！");
			return "Invalidate date format !, use YYYY-mm-dd HH:MM:SS";
		}
		//如果体提交数据中没有活动id则常见一个
		if(empty($_POST['event_id'])){
			$sql = "insert into events(event_title, event_desc, event_start, event_end) 
					values(:title, :desc, :start, :end)";
		}else {		//否则更新这个活动
			$id = (int)$_POST['event_id'];
			$sql = "update events set event_title=:title, 
									  event_desc=:desc, 
									  event_start=:start, 
									  event_end=:end 
								  where event_id=$id";
		}
		//绑定参数执行查询
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":title", $title, PDO::PARAM_STR);
			$stmt->bindParam(":desc", $desc, PDO::PARAM_STR);
			$stmt->bindParam(":start", $start, PDO::PARAM_STR);
			$stmt->bindParam(":end", $end, PDO::PARAM_STR);
			$stmt->execute();	//执行查询
			$stmt->closeCursor();	//交出stmt共下一次查询使用
			return $this->db->lastInsertId();	//调用PDO 中的方法输出新活动的id
		}catch (Exception $e){
			return $e->getMessage();
		}
	}
	
	private function _adminGeneralOptions(){
		//显示管理界面
		if(isset($_SESSION['user'])){
			return "
				<a href='admin.php' class='admin'>+ Add a New Event</a>
				<form action='assets/inc/process.inc.php' method='post'>
					<input type='submit' value='Log out' class='admin' />
					<input type='hidden' name='token' value='$_SESSION[token]' />
					<input type='hidden' name='action' value='user_logout' />
				</form>";
		}else{
			return "
				<a href='login.php'>Log In</a>";
		}
			
	}
	
	/**
	 * 为给定活动id生成修改和删除选项按钮
	 * @param $id 活动id
	 * @return string 修改删除选项标记
	 */
	private function _adminEntryOptions($id){
		if(isset($_SESSION['user']))	{
			return "<div class='admin-options'>
						<form action=\"admin.php\" method=\"post\">
							<p>
								<input type=\"submit\" name=\"edit_event\" value=\"Edit this Event\">
								<input type=\"hidden\" name=\"event_id\" value='$id'>
							</p>
						</form>
						<form action=\"confirmdelete.php\" method=\"post\">
							<p>
								<input type=\"submit\" name=\"delete_event\" value=\"Delete this Event\">
								<input type=\"hidden\" name=\"event_id\" value='$id'>
							</p>
						</form>
					</div>";	
		}else{
			return NULL;
		}
	}
	/**
	 * 确认一个活动是否被删除， 并执行之
	 * 在单击删除按钮是会生成一个确认窗口，如果用户点击确认则删除之，并将用户送回主页，
	 * 如果用户决定不删则不执行任何操作，并将用户送回主页
	 * @param int $id 活动id
	 * return 确认删除可能返回异常或null，否则返回空
	 */
	public function confirmDelete($id){		
		if(empty($id)){
			return NULL;
		}
		$id = preg_replace('/[^0-9]/', '', $id);	
		//确认表单被提交，且具有一个正确的标记，检查表单提交的数据
		if(isset($_POST['confirm_delete']) && $_POST['token']==$_SESSION['token']){
			//确认用户删除操作
			if($_POST['confirm_delete'] == 'Yes, Delete It'){
				$sql = 'delete from events where event_id=:id limit 1';
				try{
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':id', $id, PDO::PARAM_INT);
					$stmt->execute();
					$stmt->closeCursor();	
					header("Location: index.php");
					return "";
				}catch (Exception $e){
					return $e->getMessage();
				}
			}else {
				header("Location: index.php");
				return ;
			}
		}
		//若表单尚未提交则显示他
		$event = $this->_loadEventById($id);
		if(!is_object($event)){
			header("Location: index.php");
		}
		return "
		    <form action='confirmdelete.php' method='post'>
				<h2>Are you sure you want to delete '$event->title'</h2>
				<p>There is <strong>no undo</strong> if you continue.</p>
				<p>
				<input type='submit' name='confirm_delete' value='Yes, Delete It' />
				<input type='submit' name='confirm_delete' value='Nope! just Kidding!' />
				<input type='hidden' name='event_id' value='$id' />
				<input type='hidden' name='token' value='$_SESSION[token]' />
			</form>
				";
	}
}
?>













