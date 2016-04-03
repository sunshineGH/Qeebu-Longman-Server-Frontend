<?php
class MeetingAction extends Action{


//----------------------------------insert--------------------------------------------
	/**
	 *create meeting
	 *update author: zhanghao
	 *update time  : 2014-11-18 11:12
	 ********************************************/
	public function create_meeting(){

		if(!Database($_POST['cid']))exit;
		if($_POST['meeting_endTime']<=time()){
			return_json(40059);return;
		}

		$_POST['uid']				= check_null(40002,true,$_POST['uid']);
		$_POST['meeting_title'] 	= check_null(40057,true,$_POST['meeting_title']);
		$_POST['meeting_startTime'] = check_null(40058,true,$_POST['meeting_startTime']);
		$_POST['meeting_local'] 	= check_null(40060,true,$_POST['meeting_local']);
		$_POST['meeting_join'] 		= check_null(40061,true,$_POST['meeting_join']);
		$_POST['schedule_date']		= date('Y-m-d',$_POST['meeting_startTime']);
		$_POST['meeting_local_type']= $_POST['meeting_local_type']==NULL?0:$_POST['meeting_local_type'];
		$_POST['time_insert']		= time();

		//选择会议室查看会议室预定情况
		if($_POST['meeting_local_type'] != 1){
			$_POST['meeting_room_id']= check_null(40076,true,$_POST['meeting_room_id']);
			$_POST['meeting_local']	 = D('Meeting')->get_meeting_local($_POST['meeting_room_id']);
			if(!(D('Meeting')->schedule_meeting_room($_POST))){
				return_json(40077);
				return;
			}
		}

		if($_REQUEST['cid'] == 102){
			$rs=D('Meeting')->create_meeting($_POST);
		}else{
			$rs=D('Meeting')->create_meeting_reg($_POST);
		}

		if($rs){
			return_json(0);
		}else{
			return_json(-1);
		}
	}

//----------------------------------delete--------------------------------------------

//----------------------------------update--------------------------------------------
	public function update_meeting_info(){
		if(!Database($_REQUEST["cid"]))exit;
		$_REQUEST["meeting_id"] = check_null(40062,true,$_REQUEST["meeting_id"]);
		$_REQUEST["uid"] 		= check_null(40002,true,$_REQUEST["uid"]);
		$_REQUEST['time_update']=time();

		if($_REQUEST['cid'] == 102){
			$rs=D('Meeting')->update_meeting_info($_REQUEST);
		}else{
			$rs=D('Meeting')->update_meeting_info_reg($_REQUEST);
		}

		if($rs){
			return_json(0);
		}else{
			return_json(-1);
		}
	}
//----------------------------------select--------------------------------------------
//获取日历接口
	public function get_meeting_calendar_list(){

		if(!Database($_REQUEST["cid"]))exit;
		$_REQUEST['uid']=check_null(40002,true,$_REQUEST['uid']);

		$rs=D('Meeting')->get_meeting_calendar_list($_REQUEST);
		if($rs){
			return_json('0',$rs,time());
		}else{
			return_json('40001');
		}
	}

//获取会议列表
/*
 * type = [1=>'我的会议',2=>'全体会议']
 * */
	public function get_meeting_list(){

		if(!Database($_REQUEST["cid"]))exit;
		$_REQUEST['uid']  =check_null(40002,true,$_REQUEST['uid']);
		$_REQUEST['count']=check_null(10,false,$_REQUEST['count']);
		$_REQUEST['page'] =check_null(1 ,false,$_REQUEST['page']);

		if($_REQUEST['cid'] != 102){
			if($_REQUEST['type']!= 2)
				$rs = D('Meeting')->get_meeting_list_reg($_REQUEST);
			else  //我的会议
				$rs = D('Meeting')->get_my_meeting_list_reg($_REQUEST);
		}else{
			if($_REQUEST['type']!= 2)
				$rs = D('Meeting')->get_meeting_list($_REQUEST);
			else  //我的会议
				$rs = D('Meeting')->get_my_meeting_list($_REQUEST);
		}

		if($rs){
			return_json('0',$rs,time());
		}else{
			return_json('40001');
		}	
	}
//查询当前room
    public function check_idle_meeting_room(){
        if(!Database($_REQUEST["cid"]))exit;
        $rs=D('Meeting')->check_idle_meeting_room($_REQUEST);
        if($rs){
            return_json(0,$rs);
        }else{
            return_json(-1);
        }
    }

//获取参会人
	public function get_meeting_join_for_idg(){

		if(!Database($_REQUEST["cid"]))exit;
		$_REQUEST["meeting_id"] = check_null(40062,true,$_REQUEST["meeting_id"]);

		$rs=D('Meeting')->get_meeting_join_for_idg($_REQUEST);
		if($rs){
			return_json(0,$rs);
		}else{
			return_json(40001);
		}
	}

//获取当前meeting的信息
	public function get_meeting_details(){

		if(!Database($_REQUEST["cid"]))exit;

		$_REQUEST["meeting_id"] = check_null(40062,true,$_REQUEST["meeting_id"]);
		$_REQUEST['time_insert']= time();

		if($_REQUEST['cid'] == 102){
			$rs=D('Meeting')->get_meeting_by_id($_REQUEST['meeting_id']);
		}else{
			$rs=D('Meeting')->get_meeting_by_id_reg($_REQUEST['meeting_id']);
		}
		if($rs){
			return_json(0,$rs);
		}else{
			return_json(-1);
		}
	}
	
//查询会议参会人信息
	public function get_meeting_join(){
		$_REQUEST["meeting_id"] = check_null(40062,true,$_REQUEST["meeting_id"]);
		if(!Database($_REQUEST["cid"]))exit;

		if($_REQUEST['cid'] == 102){
			$rs=D('Meeting')->get_meeting_join($_REQUEST['meeting_id']);
		}else{
			$rs=D('Meeting')->get_meeting_join_reg($_REQUEST['meeting_id']);
		}

		if($rs){
			return_json(0,$rs);
		}else{
			return_json(40001);
		}
	}
	
//修改会议状态
	public function update_join_state(){
		if(!Database($_REQUEST["cid"]))exit;
		switch(''){
			case $_REQUEST['uid']:return_json(40002);return;
			case $_REQUEST['meeting_id']:return_json(40062);return;
			case $_REQUEST['join_state']:return_json(40063);return;
		}
		$rs=D('Meeting')->update_join_state($_REQUEST);
		if($rs){
			return_json(0);
		}else{
			return_json(-1);
		}
	}
/*
 * @name
 * 修改会议信息
 *
 * @arg
 * cid int 缺省 返回 40038
 * uid int 缺省 返回 40002
 * meeting_title 缺省 返回 40057
 * meeting_startTime 缺省 返回 40058
 * meeting_endTime 缺省 返回 40059
 * meeting_local 会议室名字
 * meeting_description 可选
 * meeting_join 参会人id字符串 // meeting_join 格式为 1,2,3,4,5
 * meeting_room_id 会议室id
 *
 * @update
 * 2014/12/10 10:26 zhanghao
 * */

	
//获取一条默认信息
	public function get_meeting_default(){
		if(!Database($_REQUEST["cid"]))exit;
		switch(''){
			case $_REQUEST["uid"]:return_json(40002);return;
			case $_REQUEST['meeting_startTime']:return_json(40058);return;
			case $_REQUEST['meeting_endTime']:return_json(40059);return;
		}
		$rs=D('Meeting')->check_idle_meeting_room_by_uid($_REQUEST);
		if($rs){
			return_json(0,$rs);
		}else{
			return_json(-1);
		}
	}
	
//获取所有的楼座信息
	public function get_meeting_room_building(){
		if(!Database($_REQUEST["cid"]))exit;
		switch(''){
			case $_REQUEST['local_id']:return_json(40069);return;
		}
		$rs=D('Meeting')->get_meeting_room_building($_REQUEST['local_id']);
		if($rs){
			return_json(0,$rs);
		}else{
			return_json(40001);
		}
	}
	
//获取所有的楼层信息
	public function get_meeting_room_floor(){
		if(!Database($_REQUEST["cid"]))exit;
		switch(''){
			case $_REQUEST['building_id']:return_json(40070);return;
		}
		$rs=D('Meeting')->get_meeting_room_floor($_REQUEST['building_id']);
		if($rs){
			return_json(0,$rs);
		}else{
			return_json(-1);
		}
	}
	
//搜索会议返回会议列表
	public function search_meeting(){

		if(!Database($_REQUEST["cid"]))exit;
		switch(''){
			case $_REQUEST['conditions']:return_json(40039);return;
			case $_REQUEST['searchType']:return_json(40072);return;
		}

		$rs=D('Meeting')->search_meeting($_REQUEST);
		if($rs){
			return_json(0,$rs);
		}else{
			return_json('40001');
		}	
	}
	
//取消会议接口
	public function cancel_meeting(){

		if(!Database($_REQUEST["cid"]))exit;
		$_REQUEST["meeting_id"] = check_null(40062,true,$_REQUEST["meeting_id"]);

		$rs=D('Meeting')->cancel_meeting($_REQUEST['meeting_id']);
		if($rs){
			return_json(0);
		}else{
			return_json(40074);
		}
	}
	
//获取会议室所有地区
	public function get_meeting_room_locals(){
		if(!Database($_REQUEST["cid"]))exit;
		
		$rs['local']	= D('Meeting')->get_all_local();
		$rs['building']	= D('Meeting')->get_all_building();
		$rs['floor']	= D('Meeting')->get_all_floor();
		
		$rs['building']	=!$rs['building']?NULL:$rs['building'];
		$rs['floor']	=!$rs['floor']	 ?NULL:$rs['floor'];
		
		if($rs['local']){
			return_json(0,$rs);
		}else{
			return_json(40001);
		}
	}

//设置推送时间
	public function set_push_notice(){

		if(!Database($_REQUEST['cid']));
		$data = [
			'meeting_id' =>check_null(40062,true,$_REQUEST["meeting_id"]),
			'audience_id'=>check_null(40002,true,$_REQUEST["uid"]),
			'push_time'	 =>check_null(40127,true,$_REQUEST["push_time"]),
			'time_update'=>time()
		];

		$rs = D('Meeting')->set_push_notice($data);
		if($rs){
			return_json(0);
		}else{
			return_json(-1);
		}
	}
}