<?php
class IndexAction extends Action {
    public function idg(){
		//$rs=D('Admin/App')->get_app_list();
		$rs=M('teamin_app','','')->db(1,'DB_CONFIG2')->where('app_project="teamin" and app_openUrl="NewTeamIn"')->find();
		$this->assign('rs',$rs);
		$this->display();    
	}

	public function index(){

		$sql = <<<EOF
			select * from setup_admin.teamin_app where app_project="LeNing" and app_openUrl="LeNing" limit 1
EOF;
		$rs=M('teamin_app')->db('DB_CONFIG2')->query($sql);

		$this->assign('rs',$rs[0]);
		$this->display();
	}

	public function get_states(){
		if(!Database($_REQUEST["cid"]))exit;
		$_REQUEST['timestamp'] = $_REQUEST['timestamp']==0?1:$_REQUEST['timestamp'];
		$_REQUEST['uid'] 	   = check_null(40002,true,$_REQUEST['uid']);

		$rs = D('Audience')->get_audience($_REQUEST['uid']);

		if(!$rs){return_json(40019);exit();}

		if($rs['time_delete'] > 2 || $rs['audience_state'] != 1){
			return_json(40019);exit();
		}

		if($rs['time_update'] > $_REQUEST['timestamp']){
			$update = '1';
		}else{
			unset($rs);
			$update = '0';
		}

//		unset($rs['audience_username']);
//		unset($rs['audience_pwd']);
//		unset($rs['audience_department']);
//		unset($rs['audience_imUsername']);
//		unset($rs['audience_imPassword']);
//		unset($rs['leader_id']);
//		unset($rs['audience_rights']);
//		unset($rs['department_id']);
//		unset($rs['department_pid']);
//		unset($rs['audience_num']);
//		unset($rs['have_child']);
//		unset($rs['floor_id']);
//		unset($rs['audience_state']);
//		unset($rs['time_insert']);
//		unset($rs['time_delete']);
//		unset($rs['time_update']);

		return_json(0,$rs,time(),$update);
	}
}