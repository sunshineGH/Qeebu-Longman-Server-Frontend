<?php


class ClassTimeAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$res = pageing('class_time',3,$_GET['search'],'id desc','1','name');

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
		$model = M('class_time');
		$result = $model->select();

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

		$arr = array(
			'class_id' 			=> $_POST['chclass_idannel_for'],
			'course_id' 		=> $_POST['course_id'],
			'course_detail_id' 	=> $_POST['course_detail_id'],
			'time_start' 		=> $_POST['time_start'],
			'time_end' 			=> $_POST['time_end'],
			'max_num' 			=> $_POST['max_num'],
			);
		$model = M('class_time');
		$model->add($arr);


		redirect(U('Admin/ClassTime/index'));

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
		$id = $_GET['class_time_id'];
		$model = M('class_time');
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
		$arr = array(
			'class_id' 			=> $_POST['chclass_idannel_for'],
			'course_id' 		=> $_POST['course_id'],
			'course_detail_id' 	=> $_POST['course_detail_id'],
			'time_start' 		=> $_POST['time_start'],
			'time_end' 			=> $_POST['time_end'],
			'max_num' 			=> $_POST['max_num'],
			);
		$model = M('class_time');
		$model->add($arr);


		redirect(U('Admin/ClassTime/index'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		$id = $_POST['class_time_id'];
		$model = M('class_time');
		$model->where('id='.$id)->delete();

		redirect(U('Admin/ClassTime/index'));
	}

}