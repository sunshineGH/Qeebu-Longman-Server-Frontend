<?php


class ClassAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$id = $_GET['courseid'];
		$this->assign('course_id',$id);

		//根据关联表class_user查出这个班的学生,再查出学生信息

		$search   = isset($_GET['search'])   ? $_GET['search']   : '';
		//班级信息
		$res = pageing('class',3,$_GET['search'],'id desc','course_id='.$id.' and time_delete=0','name','courseid='.$id);
		//查询班级的老师
		$class_user = M('class_user');
		$user = M('user');
		foreach($res['data'] as &$val) {
			$zh_name = $user->field('nickname')->where('uid='.$val['zh_teacher'])->find();
			$en_name = $user->field('nickname')->where('uid='.$val['en_teacher'])->find();
			$hm_name = $user->field('nickname')->where('uid='.$val['hm_teacher'])->find();
			$val['zh_name'] = $zh_name['nickname'];
			$val['en_name'] = $en_name['nickname'];
			$val['hm_name'] = $hm_name['nickname'];

			//查询班级的人数
			$num = $class_user->where('class_id='.$val['id'])->count();
			$val['num'] = $num;
		}
		// echo '<pre>';
		// var_dump($res['data']);
		// exit();

		$this->assign('data',$res['data']);
		$this->assign('show',$res['show']);
		$this->display('index');
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$course_id = $_GET['course_id'];
		$this->assign('course_id',$course_id);
		//查现有中教
		$teacher = M('user');
		$zh_teacher = $teacher->field('uid,nickname')->where('role=2 and type=1')->select();
		$this->assign('zh_teacher',$zh_teacher);

		//查现有外教
		$en_teacher = $teacher->field('uid,nickname')->where('role=2 and type=2')->select();
		$this->assign('en_teacher',$en_teacher);

		//查现有班主任
		$hm_teacher = $teacher->field('uid,nickname')->where('role=2 and type=3')->select();
		$this->assign('hm_teacher',$hm_teacher);

		//查现有课程
		$course = M('course');
		$course = $course->field('id,name')->select();
		$this->assign('course',$course);

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
		if(empty($_POST['class_num'])){
			$this->error('请填写班级名称!');
		}

		$arr = array(
			'class_num' 	=> $_POST['class_num'],
			'course_id' 	=> $_POST['course_id'],
			'zh_teacher' 	=> $_POST['zh_teacher'],
			'en_teacher' 	=> $_POST['en_teacher'],
			'hm_teacher' 	=> $_POST['hm_teacher'],
			'time_begin' 	=> strtotime($_POST['time_begin']),
			'time_end' 		=> strtotime($_POST['time_end']),
			'time_insert' 	=> time(),
			'time_update' 	=> time(),
			'time_delete' 	=> 0,
			'max_num' 		=> $_POST['max_num'],
			'school_address_id' => $_POST['school_address_id'],
			);
		$class = M('class');
		$class_id = $class->add($arr);

		//将教师加入班级
		$class_user = M('class_user');
		$zh_teacher = array(
			'class_id' 		=> $class_id,
			'course_id' 	=> $_POST['course_id'],
			'user_id' 		=> $_POST['zh_teacher'],
			'role' 			=> 2,
			'time_insert' 	=> time(),
			'time_update' 	=> time(),
			'time_delete' 	=> 0,
			);
		$en_teacher = array(
			'class_id' 		=> $class_id,
			'course_id' 	=> $_POST['course_id'],
			'user_id' 		=> $_POST['en_teacher'],
			'role' 			=> 2,
			'time_insert' 	=> time(),
			'time_update' 	=> time(),
			'time_delete' 	=> 0,
			);
		$hm_teacher = array(
			'class_id' 		=> $class_id,
			'course_id' 	=> $_POST['course_id'],
			'user_id' 		=> $_POST['hm_teacher'],
			'role' 			=> 2,
			'time_insert' 	=> time(),
			'time_update' 	=> time(),
			'time_delete' 	=> 0,
			);
		$class_user->add($zh_teacher);
		$class_user->add($en_teacher);
		$class_user->add($hm_teacher);

		//根据线下课程的课程安排添加课程的时间表
		$course_id = $_POST['course_id'];
		$course_detail = M('course_detail');
		$class_time = M('class_time');

		$data = $course_detail->where('course_id='.$course_id)->select();
		foreach($data as $val) {
			$arr = array(
				'class_id' 			=> $class_id,
				'course_id' 		=> $course_id,
				'course_detail_id' 	=> $val['id'],
				'time_start' 		=> '',
				'time_end' 			=> '',
				'max_num' 			=> $val['max_num'],
				);
			$class_time->add($arr);
		}


		redirect(U('Admin/Class/index',array('courseid'=>$course_id)));

	}



	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit()
	{
		//查现有中教
		$teacher = M('user');
		$zh_teacher = $teacher->field('uid,nickname')->where('role=2 and type=1')->select();
		$this->assign('zh_teacher',$zh_teacher);

		//查现有外教
		$en_teacher = $teacher->field('uid,nickname')->where('role=2 and type=2')->select();
		$this->assign('en_teacher',$en_teacher);

		//查现有班主任
		$hm_teacher = $teacher->field('uid,nickname')->where('role=2 and type=3')->select();
		$this->assign('hm_teacher',$hm_teacher);


		$course_id = $_GET['course_id'];
		$this->assign('course_id',$course_id);
		//查现有课程
		$course = M('course');
		$course = $course->field('id,name')->select();
		$this->assign('course',$course);

		//查现在所有的校区
		$school = M('school_address');
		$school = $school->select();
		$this->assign('school',$school);

		//查班级信息
		$id = $_GET['id'];
		$class = M('class');
		$data = $class->where('time_delete=0')->find($id);

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
		if(empty($_POST['class_num'])){
			$this->error('请填写班级名称!');
		}
		$course_id = $_POST['course_id'];

		$class = M('class');
		//根据原来的老师ID和班级ID将原来的信息修改
		$class_res = $class->where('id='.$_POST['id'])->find($arr);
		$class_user = M('class_user');
		$zh_teacher = array(
			'user_id' 		=> $_POST['zh_teacher'],
			'time_update' 	=> time(),
			);
		$en_teacher = array(
			'user_id' 		=> $_POST['en_teacher'],
			'time_update' 	=> time(),
			);
		$hm_teacher = array(
			'user_id' 		=> $_POST['hm_teacher'],
			'time_update' 	=> time(),
			);
		$class_user->where('class_id='.$_POST['id'].' and user_id='.$class_res['zh_teacher'])->save($zh_teacher);
		$class_user->where('class_id='.$_POST['id'].' and user_id='.$class_res['en_teacher'])->save($en_teacher);
		$class_user->where('class_id='.$_POST['id'].' and user_id='.$class_res['hm_teacher'])->save($hm_teacher);

		//修改班级信息
		$arr = array(
			'class_num' 	=> $_POST['class_num'],
			'course_id' 	=> $_POST['course_id'],
			'zh_teacher' 	=> $_POST['zh_teacher'],
			'en_teacher' 	=> $_POST['en_teacher'],
			'hm_teacher' 	=> $_POST['hm_teacher'],
			'time_begin' 	=> strtotime($_POST['time_begin']),
			'time_end' 		=> strtotime($_POST['time_end']),
			'time_update' 	=> time(),
			'max_num' 		=> $_POST['max_num'],
			'school_address_id' => $_POST['school_address_id'],
			);
		$class->where('id='.$_POST['id'])->save($arr);

		//更改class_time表里的max_num字段
		$class_time = M('class_time');
		$arr = array(
			'max_num' 		=> $_POST['max_num'],
			);
		$class_time->where('class_id='.$_POST['id'])->save($arr);

		redirect(U('Admin/Class/index',array('courseid'=>$course_id)));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		$course_id = $_GET['course_id'];
		//判断如果该班级有学生则不允许删除
		$id = $_GET['id'];
		$class_user = M('class_user');
		$res = $class_user->where('class_id='.$id.' and role=1 and time_delete=0')->count();
		if($res) {
			$this->error('该班级有学生,不允许删除!');
			exit();
		}

		$arr = array(
			'time_delete' => time(),
			);
		$class_user->where('class_id='.$id)->save($arr);

		$class = M('class');
		$class->where('id='.$id)->save($arr);

		//删除对应的class_time记录
		$class_time = M('class_time');
		$class_time->where('class_id='.$id)->delete();

		$class_time_student = M('class_time_student');
		$class_time_student->where('class_id='.$id)->delete();


		redirect(U('Admin/Class/index',array('courseid'=>$course_id)));
	}

}