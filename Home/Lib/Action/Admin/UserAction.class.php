<?php

class UserAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(){

		$school = M('school_address');
		$class_user = M('class_user');
		$search   = isset($_GET['search'])   ? $_GET['search']   : '';
		$rs = pageing('user',7,$search,'','role=1','nickname');
		foreach($rs['data'] as &$val){
			$val['age'] 		= $val['birthday'];
			$class_user_res = $class_user->where('user_id='.$val['uid'].' and time_delete=0')->field('continue_state')->find();
			$val['continue_state'] 		= $class_user_res['continue_state'];

			//查校区
			$school_res = $school->where('id='.$val['school_address_id'])->field('name')->find();
			$val['school'] 		= $school_res['name'];
		}
		// dump($rs['data']);
		$page   = isset($_GET['page'])   ? $_GET['page']   : '';
		$this->assign('page',$page);

		$this->assign('rs',$rs);
		$this->display();
	}


	/**
	 * 续班操作
	 *
	 * @return Response
	 */
	public function add_continue_status()
	{
		$id = $_GET['user_id'];
		$page   = isset($_GET['page'])   ? $_GET['page']   : '';
		$order   = isset($_GET['order'])   ? $_GET['order']   : '';

		$class_user = M('class_user');
		$res = $class_user->where('user_id='.$id.' and time_delete=0')->count();
		if(!$res){
			$this->error('该用户不在任何班级',U('Admin/User/index',array('page'=>$page,'order'=>$order)));
		}
		$arr = array(
			'continue_state' => 1,
			);
		$res = $class_user->where('user_id='.$id.' and time_delete=0')->save($arr);

		$this->success('成功',U('Admin/User/index',array('page'=>$page,'order'=>$order)));

	}


	/**
	 * 取消续班操作
	 *
	 * @return Response
	 */
	public function delete_continue_status()
	{
		$id = $_GET['user_id'];
		$page   = isset($_GET['page'])   ? $_GET['page']   : '';
		$order   = isset($_GET['order'])   ? $_GET['order']   : '';

		$class_user = M('class_user');
		$arr = array(
			'continue_state' => 0,
			);
		$res = $class_user->where('user_id='.$id.' and time_delete=0')->save($arr);


		$this->success('成功',U('Admin/User/index',array('page'=>$page,'order'=>$order)));

	}




	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$course = M('course');

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
			'parents_name' 	=> $_POST['parents_name'],
			'sex' 			=> 1,
			'tel' 			=> $_POST['tel'],
			'role' 			=> 1,
			'photo' 		=> '',
			'type' 			=> 0,
			'desc' 			=> '',
			'birthday' 		=> strtotime($_POST['birthday']),
			'coming_data' 	=> time(),
			'coming_style' 	=> $_POST['coming_style'],
			'level_test' 	=> $_POST['level_test'],
			'speaking_level'=> $_POST['speaking_level'],
			'grammar_level' => $_POST['grammar_level'],
			'im_username' 	=> '',
			'school_address_id' => $_POST['school_address_id'],
			);

		// echo '<pre>';
		// var_dump($data_user);
		$user_id = $user->add($data_user);


		D('Company/User')->im_register_for_user($user_id);

		//加入class_user表,证明这个班有这个人
		$class_user = M('class_user');
		$data_user_class = array(
			'class_id' 		=> $_POST['class_id'],
			'course_id' 	=> $_POST['course_id'],
			'user_id' 		=> $user_id,
			'role' 			=> $_POST['role'],
			'time_insert' 	=> time(),
			'time_update' 	=> time(),
			'time_delete' 	=> 0,
			);
		$class_user->add($data_user_class);

		//根据课加class_time_student表,查这个班级有多少次课,每次课增加一条class_time_user记录
		$class_time = M('class_time');
		$class_time_user = M('class_time_student');
		$res = $class_time->where('class_id='.$_POST['class_id'])->select();
		// dump($res);

		foreach($res as $val) {
			$arr = array(
				'class_id' 		=> $_POST['class_id'],
				'class_time_id' => $val['id'],
				'user_id' 		=> $user_id,
				'time_start' 	=> $val['time_start'],
				'state' 		=> 1,
				'coming_style' 	=> 0,
				);
			$class_time_user->add($arr);
			// echo $class_time_user->getLastSql();
		}


		$this->success('成功',U('Admin/User/index'));

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
		$course = M('course');


		//查现在所有的校区
		$school = M('school_address');
		$school = $school->select();
		$this->assign('school',$school);
		// echo '<pre>';
		// var_dump($course);
		// exit();
		//查现有老师
		$teacher = M('teacher');
		$teacher = $teacher->where('role=2')->select();
		$this->assign('teacher',$teacher);

		$model = M('user');
		$data = $model->find($id);

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
			'username' 		=> $_POST['username'],
			'password' 		=> $_POST['password'],
			'nickname' 		=> $_POST['nickname'],
			'parents_name' 	=> $_POST['parents_name'],
			'sex' 			=> 1,
			'tel' 			=> $_POST['tel'],
			'role' 			=> 1,
			'photo' 		=> '',
			'type' 			=> 0,
			'desc' 			=> '',
			'birthday' 		=> strtotime($_POST['birthday']),
			'coming_style' 	=> $_POST['coming_style'],
			'level_test' 	=> $_POST['level_test'],
			'speaking_level'=> $_POST['speaking_level'],
			'grammar_level' => $_POST['grammar_level'],
			'school_address_id' => $_POST['school_address_id'],
			);

		$user = M('user');
		$user->where('uid='.$_POST['user_id'])->save($data_user);
		D('Company/User')->im_register_for_user($_POST['user_id']);

		$class_user = M('class_user');
		$arr = array(
			'time_delete' => time(),
			);
		$class_user->where('user_id='.$_POST['user_id'])->save($arr);

		$this->success('成功',U('Admin/User/index'));
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