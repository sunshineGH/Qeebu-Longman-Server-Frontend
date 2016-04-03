<?php

class SpcourseAction extends CommonAction {
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$rs = pageing('supporting_course',6,$_GET['search'],'id desc','1','name');
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
		$arr = array(
			'name'      	=> $_POST['name'],
			'desc' 			=> $_POST['support_desc'],
			'level' 		=> $_POST['support_level'],
			'star_level'   => 0,
			);

		$course = M('supporting_course');
		$course->add($arr);

		redirect(U('Admin/Spcourse/index'));

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
		$id = $_GET['id'];
		$course = M('supporting_course');
		$data = $course->find($id);

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
		$id = $_POST['id'];
		if(empty($_POST['name'])){
			$this->error('请填写课程名称!');
		}
		$arr = array(
			'name'      	=> $_POST['name'],
			'desc' 			=> $_POST['support_desc'],
			'level' 		=> $_POST['support_level'],
			'star_level'   => 0,
			);

		$course = M('supporting_course');
		$course->where('id='.$id)->save($arr);

		redirect(U('Admin/Spcourse/index'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		$id = $_GET['id'];
		//删除配套课程的时候先查询该课程是否正在被使用,如果正在被使用不允许删除
		$course = M('course');
		$res = $course->where('supporting_course_id='.$id)->count();
		if($res) {
			$this->error('该配套课程正在被使用,不允许删除');
		}
		//删除课程
		$support = M('supporting_course');
		$res = $support->where('id='.$id)->delete();
		if(!$res) {
			$this->error('supporting_course表删除失败');
		}
		redirect(U('Admin/Spcourse/index'));
	}

}