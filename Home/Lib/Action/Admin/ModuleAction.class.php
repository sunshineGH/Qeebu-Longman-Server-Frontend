<?php


class ModuleAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

		//班级信息
		$rs = pageing('supporting_course_module',7,$_GET['search'],'id desc','1','name');

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
		//查现有老师
		$teacher = M('user');
		$teacher = $teacher->field('uid,nickname')->where('role=2')->select();
		$this->assign('teacher',$teacher);

		//查现有课程
		$course = M('course');
		$course = $course->field('id,name')->select();
		$this->assign('course',$course);

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
			$this->error('请填写班级名称!');
		}

		// echo '<pre>';
		// var_dump($_POST);
		// var_dump($_FILES);
		// exit();

		$file_id = D('Admin/File')->uploadfile();

		$module = M('supporting_course_module');
		$arr = array(
			'name' => $_POST['name'],
			'image' => $file_id,
			'color' => $_POST['color'],
			);

		$res = $module->add($arr);
		if(!$res) {
			$this->error('添加失败');
		}


		redirect(U('Admin/Module/index'));

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function upload_photo()
	{
		$path = 'http://huiyi.qeebu.cn/longman/';
		$file_id = D('Admin/File')->uploadfile();
		if(!$file_id) {
			$this->error('文件上传失败!!');
		}
		$files = M('files');
		$file_res = $files->where('id='.$file_id)->find();
		$photo = $path.$file_res['file_path'];

		$str = <<<EEE
		<script>
			parent.document.getElementById('file_id').setAttribute('value',"$file_id");
			parent.document.getElementById('photo').setAttribute('src',"$photo");
		</script>
EEE;

			echo $str;
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit()
	{
		$path = 'http://huiyi.qeebu.cn/longman/';
		$id = $_GET['id'];
		$theme = M('supporting_course_module');
		$data = $theme->where('id='.$id)->find();

		$files = M('files');
		$file_res = $files->where('id='.$data['image'])->find();
		$data['photo'] = $path.$file_res['file_path'];

		// echo '<pre>';
		// var_dump($data);
		// exit();

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
			$this->error('请填写班级名称!');
		}
		$module = M('supporting_course_module');
		$arr = array(
			'name' => $_POST['name'],
			'image' => $_POST['photo'],
			'color' => $_POST['color'],
			);

		$res = $module->where('id='.$id)->save($arr);
		if(!$res) {
			$this->error('修改失败');
		}


		redirect(U('Admin/Module/index'));
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
		//查询关联表判断是否有关联,如果有关联不允许删除
		$unit_module = M('supporting_course_unit_module');
		$res = $unit_module->where('module_id='.$id)->count();
		if($res) {
			$this->error('该模块正在被使用,不允许删除');
		}
		//删除
		$module = M('supporting_course_module');
		$module->where('id='.$id)->delete();

		redirect(U('Admin/Module/index'));
	}

}