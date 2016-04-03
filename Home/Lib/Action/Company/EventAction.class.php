<?php
class EventAction extends Action{

    private $event_project_cows	='`project_id`,`project_name`';
    private $event_project_cow  ='`project_id`';
/**
  *同步数据库接口
  *update author: zhanghao
  *update time  : 2014-11-13 15:54
  ********************************************/
	public function get_event_database_synchronization(){
		if(!Database($_REQUEST["cid"]))exit;
		$_REQUEST['timestamp']=$_REQUEST['timestamp']==0?1:$_REQUEST['timestamp'];
		$_REQUEST['audience'] =check_null(40080,true,$_REQUEST['audience_id']);
		$rs['project_list']		=D('Sqlite')->get_database_synchronization('project',$this->event_project_cows,$this->event_project_cow,$_REQUEST['timestamp']);
		$rs['event_list']		=D('Sqlite')->get_event_database_synchronization($_REQUEST);
		$rs['event_log_list']	=D('Sqlite')->get_event_log_database_synchronization($_REQUEST);
		$rs['event_notice_list']=D('Sqlite')->get_event_notice_database_synchronization($_REQUEST);
		if($rs){
			$rs['timestamp']=time();
			return_json('0',$rs);
		}else{
			return_json(40001);
		}
	}
	
/**
  *修改当前时间状态
  *update author: zhanghao
  *update time  : 2014-11-13 15:54
  ********************************************/
	public function update_state()
    {
        //init
        if (!Database($_REQUEST["cid"])) exit;
        $_REQUEST['uid'] = check_null(40002, true, $_REQUEST['uid']);
        $_REQUEST['event_state'] = check_null(40051, true, $_REQUEST['event_state']);
        $_REQUEST['event_id'] = check_null(40050, true, $_REQUEST['event_id']);
        $_REQUEST['time_update'] = time();

        $pass_date = D('Event')->get_data_by_id($_REQUEST["event_id"]);

        if ($_REQUEST['event_execution_id'] == null && $pass_date['event_execution_id'] == 0){
            return_json(40046);
            exit();
        }
        if( $_REQUEST['event_acceptance_id']== null && $pass_date['event_acceptance_id'] == 0){
            return_json(40048);
            exit();
        }

        D('Event')->update_state($_REQUEST,$pass_date) ? return_json(0) : return_json(-1);
	}
	
/**
  *创建一个事件
  *update author: zhanghao
  *update time  : 2014-11-13 15:54
  ********************************************/
	public function create_event(){
		//init
		if(!Database($_POST["cid"]))exit;
		switch(''){
			case $_POST['uid']:return_json(40002);return;
			case $_POST['event_name']:return_json(40043);return;
			case $_POST['event_start_time']:return_json(40044);return;
			case $_POST['event_end_time']:return_json(40045);return;
			case $_POST['event_examination_id']:return_json(40047);return;
			case $_POST['project_id']:return_json(40049);return;
		}
		$rs=D('Event')->create_event($_POST);
		if($rs){
			return_json(0);
		}else{
			return_json(-1);
		}
	}

/**
  *添加执行日志文件
  *update author: zhanghao
  *update time  : 2014-11-14 14:37
  ********************************************/
	public function add_execution_log(){
		if(!Database($_REQUEST["cid"]))exit;
		switch(''){
			case $_REQUEST['event_id']:return_json(40050);return;
			case $_REQUEST['uid']:return_json(40002);return;
			case $_REQUEST['event_responds']:return_json(40005);return;
		}
		$_REQUEST['time_insert']=time();
		$rs=D('Event')->add_execution_log($_REQUEST);
		if($rs){
			return_json(0);
		}else{
			return_json(-1);
		}
	}

//获取事件列表
// +---------------------------------------+
// | 获取当前事物的详情                   	   |
// +---------------------------------------+
// | last update author: zhanghao		   |
// | last update time  : 2014-10-28 02:47  |
// +---------------------------------------+

//获取历史事件列表
	public function get_personal_log(){
		switch(''){
			case $_REQUEST["uid"]:return_json(40002);return;
			case $_REQUEST["count"]:$_REQUEST['count']=10;
			case $_REQUEST['page']:$_REQUEST['page']=1;
		}
		if(!Database($_REQUEST["cid"]))exit;
		
		$rs=D('Event')->get_personal_log($_REQUEST);
		if($rs){
			return_json(0,$rs);
		}else{
			return_json(40001);
		}
	}

//事件查询
	public function search_event(){
		if(!Database($_REQUEST["cid"]))exit;
		switch(''){
			case $_REQUEST['uid']:return_json(40002);return;
			case $_REQUEST['conditions']:return_json(40039);return;
		}
		$rs=D('Event')->search_event($_REQUEST);
		if($rs){
			return_json(0,$rs);
		}else{
			return_json(40001);
		}
	}
//删除任务
	public function del_event(){
		if(!Database($_GET["cid"]))exit;
		if(!isset($_GET["event_id"])){
			return_json(40050);
			return;
		}	
		$rs=D('Event')->del_event($_GET["event_id"]);
		if($rs){
			return_json(0);
		}else{
			return_json(-1);
		}
	}

//修改消息显示方式
	public function display_msg(){
		if(!Database($_REQUEST["cid"]))exit;
		switch(''){
			case $_REQUEST['notice_id']:return_json(40075);return;
		}
		$_REQUEST['time_delete']=time();
		$rs=M('event_notice','','DB_MEETING')->where('notice_id='.$_REQUEST['notice_id'])->save($_REQUEST);
		if($rs){
			return_json(0);
		}else{
			return_json(-1);
		}	
	}

/**
  *生成sqlite数据库
  *update author: zhanghao
  *update time  : 2014-11-13 15:51
  ********************************************/
public function create_database_init(){
	try{
		$path=C('SQLITE_TASK_PATH');
		unlink(C('SQLITE_TASK_PATH'));
		$dbh=new PDO("sqlite:{$path}");
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql='';
		$sql .= "CREATE TABLE personal_task(
			task_id integer PRIMARY KEY,
			task_title Varchar DEFAULT NULL,
			task_time Varchar DEFAULT NULL,
			task_mark integer DEFAULT 0,
			task_top integer DEFAULT 0
		);";
		
		$sql .= "CREATE TABLE event(
			event_id integer PRIMARY KEY,
			event_name Varchar DEFAULT NULL,
			event_description TEXT DEFAULT NULL,
			project_id integer DEFAULT 1,
			event_image Varchar DEFAULT NULL,
			event_start_time integer DEFAULT 0,
			event_end_time integer DEFAULT 0,
			event_current_audience integer DEFAULT 0,
			event_current_date date DEFAULT '0000-00-00',
			event_current_state integer DEFAULT 0,
			event_current_responds Text,
			event_launch_id integer DEFAULT 0,
			event_examination_id integer DEFAULT 0,
			event_execution_id integer DEFAULT 0,
			event_acceptance_id integer DEFAULT 0,
			event_launch_name Varchar DEFAULT NULL,
			event_examination_name Varchar DEFAULT NULL,
			event_excution_name Varchar DEFAULT NULL,
			event_acceptance_name Varchar DEFAULT NULL,
			event_launch_portrait Varchar DEFAULT NULL,
			event_examination_portrait Varchar DEFAULT NULL,
			event_excution_portrait Varchar DEFAULT NULL,
			event_acceptance_portrait Varchar DEFAULT NULL
		);";
		
		$sql.="CREATE TABLE event_log(
			log_id integer PRIMARY KEY,
			log_type integer DEFAULT 1,
			audience_id integer DEFAULT 0,
			event_id integer DEFAULT 0,
			event_state integer DEFAULT 0,
			event_responds TEXT DEFAULT NULL,
			event_time_update integer DEFAULT 0
		);";
		
		$sql.="CREATE TABLE event_notice(
			notice_id integer PRIMARY KEY,
			audience_name Varchar DEFAULT NULL,
			event_id integer DEFAULT 0,
			event_state integer DEFAULT 0,
			notice_date Varchar DEFAULT '0000-00-00',
			time_insert integer DEFAULT 0,  
			time_delete integer DEFAULT 0
		);";
		
		$sql.="CREATE TABLE project(
			project_id integer PRIMARY KEY,
			project_name Varchar DEFAULT NULL
		);";
		
		$dbh->exec($sql);
	}catch(Exception $e) {
		echo "error!!:$e";
		exit;
	}
}

//已废接口
//任务列表
/*public function get_events(){
	if(!Database($_REQUEST["cid"]))exit;
	switch(''){
		case $_REQUEST["uid"]:return_json(40002);return;
		case $_REQUEST["count"]:$_REQUEST['count']=10;
		case $_REQUEST['page']:$_REQUEST['page']=1;
	}
	$rs=D('Event')->get_event_list($_REQUEST);
	if(!$rs)
		$rs=NULL;
	$re['event_list']=$rs;
	$re['log_list']=D('Event')->get_personal_log($_REQUEST);
	$re['log_list']=$re['log_list']==false?NULL:$re['log_list'];
	$re['msg_list']=D('Event')->get_personal_msg($_REQUEST);
	$re['msg_list']=$re['msg_list']==false?NULL:$re['msg_list'];
	return_json('0',$re,time());
}*/
//任务详情
/*public function get_event(){
	switch(''){
		case $_REQUEST["cid"]:return_json(40038);return;
		case $_REQUEST["event_id"]:return_json(40050);return;
	}
	if(!Database($_REQUEST["cid"]))exit;
	$rs=D('Event')->get_event_by_id($_REQUEST["event_id"]);
	$log=D('Event')->get_logs($_REQUEST["event_id"]);
	if($rs){
		$rs['log']=$log==array()?null:$log;
		return_json(0,$rs);
	}else{
		return_json(40001);
	}
}*/

}
?>