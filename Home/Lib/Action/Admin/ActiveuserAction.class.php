<?php


class ActiveuserAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$search   = isset($_GET['search'])   ? $_GET['search']   : '';
		$max_page = isset($_GET['max_page']) ? $_GET['max_page'] : 3;

		$res = pageing('active_user',$max_page,$search,'id desc','1','time_insert');

		$active = M('active');
		$user = M('user');

		foreach($res['data'] as &$val)
		{
			$active_name = $active->field('title')->where('id='.$val['active_id'])->find();
			$val['active_name'] = $active_name['title'];

			$user_name = $user->field('nickname')->where('uid='.$val['user_id'])->find();
			$val['user_name'] = $user_name['nickname'];
		}
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
		if(empty($_POST['title'])) {
			$this->error('标题不能为空!');
		}

		$arr = array(
			'file_id' 		=> 0,
			'title' 		=> $_POST['title'],
			'content' 		=> $_POST['content'],
			'category' 		=> $_POST['category'],
			'reg_time_begin'=> $_POST['reg_time_begin'],
			'reg_time_end' 	=> $_POST['reg_time_end'],
			'max_num' 		=> $_POST['max_num'],
			'price' 		=> $_POST['price'],
			'time_insert' 	=> time(),
			);

		$active = M('active_user');
		$active->add($arr);

		$this->success('成功',U('Admin/Activeuser/index'));

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show()
	{
		$active = M('active_user');
		$result = $active->where('id='.$_GET['id'])->find();

		$this->assign('data',$result);
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
		$active = M('active_user');
		$result = $active->where('id='.$_GET['id'])->find();

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

		if(empty($_POST['title'])) {
			$this->error('标题不能为空!');
		}

		$arr = array(
			'file_id' 		=> 0,
			'title' 		=> $_POST['title'],
			'content' 		=> $_POST['content'],
			'category' 		=> $_POST['category'],
			'reg_time_begin'=> $_POST['reg_time_begin'],
			'reg_time_end' 	=> $_POST['reg_time_end'],
			'max_num' 		=> $_POST['max_num'],
			'price' 		=> $_POST['price'],
			'time_insert' 	=> time(),
			);

		$active = M('active_user');
		$active->where('id='.$_POST['id'])->save($arr);

		$this->success('成功',U('Admin/Activeuser/index'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		$active = M('active_user');
		$active->where('id='.$_GET['id'])->delete();

		$this->success('成功',U('Admin/Activeuser/index'));

	}

}