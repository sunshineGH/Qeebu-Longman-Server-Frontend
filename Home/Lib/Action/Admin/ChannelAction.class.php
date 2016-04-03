<?php


class ChannelAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$rs = pageing('library_channel',7,$_GET['search'],'id desc','1','name');
		$rs['data'] = array_map(function($val){
			switch($val['channel_for']){
				case 0:$val['channel_for'] = '资料';break;
				case 1:$val['channel_for'] = '图书馆';break;
			}
			return $val;
		},$rs['data']);
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
		$channel = M('channel');
		$result = $channel->select();

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
			$this->error('请填写频道名称!');
		}

		$arr = array(
			'name'      	=> $_POST['name'],
			'channel_for' 	=> $_POST['channel_for'],
			'channel_sort' 	=> 0,
			'time_insert' 	=> time(),
			'time_update' 	=> time(),
			'time_delete' 	=> time(),
			);
		$channel = M('library_channel');
		$channel->add($arr);


		redirect(U('Admin/Channel/index'));

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
		$id = $_GET['channelid'];
		$channel = M('library_channel');
		$data = $channel->find($id);

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
			$this->error('请填写频道名称!');
		}

		$arr = array(
			'name'      	=> $_POST['name'],
			'channel_for' 	=> $_POST['channel_for'],
			'channel_sort' 	=> 0,
			'time_insert' 	=> time(),
			'time_update' 	=> time(),
			);
		$channel = M('library_channel');
		$channel->where('id='.$_POST['id'])->save($arr);


		redirect(U('Admin/Channel/index'));
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
		//判断该频道下如果没有资料才允许删除
		$library = M('library');
		$res = $library->where('channel_id='.$id)->count();
		if($res) {
			$this->error('该频道下有资料,不允许删除');
		}
		$channel = M('library_channel');
		$channel->where('id='.$id)->delete();

		redirect(U('Admin/Channel/index'));
	}

}