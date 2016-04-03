<?php


class LibraryAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(){
		$rs = pageing('library',7,$_GET['search'],'id desc','1','name');
		$channel = M('library_channel');
		foreach($rs['data'] as &$val)
		{
			$channel_name = $channel->field('name')->where('id='.$val['channel_id'])->find();
			$val['channel_name'] = $channel_name['name'];
			$val['time_insert']  = date('Y-m-d H:i',$val['time_insert']);
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
		$course = M('course');
		$course = $course->field('id,name')->select();
		$this->assign('course',$course);

		$channel = M('library_channel');
		$channel = $channel->field('id,name')->select();
		$this->assign('channel',$channel);

		$this->display('create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		if(empty($_POST['title'])){
			$this->error('请填写资料名称!');
		}

		$file_id = D('Admin/File')->uploadfile();
		if(!$file_id) {
			$this->error('文件上传失败!');
		}
		$arr = array(
			'course_id' 		=> $_POST['course_id'],
			'title' 		=> $_POST['title'],
			'desc' 			=> $_POST['desc'],
			'file_id' 		=> $file_id,
			'price' 		=> 0,
			'channel_id' 	=> $_POST['channel_id'],
			'library_for' 	=> time(),
			'time_insert' 	=> time(),
			'time_update' 	=> time(),
			'time_delete' 	=> time(),
			);
		$library = M('library');
		$library->add($arr);


		redirect(U('Admin/Library/index'));

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
    	$files   = M('files');
		$library = M('library');
		//查library表
		$library_res = $library->where('id='.$id)->find();
		//查文件表
		$files_res = $files->where('id='.$library_res['file_id'])->find();
		//删除文件
		unlink($files_res['file_path']);
		// 删除file表
		$files->where('id='.$library_res['file_id'])->delete();
		//删除library表
		$library->where('id='.$id)->delete();

		redirect(U('Admin/Library/index'));
	}

}