<?php
class PushModel extends Model{

	protected $open_url = [
		'1'=>'TeamInMeeting',
		'2'=>'TeamInEvent',
		'3'=>'TeamInWorkLog',
		'4'=>'TeamInLeave',
		'5'=>'TeamInNotice'
	];
	protected $color = [
		'1'=>'#44b2cc',
		'2'=>'#18c3c0',
		'3'=>'#44b2cc',
		'4'=>'#e65d5d',
		'5'=>'#44b2cc'
	];
	protected $images_arr = [
		'1'=>'http://huiyi.qeebu.cn/teamin/Public/Images/push_icon/homeMeetingList@3x.png',
		'2'=>'http://huiyi.qeebu.cn/teamin/Public/Images/push_icon/homeTodoList@3x.png',
		'3'=>'http://huiyi.qeebu.cn/teamin/Public/Images/push_icon/homeWorklogList@3x.png',
		'4'=>'http://huiyi.qeebu.cn/teamin/Public/Images/push_icon/homeLeaveList@3x.png',
		'5'=>'http://huiyi.qeebu.cn/teamin/Public/Images/push_icon/homeAnnounceList@3x.png'
	];

	protected $app_fActivity  = [
		'1'=>'Launch',
		'2'=>'TaskManageLaunchActivity',
		'3'=>'DailyLog_MainActivity',
		'4'=>'LeaveMainActivity',
		'5'=>'Launch'
	];

	protected $app_pName = [
		'1'=>'com.qeebu.teamin.meeting',
		'2'=>'com.qeebu.teamin.taskmanage.activities',
		'3'=>'com.qeebu.teamin.dailylog.activities',
		'4'=>'com.qeebu.teamin.leave',
		'5'=>'com.qeebu.teamin.teaminnotice.activity'
	];
//-------------------------------insert-----------------------------------------
	public function add_devicetoken($data){
		$rs = false;

		$_REQUEST['cid']!=102 && $rs = D('Uri')->add_token($data);

		if($_REQUEST['cid']!=0){
			if(!Database($_REQUEST["cid"]))exit;
			$this->delete_uri_by_devicetoken($data);
			$this->delete_uri_by_uid($data);
			$rs = M('uri','','DB_MEETING')->add($data);
		}
		return $rs;
	}

	public function create_push_notice($module,$module_id,$content='',$time,$audience_id){

		if($_REQUEST['cid'] == 102)return;
		$push_notice_id = $this->add_push_notice([
			'content'	 =>$content,
			'module' 	 =>$module,
			'module_id'  =>$module_id,
			'time_insert'=>$time
		]);
		foreach($audience_id as $val){
			$this->add_push_notice_user([
				'push_notice_id'=>$push_notice_id,
				'audience_id'   =>$val,
				'time_insert'   =>$time
			]);
		}
	}

	public function add_push_notice($data){
		return M('push_notice','','DB_MEETING')->add($data);
	}

	public function add_push_notice_user($data){
		return M('push_notice_user','','DB_MEETING')->add($data);
	}


//-------------------------------delete-----------------------------------------
	public function delete_uri_by_devicetoken($data){
		$map = [
			'devicetoken'=>$data['devicetoken'],
			'app_for'	 =>$data['app_for']
		];
		return M('uri','','DB_MEETING')->where($map)->delete();
	}

	public function delete_uri_by_uid($data){
		$map = [
			'uid'	  =>$data['uid'],
			'app_for' =>$data['app_for']
		];
		return M('uri','','DB_MEETING')->where($map)->delete();
	}

	public function delete_push_notice_by_update_audience_character($data){

		$push_notice_id = $this->get_team_push_solve_notice_by_uid_cid($data);

		$this->del_team_push_notice($push_notice_id);
		$this->del_team_push_notice_user($push_notice_id);
	}

	public function del_team_push_notice($push_notice_id){
		$map = ['push_notice_id'=>$push_notice_id];
		return M('team_push_notice','','')->db('CONFIG1')->where($map)->delete();
	}

	public function del_team_push_notice_user($push_notice_id){
		$map = ['push_notice_id'=>$push_notice_id];
		return M('team_push_notice_user','','')->db('CONFIG1')->where($map)->delete();
	}
//-------------------------------update-----------------------------------------

	public function update_uid_by_devicetoken($data){
		$sql='update uri set uid='.$data['uid'].' where devicetoken="'.$data['devicetoken'].'" and app_for="'.$data['app_for'].'"';
		return M('uri','','DB_MEETING')->execute($sql);
	}


	public function update_devicetoken_by_uid($data){
		$sql='update uri set devicetoken="'.$data['devicetoken'].'" where uid="'.$data['uid'].'" and app_for="'.$data['app_for'].'"';
		return M('uri','','DB_MEETING')->execute($sql);
	}

	public function update_badge_number($badge,$data){

		if($_REQUEST['cid']==102)
			return M('uri','','DB_MEETING')->execute("update uri set badge=".$badge." where uid={$data['uid']} and app_for='{$data['app_for']}'");
		else
			return D('Uri')->update_badge_number($badge,$data);
	}
	//将推送小红点制为0
	public function init_badge($data){
		$data['timestamp'] 		= time();
		$data['badge']	   		= 0;
		$data['last_open_time'] = time();

		$map = [
			'uid'		  => $data['uid'],
			'devicetoken' => $data['devicetoken']
		];
		$rs = M('uri','','DB_MEETING')->where($map)->save($data);
		$map['token'] = $map['devicetoken'];
		M('push_uri','','')->db('CONFIG1')->where($map)->save($data);
		return $rs;
	}
//-------------------------------select-----------------------------------------
	public function get_data_by_devicetoken($data){
		$map = [
			'devicetoken'=>$data['devicetoken'],
			'app_for'	 =>$data['app_for']
		];

		return M('uri','','DB_MEETING')
			->where($map)
			->find();
	}

	public function get_team_push_solve_notice_by_uid_cid($data){
		$map = [
			'audience_id'=>$data['uid'],
			'state'      =>['in','4,6'],
			'company_id' =>$data['cid']
		];
		return $push_notice_id=M('team_push_notice','','')->db('CONFIG1')->where($map)->getField('id');
	}
	
	public function get_data_by_uid($uid,$appfor){
		$map = [
			'uid'	  =>$uid,
			'app_for' =>$appfor
		];
		return M('uri','','DB_MEETING')
			->where($map)
			->find();
	}

	public function get_meeting_uri($meeting_id,$content='',$arr=[]){

		$time = time();

		if($arr==[]){
			$audience 	 = $this->get_audience_by_meeting_id($meeting_id);
			$audience_id = i_array_column($audience,'audience_id');
		}else{
			$audience_id = $arr;
		}
		$audience_id = array_filter($audience_id,function($v){return $v!=0;});
		if($_REQUEST['cid']==102){
			$map['uid'] = ['in',implode(',',$audience_id)];
			$uri 		= M('uri','','DB_MEETING')->where($map)->select();
		}else{
			$uri = D('Uri')->get_uri_by_user_arr($audience_id);
		}

		$this->create_push_notice('会议管理','1',$content,$time,$audience_id);

		return $uri;
	}

	public function get_audience_by_meeting_id($id){
		$map['meeting_id'] = $id;
		return M('meeting_join','','DB_MEETING')->field('audience_id')->where($map)->select();
	}

	public function get_task_uri($task_id,$content,$user_arr=[]){

		$time		 = time();
		if($user_arr==[]){
			$audience 	 = $this->get_audience_by_task_id($task_id);
			$audience_id = i_array_column($audience,'uid');
		}else{
			$audience_id = $user_arr;
		}

		$uri = D('Uri')->get_uri_by_user_arr($audience_id);

		$this->create_push_notice('任务管理','2',$content,$time,$audience_id);

		return $uri;
	}

	public function get_leave_uri($content,$user_arr){

		$time		 = time();
		$uri = D('Uri')->get_uri_by_user_arr($user_arr);

		$this->create_push_notice('请假管理','4',$content,$time,$user_arr);

		return $uri;
	}

	public function get_audience_by_task_id($id){

		$map = [
			'task_id'=>$id,
			'_string'=>'time_insert>time_delete'
		];
		return M('task_task_members','','DB_MEETING')->field('uid')->where($map)->select();
	}

	public function get_daily_log_uri($log_id,$content){

		$time		 = time();
		$audience 	 = $this->get_audience_by_log_id($log_id);

		$audience_id = i_array_column($audience,'log_create_uid');
		$uri = D('Uri')->get_uri_by_user_arr($audience_id);

		$this->create_push_notice('日志管理','3',$content,$time,$audience_id);

		return $uri;
	}

	public function get_audience_by_log_id($id){

		return M('daily_log','','DB_MEETING')->field('log_create_uid')->where(['log_id'=>$id])->select();
	}

	public function get_daily_log_reply_uri($reply_id,$content){

		$time		 = time();
		$audience 	 = $this->get_audience_by_reply_id($reply_id);

		$audience_id = i_array_column($audience,'uid');
		$uri = D('Uri')->get_uri_by_user_arr($audience_id);

		$this->create_push_notice('日志管理','3',$content,$time,$audience_id);

		return $uri;
	}

	public function get_audience_by_reply_id($reply_id){
		$map['id'] = $reply_id;
		return M('daily_log_reply','','DB_MEETING')->field('uid')->where($map)->select();
	}

	public function get_daily_log_remind_uri($log_id,$reply_id,$content,$without){

		$time		 = time();

		if($log_id!=0)
			$audience = $this->get_audience_remind_by_log_id($log_id,$without);
		else
			$audience = $this->get_audience_remind_by_reply_id($reply_id,$without);

		$audience_id = i_array_column($audience,'uid');
		$uri = D('Uri')->get_uri_by_user_arr($audience_id);

		$this->create_push_notice('日志管理','3',$content,$time,$audience_id);

		return $uri;
	}

	public function get_audience_remind_by_log_id($log_id,$without){
		$map 		= ['log_id'=>$log_id];
		$without!=0 && $map['uid'] = ['neq',$without];
		return M('daily_log_remind','','DB_MEETING')->field('uid')->where($map)->select();
	}

	public function get_audience_remind_by_reply_id($reply_id,$without){
		$map 		= ['reply_id'=>$reply_id];
		$without!=0 && $map['uid'] = ['neq',$without];

		return M('daily_log_remind','','DB_MEETING')->field('uid')->where($map)->select();
	}

	public function get_uri($uid = ''){

		$map['1'] = 1;
		$uid != '' && $map['uid'] = array('in',$uid);

		return M('uri','','DB_MEETING')->where($map)->select();
	}

	public function get_my_push_notice($data){

		$images_arr = [
			'1'=>'http://huiyi.qeebu.cn/teamin/Public/Images/push_icon/meeting@3x.png',
			'2'=>'http://huiyi.qeebu.cn/teamin/Public/Images/push_icon/todo@3x.png',
			'3'=>'http://huiyi.qeebu.cn/teamin/Public/Images/push_icon/worklog@3x.png',
			'4'=>'http://huiyi.qeebu.cn/teamin/Public/Images/push_icon/worklog@3x.png',
			'5'=>'http://huiyi.qeebu.cn/teamin/Public/Images/push_icon/homeAnnounceList@3x.png'
		];

		$open_url = [
			'1'=>'TeamInMeeting',
			'2'=>'TeamInEvent',
			'3'=>'TeamInWorkLog',
			'4'=>'TeamInLeave',
			'5'=>'TeamInNotice'
		];

		$app_fActivity  = [
			'1'=>'Launch',
			'2'=>'Launch',
			'3'=>'DailyLog_MainActivity',
			'4'=>'LeaveMainActivity',
			'' =>''
		];

		$app_pName = [
			'1'=>'com.qeebu.teamin.meeting',
			'2'=>'com.qeebu.teamin.selftask',
			'3'=>'com.qeebu.teamin.dailylog.activities',
			'4'=>'com.qeebu.teamin.leave',
			'5'=>''
		];

		$color = [
			'1'=>'#3573d8',
			'2'=>'#18c3c0',
			'3'=>'#18c3c0',
			'4'=>'#18c3c0',
			'5'=>'#18c3c0',
		];


		$rs = [];
		$map = [
			'audience_id' => $data['uid'],
			'time_insert' => ['gt',$data['timestamp']]
		];
		$push_notice_ids = M('push_notice_user','','DB_MEETING')->field('distinct push_notice_id')->where($map)->select();
		$push_notice_ids = i_array_column($push_notice_ids,'push_notice_id');
		foreach($push_notice_ids as $val){
			$re = M('push_notice','','DB_MEETING')->where(['id'=>$val])->find();
			if(!$re)continue;
			$rs[$re['module_id'].''][]=$re;
		}

		if($rs==[])return false;

		$arr=[];
		foreach($rs as $key=>$val){
			$arr[]=[
				'module_id'   	 =>$key,
				'module_name' 	 =>$val[0]['module'],
				'module_data' 	 =>$val,
				'module_color'	 =>$color[$key],
				'module_image'	 =>$images_arr[$key],
				'module_open_url'=>$open_url[$key],
				'module_fActivity'=>$app_fActivity[$key],
				'module_pName'	  =>$app_pName[$key],
			];
		}

		return $arr;

	}

	public function get_my_home_notice($data){

		$rs = [];
		$map = [
			'audience_id' => $data['uid'],
			'time_insert' => ['gt',$data['timestamp']]
		];
		$push_notice_ids = M('push_notice_user','','DB_MEETING')->field('distinct push_notice_id')->where($map)->select();
		$push_notice_ids = i_array_column($push_notice_ids,'push_notice_id');
		foreach($push_notice_ids as $val){
			$re = M('push_notice','','DB_MEETING')->where(['id'=>$val])->find();
			if(!$re)continue;
			$rs[]=$re;
		}

		if($rs==[])return [];
		foreach($rs as &$val){
			$val['module_color'] 	= $this->color[$val['module_id']];
			$val['module_image'] 	= $this->images_arr[$val['module_id']];
			$val['module_open_url'] = $this->open_url[$val['module_id']];
			$val['module_fActivity']= $this->app_fActivity[$val['module_id']];
			$val['module_pName']	= $this->app_pName[$val['module_id']];
		}

		usort($rs,function($a,$b){
			if($a['time_insert'] == $b['time_insert']){
				return 0;
			}
			return $a['time_insert'] > $b['time_insert'] ? -1 : 1;
		});

		return $rs;
	}

	public function get_init_data(){

		$time = time();
		$rs = [
			[
				'id'=>0,
				'content'	=>'您已成功加入【TeamIn】，与您的同事开始TeamIn之旅吧',
				'module' 	=>'公告管理',
				'module_id' =>'5',
				'time_insert'=>$time,
			],
			[
				'id'=>0,
				'content'	=>'新建您的第一条任务，从此工作更高效',
				'module' 	=>'任务管理',
				'module_id' =>'2',
				'time_insert'=>$time,
			],
			[
				'id'=>0,
				'content'	=>'发布工作日志，分享您的工作进展',
				'module' 	=>'日志管理',
				'module_id' =>'3',
				'time_insert'=>$time,
			],
			[
				'id'=>0,
				'content'	=>'安排会议，从此更方便',
				'module' 	=>'会议管理',
				'module_id' =>'1',
				'time_insert'=>$time,
			],
		];


		foreach($rs as &$val){
			$val['module_color'] 	= $this->color[$val['module_id']];
			$val['module_image'] 	= $this->images_arr[$val['module_id']];
			$val['module_open_url'] = $this->open_url[$val['module_id']];
			$val['module_fActivity']= $this->app_fActivity[$val['module_id']];
			$val['module_pName']	= $this->app_pName[$val['module_id']];
		}
		return $rs;
	}

	public function get_team_uri($team_id,$content,$state,$user_arr,$uid,$admin_id,$old_company_name){

		$time= time();
		$uri = D('Uri')->get_uri_by_user_arr($user_arr);
		foreach($uri as &$val1){
			$val1['devicetoken'] = $val1['token'];
			$val1['mobiletype']	= $val1['ad_or_ios'];
		}
		if($content!=''){

			$push_notice_data = [
				'content'	 		=> $content,
				'module' 	 		=> 'Team管理信息',
				'module_id'  		=> $state,
				'old_company_name'  => $old_company_name==null?'':$old_company_name,
				'time_insert'		=> $time,
				'admin_id'	 		=> $admin_id==null?0:$admin_id,
				'company_id' 		=> $team_id,
				'audience_id'		=> $uid,
				'state'		 		=> $state,
			];
			$push_notice_id = $this->add_team_push_notice($push_notice_data);

			foreach($user_arr as $val){
				$this->add_team_push_notice_user([
					'push_notice_id'=>$push_notice_id,
					'audience_id'   =>$val,
					'time_insert'   =>$time,
					'state'			=>$state,
				]);
			}
		}
		return $uri;

	}

	public function get_leave_team_uri($team_id,$content,$uid){

		$time= time();

		$user_arr = D('Team')->get_team_admin_by_cid($team_id);
		$user_arr = i_array_column($user_arr,'uid');

		$uri = D('Uri')->get_uri_by_user_arr($user_arr);
		foreach($uri as &$val1){
			$val1['devicetoken'] = $val1['token'];
			$val1['mobiletype']	 = $val1['ad_or_ios'];
		}
		if($content!=''){

			$push_notice_data = [
				'content'	 		=> $content,
				'module' 	 		=> 'Team管理信息',
				'module_id'  		=> '15',
				'old_company_name'  => '',
				'time_insert'		=> $time,
				'admin_id'	 		=> '',
				'company_id' 		=> $team_id,
				'audience_id'		=> $uid,
				'state'		 		=> '15',
			];
			$push_notice_id = $this->add_team_push_notice($push_notice_data);

			foreach($user_arr as $val){
				$this->add_team_push_notice_user([
					'push_notice_id'=>$push_notice_id,
					'audience_id'   =>$val,
					'time_insert'   =>$time,
					'state'			=>'15',
				]);
			}
		}
		return $uri;

	}

	public function add_team_push_notice($push_notice_data){

		return M('team_push_notice','','')->db('CONFIG1')->data($push_notice_data)->add();
	}

	public function add_team_push_notice_user($push_notice_data){
		return M('team_push_notice_user','','')->db('CONFIG1')->add($push_notice_data);
	}

	public function get_my_team_push_notice($data){

		$rs = [];
		$map = [
			'audience_id' => $data['uid'],
			'time_insert' => ['gt',$data['timestamp']],
			'state'		  => ['in','4,6'],
		];
		$push_notice_ids = M('team_push_notice_user','','DB_MEETING')
			->db('CONFIG1')
			->field('push_notice_id')
			->where($map)
			->order('time_insert desc')
			->select();
		$push_notice_ids = i_array_column($push_notice_ids,'push_notice_id');
		$push_notice_ids = array_unique($push_notice_ids);
		foreach($push_notice_ids as $val){
			$re = M('team_push_notice','','DB_MEETING')
				->db('CONFIG1')
				->field('id,module,module_id,state,admin_id,audience_id,company_id,time_insert')
				->where(['id'=>$val])
				->find();
			if(!$re)continue;
			$rs[$re['module_id'].''][]=$re;
		}

		if($rs==[])return false;

		$arr=[];
		foreach($rs as $key=>$val){

			foreach($val as &$val_data){
				//company
				$val_data['company'] = D('Team')	->get_team_by_cid($val_data['company_id']);
				$val_data['company']['audience_count'] = D('Team')->get_team_member_count($val_data['company_id']);

				//audience
				$val_data['admin']	 = D('Audience')->new_get_audience_info($val_data['admin_id'],$val_data['company_id']);
				$val_data['audience']= D('Audience')->new_get_audience_info($val_data['audience_id'],$val_data['company_id']);

				$val_data['admin_id'] 	 = StrCode($val_data['admin_id']);
				$val_data['company_id']  = StrCode($val_data['company_id']);
				$val_data['audience_id'] = StrCode($val_data['audience_id']);
			}

			$arr[]=[
				'module_id'   	 =>$key,
				'module_name' 	 =>$val[0]['module'],
				'module_data' 	 =>$val,
			];
		}

		return $arr;

	}

	public function get_my_team_push_news($data){

		$rs = [];
		$map = [
			'audience_id' => $data['uid'],
			'time_insert' => ['gt',$data['timestamp']],
			'state'		  => ['in','5,7,8,9,14'],
		];
		$push_notice_ids = M('team_push_notice_user','','')
			->db('CONFIG1')
			->field('push_notice_id')
			->where($map)
			->order('time_insert desc')
			->select();

		$push_notice_ids = i_array_column($push_notice_ids,'push_notice_id');
		$push_notice_ids = array_unique($push_notice_ids);

		foreach($push_notice_ids as $val){
			$re = M('team_push_notice','','')
				->db('CONFIG1')
				->where(['id'=>$val])
				->find();
			if(!$re)continue;
			$rs[]=$re;
		}

		if($rs==[])return false;

		foreach($rs as &$val_data){
			//company
			$val_data['company'] 				   = D('Team')->get_team_by_cid($val_data['company_id']);
			$val_data['company']['audience_count'] = D('Team')->get_team_member_count($val_data['company_id']);

			//audience
			$val_data['admin']	 = D('Audience')->new_get_audience_info($val_data['admin_id'],$val_data['company_id']);
			$val_data['audience']= D('Audience')->new_get_audience_info($val_data['audience_id'],$val_data['company_id']);

			$val_data['admin_id'] 	 = StrCode($val_data['admin_id']);
			$val_data['company_id']  = StrCode($val_data['company_id']);
			$val_data['audience_id'] = StrCode($val_data['audience_id']);
		}

		return $rs;

	}

	public function get_meeting_push($time){

		$meeting_arr = [];
		$re			 = [];

		$map = ['push_time'=>['lt',$time]];
		$rs  = M('meeting_push','','DB_MEETING')->where($map)->select();

		foreach($rs as $val){
			$meeting_arr[$val['meeting_id']][] = $val;
		}

		foreach($meeting_arr as $key=>$val2){
			$before_time = [];

			foreach($val2 as $val1){
				$before_time[$val1['befor_time']][] = $val1['audience_id'];
			}

			$re[$key] = $before_time;
		}

		M('meeting_push','','DB_MEETING')->where($map)->delete();
		return $re;
	}

	public function solve_push_notice($data){

		if($data['push_type'] == 1){
			if(!Database($data['cid']))return;
			$map = [
				'audience_id'   =>$data['audience_id'],
				'push_notice_id'=>['in',$data['push_notice_id']]
			];
			$rs =  M('push_notice_user','','DB_MEETING')->where($map)->delete();

			$push_notice_id = explode(',',$data['push_notice_id']);
			foreach($push_notice_id as $val){
				$map = ['push_notice_id'=>$val];
				$re = M('push_notice_user','','DB_MEETING')->where($map)->find();
				if(!$re){
					$map = ['id'=>$val];
					M('push_notice','','DB_MEETING')->where($map)->delete();
				}
			}
			return $rs;
		}else{
			$map = [
				'audience_id'   =>$data['audience_id'],
				'push_notice_id'=>['in',$data['push_notice_id']]
			];
			$rs = M('team_push_notice_user','','')->db('CONFIG1')->where($map)->delete();

			foreach(explode(',',$data['push_notice_id']) as $val){
				$map = ['push_notice_id'=>$val];
				$re = M('team_push_notice_user','','')->db('CONFIG1')->where($map)->getField('push_notice_id');
				if(!$re){
					$map = ['id'=>$val];
					M('team_push_notice','','')->db('CONFIG1')->where($map)->delete();
				}
			}
			return $rs;
		}

	}

	public function check_push_notice($data){

		$map = [
			'uid' 				=> $data['uid'],
			'cid' 				=> $data['cid'],
			'audience_character'=> ['in','4,6'],
		];
		$rs = M('audience_with_company','','')->db('CONFIG1')->where($map)->find();
		if(!$rs){
			$this->delete_push_notice_by_update_audience_character([
				'audience_id'=>$data['uid'],
				'company_id' =>$data['cid']
			]);
		}
		return $rs;
	}
}
?>