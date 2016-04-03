<?php
class MeetingModel extends Model{

	private $meeting_join_cows="b.`audience_id`,b.`audience_name`,a.`join_state`,b.`audience_portrait`,a.`join_content`";
	//新添加
	private $meeting_list_cows_new = 'a.meeting_id,a.meeting_local,a.`meeting_type`,a.meeting_title, a.`meeting_sponsorsId`, a.meeting_startTime,a.meeting_endTime,meeting_state,"" as room_name,"" as floor_name,"" as building_name,"" as local_name,a.`time_insert`,a.`time_update`';

//-------------------------------insert---------------------------------------
	public function create_meeting($data){

		$data['time_insert'] 		= time();
		$data['meeting_sponsorsId'] = $data['uid'];
		$data['meeting_date']		= date('Y-m-d',$data['meeting_startTime']);

		$rs=M('meeting','','DB_MEETING')->add($data);
		if(!$rs)return false;

		$arr=explode(',',$data['meeting_join']);

		foreach($arr as $val){
			M('meeting_join','','DB_MEETING')->add([
				'meeting_id'  =>$rs,
				'audience_id' =>$val,
				'join_state'  =>0
			]);

			$this->set_push_notice([
				'meeting_id' =>$rs,
				'audience_id'=>$val,
				'push_time'	 =>900,
				'time_update'=>time()
			]);
		}
		A('Company/Push')->meeting_push($rs,'您有一条新的会议!');

		return $rs;
	}

//-------------------------------delete---------------------------------------
//-------------------------------update---------------------------------------
	//use by A('Meeting')->update_meeting_info
	public function update_meeting_info($data){

		$data['meeting_startTime']!='' && $data['meeting_date'] = date('Y-m-d',$data['meeting_startTime']);


		$map['meeting_id'] = $data['meeting_id'];
		$rs = M('meeting','','DB_MEETING')->where($map)->save($data);
		if($data['meeting_join']!=''){
			$this->del_meeting_push_by_meeting_id($data['meeting_id']);
			M('meeting_join','','DB_MEETING')->where($map)->delete();
			$arr=explode(',',$data['meeting_join']);
			foreach($arr as $val){

				M('meeting_join','','DB_MEETING')->add([
					'meeting_id'  =>$data['meeting_id'],
					'audience_id' =>$val,
					'join_state'  =>0
				]);

				$this->set_push_notice([
					'meeting_id' =>$data['meeting_id'],
					'audience_id'=>$val,
					'push_time'	 =>900,
					'time_update'=>time()
				]);
			}
		}

		A('Company/Push')->meeting_push($data['meeting_id'],'您的会议被修改!');

		return $rs;
	}

//-------------------------------selete---------------------------------------

	/**
 * update by zhanghao in 2014/11/29 11:30
 * use by A('Meeting')->get_meeting_calendar_list
 */
	public function get_meeting_calendar_list($data){

		$sql1  = '';
		$time  = time() - 2592000;
		$files = 'a.meeting_date,a.meeting_type';

		/*//1、必须和我的分组有关
		$user_group = D('Audience')->get_user_meeting_group($data['uid']);
		// 和 我相关的分组,我需要参加
		if($user_group != ''){
			$sql1 = 'select '.$files.' from meeting a';
			$sql1.= ' where meeting_state=1 and a.meeting_category in ('.$user_group.')';
			$sql1.= ' and meeting_endTime >= '. $time ;
		}*/

		//2、必须和我相关的会议，有我参与 或者 是我发起
		// 和我相关的会议我必须参加
		$sql2 = 'select '.$files.' from meeting_join b';
		$sql2.= ' join meeting a on b.meeting_id = a.meeting_id ';
		$sql2.= ' where meeting_state=1 and audience_id='.$data['uid'];
		$sql2.= ' and meeting_endTime >='.$time;

		//3、我发起的会议，我必须参加
		$sql3 = 'select '.$files.' from meeting a';
		$sql3.= ' where meeting_state=1 and a.meeting_sponsorsId='.$data['uid'];
		$sql3.= ' and meeting_endTime >='.$time;

		//4、全体会议
		$sql4 = 'select '.$files.' from meeting a';
		$sql4.= ' where meeting_state=1 and a.meeting_join_type=1';
		$sql4.= ' and meeting_endTime >='.$time;


		if($sql1 != '')
			$sql = $sql1.' union '.$sql2.' union '.$sql3.' union '.$sql4 ;
		else
			$sql = $sql2.' union '.$sql3.' union '.$sql4 ;

		$rs=M('meeting','','DB_MEETING')->query($sql);

        //排序
        $new_arr=array();
		foreach($rs as $val){
			if( $new_arr[$val['meeting_date']] === NULL )
				$new_arr[$val['meeting_date']] = $val['meeting_type'];
			else
                $new_arr[$val['meeting_date']] < $val['meeting_type'] && $new_arr[$val['meeting_date']] = $val['meeting_type'];
		}
		ksort($new_arr);
		foreach($new_arr as $key=>$val){
			$new_arr1[]=array($key,$val);	
		}
		return $new_arr1;
	}
/*
 * 获取会议列表
 * use by A('Meeting')->check_idle_meeting_room
 * create by zhanghao 2014-11-17 10:58
 * */
    public function get_meeting_list($data){
		$is_admin = D('Audience')->get_uid_meeting_group_admin($data['uid']);
		if(!$is_admin)
			return false;
		if($_REQUEST['cid']=='102')
			$where = ' and use_in_calendar!=1';
		else
			$where = '';

		$sql = 'select '.$this->meeting_list_cows_new.' from meeting a';
		$sql.= ' where meeting_state!=0 '.$where.' and group_id in ('.$is_admin.')';
		$sql.= ' order by `meeting_startTime` desc';
		$sql.= ' limit '.(($data['page']-1)*$_REQUEST['count']).','.$data['count'];
		return M('meeting','','DB_MEETING')->query($sql);
    }


	public function get_meeting_list_reg($data){
		$sql = 'select '.$this->meeting_list_cows_new.' from meeting a';
		$sql.= ' where meeting_state!=0';
		$sql.= ' order by `meeting_startTime` desc';
		$sql.= ' limit '.(($data['page']-1)*$_REQUEST['count']).','.$data['count'];
		$rs  =  M('meeting','','DB_MEETING')->query($sql);
		foreach($rs as &$val){
			$val['meeting_sponsorsId'] = StrCode($val['meeting_sponsorsId']);
		}
		return $rs;

	}

	public function get_my_meeting_list($data){
		$sql1  = '';
		$time  = time() - 2592000;
		$files = 'a.meeting_id,a.meeting_local,a.`meeting_type`,a.meeting_title, a.`meeting_sponsorsId`, a.meeting_startTime,a.meeting_endTime,meeting_state,"" as room_name,"" as floor_name,"" as building_name,"" as local_name,a.`time_insert`,a.`time_update`';

		$user_time = M('audience','','DB_MEETING')->where('audience_id='.$data['uid'])->getField('time_insert');

		if($_REQUEST['cid']=='102')
			$where = ' and use_in_calendar!=1';
		else
			$where = '';
		//1、必须和我的分组有关
		/*$user_group = D('Audience')->get_user_meeting_group($data['uid']);*/

		// 和 我相关的分组,我需要参加
		/*if($user_group != ''){
			$sql1 = 'select '.$files.' from meeting a';
			$sql1.= ' where meeting_state!=0 and a.meeting_category in (0,'.$user_group.')';
			$sql1.= ' and meeting_endTime >= '. $time ;
		}*/

		//2、必须和我相关的会议，有我参与 或者 是我发起
		// 和我相关的会议我必须参加
		$sql2 = 'select '.$files.' from meeting_join b';
		$sql2.= ' join meeting a on b.meeting_id = a.meeting_id ';
		$sql2.= ' where meeting_state!=0 '.$where.' and audience_id='.$data['uid'];
		$sql2.= ' and meeting_endTime >='.$time;

		//1、我发起的会议，我必须参加
		$sql3 = 'select '.$files.' from meeting a';
		$sql3.= ' where meeting_state!=0 '.$where.' and a.meeting_sponsorsId='.$data['uid'];
		$sql3.= ' and meeting_endTime >='.$time;

		//4、全体会议
		$sql4 = 'select '.$files.' from meeting a';
		$sql4.= ' where meeting_state!=0 '.$where.' and a.meeting_join_type=1';
		$sql4.= ' and meeting_endTime >='.$time.' and meeting_endTime >'.$user_time;

		if($sql1 != '')
			$sql = $sql1.' union '.$sql2.' union '.$sql3.' union '.$sql4 ;
		else
			$sql = $sql2.' union '.$sql3.' union '.$sql4 ;
		/*if($_GET['uid']==102)
			echo $sql;*/

		$sql.= ' order by `meeting_startTime` desc';
		$sql.= ' limit '.(($data['page']-1)*$_REQUEST['count']).','.$data['count'];

		//dump($sql);

		return M('meeting','','DB_MEETING')->query($sql);
	}

	//获取我的会议接口 -- 注册版本
	public function get_my_meeting_list_reg($data){

		$sql1  = '';
		$time  = time() - 2592000;
		$files = 'a.meeting_id,a.meeting_local,a.`meeting_type`,a.meeting_title, a.`meeting_sponsorsId`, a.meeting_startTime,a.meeting_endTime,meeting_state,"" as room_name,"" as floor_name,"" as building_name,"" as local_name,a.`time_insert`,a.`time_update`';

		$map['audience_id'] = $data['uid'];

		$user_time = M('audience','','DB_MEETING')->db('CONFIG1')
			->where($map)->getField('time_insert');

		//2、必须和我相关的会议，有我参与 或者 是我发起
		// 和我相关的会议我必须参加

		$sql2 = 'select '.$files.' from meeting_join b';
		$sql2.= ' join meeting a on b.meeting_id = a.meeting_id ';
		$sql2.= ' where meeting_state!=0 and audience_id='.$data['uid'];
		$sql2.= ' and meeting_endTime >='.$time;

		//1、我发起的会议，我必须参加
		$sql3 = 'select '.$files.' from meeting a';
		$sql3.= ' where meeting_state!=0 and a.meeting_sponsorsId='.$data['uid'];
		$sql3.= ' and meeting_endTime >='.$time;

		//4、全体会议
		$sql4 = 'select '.$files.' from meeting a';
		$sql4.= ' where meeting_state!=0 and a.meeting_join_type=1';
		$sql4.= ' and meeting_endTime >='.$time.' and meeting_endTime >'.$user_time;

		if($sql1 != '')
			$sql = $sql1.' union '.$sql2.' union '.$sql3.' union '.$sql4 ;
		else
			$sql = $sql2.' union '.$sql3.' union '.$sql4 ;
		/*if($_GET['uid']==102)
			echo $sql;*/

		$sql.= ' order by `meeting_startTime` desc';
		$sql.= ' limit '.(($data['page']-1)*$_REQUEST['count']).','.$data['count'];

		$rs = M('meeting','','DB_MEETING')->query($sql);
		foreach($rs as &$val){
			$val['meeting_sponsorsId'] = StrCode($val['meeting_sponsorsId']);
		}
		return $rs;
	}

//---------------------------------start of search meeting-----------------------------------------
/**
 *搜索会议
 *update by: zhanghao 2015-03-16
 *use by   : A('Meeting')->search_meeting
 ****************************************/
	public function search_meeting($data){

		$data['cows']	= 'distinct(a.`meeting_id`),a.`meeting_type`, a.`meeting_title`, a.`meeting_sponsorsId`, a.`meeting_description`, a.`meeting_startTime`, a.`meeting_endTime`, a.`meeting_local`, a.`time_insert`,a.`meeting_state`';
		$data['count']	= $data['count']==NULL?10:$data['count'];
		$data['page']	= $data['page'] ==NULL?1 :$data['page'];

		switch($data['searchType']){
			case 1:  // 发起人
				$rs = $this->search_meeting_by_meeting_sponsorsName($data);
				break;
			case 2:  // 会议标题
				$rs = $this->search_meeting_by_meeting_title($data);
				break;
			case 3:  // 日
				$rs = $this->search_meeting_by_meeting_day($data);
				break;
			case 4:  //	月
				$rs = $this->search_meeting_by_month($data);
				break;
		}
		return $rs;
	}
	public function search_meeting_by_meeting_sponsorsName($data){

		$map['b.audience_name'] = array('like','%'.$data['conditions'].'%');
		$map['a.meeting_state'] = 1;
		$map['_complex'] = array(
			'_logic' => 'or',
			'c.audience_id' => $data['uid'],
			'a.meeting_sponsorsId' => $data['uid'],
		);

		if($_REQUEST['cid']==102)$table='audience';
		else	$table='teamin_admin.audience';

		$rs=M('meeting','','DB_MEETING')->alias('a')
			->field($data['cow'])
			->join('join '.$table.' b on a.meeting_sponsorsId = b.audience_id')
			->join('join meeting_join c on a.meeting_id=c.meeting_id')
			->where($map)
			->group('a.meeting_id')
			->order('a.meeting_id desc')
			->limit((($data['page']-1)*$_REQUEST['count']).",".$data['count'])
			->select();
		//dump(M('meeting','','DB_MEETING')->_sql());
		return $rs;
	}

	public function search_meeting_by_meeting_title($data){

		$map['a.meeting_title'] = array('like','%'.$data['conditions'].'%');
		$map['a.meeting_state'] = 1;
		$map['_complex'] = array(
			'_logic' => 'or',
			'b.audience_id' => $data['uid'],
			'a.meeting_sponsorsId' => $data['uid'],
		);

		$rs=M('meeting','','DB_MEETING')->alias('a')
			->field($data['cow'])
			->join('join meeting_join b on a.meeting_id=b.meeting_id')
			->where($map)
			->group('a.meeting_id')
			->order('a.meeting_id desc')
			->limit((($data['page']-1)*$_REQUEST['count']).",".$data['count'])
			->select();

		return $rs;

	}
	public function search_meeting_by_meeting_day($data){
		$preg="/^\d{4}-\d{2}-\d{2}$/";
		preg_match($preg,$data['conditions'],$matches);
		if($matches==array() || count($matches)==0) {
			return false;
		}
		$startTime=strtotime($data['conditions'].' 00:00:00');
		$endTime  =strtotime($data['conditions'].' 23:59:59');

		$map['meeting_startTime'] = array('egt',$startTime);
		$map['meeting_endTime']	  = array('elt',$endTime);
		$map['meeting_state'] = 1;
		$map['_complex'] = array(
			'_logic' => 'or',
			'b.audience_id' => $data['uid'],
			'a.meeting_sponsorsId' => $data['uid'],
		);

		$rs=M('meeting','','DB_MEETING')->alias('a')
			->field($data['cow'])
			->join('join meeting_join b on a.meeting_id=b.meeting_id')
			->where($map)
			->group('a.meeting_id')
			->order('a.meeting_id desc')
			->limit((($data['page']-1)*$_REQUEST['count']).",".$data['count'])
			->select();
		return $rs;
	}
	public function search_meeting_by_month($data){
		$preg="/^\d{4}-\d{2}-\d{2}$/";
		preg_match($preg,$data['conditions'],$matches);
		if($matches==array() || count($matches)==0) {
			return false;
		}
		$date=explode('-',$data['conditions']);
		$startTime = strtotime($date[0].'-'.$date[1].'-'.'01 00:00:00');

		if($date[1]==12){
			$date[0]=(int)$date[0]+1;
			$date[1]='01';
		}else{
			$date[1]=(int)$date[1]+1;
		}

		$endTime   = strtotime($date[0].'-'.$date[1].'-'.'01 00:00:00');

		$map['meeting_startTime'] = array('egt',$startTime);
		$map['meeting_endTime']	  = array('elt',$endTime);
		$map['meeting_state'] = 1;
		$map['_complex'] = array(
			'_logic' => 'or',
			'b.audience_id' => $data['uid'],
			'a.meeting_sponsorsId' => $data['uid'],
		);

		$rs=M('meeting','','DB_MEETING')->alias('a')
			->field($data['cow'])
			->join('join meeting_join b on a.meeting_id=b.meeting_id')
			->where($map)
			->group('a.meeting_id')
			->order('a.meeting_id desc')
			->limit((($data['page']-1)*$_REQUEST['count']).",".$data['count'])
			->select();

		return $rs;
	}
//---------------------------------end of search meeting-----------------------------------------


	public function create_meeting_reg($data){
		$data['time_insert'] 	   = time();
		$data['meeting_sponsorsId']= $data['uid'];
		$data['meeting_date']	   = date('Y-m-d',$data['meeting_startTime']);
		$rs=M('meeting','','DB_MEETING')->add($data);

		if(!$rs)return false;

		$arr=explode(',',$data['meeting_join']);
		foreach($arr as $val){
			M('meeting_join','','DB_MEETING')->add([
				'meeting_id'  =>$rs,
				'audience_id' =>StrCode($val,'DECODE'),
				'join_state'  =>0
			]);
		}
		A('Company/Push')->meeting_push($rs,'您有一条新的会议!');

		return $rs;
	}



	//use by A('Meeting')->update_meeting_info
	public function update_meeting_info_reg($data){

		$map['meeting_id'] = $data['meeting_id'];

		if($data['meeting_startTime']!='')
			$data['meeting_date']=date('Y-m-d',$data['meeting_startTime']);

		$old_meeting_join = $this->get_audience_by_meeting_id($data['meeting_id']);

		if($data['meeting_join']!=''){

			M('meeting_join','','DB_MEETING')->where($map)->delete();
			$arr=explode(',',$data['meeting_join']);
			foreach($arr as $val){
				$newdata['meeting_id']=$data['meeting_id'];
				$newdata['audience_id']=StrCode($val,'DECODE');
				$newdata['join_state']=0;
				M('meeting_join','','DB_MEETING')->add($newdata);
			}
			unset($val);
		}

		$new_meeting_join = $this->get_audience_by_meeting_id($data['meeting_id']);
		$arr = array_diff($old_meeting_join,$new_meeting_join);

		if($arr != []){
			//这些是那些被删除会议的可怜人
			A('Company/Push')->meeting_push($data['meeting_id'],'您的会议被被取消!',$arr);
		}

		unset($data['meeting_sponsorsId']);
		$rs = M('meeting','','DB_MEETING')->where($map)->save($data);
		A('Company/Push')->meeting_push($data['meeting_id'],'您的会议被修改!');
		return $rs;
	}
	
	//根据会议id获取会议相关信息
	public function get_meeting_by_id($id){

		$meeting_details_cows="`meeting_id`,`meeting_category`,`group_id`,`meeting_type`,`meeting_title`,`meeting_sponsorsId`,'meeting_local_type',`meeting_description`,`meeting_startTime`,`meeting_endTime`,`meeting_local`,`time_insert`,`meeting_state`,`meeting_room_id`";

		$rs = M('meeting','','DB_MEETING')->field($meeting_details_cows)->where('meeting_id='.$id)->find();
		if(!$rs)
			return false;

		$audience = M('audience','','DB_MEETING')->field('audience_name,audience_portrait')->where('audience_id='.$rs['meeting_sponsorsId'])->find();
		if($audience){
			$rs['meeting_sponsorsName'] = $audience['audience_name'];
			$rs['audience_portrait']	= $audience['audience_portrait'];
		}else{
			$rs['meeting_sponsorsName'] = 'admin';
			$rs['audience_portrait']	= '';
		}

		$rs['room_name'] 	= '';
		$rs['floor_name']	= '';
		$rs['building_name']= '';
		$rs['local_name'] 	= '';

		switch($rs['meeting_type']){
			case 0:$rs['meeting_category']='普通会议';break;
			case 1:$rs['meeting_category']='周会';break;
			case 2:$rs['meeting_category']='月会';break;
		}

		$group_name = $this->get_meeting_group_name_by_id($rs['group_id']);
		$rs['meeting_type_name'] = $group_name == false ? '日常会议' : $group_name;

		return $rs;
	}

	//根据会议id获取会议相关信息
	public function get_meeting_by_id_reg($id){

		$meeting_details_cows="`meeting_id`,`meeting_category`,`group_id`,`meeting_type`,`meeting_title`,`meeting_sponsorsId`,'meeting_local_type',`meeting_description`,`meeting_startTime`,`meeting_endTime`,`meeting_local`,`time_insert`,`meeting_state`,`meeting_room_id`";

		$rs = M('meeting','','DB_MEETING')->field($meeting_details_cows)->where('meeting_id='.$id)->find();
		if(!$rs)
			return false;

		$audience = M('audience','','DB_MEETING')->field('audience_name,audience_portrait')->where('audience_id='.$rs['meeting_sponsorsId'])->find();
		if($audience){
			$rs['meeting_sponsorsName'] = $audience['audience_name'];
			$rs['audience_portrait']	= $audience['audience_portrait'];
		}else{
			$rs['meeting_sponsorsName'] = 'admin';
			$rs['audience_portrait']	= '';
		}

		$rs['room_name'] 	= '';
		$rs['floor_name']	= '';
		$rs['building_name']= '';
		$rs['local_name'] 	= '';

		switch($rs['meeting_type']){
			case 0:$rs['meeting_category']='普通会议';break;
			case 1:$rs['meeting_category']='周会';break;
			case 2:$rs['meeting_category']='月会';break;
		}

		$group_name = $this->get_meeting_group_name_by_id($rs['group_id']);
		$rs['meeting_type_name'] = $group_name == false ? '日常会议' : $group_name;
		$rs['meeting_sponsorsId'] = StrCode($rs['meeting_sponsorsId']);
		return $rs;
	}

	public function get_meeting_group_name_by_id($group_id){
		return M('meeting_group','','DB_MEETING')->where('group_id='.$group_id)->getField('group_name');
	}
	//查询会议的参会人
	public function get_meeting_join($id){
		$type = M('meeting','','DB_MEETING')->where('meeting_id='.$id)->getField('meeting_join_type');
		if($type==2){

			$psql = "select ".$this->meeting_join_cows." from `meeting_join` as a";
			$psql.= " join audience as b on a.audience_id=b.audience_id";
			$psql.= " join meeting c on a.meeting_id=c.meeting_id";
			$psql.= " where a.meeting_id=".$id.' and b.time_insert>b.time_delete';
			$psql.= ' and a.is_secret=0 and meeting_state!=0';
			$psql.= ' order by audience_sort desc';
			$rs1  = M("meeting_join","",'DB_MEETING')->query($psql);
		}else{
			$cows = "`audience_id`,`audience_name`,'0' as `join_state`,`audience_portrait`,'' as `join_content`";
			$rs1 = M("audience","",'DB_MEETING')->field($cows)->where('time_insert>time_delete')->order('audience_sort desc')->select();
		}
		return $rs1;
	}

	//查询会议的参会人
	public function get_meeting_join_reg($id){

		$rs = [
			'sponsor' => [],
			'join'	  => []
		];

		$map   = ['meeting_id'=>$id];
		$field = 'meeting_sponsorsId';
		$meeting_info = M('meeting','','DB_MEETING')->field($field)->where($map)->find();

		//发起人信息
		$audience = D('Audience')->new_get_audience_by_uid($meeting_info['meeting_sponsorsId']);
		if(!$audience){
			$rs['sponsor'][] = [
				'audience_id'  		=> '0',
				'audience_name'		=> 'Admin',
				'join_state'   		=> '0',
				'audience_portrait' => '',
				'join_content' 		=> ''
			];
		}else{
			$rs['sponsor'][] = [
				'audience_id'  => StrCode($audience['audience_id']),
				'audience_name'=> $audience['audience_name'],
				'join_state'   => 0,
				'audience_portrait' => $audience['audience_portrait'],
				'join_content' => ''
			];
		}

		//参会人信息

		$map = [
			'meeting_id' 	=> $id,
			'is_secret'	 	=> 0,
			'meeting_state' => ['neg',0]
		];

		$re = M("meeting_join","",'DB_MEETING')
			->where($map)
			->select();

		foreach($re as $val){

			$audience = false;
			$audience = D('Audience')->new_get_audience_by_uid($val['audience_id']);
			if($audience){
				$rs['join'][] = [
					'audience_id'  => StrCode($audience['audience_id']),
					'audience_name'=> $audience['audience_name'],
					'join_state'   => $val['join_state'],
					'audience_portrait' => $audience['audience_portrait'],
					'join_content' => $val['join_state']
				];
			}
		}

		return $rs;
	}

	//获取会议信息
	public function check_idle_meeting_room_by_uid($data){
		//1.通过uid获取当前用户的部门信息
		$department	 = D('Department')->get_department_by_uid($data['uid']);
		$re['local_id']=$department['local_id'];
		$re['building_id']=$department['building_id'];
		$re['floor_id']=$department['floor_id'];
		//查询相关楼层信息
		$re['locals'] = $this->get_meeting_room_local();
		$re['buildings'] = $this->get_meeting_room_building($re['local_id']);
		$re['floors'] = $this->get_meeting_room_floor($re['building_id']);
		//查询符合条件的meeting_room
		$sql ='select room_id,room_name from meeting_room';
		$sql.=' where floor_id='.$department['floor_id'];
		$rs=M('meeting_room','','DB_MEETING')->query($sql);
		foreach($rs as $val){
			$sql ='select schedule_id from meeting_room_schedule';
			$sql.=' where room_id='.$val['room_id'];
			$sql.=' and schedule_startTime>='.$data['meeting_startTime'];
			$sql.=' and schedule_endTime<='.$data['meeting_endTime'];
			$rs=M('meeting_roomSchedule','','DB_MEETING')->query($sql);
			if(!$rs)
				$new[]=$val;
		}
		$re['meeting_room']=$new;
		return $re;	
	}
	
/**
  *查询会议室
  *update author: zhanghao
  *update time  : 2014-11-12 15:40
  *use    this	: A('Meeting')->check_idle_meeting_room
  ****************************************/	
	public function check_idle_meeting_room($data){
		$data['meeting_date']	= $data['meeting_date']==NULL?date('Y-m-d'):$data['meeting_date'];
		//$data['count']		= $data['count']==NULL?10:$data['count'];
		//$data['page']			= $data['page']==NULL?1:$data['page'];
		
		$sql = 'select * from meeting_room as b';
		$sql.= ' join meeting_roomfloor c on b.floor_id=c.floor_id';
		$sql.= ' join meeting_roombuilding d on c.building_id=d.building_id';
		$sql.= ' join meeting_roomlocal e on d.local_id=e.local_id';
		if($data['floor_id']!='')
			$sql.= ' where b.floor_id='.$data['floor_id'];
		$rs	 = M('meeting_room','','DB_MEETING')->query($sql);
		if(!$rs)
			return false;
		
		foreach($rs as $val){
			$sql1 ='select * from meeting_roomschedule';
			$sql1.=' where room_id='.$val['room_id'].' and schedule_date="'.$data['meeting_date'].'"';
			$sql1.=' limit 1';
			$schedule=M('meeting_roomschedule','','DB_MEETING')->query($sql1);
			if($schedule){
				if($data['maxstep']!=''){
					if($data['maxstep']>$schedule['schedule_maxstep'])
					continue;
				}
				$val['have_schedule']	=true;
				$val['schedule_states']	=$schedule;
			}else{
				$val['have_schedule']=false;
				$val['schedule_states']=false;
			}
            $re = $this->get_current_day_schedule_time($val['room_id'],$data['meeting_date']);

            if($re){
                //uasort($re,'sort_meeting_startTime');
                $val['schedule_time'] = $re;
            }else
                $val['schedule_time'] = null;
			$arr[]=$val;
		}
		return $arr;
	}

//use by this->check_idle_meeting_room
    public function get_current_day_schedule_time($room_id,$meeting_date)
    {
        $sql = 'select meeting_startTime,meeting_endTime,meeting_title from meeting where meeting_state=1 and meeting_room_id='.$room_id.' and meeting_date="'.$meeting_date.'" order by meeting_startTime';
        return M('meeting','','DB_MEETING')->query($sql);
    }
	
	//获取所有的地区信息
	public function get_meeting_room_local(){
		return M('meeting_room_local','','DB_MEETING')->query('SELECT * FROM  `meeting_room_local`');
	}
	//获取所有的楼座信息
	public function get_meeting_room_building($local){
		return M('meeting_room_building','','DB_MEETING')->query('SELECT * FROM  `meeting_room_building` where local_id='.$local); 	
	}
	//获取所有的楼层信息
	public function get_meeting_room_floor($building){
		return M('meeting_room_floor','','DB_MEETING')->query('SELECT * FROM  `meeting_room_floor` where building_id='.$building); 	
	}
	
	//通知所有人取消会议
	public function cancel_meeting($id){
		$data['meeting_state']	= 0;
		$data['meeting_update'] = time();
		$rs = M('meeting','','DB_MEETING')->where('meeting_id='.$id)->save($data);
		A('Company/Push')->meeting_push($id,'您有一条会议被取消!');
		$this->del_meeting_push_by_meeting_id($id);
		return $rs;
	}

	//修改参会状态
	public function update_join_state($data){
		return M('meeting_join','','DB_MEETING')->where('meeting_id='.$data['meeting_id'].' and audience_id='.$data['uid'])->save($data);
	}
/**
  *获取所有楼层
  *update author: zhanghao
  *update time  : 2014-11-10 09:52
  *use    this	: A('Meeting')->get_meeting_room_locals
  ****************************************/
  	public function get_all_floor(){
		return M('meeting_roomfloor','','DB_MEETING')->select();
	}
/**
  *获取所有地区
  *update author: zhanghao
  *update time  : 2014-11-10 09:52
  *use    this	: A('Meeting')->get_meeting_room_locals
  ****************************************/	
	public function get_all_local(){
		return M('meeting_roomlocal','','DB_MEETING')->select();
	}
/**
  *获取所有大楼
  *update author: zhanghao
  *update time  : 2014-11-10 09:52
  *use    this	: A('Meeting')->get_meeting_room_locals
  ****************************************/	
	public function get_all_building(){
		return M('meeting_roombuilding','','DB_MEETING')->select();
	}
/**
  *预定会议室
  *update author: zhanghao
  *update time  : 2014-11-28 11:28
  *use    this	: A('Meeting')->create_meeting
  ****************************************/	
	public function schedule_meeting_room($data){
		$data['room_id']= $data['meeting_room_id'];
		$data['meeting_startTime'];
		$data['meeting_endTime'];
		//first 查询当前所有有关room_id的预定
		$rs = M('meeting','','DB_MEETING')->field('meeting_startTime,meeting_endTime')->where('meeting_room_id='.$data['meeting_room_id'].' and meeting_endTime>'.time())->select();
		foreach($rs as $val)
		{

			// if($data['meeting_startTime'] > $val['meeting_startTime'] && $data['meeting_startTime'] < $val['meeting_endTime'])
			// {
			// 	//当前开始时间已经预定
			// 	return false;
			// }else if($data['meeting_endTime'] > $val['meeting_startTime'] && $data['meeting_startTime'] < $val['meeting_endTime'])
			// {
			// 	//当前结束时间已经预定
			// 	return false;
			// }else if($val['meeting_endTime'] < $data['meeting_endTime'] && $val['meeting_startTime'] > $data['meeting_startTime'] )
			// {
			// 	//有一个会议在 预定的的会议中间
			// 	return false;
			// }else if($val['meeting_startTime'] > $data['meeting_startTime'] && $val['meeting_endTime'] < $data['meeting_endTime'] )
			// {
			// 	//有一个会议在 预定的的会议中间
			// 	return false;
			// }


			// 2015/03/14
			if(($data['meeting_startTime']- $val['meeting_endTime']) * ($data['meeting_endTime']- $val['meeting_startTime']) < 0){
				return false;
			}

		}
		return true;
	}
/**
  *预定会议室
  *update author: zhanghao
  *update time  : 2014-11-28 11:28
  *use    this	: A('Meeting')->create_meeting
  ****************************************/
	public function upt_max_step($id){
	   	$sql ='select * from meeting_roomschedule';
		$sql.=' where schedule_id='.$id;
		$sql.=' limit 1';
		$rs = M('meeting_roomschedule','','DB_MEETING')->query($sql);
		
		for($i=0;$i<=23;$i++){
			$data[]=$rs[0]['schedule_'.$i];
		}
		$j=0;
		$y=0;
		$maxtimes=array(0);
		foreach($data as $key=>$value){
			//重复1次
			if($value===0)
				$j++;
			//当前上一个值
			$i=$data[$key-1];
			if($i===NULL)
				continue;
			//如果当前和上一个相同
			if($i===$value){
				//重复次数加1
				$y++;
			}else{
				$maxtimes[]=$y;
				$y=0;	
			}
		}
		$data1['schedule_maxstep']=max($maxtimes);
		return M('meeting_roomschedule','','DB_MEETING')->where('schedule_id='.$rs[0]['schedule_id'])->save($data1);
	}

	public function get_meeting_local($room_id){
		$sql   = 'select * from meeting_room as b';
		$sql  .= ' join meeting_roomfloor c on b.floor_id=c.floor_id';
		$sql  .= ' join meeting_roombuilding d on c.building_id=d.building_id';
		$sql  .= ' join meeting_roomlocal e on d.local_id=e.local_id';
		$sql  .= ' where room_id='.$room_id;
		$rs    = M('meeting_room','','DB_MEETING')->query($sql);
		return $rs[0]['local_name'].' '.$rs[0]['building_name'].' '.$rs[0]['floor_name'].' '.$rs[0]['room_name'];
	}

	public function get_red_point($data){

		$map['audience_id'] = $data['uid'];

		$map1['_complex'] = [
			'_logic' => 'or',
			'time_insert' => ['gt',$data['timestamp']],
			'time_update' => ['gt',$data['timestamp']]
		];


		//是否有消息更改
		$rs = M('meeting_join','','DB_MEETING')
			->field('meeting_id')->where($map)->select();

		foreach($rs as $val){
			$map1['meeting_id'] = $val['meeting_id'];
			$red = M('meeting','','DB_MEETING')->field('meeting_id')
				->where($map1)->find();
			if($red)
				return true;
		}
		return false;
	}
/*
 * 1、全体情况
 * 		1.获取所有的组
 * 		2.获取所有组下面的用户
 * 2、不是全体，判断当前group情况
 * */
	public function get_meeting_join_for_idg($data){

		$return_data = [];
		$groups		 = [];
		$tmp	     = [];

		$map = [
			'meeting_id' => $data['meeting_id'],
			'is_secret'  => 0
		];
		$meeting_join 	  = M('meeting_join','','DB_MEETING')
			->field('audience_id')
			->where($map)
			->select();

		$map1 = [
			'_string' => 'time_insert > time_delete',
			'category_name' => [
				'in','Partners,Venture Partners,VPs,Legal&Finance,Associates,Analysts,DDs,ADMIN,EIR,Directors'
			]
		];
		$meeting_category = M('meeting_category','','DB_MEETING')
			->where($map1)
			->order('category_sort desc')
			->select();

		//获取所有成员
		foreach($meeting_category as $val){
			$group = [
				'group_id'   =>$val['category_id'],
				'group_name' =>$val['category_name'],
				'group_sort' =>$val['category_sort']
			];
			$group['members']	  = $this->getmeeting_category_members_by_category_id($val['category_id']);
			$group['members_num'] = count($group['members']);
			$group['members_num'] && $groups[]= $group;
		}

		//排查是否在当前组
		foreach($meeting_join as $member){
			$join_state = false;
			foreach($groups as &$val){
				if(in_array($member['audience_id'],$val['members'])){
					$val['tmp'][] = $member['audience_id'];
					unset($member['audience_id']);
					$join_state = true;
				}
			}
			$join_state == false && $tmp[] = $member['audience_id'];
		}

		//未分组的成员
		if( $tmp != [] && count($tmp) ){
			$group = [

				'group_id'   => 0,
				'group_name' => 'No group',
				'group_sort' => 0,
				'tmp'		 => $tmp,
				'members_num'=> count($tmp)

			];
			$groups[]=$group;
		}

		//去除没有的组
		foreach($groups as $val1){
			if($val1['tmp']){
				unset($val1['members']);
				$return_data[]=$val1;
			}
		}

		//获取用户信息
		$filed = 'audience_id,audience_name,audience_portrait,audience_sort';
		foreach($return_data as &$val2){
			foreach($val2['tmp'] as $val3){

				$map = [
					'audience_id'=>$val3,
					'_string'	 =>'time_insert>time_delete'
				];

				$audience_info = M('audience','','DB_MEETING')->field($filed)->where($map)->find();

				if(!$audience_info){
					continue;
				}
				if($audience_info['audience_portrait'] != '')
					$audience_info['audience_portrait'] = C('DOMAIN_NAME').__ROOT__.'/'.$audience_info['audience_portrait'];

				$val2['members'][] = $audience_info;
			}
			$val2['members_num'] = count($val2['members']).'';
			unset($val2['tmp']);
		}
		return $return_data;
	}

	public function getmeeting_category_members_by_category_id($category_id){
		$rs = M('audience_meeting_category','','DB_MEETING')
			->field('audience_id')
			->where(['category_id'=>$category_id])
			->select();
		return i_array_column($rs,'audience_id');
	}

	public function get_title_by_id($meeting_id){
		return M('meeting','','DB_MEETING')
			->where(['meeting_id'=>$meeting_id])
			->getField('meeting_title');
	}

	public function get_audience_by_meeting_id($id){
		$map['meeting_id'] = $id;
		$rs = M('meeting_join','','DB_MEETING')->field('audience_id')->where($map)->select();
		return i_array_column($rs,'audience_id');
	}

	public function set_push_notice($data){

		$rs = $this->get_meeting_push($data);
		$start_time = $this->get_meeting_start_time($data['meeting_id']);
		if($data['push_time'] == 1){//删除
			$this->del_meeting_push($data);
			return true;
		}else if($data['push_time'] == 2){//修改startTime
			$data['push_time']  = $start_time - $rs['push_time'];
			$data['befor_time'] = $start_time - $rs['befor_time'];
		}else{//设置
			$data['befor_time'] = $data['push_time'];
			$data['push_time']  = $start_time - $data['push_time'];
		}

		if($rs)
			return M('meeting_push','','DB_MEETING')->where(['id'=>$rs['id']])->save($data);
		else
			return M('meeting_push','','DB_MEETING')->add($data);

	}

	public function del_meeting_push_by_meeting_id($meeting_id){
		$map = [
			'meeting_id'=>$meeting_id
		];
		return M('meeting_push','','DB_MEETING')->where($map)->delete();
	}

	public function del_meeting_push($data){
		$map = [
			'meeting_id'=>$data['meeting_id'],
			'audience_id'=>$data['audience_id']
		];
		return M('meeting_push','','DB_MEETING')->where($map)->delete();
	}

	public function get_meeting_start_time($meeting_id){
		$map = ['meeting_id'=>$meeting_id];
		return M('meeting','','DB_MEETING')->where($map)->getField('meeting_startTime');
	}

	public function get_meeting_start_time_meeting_name($meeting_id){
		$map = ['meeting_id'=>$meeting_id];
		return M('meeting','','DB_MEETING')->field('meeting_title,meeting_startTime')->where($map)->find();
	}

	public function get_meeting_push($data){
		$map = [
			'meeting_id'=>$data['meeting_id'],
			'audience_id'=>$data['audience_id']
		];
		$rs  = M('meeting_push','','DB_MEETING')->where($map)->find();
		return $rs;
	}
}