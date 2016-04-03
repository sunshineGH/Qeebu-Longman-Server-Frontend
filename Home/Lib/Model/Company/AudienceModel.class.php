<?php
class AudienceModel extends Model{


	public $model = null;					//M('audience','','DB_MEETING')
	public $model_admin = null;				//M('audience','','')->db('CONFIG1')

	private $audience_id = "`audience_id`";
	private $audience_list_cows="a.`audience_id`,a.`audience_tel`,a.`audience_email`,a.`audience_name`,a.`audience_position`,a.`audience_portrait`,b.`department_name` as `audience_department`,a.`audience_imUsername` ,a.`audience_imPassword`";

//-------------------------------------select-------------------------------------------
	public function get_model(){
		if($this->model)
			return $this->model;
		else{
			$this->model = M('audience','','DB_MEETING');
			return $this->model;
		}
	}

	public function get_admin_model(){
		if($this->model_admin)
			return $this->model_admin;
		else{
			$this->model_admin = M('audience','','DB_MEETING')->db('CONFIG1');
			return $this->model_admin;
		}
	}

	public function get_my_company($data){

		$map = ['uid' => $data['uid']];

		$data['startid']!=null && $map['audience_id'] = ['egt',$data['startid']];

		$rs = M("audience","",'DB_MEETING')
			->where($map)
			->limit((($data['page']-1)*$data['count']).",".$data['count'])
			->select();

		return $rs;
	}

//获取用户列表
	public function get_audience_list($data){
		$sql = "select ".$this->audience_list_cows." from audience as a";
		$sql.= " join audience_department as b on a.audience_department=b.department_id";
		$sql.= " where audience_state!=0";
		$sql.= " order by a.audience_nick";
		if($_REQUEST['startid']!=null)
			$sql.=" and ".$this->audience_id." >=".$data['startid'];
		else
			$sql.= " limit ".(($data['page']-1)*$data['count']).",".$data['count'];
		return M("audience","",'DB_MEETING')->query($sql);
	}
//通过uid搜索用户名 Register version
	public function new_get_audience_name_by_uid($uid){
		return M('audience','','')->db('CONFIG1')->where('audience_id='.$uid)->getField('audience_name');
	}
//-------------------------------------insert-------------------------------------------

	public function join_team_come_from_web($data){


		$data['audience_tel']= $data['audience_username'];
		$data['current_cid'] = $data['cid'];
		$data['time_insert'] = time();
		$data['time_update'] = time();
		$data['time_delete'] = 0;

		$rs = M('audience','','')->db('CONFIG1')->add($data);

		$data1['uid'] 		= $rs;
		$data1['cid'] 		= $data['cid'];
		$data1['cid_mark']	= StrCode($data['cid']);
		$data1['audience_character'] = 3;
		$data1['character_incomming']= 6;
		$data1['time_insert']= time();
		$data1['time_update']= time();
		$data1['time_delete']= 0;

		$re = D('Company/Team')->get_team_with_cid_uid($data1);

		if($re)
			M('audience_with_company','','')->db('CONFIG1')->where('cid='.$data1['cid'].' and uid='.$data1['uid'])->save($data1);
		else{
			M('audience_with_company','','')->db('CONFIG1')->add($data1);
		}

		return $rs;
	}

	public function check_tel_in($tel){
		return M('audience','','DB_MEETING')->where('audience_tel="'.$tel.'" or audience_username="'.$tel.'"')->getField('audience_id');
	}

	public function create_random_password($tel){

		$data = [
			'random_pwd'  => rand(1,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).'',
			'random_tel'  => $tel,
			'random_time' => 0,
			'random_state'=>0,
		];
		$this->save_random_pwd($data);

		return $data['random_pwd'];
	}

	public function save_random_pwd($data){
		$re = $this->get_random_pwd($data);
		if($re){
			$map = [ 'random_tel'=>$data['random_tel']];
			return M('audience_random_password','','DB_MEETING')->where($map)->save($data);
		}else{
			return M('audience_random_password','','DB_MEETING')->add($data);
		}
	}

	public function get_random_pwd($data){
		$map = [ 'random_tel'=>$data['random_tel']];
		return M('audience_random_password','','DB_MEETING')->where($map)->find();
	}



//-------------------------------------update-------------------------------------------
	public function update_random_pwd_state($random_tel){
		$data['random_state'] = 1;
		return M('audience_random_password','','DB_MEETING')->where('random_tel="'.$random_tel.'"')->save($data);
	}

//-------------------------------------delete-------------------------------------------
//-------------------------------------select-------------------------------------------


	//获取用户信息
	public function get_audience_name_by_uid($uid){

		$map['audience_id'] = $uid;

		return M('Audience','','DB_MEETING')
			->where($map)
			->getField('audience_name');
	}
	/*
	 *get_audience 查询当前用户数据
	 *arg
	 *uid int
	 *usernmae varchar
	 *****/
	public function get_audience($uid='',$username=''){

		if($uid!=''){
			$map['audience_id'] = $uid;
		}else{
			$map['audience_username'] = $username;
		}

		$rs = M('audience','','DB_MEETING')->where($map)->find();

		if(!$rs)
			return false;

		$rs['audience_id'] == 879 && $rs['time_delete'] = 0;

		$rs['audience_password']   = md5($rs['audience_password']);
		$rs['audience_department'] = D('Department')->get_department_id_by_audience_id($rs['audience_id']);
		$rs['department_name'] 	   = D('Department')->get_department_name_by_id($rs['audience_department']);
		$rs['is_leader']   	 	   = M('audience_department','','DB_MEETING')->field('department_id')->where('leader_id='.$rs['audience_id'])->find()?true:false;
		$rs['is_personnel']	  	   = M('department_personnel','','DB_MEETING')->field('dpid')->where('personnel_id='.$rs['audience_id'])->find()?true:false;
		return $rs;
	}
	/*
	 *find_pwd 查询当前用户密码
	 *arg
	 *tel varchar
	 *email varchar
	 *****/
	public function find_pwd($tel,$email){
		if($_REQUEST['cid'] == 0){
			if($tel!=''){
				return M('audience','','')->db('CONFIG1')->where('audience_tel="'.$tel.'"')->getField('audience_pwd');
			}else{
				return M('audience','','')->db('CONFIG1')->where('audience_email="'.$email.'"')->getField('audience_pwd');
			}
		}else {
			if ($tel != '') {
				return M('Audience', '', 'DB_MEETING')->where('audience_tel="' . $tel . '"')->getField('audience_pwd');
			} else {
				return M('Audience', '', 'DB_MEETING')->where('audience_email="' . $email . '"')->getField('audience_pwd');
			}
		}
	}
	/*
	 *find_person
	 *通过姓名或者职位查找人或项目
	 *arg
	 *conditions varchar
	 ****/
	public function find_person($conditions){
		$sql = 'select '.$this->audience_list_cows.' from audience as a';
		$sql.= " join audience_department as b on a.audience_department=b.department_id";
		$sql.= " where audience_state!=0";
		$sql.= ' and (audience_name like "'.$conditions.'%"';//通过姓名
		$sql.= ' or audience_position like "'.$conditions.'%"';//通过职位
		$sql.= ' or b.department_name like "'.$conditions.'%"';//通过部门
		$sql.= ')';
		return M('Audience','','DB_MEETING')->limit(30)->query($sql);
	}

	public function update_audience($data){
		return M('Audience','','DB_MEETING')->where('audience_id='.$data['uid'])->save($data);
	}

	public function get_audience_by_imUsernameArray($imUsernameArray){
		$sql='';
		foreach($imUsernameArray as $val){
			$sqlnew = 'select '.$this->audience_list_cows.' from audience as a';
			$sqlnew.= ' join audience_department as b on a.audience_department=b.department_id';
			$sqlnew.= ' where a.audience_imUsername="'.$val.'"';
			if($sql==''){
				$sql=$sqlnew;
			}else{
				$sql.=' union '.$sqlnew;
			}
		}
		return M("audience","",'DB_MEETING')->query($sql);
	}

	public function get_leader_id($uid){
		$sql ="select b.leader_id from audience as a";
		$sql.=" join audience_department as b on a.audience_department=b.department_id";
		$sql.=" where a.audience_id={$uid}";
		$sql.=" limit 1";
		$rs = M('audience','','DB_MEETING')->query($sql);
		return $rs[0]['leader_id'];
	}

	public function get_user_meeting_group($uid)
	{
		$group = '';
		$rs = M('audience_meeting_category','','DB_MEETING')->where('audience_id=%d',$uid)->select();
		foreach($rs as $val)
		{
			if($group != '')
				$group = ','.$val['category_id'];
			else
				$group = $val['category_id'];
		}
		return $group;
	}

	public function create_verify($data){
		$data['verify_key'] = ''.rand(1,9).rand(1,9).rand(1,9).rand(1,9);
		$data['verify_time']= time();
		$data['verify_state']= 0;
		$this->save_verify($data);
		return $data['verify_key'];
	}

	public function save_verify($data){
		if($this->get_verify($data)){
			$map = [
				'conditions'=>$data['conditions']
			];
			return M('audience_verify','','')->db('CONFIG1')->where($map)->save($data);
		}
		return M('audience_verify','','')->db('CONFIG1')->add($data);
	}

	public function get_verify($data){

		$map = [
			'conditions'=>$data['conditions']
		];
		return M('audience_verify','','')->db('CONFIG1')->where($map)->find();
	}

	public function update_verify_state($data)
	{
		$state['verify_state'] = 1;
		return M('audience_verify','','')->db('CONFIG1')->where('conditions="'.$data['conditions'].'"')->save($state);
	}

	public function register_audience($data)
	{
		$data['audience_username'] = $data['username'];
		$data['audience_pwd'] 	   = $data['password'];
		$data['time_insert']	   = time();
		$data['time_update']	   = time();
		$data['time_delete']	   = 0;

		return M('audience','','')->db('CONFIG1')->add($data);
	}

	public function search_audience_id_by_audience_name(){

	}

	public function search_audience($data,$type){

		if($data['need_detail'] == '')
			$field = 'audience_id,audience_name,audience_tel';
		else
			$field = 'audience_id,audience_name,audience_tel,audience_portrait';

		if($type == 'tel')
			$rs = M('audience','','')->db('CONFIG1')->field($field)->where('audience_tel="'.$data['conditions'].'"')->find();
		else
			$rs = M('audience','','')->db('CONFIG1')->field($field)->where('audience_email="'.$data['conditions'].'"')->find();
		if(!$rs){
			return false;
		}else{
			$rs['audience_id'] 		 = StrCode($rs['audience_id']);
			$rs['audience_portrait'] != '' && $rs['audience_portrait'] = C('DOMAIN_NAME').__ROOT__.'/'.$rs['audience_portrait'];
			return $rs;
		}
	}

	public function new_get_audience($data){
		return M('audience','','')->db('CONFIG1')->where('audience_username="'.$data['username'].'"')->find();
	}

	public function new_get_audience_id_by_audience_name($audience_name){
		return M('audience','','')->db('CONFIG1')->where(['audience_name'=>$audience_name])->getField('audience_id');
	}

	public function new_get_audience_by_uid($uid){
		$field = 'audience_id,audience_username,audience_name,audience_tel,audience_email,audience_portrait,audience_nick,audience_sex,audience_age,audience_qq,audience_weixin,current_token';
		return M('audience','','')
			->db('CONFIG1')
			->field($field)
			->where('audience_id='.$uid)
			->find();
	}

	public function find_audience_id_by_username($username){
		return M('audience','','')->db('CONFIG1')->field('audience_id')->where('audience_username="'.$username.'"')->find();
	}

	public function get_home_audience_info($data){
		$audience = $this->new_get_audience_by_uid($data['uid']);
		if(!$audience)return false;
		$audience['team_count']	 	    = D('Team')->get_team_count($data['uid']);
		$audience['audience_imUsername']= '';
		$audience['audience_imPassword']= '';
		$audience['department_name']    = '';
		$audience['audience_position']  = '';
		$audience['audience_id']	    = StrCode($audience['audience_id']);
		return $audience;
	}

	public function new_update_audience($data){
		$data['time_update'] = time();
		$map1['audience_id'] = $data['uid'];
		M('audience','','')->db('CONFIG1')->where($map1)->save($data);

		$map['uid'] = $data['uid'];
		$data1['time_update'] = time();
		M('audience_with_company','','')->db('CONFIG1')->where($map)->save($data1);

		return $this->get_home_audience_info($data);
	}

	//zhanghao 2015/4/18 16:56
	public function get_uid_meeting_group_admin($uid){
		$rs = $this->get_rights($uid);
		if($rs['admin_rights_id'] == 2){
			$re = M('admin_rights','','')->db('CONFIG1')->where('admin_id='.$rs['admin_id'].' and rights_for="meeting"')->getField('rights_id');
			return $re;
		}else{
			return false;
		}
	}

	public function get_rights($id){
		return M('admin','','')->db('CONFIG1')->where('audience_id='.$id)->find();

	}

	public function get_uid_by_cid($cid){
		$re = [];
		$map_insert['cid'] 				  = $cid;
		$map_insert['audience_character'] = array('in','1,2,3');
		$map_insert['_string']    		  = 'time_delete < time_insert';
		$rs=M('audience_with_company','','')
			->field('uid')
			->where($map_insert)
			->db('CONFIG1')
			->select();
		foreach($rs as $val){
			$re[] = $val['uid'];
		}
		return $re;
	}

	public function new_check_user_pwd($data){

		$rs = M('audience','','')
			->field('audience_pwd')
			->where(['audience_id'=>$data['uid']])
			->db('CONFIG1')
			->find();
		if(!$rs || $rs['audience_pwd']!=$data['pwd'])
			return false;

		return true;
	}

	public function new_upt_user_pwd($data){

		if($this->new_check_user_pwd($data))return true;

		$rs = M('audience','','')
			->where(['audience_id'=>$data['uid']])
			->db('CONFIG1')
			->save(['audience_pwd'=>$data['pwd']]);

		return $rs;
	}

	public function upt_user_current_devicetoken($data){


		return M('audience','','')
			->db('CONFIG1')
			->where(['audience_id' => $data['uid']])
			->save(['current_token'=>$data['devicetoken']]);
	}

	public function new_get_audience_info($uid,$cid){

		if($uid == 0) return null;

		$rs['audience_character'] =
			M('audience_with_company','','')->db('CONFIG1')
			->where(['uid'=>$uid,'cid'=>$cid])
			->getField('audience_character');
		if(!$rs)
			return [];
		$audience 				 = $this->new_get_audience_by_uid($uid);
		$rs['audience_name'] 	 = $audience['audience_name'];
		$rs['audience_portrait'] = C('DOMAIN_NAME').__ROOT__.'/'.$audience['audience_portrait'];

		if($cid!=0 && M('audience_with_company','','')->db('CONFIG1')->query("show database like 'teamin_".$cid."'")){
			if(!Database($cid))exit;
			$rs['department_name'] = D('Department')->get_department_name_by_audience_id($uid);
		}

		return $rs;

	}

}
?>