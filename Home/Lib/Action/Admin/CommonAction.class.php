<?php


class CommonAction extends Action {

	public function __construct(){
		//判断用户登录状态
		if(!isset($_SESSION['ln_user'])) {
			// $this->error('请登录!',U('Admin/Login/index'));
			redirect(U('Admin/Login/index'));
		}
	}
}