<?php


class TeacherAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(){
		$school = M('school_address');
		$search   = isset($_GET['search'])   ? $_GET['search']   : '';
		$rs = pageing('user',7,$search,'uid desc','role=2','nickname');
		foreach($rs['data'] as &$val){
			//查校区
			$school_res = $school->where('id='.$val['school_address_id'])->field('name')->find();
			$val['school'] 		= $school_res['name'];
		}
		$rs['data'] = array_map(function($val){
			switch($val['type']){
				case 1:$val['type'] = '中教';break;
				case 2:$val['type'] = '外教';break;
				case 3:$val['type'] = '班主任';break;
			}
			return $val;
		},$rs['data']);
		$this->assign('rs',$rs);
		$this->display('index');
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{

		//查现在所有的校区
		$school = M('school_address');
		$school = $school->select();

		$this->assign('school',$school);
		$this->display('create');
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$user = M('user');

		$res = $user->where('username='.$_POST['username'])->count();
		if($res) {
			$this->error('用户名已存在,请更换用户名!');
		}

		if($_POST['password']!=$_POST['repassword']) {
			$this->error('密码与确认密码不相等!');
		}

		$data_user = array(
			'username' 		=> $_POST['username'],
			'password' 		=> self_md5($_POST['password']),
			'nickname' 		=> $_POST['nickname'],
			'parents_name' 	=> '',
			'sex' 			=> 1,
			'tel' 			=> $_POST['tel'],
			'role' 			=> 2,
			'photo' 		=> '',
			'type' 			=> $_POST['type'],
			'desc' 			=> $_POST['desc'],
			'coming_data' 	=> time(),
			'coming_style' 	=> '',
			'im_username' 	=> '',
			'school_address_id' => $_POST['school_address_id'],
			);

		$user_id = $user->add($data_user);


		D('Company/User')->im_register_for_user($user_id);


		$this->success('成功',U('Admin/Teacher/index'));

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show()
	{
		$this->display('show');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit()
	{
		$id = $_GET['user_id'];
		$model = M('user');
		$data = $model->find($id);
		//查现在所有的校区
		$school = M('school_address');
		$school = $school->select();
		$this->assign('school',$school);

		$this->assign('data',$data);
		$this->display('edit');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update()
	{
		if($_POST['password']!=$_POST['repassword']) {
			$this->error('密码与确认密码不相等!');
		}

		$data_user = array(
			'password' 		=> $_POST['password'],
			'nickname' 		=> $_POST['nickname'],
			'tel' 			=> $_POST['tel'],
			'type' 			=> $_POST['type'],
			'desc' 			=> $_POST['desc'],
			'school_address_id' => $_POST['school_address_id'],
			);

		$user = M('user');
		$res = $user->where('uid='.$_POST['user_id'])->save($data_user);
		D('Company/User')->im_register_for_user($_POST['user_id']);

		$this->success('成功',U('Admin/Teacher/index'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		echo '没有删除功能';
	}

}