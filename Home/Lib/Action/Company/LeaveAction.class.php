<?php
class LeaveAction extends Action{

//-------------------------------------insert-------------------------------------------
	//创建请假
	public function ln_create_leave($data){

		D('User')->check_token($data['uid'],$data['token']);

		check_null(40018,true,$data['class_id']);
		check_null(40024,true,$data['leave_date']);
		check_null(40024,true,$data['class_time_id']);
		$data['time_insert'] = time();

		$rs=D("Leave")->create_leave($data);
		if($rs){
			return_json(0);
		}else{
			return_json(-1);
		}
	}
	//选择班级
	public function ln_choose_my_class($data){

		D('User')->check_token($data['uid'],$data['token']);

		check_null(40024,true,$data['class_time_id']);

		$data['time_insert'] = time();

		$rs=D("Leave")->create_leave_history($data);
		if($rs){
			return_json(0);
		}else{
			return_json(-1);
		}
	}
//------------------------------------delete--------------------------------------------
//------------------------------------update--------------------------------------------
	public function ln_upt_leave_state($data){

		D('User')->check_token($data['uid'],$data['token']);
		check_null(40027,true,$data['leave_id']);
		$data['time_update'] = time();
		unset($data['uid']);
		//0=待处理,1=已处理,2=已安排补课
		$rs=D("Leave")->upt_leave_state($data);
		if($rs){
			return_json(0);
		}else{
			return_json(-1);
		}
	}
//------------------------------------select--------------------------------------------
	public function ln_get_class_time($data){

		D('User')->check_token($data['uid'],$data['token']);
		check_null(40018,true,$data['class_id']);
		$rs=D("Classes")->get_classes_time_by_uid_class_id($data);
		if($rs){
			return_json(0,$rs);
		}else{
			return_json(40001);
		}
	}

	public function ln_get_leave_list($data){

		D('User')->check_token($data['uid'],$data['token']);

		check_null(40018,true,$data['class_id']);

		$rs=D("Leave")->get_leave_list_by_class_id($data);
		if($rs){
			return_json(0,$rs);
		}else{
			return_json(40001);
		}
	}

	public function ln_get_free_class($data){

		D('User')->check_token($data['uid'],$data['token']);

		check_null(40025,true,$data['class_time_id']);

		$rs=D("Classes")->get_free_class($data);
		if($rs){
			return_json(0,$rs);
		}else{
			return_json(40001);
		}
	}

	public function ln_choose_free_class($data){
		D('User')->check_token($data['uid'],$data['token']);

		check_null(40025,true,$data['class_time_id']);
		check_null(40026,true,$data['student_id']);
		$data['student_id'] = StrCode($data['student_id'],'DECODE');

		$rs=D("Classes")->choose_free_class($data);
		if($rs){
			return_json(0);
		}else{
			return_json(-1);
		}
	}


}
?>