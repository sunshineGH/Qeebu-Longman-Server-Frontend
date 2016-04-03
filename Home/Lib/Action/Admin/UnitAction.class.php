<?php


class UnitAction extends CommonAction {
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(){

		$support_id = $_GET['support_id'];
		$this->assign('support_id',$support_id);

		if(isset($_GET['unit_id'])) {
			$unit_id = $_GET['unit_id'];
		} else {
			$unit_id = 0;
		}
		$this->assign('unit_id',$unit_id);

		if(isset($_GET['unit_theme_id'])) {
			$unit_theme_id = $_GET['unit_theme_id'];
		} else {
			$unit_theme_id = 0;
		}
		$this->assign('unit_theme_id',$unit_theme_id);

		//查询unit
		$unit = M('supporting_course_unit');
		$res = $unit->where('supporting_course_id='.$support_id)->select();
		$this->assign('unit',$res);


		//根据unit和theme的关联表查询theme
		$relation = M('supporting_course_unit_theme');
		$theme = M('supporting_course_theme');

		$relation = $relation->where('unit_id='.$unit_id)->select();

		foreach($relation as &$val) {
			$data = $theme->where('id='.$val['theme_id'])->select();
			$val['theme'] = $data[0]['name'];
			$val['theme_type'] = $data[0]['theme_type'];
			$val['theme_id'] = $data[0]['id'];
		}

		$this->assign('theme',$relation);

		//根据theme和module的关联表查询module
		$relation = M('supporting_course_unit_module');
		$module = M('supporting_course_module');

		$res = $relation->where('unit_theme_id='.$unit_theme_id)->select();
		// echo $relation->getLastSql();

		foreach($res as &$val) {
			$data = $module->where('id='.$val['module_id'])->select();
			$val['module'] = $data[0]['name'];
			$val['module_id'] = $data[0]['id'];
			if($val['exercises_topic_id']!=0){
				$val['title'] = M('exercises_topic')->where(['id'=>$val['exercises_topic_id']])->getField('name');
			}else if($val['file_id']!=0){
				$val['title'] = M('files')->where(['id'=>$val['file_id']])->getField('title');
			}else{
				$val['title'] = M('files')->where(['id'=>$val['topic_zip_file_id']])->getField('title');
			}
		}
		$this->assign('module',$res);
		$this->display('index');
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create_theme()
	{
		$unit_id = $_GET['unit_id'];
		$this->assign('unit_id',$unit_id);

		$support_id = $_GET['support_id'];
		$this->assign('support_id',$support_id);

		//查现有module,供选择使用
		$theme = M('supporting_course_theme');
		$data = $theme->field('id,name')->select();
		$this->assign('theme',$data);

		$this->display();
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function change_name()
	{
		$unit_id = $_GET['unit_id'];
		$support_id = $_GET['support_id'];
		$this->assign('unit_id',$unit_id);
		$this->assign('support_id',$support_id);
		$this->display('change_name');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function store_name()
	{
		$unit_id = $_POST['unit_id'];
		$support_id = $_POST['support_id'];
		$this->assign('unit_id',$unit_id);

		$unit = M('supporting_course_unit');
		$arr = array(
			'name' => $_POST['name'],
			);
		$unit->where('id='.$unit_id)->save($arr);

		redirect(U('Admin/Unit/index',array('support_id'=>$support_id)));
	}
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create_module()
	{
		$unit_id = $_GET['unit_id'];
		$this->assign('unit_id',$unit_id);

		$support_id = $_GET['support_id'];
		$this->assign('support_id',$support_id);

		$unit_theme_id = $_GET['unit_theme_id'];
		$this->assign('unit_theme_id',$unit_theme_id);

		//查现有module,供选择使用
		$module = M('supporting_course_module');
		$data = $module->field('id,name')->select();
		$this->assign('module',$data);


		//查询现有套题,供选择使用
		$exercise = M('exercises_topic');
		$data = $exercise->field('id,name')->select();
		$this->assign('exercise',$data);

		//根据unit_theme_id查出theme_id,在查theme表得到theme_type
		$unit_theme = M('supporting_course_unit_theme');
		$res = $unit_theme->where('id='.$unit_theme_id)->find();

		$theme = M('supporting_course_theme');
		$res = $theme->where('id='.$res['theme_id'])->find();

		switch($res['theme_type']) {
			case 0:
				$this->display('create_module');
				break;
			case 1:
				$this->display('create_module_video');
				break;
			case 2:
				$this->display('create_module_html');
				break;
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create_module_html()
	{
		$unit_id = $_GET['unit_id'];
		$this->assign('unit_id',$unit_id);

		$support_id = $_GET['support_id'];
		$this->assign('support_id',$support_id);

		$unit_theme_id = $_GET['unit_theme_id'];
		$this->assign('unit_theme_id',$unit_theme_id);

		//查现有module,供选择使用
		$module = M('supporting_course_module');
		$data = $module->field('id,name')->select();
		$this->assign('module',$data);

		$this->display();
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create_module_video()
	{
		$unit_id = $_GET['unit_id'];
		$this->assign('unit_id',$unit_id);

		$support_id = $_GET['support_id'];
		$this->assign('support_id',$support_id);

		$unit_theme_id = $_GET['unit_theme_id'];
		$this->assign('unit_theme_id',$unit_theme_id);

		//查现有module,供选择使用
		$module = M('supporting_course_module');
		$data = $module->field('id,name')->select();
		$this->assign('module',$data);

		$this->display();
	}
	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store_theme()
	{
		// //创建theme与unit的关联
		$unit_theme = M('supporting_course_unit_theme');
		$arr = array(
			'unit_id' => $_POST['unit_id'],
			'theme_id' => $_POST['theme_id'],
			);
		echo '<pre>';
		var_dump($arr);
		$unit_theme->add($arr);

		redirect(U('Admin/Unit/index',array('support_id'=>$_POST['support_id'],'unit_id'=>$_POST['unit_id'])));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store_module()
	{
		//根据unit_theme_id查询theme_id
		$theme = M('supporting_course_unit_theme');
		$res = $theme->field('theme_id')->where('id='.$_POST['unit_theme_id'])->find();
		$theme_id = $res['theme_id'];
		//创建theme与unit_theme关联表的关联的关联
		$unit_theme = M('supporting_course_unit_module');
		$arr = array(
			'unit_id' => $_POST['unit_id'],
			'theme_id' => $theme_id,
			'module_id' => $_POST['module_id'],
			'unit_theme_id' => $_POST['unit_theme_id'],
			'exercises_topic_id' => $_POST['topic_id'],
			'topci_type' => 1,
			);
		// echo '<pre>';
		// var_dump($arr);
		// exit();
		$unit_theme->add($arr);

		redirect(U('Admin/Unit/index',array('support_id'=>$_POST['support_id'],'unit_id'=>$_POST['unit_id'],'unit_theme_id'=>$_POST['unit_theme_id'])));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store_module_html()
	{
		//根据unit_theme_id查询theme_id
		$theme = M('supporting_course_unit_theme');
		$res = $theme->field('theme_id')->where('id='.$_POST['unit_theme_id'])->find();
		$theme_id = $res['theme_id'];
		//创建theme与unit_theme关联表的关联的关联
		$file_id = D('Admin/File')->uploadfile();
		if(!$file_id) {
			$this->error('文件上传失败!!');
		}
		$unit_theme = M('supporting_course_unit_module');
		$arr = array(
			'unit_id' => $_POST['unit_id'],
			'theme_id' => $theme_id,
			'module_id' => $_POST['module_id'],
			'unit_theme_id' => $_POST['unit_theme_id'],
			'exercises_topic_id' => 0,
			'topic_type' => 2,
			'topic_zip_file_id' => $file_id,
			);
		// echo '<pre>';
		// var_dump($arr);
		// exit();
		$unit_theme->add($arr);

		redirect(U('Admin/Unit/index',array('support_id'=>$_POST['support_id'],'unit_id'=>$_POST['unit_id'],'unit_theme_id'=>$_POST['unit_theme_id'])));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store_module_video()
	{
		//根据unit_theme_id查询theme_id
		$theme = M('supporting_course_unit_theme');
		$res = $theme->field('theme_id')->where('id='.$_POST['unit_theme_id'])->find();
		$theme_id = $res['theme_id'];
		//创建theme与unit_theme关联表的关联的关联
		$file_id = D('Admin/File')->uploadfile();
		if(!$file_id) {
			$this->error('文件上传失败!!');
		}
		$unit_theme = M('supporting_course_unit_module');
		$arr = array(
			'unit_id' => $_POST['unit_id'],
			'theme_id' => $theme_id,
			'module_id' => $_POST['module_id'],
			'unit_theme_id' => $_POST['unit_theme_id'],
			'exercises_topic_id' => 0,
			'file_id' => $file_id,
			'topic_type' => 2,
			'topic_zip_file_id' => 0,
			);
		// echo '<pre>';
		// var_dump($arr);
		// exit();
		$unit_theme->add($arr);

		redirect(U('Admin/Unit/index',array('support_id'=>$_POST['support_id'],'unit_id'=>$_POST['unit_id'],'unit_theme_id'=>$_POST['unit_theme_id'])));
	}
	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function delete_theme()
	{
		$support_id = $_GET['support_id'];
		$unit_id = $_GET['unit_id'];
		$unit_theme_id = $_GET['unit_theme_id'];
		//删除所对应的模型
		$unit_module = M('supporting_course_unit_module');
		$unit_module->where('unit_theme_id='.$unit_theme_id)->delete();
		// //创建theme与unit的关联
		$unit_theme = M('supporting_course_unit_theme');
		$unit_theme->where('id='.$unit_theme_id)->delete();

		redirect(U('Admin/Unit/index',array('support_id'=>$support_id,'unit_id'=>$unit_id)));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function delete_module()
	{
		$support_id = $_GET['support_id'];
		$unit_id = $_GET['unit_id'];
		$unit_theme_id = $_GET['unit_theme_id'];
		$unit_module_id = $_GET['unit_module_id'];
		// //创建theme与unit的关联
		$unit_module = M('supporting_course_unit_module');
		$unit_module->where('id='.$unit_module_id)->delete();

		redirect(U('Admin/Unit/index',array('support_id'=>$support_id,'unit_id'=>$unit_id,'unit_theme_id'=>$unit_theme_id)));
	}
}