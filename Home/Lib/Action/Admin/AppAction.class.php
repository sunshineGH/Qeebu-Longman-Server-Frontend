<?php
class AppAction extends CommonAction{

	public function get_app_list(){

		$app_id = false;
		$cid = is_numeric($_REQUEST['cid']) ? $_REQUEST['cid'] : StrCode($_REQUEST['cid'],'DECODE');

		$map = [
			'app_openUrl'=>[
				['neq','TeamInHome'],
				['neq','NewTeamIn']
			],
			'app_project'=>'Teamin'
		];
		//$cid != 102 && $map['app_openUrl'][]=['neq','TeamInAdress'];

		if($cid != null){
			$ids = M('company_app_rds', '', 'DB_MEETING')->db(2, 'DB_CONFIG1')->where(['cid' => $cid])->select();
			$app_id = implode(',', i_array_column($ids, 'app_id'));

			if (!$app_id) {
				$rs = M('teamin_app', '', '')->db(1, 'DB_CONFIG2')->where($map)->select();
				foreach ($rs as $val) {
					$this->add_app([
						'cid' => $cid,
						'app_id' => $val['app_id']
					]);
				}
			}else{
				$app_id && $map['app_id'] = ['in', $app_id];
				$rs = M('teamin_app', '', '')->db(1, 'DB_CONFIG2')->where($map)->select();
			}
		}else{
			$rs = M('teamin_app', '', '')->db(1, 'DB_CONFIG2')->where($map)->select();
		}
		$rs ? return_json(0,$rs,$app_id) : retrun_json(40001);
	}

	public function get_app_list2(){

		$app_id = false;
		$cid = is_numeric($_REQUEST['cid']) ? $_REQUEST['cid'] : StrCode($_REQUEST['cid'],'DECODE');

		$map = [
			'app_openUrl'=>[
				['neq','TeamInHome'],
				['neq','NewTeamIn']
			],
			'app_project'=>'Teamin'
		];
		$cid != 102 && $map['app_openUrl'][]=['neq','TeamInAdress'];

		if($cid != null){
			$ids = M('company_app_rds', '', 'DB_MEETING')->db(2, 'DB_CONFIG1')->where(['cid'=>$cid])->select();
			dump($ids);
			$app_id = implode(',', i_array_column($ids, 'app_id'));
			dump($app_id);
			if (!$app_id) {
				$rs = M('teamin_app', '', '')->db(1, 'DB_CONFIG2')->where($map)->select();
				foreach ($rs as $val) {
					$this->add_app([
						'cid' => $cid,
						'app_id' => $val['app_id']
					]);
				}
			}else{
				$app_id && $map['app_id'] = ['in', $app_id];
				$rs = M('teamin_app', '', '')->db(1, 'DB_CONFIG2')->where($map)->select();
			}
		}else{
			$rs = M('teamin_app', '', '')->db(1, 'DB_CONFIG2')->where($map)->select();
		}
		$rs ? return_json(0,$rs) : retrun_json(40001);
	}

	public function to_update(){
		if($_GET['app_openUrl']==''){
			return_json(40067);return;
		}
		if($_GET['mobile_type']!='android')
			$cows = 'app_downloadUrl,app_version,ios_update_why';
		else
			$cows = 'android_url as app_downloadUrl,android_version as app_version,android_version_name,android_update_why';
		$sql ='select '.$cows.' from teamin_app';
		$sql.=' where app_openUrl="'.$_GET['app_openUrl'].'"';
		$sql.=' limit 1';
		$rs=M('teamin_app','','')->db(1,'DB_CONFIG2')->query($sql);
		if($rs[0])
			return_json(0,$rs[0]);
		else
			retrun_json(40001);
	}

	public function get_app_icon(){
		$rs = 'http://huiyi.qeebu.cn/teamin/Public/img/inviteImg1.jpg';
		return_json(0,$rs);
	}

	public function add_app($data){
		return M('company_app_rds','','')->db('CONFIG1')->add($data);
	}
}
?>