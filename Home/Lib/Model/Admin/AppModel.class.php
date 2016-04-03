<?php
class AppModel extends Model{

	public function get_app_list(){
		$arr1=M('teamin_app','','')->db(1,'DB_CONFIG2')->where('app_project="teamin" and app_openUrl="TeamInHome"')->select();
		$arr2=M('teamin_app','','')->db(1,'DB_CONFIG2')->where('app_project="teamin" and app_openUrl!="TeamInHome"')->select();
		return array_merge($arr1,$arr2);
	}

	public function get_app_setup_info(){

		$info = [
			'user_count' => M('user')->count(),

			'android_count' => 0,
			'teacher_count' => M('user')->where('role=2')->count()
		];
		$info['ios_count'] = $info['user_count'];
		return $info;
	}

}
?>