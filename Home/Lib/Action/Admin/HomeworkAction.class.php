<?php


class HomeworkAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(){
		$this->assign('class_id',$_GET['id']);

		$search   = isset($_GET['search'])   ? $_GET['search']   : '';
		$rs = pageing('class_homework',7,$search,'id desc','class_id='.$_GET['id'].' and type=2','nickname','id='.$_GET['id']);

		$this->assign('data',$rs['data']);
		$this->assign('show',$rs['show']);
		$this->display('index');
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$this->assign('class_id',$_GET['id']);
		$this->display('create');
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

		$class_homework = M('class_homework');
		$arr = array(
			'uid' 			=> $_SESSION['ln_user']['uid'],
			'class_id' 		=> $_POST['class_id'],
			'type' 			=> 2,
			'title' 		=> $_POST['title'],
			'content' 		=> $_POST['content'],
			'homework_date' => $_POST['homework_date'],
			'time_insert' 	=> time(),
			);

		$class_homework_id = $class_homework->add($arr);

		$file_id = D('Admin/File')->uploadfile();

		$class_homework_attr = M('class_homework_attr');
		$arr = array(
			'class_homework_id' => $class_homework_id,
			'file_id' 			=> $file_id,
			'audio_title' 		=> $_POST['audio_title'],
			'audio_time' 		=> $_POST['audio_time'],
			);

		$res = $class_homework_attr->add($arr);


		$this->success('成功',U('Admin/Homework/index',array('id'=>$_POST['class_id'])));

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
		$id = $_GET['id'];
		$class_id = $_GET['class_id'];

		//
		$class_homework = M('class_homework');
		$res = $class_homework->where('id='.$id)->delete();

    	$files   = M('files');
		$class_homework_attr = M('class_homework_attr');
		//查library表
		$class_homework_attr_res = $class_homework_attr->where('class_homework_id='.$id)->find();
		//查文件表
		$files_res = $files->where('id='.$class_homework_attr_res['file_id'])->find();
		//删除文件
		unlink($files_res['file_path']);
		// 删除file表
		$files->where('id='.$library_res['file_id'])->delete();
		//删除library表
		$class_homework_attr->where('class_homework_id='.$id)->delete();


		$this->success('成功',U('Admin/Homework/index',array('id'=>$class_id)));
	}

}