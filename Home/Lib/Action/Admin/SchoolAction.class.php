<?php


class SchoolAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

		$res = pageing('school_address',3,$_GET['search'],'id desc','1','name');


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
			$this->error('请填写校区名称!');
		}

		$arr = array(
			'name' 	=> $_POST['name'],
			);
		$school = M('school_address');
		$school_id = $school->add($arr);

		redirect(U('Admin/School/index'));

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
		$school = M('school_address');
		$data = $school->find($id);

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
		if(empty($_POST['name'])){
			$this->error('请填写校区名称!');
		}

		$arr = array(
			'name' 	=> $_POST['name'],
			);
		$school = M('school_address');
		$school->where('id='.$_POST['id'])->save($arr);

		redirect(U('Admin/School/index'));
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
		//判断如果该校区有人不允许删除
		$user = M('user');
		$res = $user->where('school_address_id='.$id)->count();
		if($res) {
			$this->error('该班级有用户,不允许删除!');
		}

		//判断如果该校区有班级不允许删除
		$class = M('class');
		$res = $class->where('school_address_id='.$id.' and time_delete=0')->count();
		if($res) {
			$this->error('该班级有班级,不允许删除!');
		}

		//判断如果该校区有活动不允许删除
		$active = M('active');
		$res = $active->where('school_address_id='.$id)->count();
		if($res) {
			$this->error('该班级有活动,不允许删除!');
		}

		//删除对应的记录
		$school_address = M('school_address');
		$school_address->where('id='.$id)->delete();


		redirect(U('Admin/School/index'));
	}

}