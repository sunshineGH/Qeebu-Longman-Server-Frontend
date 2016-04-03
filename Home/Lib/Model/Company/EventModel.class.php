<?php
class EventModel extends Model{
	
	private $event_id="`event_id`";
	private $event_detail_cows= "a.`event_id`,a.`event_mark_image`, a.`event_name`, a.`event_description`, a.`event_startTime`, a.`event_endTime`, a.`event_launch_id`, a.`event_execution_id`, a.`event_examination_id`, a.`event_acceptance_id`, a.`event_launch_name`, a.`event_execution_name`, a.`event_examination_name`, a.`event_acceptance_name`, a.`event_state`, a.`project_id`, a.`project_name`, a.`time_insert`, a.`time_update`,b.audience_portrait as `event_launch_image`,d.audience_portrait as `event_examination_image`";
	private $events="`event_id`, `event_name`, `event_description`, `event_startTime`, `event_endTime`, `event_launch_id`, `event_execution_id`, `event_examination_id`, `event_acceptance_id`,`event_launch_name`, `event_execution_name`, `event_examination_name`, `event_acceptance_name`,`event_state`,`project_id`,`project_name`";
	private $event ="`event_id`";
	private $event_log_cows = "a.`log_id`,b.`audience_name`,a.`event_state`,a.`event_content`,a.`time_update`,a.`log_type`";
	private $event_execution_log_cows = "a.`log_id`,b.`audience_name`,a.`log_content`,a.`time_insert`";
	private $event_personal_log_cows = '`log_id`,`event_state`,`event_id`,`time_date`';
	private $event_personal_log_cow  = 'a.`log_id`,a.`event_state`,a.`event_id`,b.`event_name`';
	private $event_personal_msg_cow  = 'a.`log_id`,a.`event_state`,a.`uid`,a.`event_content`,a.`event_id`,b.`event_name`,a.`time_update`,c.`audience_name`';


/**
  *创建一条团队任务
  *update author: zhanghao
  *update time  : 2014-11-14 11:32
  *use    this	: A(Event)/update_state
  ****************************************/
	public function create_event($data){
		//init
		$time=time();
		
		$data['event_launch_id']=$data['uid'];
		$data['time_insert']=$time;
		$data['time_update']=$time;
		$data['time_delete']=-1;
		$data['event_current_state']	=$data['event_state']==0?1:$data['event_state'];
		$data['event_current_date'] 	=date('Y-m-d');
		$data['event_current_audience'] =$data['event_examination_id'];
		$data['event_current_responds'] ='';
		$rs=M('event','','DB_MEETING')->add($data);
		
		//add log infomation
		$log_data['log_type']=2;
		$log_data['event_id'] = $rs;
		$log_data['audience_id']=$data['uid'];
		$log_data['event_state']=6;
		$log_data['event_responds']='';
		$log_data['time_insert']=$time;
		$log_data['time_update']=$time;
		$log_data['time_delete']=-1;
		$log_data['time_date'] = date('Y-m-d');;
		M('event_log','','DB_MEETING')->add($log_data);	
		
		//add notice push
		$notice=array(
			'audience_id'=>$data['event_examination_id'],
			'event_id'	 =>$rs,
			'event_state'=>1,
			'time_insert'=>$time,
			'time_update'=>$time,
			'time_delete'=>-1
		);
		$rn=M('event_notice','','DB_MEETING')->add($notice);
		
		//push notice
		$data1=array(
			'uid'=>$data['event_examination_id'],
			'title'=>'审核',
			'content'=>'您有一个任务需要审核!',
			'sound'=>'default',
			'cid'=>$data['cid'],
			'app_for'=>'event'
		);
		$re=A('Home://Company/Push')->push_one($data1);
		
		
		
		return $rs;	
	}

/**
  *修改event状态,添加动态日志
  *update author: zhanghao
  *update time  : 2014-11-14 10:49
  *use    this	: A(Event)/update_state
  ****************************************/
	public function update_state($data,$pass_date){

		//init
		if($pass_date['event_current_state']==1 && $data['event_state']==4){
			//审核未通过推送发起人
            $push = array(
                'uid'    =>$pass_date['event_launch_id'],
                'title'  =>'团队任务',
                'content'=>'您有一个任务未通过审核!'
            );
		}else if($pass_date['event_current_state']==1 && $data['event_state']==2){
			//审核通过推送给执行人
			$push['uid']=$pass_date['event_execution_id'];
			$push['title']='团队任务';
			$push['content']='您有一个任务需要执行';
		}else if($pass_date['event_current_state']==4 && $data['event_state']==1){
			//重新提交推送给审核人
			$push['uid']=$pass_date['event_examination_id'];
			$push['title']='团队任务';
			$push['content']='您有一个任务需要再次审核';	
		}else if($pass_date['event_current_state']==2 && $data['event_state']==3){
			//执行成功推送给验收人
			$push['uid']=$pass_date['event_acceptance_id'];
			$push['title']='团队任务';
			$push['content']='您有一个任务需要验收';	
		}else if($pass_date['event_current_state']==3 && $data['event_state']==2){
			//验收失败推送给执行人
			$push['uid']=$pass_date['event_execution_id'];
			$push['title']='团队任务';
			$push['content']='您有一个任务需要执行';	
		}
		//update event_data
        $event_data = array(
            'event_current_date'     => date('Y-m-d'),
            'event_current_state'    => $data['event_state'],
            'event_current_audience' => $push['uid'],
            'time_update'            => $data['time_update']
        );
        if($data['event_state']==4)
            $event_data['event_current_responds'] = $data['event_current_responds'];
        else
            $event_data['event_current_responds'] = '';
        $event_data['event_execution_id']     = $data['event_execution_id']     !=null?$data['event_execution_id']    :$pass_date['event_execution_id'];
        $event_data['event_acceptance_id']    = $data['event_acceptance_id']    !=null?$data['event_acceptance_id']   :$pass_date['event_acceptance_id'];
        $rs=M('event','','DB_MEETING')->where('event_id='.$data['event_id'])->save($event_data);
		
		//add log infomation
		$data['log_type']       = 2;
        $data['time_date']      = date('Y-m-d');
		$data['audience_id']    = $data['uid'];
		$data['time_insert']    = $data['time_update'];
		$data['time_delete']    = -1;
		$data['event_responds'] =$data['event_current_responds']==null?'':$data['event_current_responds'];
		M('event_log','','DB_MEETING')->add($data);
		
		//push notice current
		$push['sound']	= 'default';
		$push['app_for']= 'event';
		$push['cid']	= $data['cid'];
		A('Home://Company/Push')->push_one($push);
		
		//add notice push
		$notice = array(
			'audience_id' => $push['uid'],
			'event_id'	  => $data['event_id'],
			'event_state' => $data['event_state'],
			'time_insert' => $data['time_update'],
			'time_update' => $data['time_update'],
			'time_delete' => -1
		);
		M('event_notice','','DB_MEETING')->add($notice);
		
		return $rs;
	}
/**
  *添加执行日志
  *update author: zhanghao
  *update time  : 2014-11-14 14:29
  *use    this	: A(Event)/add_execution_log
  ****************************************/	
	public function add_execution_log($data){
		//add log infomation
		$data['log_type']	=$data['log_type']==null?3:$data['log_type'];
		$data['audience_id']=$data['uid'];
		$data['event_state']=7;
		$data['time_insert']=time();
		$data['time_update']=time();
		$data['time_delete']=-1;
		$data['time_date']=date('Y-m-d');
		$rs=M('event_log','','DB_MEETING')->add($data);
		return $rs;
	}
	// +---------------------------------------+
	// | 获取当前历史记录                      	   |
	// +---------------------------------------+
	// | last update author: zhanghao		   |
	// | last update time  : 2014-10-28 03:22  |
	// +---------------------------------------+
	public function get_personal_log($data){
		//建议 = 1,修改状态 = 2,执行日志 = 3
		$sql ='select '.$this->event_personal_log_cows.' from event_log';
		$sql.=' where uid='.$data['uid'].' and log_type=2';
		$sql.=' group by time_date';
		$sql.=' order by time_update desc';
		$rs	 =M('event_log','','DB_MEETING')->query($sql);
		if($rs){
			foreach($rs as $val){
				$re[$val['time_date']]=$this->get_log_by_time_date($data,$val['time_date']);
			}	
		}
		return $re;	
	}
	
	public function get_log_by_time_date($data,$time_date){
		$sql ='select '.$this->event_personal_log_cow.' from event_log as a';
		$sql.=' join event as b on a.event_id=b.event_id';
		$sql.=' where a.uid='.$data['uid'].' and a.log_type=2 and a.time_date="'.$time_date.'"';
		$sql.=' order by a.time_update desc';
		$rs=M('event_log','','DB_MEETING')->query($sql);
		foreach($rs as $val){
			switch($val['event_state']){
				case 1:$content='重新提交'.$val['event_name'];break;
				case 2:$content='移交执行'.$val['event_name'];break;
				case 3:$content='执行完成'.$val['event_name'];break;
				case 4:$content='审核驳回'.$val['event_name'];break;
				case 5:$content='验收通过'.$val['event_name'];break;
				case 6:$content='发起任务'.$val['event_name'];break;
			}
			//是否归档
			$guidang = M('event_log','','DB_MEETING')->where('event_id='.$val['event_id'].' and event_state="5"')->find()?1:0;
			$arr[]=array($content,$val['event_id'],$guidang);
		}
		return $arr;
	}

	// +---------------------------------------+
	// | 获取当前消息                      	   |
	// +---------------------------------------+
	// | last update author: zhanghao		   |
	// | last update time  : 2014-10-28 11:00  |
	// +---------------------------------------+
	public function get_personal_msg($data){
		$sql = 'select '.$this->events.' from event where 1=1';
		//审核人是我-->审核
		$sql1= $sql.' and `event_examination_id`='.$_REQUEST['uid'].' and `event_state`=1';
		//发起人-->未通过审核
		$sql2= $sql.' and `event_launch_id`='.$_REQUEST['uid'].' and `event_state`=4';
		//执行人 -->执行
		$sql3= $sql.' and `event_execution_id`='.$_REQUEST['uid'].' and `event_state`=2';
		//验收人 -->验收
		$sql4= $sql.' and `event_acceptance_id`='.$_REQUEST['uid'].' and `event_state`=3';
		
		$sqlend = '('.$sql1.') union ('.$sql2.') union ('.$sql3.') union ('.$sql4.')';
		$sqlend.= ' order by event_endTime desc';
		//$sqlend.= ' limit '.(($data['page']-1)*$data['count']).','.$data['count'];
		
		$rs=M("event","",'DB_MEETING')->query($sqlend);
		foreach($rs as $val){
			switch($val['event_state']){
				case 1:$content='需要审核';$uid=$val['event_launch_id'];break;
				case 2:$content='需要执行';$uid=$val['event_examination_id'];break;
				case 3:$content='需要验收';$uid=$val['event_execution_id'];break;
				case 4:$content='被审核驳回';$uid=$val['event_examination_id'];break;
				case 5:$content='验收已通过';$uid=$val['event_acceptance_id'];break;
				case 6:$content='已发起';break;
			}
			$log = $this->get_log_infor($val['event_id'],$uid);
			$new[]=array(
				'event_id'=>$val['event_id'],
				'log_id'=>$log[0]['log_id'],
				'time_delete'=>$log[0]['time_delete'],
				'event_name'=>$val['event_name'],
				'event_state'=>$content,
				'event_content'=>$log[0]['event_content'],
				'time_update'=>$log[0]['time_update'],
				'audience_name'=>$log[0]['audience_name'],
			);
		}
		return $new;
	}
	
	public function get_log_infor($event_id,$uid){
		$sql = 'select a.time_update,a.time_delete,a.event_content,a.log_id,b.audience_name from event_log as a';
		$sql.= ' join audience as b on a.uid=b.audience_id';
		$sql.= ' where a.event_id='.$event_id.' and uid='.$uid;
		$sql.= ' order by a.time_update desc limit 1';
		return M('event_log','','DB_MEETING')->query($sql);
	}

	//任务搜索信息
	public function search_event($data){
		$sql = "select ".$this->events." from event";
		$sql.= " where event_name like '%{$data['conditions']}%'";
		$sql_1= $sql.' and `event_launch_id`='.$data['uid'];
		$sql_2= $sql.' and `event_execution_id`='.$data['uid'];
		$sql_3= $sql.' and `event_examination_id`='.$data['uid'];
		$sql_4= $sql.' and `event_acceptance_id`='.$data['uid'];
		$sql_all=$sql_1.' union '.$sql_2.' union '.$sql_3.' union '.$sql_4;
		$rs=M('event','','DB_MEETING')->query($sql_all);	
		return $rs;	
	}
	
	//获取单条数据
	public function get_data_by_id($id){
		return M('event','','DB_MEETING')->where('event_id='.$id)->find();	
	}
	
	//获取log信息
	public function get_logs($id){
		//建议 = 1,修改状态 = 2,执行日志 = 3
		$sql ='select '.$this->event_log_cows.' from event_log as a';
		$sql.=' join audience as b on a.uid=b.audience_id';
		$sql.=' where event_id='.$id;
		$rs	 =M('event_log','','DB_MEETING')->query($sql);
		return $rs;
	}
	
	//查询执行日志
	public function get_execution_log($id){
		$sql ='select '.$this->event_execution_log_cows.' from event_executionLog as a';
		$sql.=' join audience as b on a.uid=b.audience_id';
		$sql.=' where event_id='.$id;
		$rs	 =M('event_executionLog','','DB_MEETING')->query($sql);
		return $rs;
	}
	
	public function del_event($id){
		M('event_log','','DB_MEETING')->where('event_id='.$id)->save(array('time_delete'=>time()));
		M('event_news','','DB_MEETING')->where('event_id='.$id)->save(array('time_delete'=>time()));
		return M('event','','DB_MEETING')->where('event_id='.$id)->save(array('time_delete'=>time()));	
	}
	
	public function get_state($id){
		return M('event','','DB_MEETING')->where('event_id='.$id)->getField('event_state');
	}
	
	public function get_passdata($id){
		return M('event','','DB_MEETING')->where('event_id='.$id)->find();
	}
	
	//获取任务详情
	public function get_event_by_id($id){
		
		$sql1='';
		$cows=$this->event_detail_cows;
		$pass_data=$this->get_data_by_id($id);
		
		//执行人为空	
		if($pass_data['event_execution_id']==0)
			$cows .= ',"" as event_execution_image';
		else{
			$sql1 .= ' join audience as c on a.event_execution_id=c.audience_id';
			$cows .= ',c.audience_portrait as `event_execution_image`';	
		}
		//验收人为空	
		if($pass_data['event_acceptance_id']==0)
			$cows .= ',"" as event_acceptance_image';
		else{
			$sql1  .= ' join audience as e on a.event_acceptance_id=e.audience_id';
			$cows .= ',e.audience_portrait as `event_acceptance_image`';	
		}
		
		$sql = 'select '.$cows.' from event as a';
		$sql.= ' join audience as b on a.event_launch_id=b.audience_id';
		$sql.= ' join audience as d on a.event_examination_id=d.audience_id';
		$sql.= $sql1;
		
		$sql.= ' where event_id='.$id;
		$sql.= ' limit 1';
		
		$rs=M('event','','DB_MEETING')->query($sql);	
		return $rs[0];
	}
	
//已废接口

	
	/*public function save_push_time($id,$cow){
		$sql='update event set '.$cow.'=CURRENT_DATE where event_id='.$id;
		return M('event','','DB_MEETING')->execute($sql);
	}*/
	/*public function get_event_list($data){
		//时间分组排序
		//审核人是我-->审核
		//'.$this->events.',
		$sql1= 'select `event_examination_time` as group_time from event where `event_examination_id`='.$_REQUEST['uid'].' and `event_state`=1';
		//发起人-->未通过审核
		$sql2= 'select `event_launch_time` as group_time from event where `event_launch_id`='.$_REQUEST['uid'].' and `event_state`=4';
		//执行人 -->执行
		$sql3= 'select `event_execution_time` as group_time from event where `event_execution_id`='.$_REQUEST['uid'].' and `event_state`=2';
		//验收人 -->验收
		$sql4= 'select `event_acceptance_time` as group_time from event where `event_acceptance_id`='.$_REQUEST['uid'].' and `event_state`=3';
				
		$sqlend = 'select * from (('.$sql1.') union ('.$sql2.') union ('.$sql3.') union ('.$sql4.')) as a';
		$sqlend.= ' group by group_time';
		$sqlend.= ' order by group_time desc';
		$rs=M("event","",'DB_MEETING')->query($sqlend);
		
		//获取相关时间信息
		$new_array=array();
		foreach($rs as $val1){
			$sql1= 'select '.$this->events.' from event where `event_examination_id`='.$_REQUEST['uid'].' and `event_state`=1 and event_examination_time="'.$val1['group_time'].'"';
			//发起人-->未通过审核
			$sql2= 'select '.$this->events.' from event where `event_launch_id`='.$_REQUEST['uid'].' and `event_state`=4 and event_launch_time="'.$val1['group_time'].'"';
			//执行人 -->执行
			$sql3= 'select '.$this->events.' from event where `event_execution_id`='.$_REQUEST['uid'].' and `event_state`=2 and event_execution_time="'.$val1['group_time'].'"';
			//验收人 -->验收
			$sql4= 'select '.$this->events.' from event where `event_acceptance_id`='.$_REQUEST['uid'].' and `event_state`=3 and event_acceptance_time="'.$val1['group_time'].'"';
			$sqlend = '('.$sql1.') union ('.$sql2.') union ('.$sql3.') union ('.$sql4.')';
			$sqlend.= ' order by event_endTime desc';
			$new_array[$val1['group_time']]=M("event","",'DB_MEETING')->query($sqlend);	
		}
		return $new_array;
	}*/
}
?>