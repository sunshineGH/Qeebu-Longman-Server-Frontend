<?php


class ActiveAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(){
		$search   = isset($_GET['search'])   ? $_GET['search']   : '';
		$max_page = isset($_GET['max_page']) ? $_GET['max_page'] : 10;

		$school = M('school_address');
		$res = pageing('active',$max_page,$search,'id desc','1','title');
		$category = [
			1=>'俱乐部',2=>'国际认证考试'
		];

		foreach($res['data'] as &$val){
			$val['reg_time_begin'] = date('Y-m-d H:i',$val['reg_time_begin']);
			$val['reg_time_end']   = date('Y-m-d H:i',$val['reg_time_end']);
			$val['category'] 	   = $category[$val['category']];
			$val['apply_num'] 	   = M('active_user')->where([
				'active_id'=>$val['id'],
				'state'	   =>2
			])->count();
			//查校区
			$school_res = $school->where('id='.$val['school_address_id'])->field('name')->find();
			$val['school'] 		= $school_res['name'];
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
		//查现在所有的校区
		$school = M('school_address');
		$school = $school->select();

		$this->assign('school',$school);
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

		$text = strip_tags($_POST['content']);
        $text = str_replace("&nbsp;","",$text);
        $text = str_replace("&","",$text);
        $text = str_replace("<","",$text);
        $text = str_replace(">","",$text);
        $text = str_replace("\n","",$text);
        $text = str_replace("\r","",$text);
        $text = str_replace("\t","",$text);
        $text = str_replace(" ","",$text);
        $intro = mb_substr($text,0,40,'UTF-8');

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
			'school_address_id' => $_POST['school_address_id'],
			'intro'			=> $intro,
			);

		$active = M('active');
		$active->add($arr);

		$this->success('成功',U('Admin/Active/index'));

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show()
	{
		$active = M('active');
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
		//查现在所有的校区
		$school = M('school_address');
		$school = $school->select();
		$this->assign('school',$school);

		$active = M('active');
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

		$text = strip_tags($_POST['content']);
        $text = str_replace("&nbsp;","",$text);
        $text = str_replace("&","",$text);
        $text = str_replace("<","",$text);
        $text = str_replace(">","",$text);
        $text = str_replace("\n","",$text);
        $text = str_replace("\r","",$text);
        $text = str_replace("\t","",$text);
        $text = str_replace(" ","",$text);
        $intro = mb_substr($text,0,40,'UTF-8');

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
			'school_address_id' => $_POST['school_address_id'],
			'intro'			=> $intro,
			);

		$active = M('active');
		$active->where('id='.$_POST['id'])->save($arr);

		$this->success('成功',U('Admin/Active/index'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		$active = M('active');
		$active->where('id='.$_GET['id'])->delete();

		$this->success('成功',U('Admin/Active/index'));

	}

	public function apple_info(){
		$map['active_id'] = $_GET['id'];
		$rs = M('active_user')->where($map)->select();
		$state = [
			'1'=>'未支付',
			'2'=>'已支付',
			'3'=>'未成功报名'
		];
		foreach($rs as &$val) {

			$active_name = M('active')->field('title')->where('id='.$val['active_id'])->find();
			$val['active_name'] = $active_name['title'];
			$user_name 	 = M('user')->field('nickname')->where('uid='.$val['user_id'])->find();
			$val['user_name'] = $user_name['nickname'];
			$val['state'] = $state[$val['state']];
		}
		$this->assign('data',$rs);
		$this->display();
	}

}