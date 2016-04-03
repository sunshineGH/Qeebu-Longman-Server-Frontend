<?php
class AudienceAction extends Action {

//-------------------------------------insert--------------------------------------
//注册用户--注册版本
	public function register_audience(){

		$data = [
			'audience_name' => check_null(40014,true,$_REQUEST['username']),
			'password'		=> check_null(40012,true,$_REQUEST['password']),
			'conditions'	=> check_null(40039,true,$_REQUEST['conditions']),
			'username'		=> check_null(40039,true,$_REQUEST['conditions']),
		];

		if(strpos($_REQUEST["conditions"],"@") === false){
			$_REQUEST['audience_tel']   = $_REQUEST['conditions'];
		}else{
			$_REQUEST['audience_email'] = $_REQUEST['conditions'];
		}

		$rs = D('Audience')->register_audience($data);
		if(!$rs){
			return_json(-1);
		}else{
			return_json(0,[
				'audience_id' => StrCode(''.$rs,'ENCODE'),
				'team_count'  => 0
			]);
		}
	}
//-------------------------------------delete----------------------------------------
//-------------------------------------update----------------------------------------
//修改用户--IDG版本
	public function update_audience(){

		if(!Database($_REQUEST["cid"]))exit;
		$_REQUEST['uid'] 		 = check_null(40002,true,$_REQUEST['uid']);
		$_REQUEST['time_update'] = time();

		$_FILES['audience_portrait']['name']!='' && $this->imgUpload();

		$rs = D('Audience')->update_audience($_REQUEST);
		if(!$rs){
			return_json(-1);
		}else{
			$audience = D('Audience')->get_audience($_REQUEST["uid"],'');
			return_json('0',$audience);
		}


	}
//修改用户-register 版本
	public function new_update_audience(){

		$_REQUEST['uid'] = check_null(40002,true,$_REQUEST['uid']);
		$_FILES['audience_portrait']['name']!='' && $this->imgUpload();

		$rs = D('Audience')->new_update_audience($_REQUEST);
		if($rs){
			return_json('0',$rs);
		}else{
			return_json(-1);
		}
	}

	public function new_check_user_pwd(){

		$data = [
			'uid'=>	check_null(40002,true,$_REQUEST['uid']),
			'pwd'=> check_null(40012,true,$_REQUEST['pwd'])
		];

		$rs = D('Audience')->new_check_user_pwd($data);
		if($rs){
			return_json('0');
		}else{
			return_json(-1);
		}
	}

	public function new_upt_user_pwd(){

		$data = [
			'uid'=>	check_null(40002,true,$_REQUEST['uid']),
			'pwd'=> check_null(40012,true,$_REQUEST['pwd'])
		];

		$rs = D('Audience')->new_upt_user_pwd($data);
		if($rs){
			return_json('0');
		}else{
			return_json(-1);
		}

	}



//-------------------------------------select--------------------------------------
	//用户登录
	public function login() {
		if(!Database($_REQUEST["cid"]))return;

		$_REQUEST['username'] = check_null(40014,true,$_REQUEST['username']);
		$_REQUEST['password'] = check_null(40012,true,$_REQUEST['password']);

		//判断当前用户名是否存在
		$audience = D('Audience')->get_audience('',$_REQUEST["username"]);
		if(!$audience){
			return_json(40015);return;
		}
		//判断当前用户名不可用
		if ($audience['audience_state'] == 0) {
			return_json(40019);return;
		}
		//判断密码是否正确
		if($_REQUEST['cid'] != 102){
			if ($audience['audience_pwd'] != $_REQUEST["password"]) {
				return_json(40008);return;
			}
		}else{
			if($audience['audience_pwd'] == $_REQUEST["password"]) {
				//return_json(40008);return;

			}else{
				$data['random_tel'] = $audience['audience_tel'];
				$rs = D('Audience')->get_random_pwd($data);
				if($rs['random_pwd'] != $_REQUEST['password'] || $rs['random_state']==1){
					return_json(40008);return;
				}else{
					D('Audience')->update_random_pwd_state($data['random_tel']);
				}
			}
		}

		if ($audience['time_delete'] > $audience['time_insert']){
			return_json(40019);return;
		}

		$audience['audience_num'] = '';
		$audience['department_pid'] = '';
		$audience['have_child'] = '';
		$audience['floor_id'] = '';
		$audience['department_id'] = '';

		return_json(0,$audience);
	}

	public function get_random_pwd(){

		if(!Database($_REQUEST['cid']));
		$_REQUEST['username'] = check_null(40014,true,$_REQUEST['username']);

		$audience = D('Audience')->get_audience('',$_REQUEST["username"]);
		$tel 	  = check_null(40114,true,$audience['audience_tel']);

		$verify  = D('Audience')->create_random_password($tel);
		$content = '尊敬的客户：动态登录密码'.$verify.'，动态码仅供客户本人使用，切勿告知他人。';
		$rs = A('Company/Message')->mt($tel,$content);
		if($rs)
			return_json(0);
		else
			return_json(-1);
	}

//找回密码
	public function find_pwd(){

		if($_REQUEST['cid'] != 0){
			if(!Database($_REQUEST["cid"]))return;
		}
		$conditions = check_null(40039,true,$_REQUEST['conditions']);

		$title = 'TeamIn密码找回！';
		$msg   = '您的TeamIn密码为 : ';

		if(filter_var($conditions, FILTER_VALIDATE_EMAIL)){//email

			$audience_pwd=D('Audience')->find_pwd('',$conditions);
			if(!$audience_pwd){return_json(40001);return;}

			if(for_mail($conditions,$title,$msg.$audience_pwd))
				return_json(0);
			else
				return_json(40040);
		}else{
			//tel
			$tel  ='/^[0-9]{11}$/';
			preg_match($tel,$conditions,$matches);
			if($matches==array() || count($matches)==0){
				return_json(40041);return;
			}

			$audience_pwd=D('Audience')->find_pwd($conditions,'');
			if(!$audience_pwd){return_json(40001);return;}

			if(for_sms($conditions,$msg.$audience_pwd)){
				return_json(0);
			}else{
				return_json(40073);
			}
		}
	}

	public function get_verify(){

		$title 		= '您好，欢迎您注册TeamIn';
		$msg_email  = '您好,欢迎您注册TeamIn,请点击下面的链接完成验证：'.'<br/>';
		$msg_mobile = '您好,欢迎您注册TeamIn,您的验证码为: ';
		$conditions = check_null(40039,true,$_REQUEST['conditions']);
		$_REQUEST['key'] = explode('-',end(explode('&',$_SERVER["HTTP_REFERER"])));
		$_REQUEST['key'] = substr($_REQUEST['key'][0],2);

		if($_REQUEST['for_reg']==1){
			if(D('Audience')->find_audience_id_by_username($conditions)){
				return_json(40131);return;
			}
		}

		if($_REQUEST['check_in']==1){
			$uid = D('Audience')->find_audience_id_by_username($conditions);
			if($uid){
				$data = ['uid'=>$uid['audience_id'],'cid'=>StrCode($_REQUEST['key'],'DECODE')];
				if($re = D('Team')->get_team_member_with_cid_uid($data)){
					return_json('1',[
						'url' =>'http://huiyi.qeebu.cn/teamin/Team/welcome?tokens=XMKUHNDLUIOQWERBNJKL&c=AS8bfrASrrrZXCqDrq&k='.$_REQUEST['key'].'-a',
					]);
					return;
				}
			}
		}

		if(filter_var($conditions, FILTER_VALIDATE_EMAIL)){
			//email
			$_REQUEST['conditions_style'] = 2;
			$verify 	= D('Audience')->create_verify($_REQUEST);

			$url		= C('DOMAIN_NAME').__ROOT__.'/Audience/test_verify';
			$email_verify 	= $url.'?c='.base64_encode($conditions).'&k='.StrCode($verify,'ENCODE');

			if($_REQUEST['web']!=1){
				if(for_mail($conditions,$title,$msg_email.$email_verify))
					return_json(0,['conditions_style'=>'2']);
				else
					return_json(40040);
			}else{
				if(for_mail($conditions,$title,$msg_mobile.$verify))
					return_json(0,['conditions_style'=>'2']);
				else
					return_json(40040);
			}


		}else{
			//tel
			preg_match('/^[0-9]{11}$/',$conditions,$matches);

			if($matches==array() || count($matches)==0){
				return_json(40041);return;
			}

			$_REQUEST['conditions_style'] = '1';
			$verify = D('Audience')->create_verify($_REQUEST);

			if(for_sms($conditions,$msg_mobile.$verify))
				return_json(0,[
					'conditions_style' =>'1',
					'conditions_to'	   =>StrCode($conditions).'&tokens=XMKUHNDLUIOQWERBNJKL&k='.$_REQUEST['key'],
				]);
			else
				return_json(40073);
		}
	}

	//验证--register
	public function check_verify(){

		$_REQUEST['conditions'] = check_null(40039,true,$_REQUEST['conditions']);
		$rs = D('Audience')->get_verify($_REQUEST);
		if(!filter_var($_REQUEST['conditions'], FILTER_VALIDATE_EMAIL)){
			$_REQUEST['verify'] = check_null(40097,true,$_REQUEST['verify']);
			if($rs['verify_key'] == $_REQUEST['verify']){
				return_json(0);return;
			}else{
				return_json(40098);return;
			}
		}else{

			if($rs['verify_state'] == 1){
				return_json(0);return;
			}else{
				return_json(40099);return;
			}
		}
	}

	//find by name or mobile phone
	public function find_person(){

		if(!Database($_REQUEST["cid"]))return;
		$conditions = check_null(40039,true,$_REQUEST['conditions']);

		$rs=D('Audience')->find_person($conditions);
		if($rs)
			return_json(0,$rs);
		else
			return_json(40001);
	}

	//find audience by uid
	public function find_person_by_uid(){

		if(!Database($_REQUEST["cid"]))return;
		$uid = check_null(40002,true,$_REQUEST['uid']);

		$rs=D('Audience')->get_audience($uid,'');
		if($rs)
			return_json(0,$rs);
		else
			return_json(40001);
	}
/*
 *get_user_list 获取用户列表
 *arg
 *uid int
 *cid int
 ****/
	public function get_user_list(){
		//检测数据完整性
		switch(''){
			//case $_REQUEST['timestamp']:return_json(40042);return;
			case $_REQUEST["count"]:$_REQUEST['count']=10;
			case $_REQUEST['page']:$_REQUEST['page']=1;
		}
		if(!Database($_REQUEST["cid"]))exit;
		$rs=D('Audience')->get_audience_list($_REQUEST);
		if($rs){
			return_json('0',$rs,time());
		}else{
			return_json('40001');
		}
	}

//通过数组imUsername名单获取audience信息
/*
 *get_user_list 获取用户列表
 *arg
 *uid int
 *cid int
 ****/
	public function get_audience_by_imUsernameArray(){
		switch(''){
			case $_REQUEST['imUsernameArray']:return_json(40068);return;
		}
		if(!Database($_REQUEST["cid"]))exit;
		$_REQUEST['imUsernameArray'] = explode(",",$_REQUEST['imUsernameArray']);
		//echo json_encode($_REQUEST);
		$rs=D('Audience')->get_audience_by_imUsernameArray($_REQUEST['imUsernameArray']);
		if($rs){
			return_json('0',$rs);
		}else{
			return_json(40001);
		}
	}

//用户数据库同步
	public function get_audience_database_synchronization(){
		if(!Database($_REQUEST["cid"]))exit;
		$_REQUEST['timestamp']=$_REQUEST['timestamp']==0?1:$_REQUEST['timestamp'];
		$rs=D('Sqlite')->get_audience_database_synchronization($_REQUEST['timestamp']);
		if($rs){
			$rs['data_type']=2;
			$rs['timestamp']=time();
			return_json('0',$rs);
		}else{
			return_json(40001);
		}
	}
//通讯录数据库同步 -- IDG
	public function get_address_list_database_synchronization(){
		if(!Database($_REQUEST["cid"]))exit;
		$_REQUEST['timestamp']				=$_REQUEST['timestamp']==0?1:$_REQUEST['timestamp'];

		$rs['audience_list']				=D('Sqlite')->get_audience_database_synchronization($_REQUEST['timestamp']);
		$rs['department_list']				=D('Sqlite')->get_department_database_synchronization($_REQUEST['timestamp']);
		$rs['audience_department_rds_list'] =D('Sqlite')->get_audience_department_rds_database_synchronization($_REQUEST['timestamp']);
		$rs['timestamp']=time();
		return_json('0',$rs);
	}
//通讯录数据库同步 -- Register
	public function new_get_address_list_database_synchronization(){
		$_REQUEST['cid']	   = check_null(40038,true,$_REQUEST['cid']);
		$_REQUEST['timestamp'] = $_REQUEST['timestamp']==0?1:$_REQUEST['timestamp'];
		if(!Database($_REQUEST["cid"]))exit;

		$rs['audience_list']				=D('Sqlite')->new_get_audience_database_synchronization($_REQUEST['timestamp']);
		$rs['department_list']				=D('Sqlite')->get_department_database_synchronization($_REQUEST['timestamp']);
		$rs['audience_department_rds_list'] =D('Sqlite')->new_get_audience_department_rds_database_synchronization($_REQUEST['timestamp']);
		$rs['timestamp']=time();

		$rs['key'] = $_REQUEST['cid'];
		return_json('0',$rs);
	}

//获取验证码 -- register


//邮箱验证 -- register
	public function test_verify(){
		header('content-type:text/html;charset=utf-8');
		$data['conditions'] = base64_decode($_GET['c']);
		$data['verify_key'] = StrCode($_GET['k'],'DECODE');
		$rs = D('Audience')->get_verify($data);
		if($rs['verify_key'] == $data['verify_key']){
			D('Audience')->update_verify_state($data);
			echo '验证成功,请返回手机完成后续操作!';
		}else{
			echo '验证码已变更，请点击最新的邮件!';
		}
	}
//搜索用户 -- register
	public function search_audience(){

		$_REQUEST['conditions']  = check_null(40039,true,$_REQUEST['conditions']);
		if(strpos($_REQUEST["conditions"],"@") === false){
			$tel  ='/^[0-9]{11}$/';
			preg_match($tel,$_REQUEST["conditions"],$matches);
			if($matches==array() || count($matches)==0){
				return_json(40041);return;
			}
			$rs = D('Audience')->search_audience($_REQUEST,'tel');

		}else{
			$email="/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/";
			preg_match($email,$_REQUEST["conditions"],$matches);
			if($matches==array() || count($matches)==0){
				return_json(40040);return;
			}
			$rs = D('Audience')->search_audience($_REQUEST,'email');
		}

		if($rs){
			return_json(0,$rs);
		}else{
			return_json(40001);
		}
	}
//登录 -- register 版本
	public function new_login(){

		$_REQUEST['username'] = check_null(40014,true,$_REQUEST['username']);
		$_REQUEST['password'] = check_null(40012,true,$_REQUEST['password']);

		//判断当前用户名是否存在
		$audience = D('Audience')->new_get_audience($_REQUEST);
		if(!$audience){
			return_json(40015);
			return;
		}else if ($audience['audience_pwd'] != $_REQUEST["password"]) {
			return_json(40008);
			return;
		}

		$data['audience_id']= StrCode($audience['audience_id']);
		$data['team_count'] = D('Team')->get_team_count($audience['audience_id']);
		return_json(0,$data);
	}




//-------------------------------------public function-------------------------------
	public function imgUpload(){

		!isset($_REQUEST['cid']) && $_REQUEST['cid'] = 0;

		$upload_path = 'Public/Uploads'.'/';
		!is_dir($upload_path) && mkdir($upload_path,0777,true);
		$upload_path.= 'audience'.'/';
		!is_dir($upload_path) && mkdir($upload_path,0777,true);
		$upload_path.= $_REQUEST["cid"] .'/';
		!is_dir($upload_path) && mkdir($upload_path,0777,true);

		import('ORG.Net.UploadFile');

		$config = [
			'savePath' 	=> $upload_path,
			'maxSize'  	=> 3145728,
			'allowExts'	=> ['jpg', 'gif', 'png', 'jpeg'],

			'thumb'				=> true,
			'thumbPrefix'		=> 'm_',
			'thumbMaxWidth'		=> '480',
			'thumbMaxHeight'	=> '320',
			'thumbRemoveOrigin' => true
		];

		$upload = new UploadFile($config);
		if(!$upload->upload()){
			$_REQUEST['audience_portrait'] = '';
		}else{
			$uploadList = $upload->getUploadFileInfo();
			$_REQUEST['audience_portrait'] = $config['savePath'].$config['thumbPrefix'].$uploadList[0]['savename'];
		}
	}

}
?>