<?php


class CourseAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(){
		$class_user = M('class_user');
		$supporting_course = M('supporting_course');
		$rs = pageing('course',6,$_GET['search'],'id desc','1','name');
		foreach($rs['data'] as &$val) {
			//查询人数
			$num = $class_user->where('course_id='.$val['id'].' and time_delete=0')->count();
			$val['num'] = $num;
			//查询配套课程
			$support = $supporting_course->field('name')->where('id='.$val['supporting_course_id'])->find();
			$val['support'] = $support['name'];
		}

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
		$support = M('supporting_course');
		$result = $support->select();

		$this->assign('data',$result);
		$this->display('create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		if(empty($_POST['name'])){
			$this->error('请填写课程名称!');
		}

		//添加线下课程
		$arr = array(
			'name'      	=> $_POST['name'],
			'place' 		=> 0,
			'desc' 			=> $_POST['desc'],
			'level' 		=> $_POST['level'],
			'speaker_level' => $_POST['speaker_level'],
			'grammar_level' => $_POST['grammar_level'],
			'continued_date' 		=> 0,
			'supporting_course_id' 	=> $_POST['support_id'],
			);
		// echo '<pre>';
		// var_dump($_POST);
		// var_dump($arr);
		// exit();
		$course = M('course');
		$course_id = $course->add($arr);

		redirect(U('Admin/Course/index'));

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
		//查线下课程信息
		$id = $_GET['courseid'];
		$course = M('course');
		$data = $course->find($id);

		$this->assign('course',$data);

		//查线下课程所关联的配套课程的信息
		$course = M('course');
		$data = $course->find($data['supporting_course_id']);
		$this->assign('support_course',$data);

		//查现在所有的配套课程,供用户选择
		$support = M('supporting_course');
		$result = $support->select();

		$this->assign('data',$result);
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

		$id = $_POST['courseid'];
		$old_support_id = $_POST['old_support_id'];
		//判断更改后的配套课程是否已经有线下课程与之关联,如果有关联则不允许改
		$course = M('course');
		$res = $course->where('supporting_course_id='.$_POST['support_id'])->count();

		if($res) {
			$this->error('该配套课程已被使用,请换其他课程!');
		}

		//更改course表
		$arr = array(
			'name'      	=> $_POST['name'],
			'place' 		=> 0,
			'desc' 			=> $_POST['desc'],
			'level' 		=> $_POST['level'],
			'speaker_level' => $_POST['speaker_level'],
			'grammar_level' => $_POST['grammar_level'],
			'continued_date' 		=> 0,
			'supporting_course_id' 	=> $_POST['support_id'],
			);

		$res = $course->where('id='.$id)->save($arr);
		if(!$res) {
			echo $course->getLastSql();
			$this->error('更新course表失败');
		}

		//更改unit表,将原来的配套课程的unit改为现在的配套课程的unit
		$unit = M('supporting_course_unit');
		$res = $unit->where('supporting_course_id='.$old_support_id)
		->save(array('supporting_course_id' => $_POST['support_id']));
		if(!$res) {
			echo $unit->getLastSql();
			$this->error('更新unit失败');
		}

		redirect(U('Admin/Course/index'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$user = M('user');
		$user->where('id='.I('get.id'))->delete();
		redirect(U('home/user/index'));

	}

}