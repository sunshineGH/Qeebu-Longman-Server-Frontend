<?php


class SatisfactionAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$question = M('satisfaction_investigate_question');
		$res = pageing('satisfaction_investigate_topic',3,$_GET['search'],'id desc','1','name');
		foreach($res['data'] as &$val){
			$res_num = $question->where('topic_id='.$val['id'])->count();
			$val['question_num'] = $res_num;
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
		if(empty($_POST['name'])){
			$this->error('请填写主题名称!');
		}

		$arr = array(
			'name'  => $_POST['name'],
			'desc' 	=> $_POST['desc'],
			'type' 	=> $_POST['type'],
			'end_time' => strtotime($_POST['end_time']),
			);

		$Satisfaction = M('satisfaction_investigate_topic');
		$Satisfaction->add($arr);


		redirect(U('Admin/Satisfaction/index'));

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
		$Satisfaction = M('satisfaction_investigate_topic');
		$data = $Satisfaction->find($id);

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
			$this->error('请填写主题名称!');
		}

		$arr = array(
			'name'  => $_POST['name'],
			'desc' 	=> $_POST['desc'],
			'type' 	=> $_POST['type'],
			'end_time' => strtotime($_POST['end_time']),
			);

		$Satisfaction = M('satisfaction_investigate_topic');
		$Satisfaction->where('id='.$_POST['id'])->save($arr);


		redirect(U('Admin/Satisfaction/index'));
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

		$question 		= M('Satisfaction_investigate_question');
		$question_images= M('Satisfaction_investigate_question_images');
		$question_opt 	= M('Satisfaction_investigate_question_opt');
		$files 			= M('files');
		$Satisfactions 		= M('satisfaction_investigate_topic');

		//根据topic_id查出所有的question
		$res = $question->field('id')->where('topic_id='.$id)->select();
		// echo '<pre>';
		// var_dump($res);
		// exit();
		foreach($res as $val) {
			//删除question_image表,及其关联的file表和文件
			$question_images_res = $question_images->where('question_id='.$val['id'])->select();
			foreach($question_images_res as $image) {
				//删除文件
				$files_res = $files->where('id='.$image['file_id'])->find();
				unlink($files_res['file_path']);
				// 删除file表
				$files->where('id='.$image['file_id'])->delete();
				//删除images表
				$question_images->where('id='.$image['id'])->delete();
			}

			//删除question_opt表,及其关联的file表和文件
			$question_opt_res = $question_opt->where('question_id='.$val['id'])->select();
			foreach($question_opt_res as $opt) {
				//删除文件
				$files_res = $files->where('id='.$opt['file_id'])->find();
				unlink($files_res['file_path']);
				//删除file表
				$files->where('id='.$opt['file_id'])->delete();
				//删除question_opt表
				$question_opt->where('id='.$opt['id'])->delete();
			}

			//删除question表
			$question->where('id='.$val['id'])->delete();
			if(!$res) {
				$this->error('question表删除失败');
			}
		}

		//删除satisfaction_investigate_topic表
		$res = $Satisfactions->where('id='.$id)->delete();
		if(!$res) {
			$this->error('question表删除失败');
		}

		redirect(U('Admin/Satisfaction/index'));
	}

}