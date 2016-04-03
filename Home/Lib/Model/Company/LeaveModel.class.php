<?php
class LeaveModel extends Model{

//-------------------------------init---------------------------------------------------
	public function get_leave_by_id($id,$field=''){
		if($field)
			$rs = M('leave')->where(['id'=>$id])->find();
		else
			$rs = M('leave')->field($field)->where(['id'=>$id])->find();
		return $rs;
	}

//------------------------------insert-------------------------------------------
	//请假
	public function create_leave($data){
		$rs= M('leave')->add($data);
		A('Company/Push_notification')->leave_push($data);
		return $rs;
	}

	//安排补课
	public function create_leave_history($data){
		$class_time 	= D('Classes')->get_class_time($data['class_time_id']);
		//1.安排班级 - 查看座位数
		$student_count  = D('Classes')->get_class_time_student_count($data['class_time_id']);
		if($student_count >= $class_time['max_num'] ){
			return_json(40028);
		}
		//新增用户
		M('class_time_student')->add([
			'user_id'		=> $data['uid'],
			'state'			=> 1,
			'coming_style'	=> 1,
			'class_id' 		=> $class_time['class_id'],
			'class_time_id' => $class_time['class_time_id'],
			'time_start'	=> $class_time['time_start']
		]);
		//原来的置为请假状态
		M('class_time_student')->where([
			'class_time_id'=> $data['old_class_time_id'],
			'user_id'	   => $data['uid']
		])->save(['state'=> 2]);
		//保存历史
		$data['course_detail_id'] = $class_time['course_detail_id'];
		$rs = M('leave_history')->add($data);

		A('Company/Push_notification')->leave_history_push($data);
		return $rs;
	}
//-----------------------------------update--------------------------------------------
//-----------------------------------delete-------------------------------------------
//-----------------------------------select-------------------------------------------
	public function get_leave_list_by_class_id($data){

		$page  = $data['page'] == '' ? 1 : $data['page'];
		$count = $data['count']== '' ? 10: $data['count'];
		$map   = [ 'class_id'=>$data['class_id'] ];
		$role  = D('User')->get_user('',$data['uid'],'role')['role'];
		if($role == 1){
			$map['uid'] = $data['uid'];
		}

		$rs = M('leave')
			->where($map)
			->order('id desc')
			->limit((($page-1)*$count).",".$count)
			->select();

		foreach($rs as &$val){
			$user = D('User')->get_user('',$val['uid'],'nickname,photo,im_username');
			$val['photo']		 = $user['photo'] == '' ? '': C('DOMAIN_NAME').__ROOT__.'/'.$val['photo'];
			$val['nickname'] 	 = $user['nickname'];
			$val['im_username']  = $user['im_username'];
			$val['free_class']   = D('Classes')->get_my_free_class([
				'uid' 			=>$val['uid'],
				'class_time_id' => $val['class_time_id']
			]);
			$val['uid'] 	 	 = StrCode($val['uid']);
			unset($user);
		}
		return $rs;
	}

	public function upt_leave_state($data){
		if(!$data['leave_id'])return false;
		return M('leave')->where(['id'=>$data['leave_id']])->save($data);
	}
}
?>