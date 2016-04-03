<?php


class LoginAction extends Action {

	public function index()
	{
		$this->display();
	}

	public function check_login()
	{

	    $_SESSION['ln_user'] = null;

	    $data['username'] = $_POST['ln_admin_username'];
	    $data['password'] = $_POST['ln_admin_password'];

	    $data['username'] == '' && $this->error('请输入用户名!');
	    $data['password'] == '' && $this->error('请输入密码!');

	    $username = $data['username'];
	    $password = self_md5($data['password']);

	    $user = D('Company/User')->get_user($username);

	    if(!$user)$this->error('用户不存在!');

	    if($user['password'] != $password)$this->error('密码错误!');

	    $user['token'] = self_md5($user['uid'].$user['username'].$user['password']);
	    $user['photo']!=''&& $user['photo'] = C('DOMAIN_NAME').__ROOT__.'/'.$user['photo'];

	    unset($user['username']);
	    unset($user['password']);

	    $_SESSION['ln_user'] = $user;
	    // $this->success('登录成功!',U('Admin/Index/index'));

		redirect(U('Admin/Index/index'));
	}

	public function login_out(){
	    $_SESSION['ln_user'] = null;
	    // $this->success('登出!',U('Admin/Login/index'));
		redirect(U('Admin/Login/index'));
	}

}