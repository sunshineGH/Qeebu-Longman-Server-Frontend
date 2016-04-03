<?php


class SatisfactionQuestionAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$res = pageing('satisfaction_investigate_topic',3,$_GET['search'],'id desc','1','name');

		$topic = M('exercises_topic');

		foreach($res['data'] as &$val)
		{
			$topic_name = $topic->field('name')->where('id='.$val['topic_id'])->find();
			$val['topic_name'] = $topic_name['name'];
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
		$topic_id = $_GET['id'];

		$this->assign('topic_id',$topic_id);
		$this->display('create');
	}






	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

		// echo '<pre>';
		// var_dump($_POST);
		// var_dump($_FILES);
		// exit();



		$question 		 = M('satisfaction_investigate_question');
    	$question_opt 	 = M('satisfaction_investigate_question_opt');
		$question_images = M('satisfaction_investigate_question_images');
    	$files 			 = M('files');


		//写question表
		$arr = array(
			'topic_id' 		=> $_POST['topic_id'],
			'title' 		=> $_POST['title'],
			'detail' 		=> $_POST['detail'],
			'question_type' => 0,
			);
		$question_id = $question->add($arr);
		if(!$question_id) {
			$this->error('satisfaction_investigate_topic表插入失败');
		}

		//判断答案是文字还是图片
		if($_POST['type'] == '1'){
			echo '选项为文字<br>';
			//将答案写入question_opt表

			//选项A
    		$arr = array(
	    		'question_id' 	=> $question_id,
	    		'opt' 			=> 'A',
	    		'answer' 		=> $_POST['A'][0],
	    		'file_id' 		=> 0,
    			);
    		$res = $question_opt->add($arr);
    		if(!$res) {
    			$this->error('question_opt表插入失败2');
    		}

    		//选项B
    		$arr = array(
	    		'question_id' 	=> $question_id,
	    		'opt' 			=> 'B',
	    		'answer' 		=> $_POST['B'][0],
	    		'file_id' 		=> 0,
    			);
    		$res = $question_opt->add($arr);
    		if(!$res) {
    			$this->error('question_opt表插入失败2');
    		}

    		if(isset($_POST['C'])) {
	    		//选项C
	    		$arr = array(
		    		'question_id' 	=> $question_id,
		    		'opt' 			=> 'C',
		    		'answer' 		=> $_POST['C'][0],
		    		'file_id' 		=> 0,
	    			);
	    		$res = $question_opt->add($arr);
	    		if(!$res) {
	    			$this->error('question_opt表插入失败2');
	    		}
    		}


    		if(isset($_POST['D'])) {
	    		//选项D
	    		$arr = array(
		    		'question_id' 	=> $question_id,
		    		'opt' 			=> 'D',
		    		'answer' 		=> $_POST['D'][0],
		    		'file_id' 		=> 0,
	    			);
	    		$res = $question_opt->add($arr);
	    		if(!$res) {
	    			$this->error('question_opt表插入失败2');
	    		}
    		}
		}

		echo '文件写入<br>';
		//以下为文件写入
		$upload_path = 'Public/Uploads'.'/';
		!is_dir($upload_path) && mkdir($upload_path,0777,true);
		$upload_path.= 'exercise'.'/';
		!is_dir($upload_path) && mkdir($upload_path,0777,true);

		import('ORG.Net.UploadFile');

		$config = [
		    'savePath' 	=> $upload_path,
		    'maxSize'  	=> 31457280000,
		];

		$upload = new UploadFile($config);
		if(!$upload->upload()){
			echo '文件上传失败<br>';
			echo $upload->getErrorMsg();

		}else{
			echo '文件上传成功<br>';
		    $uploadList = $upload->getUploadFileInfo();

		    $i = 0;
		    foreach($uploadList as $key=>&$val){
		    	//写入file表
		    	$arr = array(
		    		'title' 	=> $val['name'],
		    		'path_type' => 1,
		    		'file_type' => $val['extension'],
		    		'file_size' => $val['size'],
		    		'file_path' => $val['savepath'].$val['savename'],
		    		'file_time' => time(),
		    		'file_title'=> $val['name'],
		    		);
		    	// echo '<pre>';
		    	// var_dump($arr);
		    	// exit();
		    	$file_id = $files->add($arr);
		    	if(!$file_id){
		    		$this->error('file表插入失败');
		    	}
				echo 'file表插入成功<br>';

		    	if($val['key']=='enclosure'){
		    		//关联到question_images表
		    		$arr = array(
			    		'question_id' 	=> $question_id,
			    		'file_id' 		=> $file_id,
		    			);
		    		$res = $question_images->add($arr);
		    		if(!$res) {
		    			$this->error('question_images表插入失败');
		    		}
					echo 'question_images表插入成功';

		    	} else{
		    		//关联到question_opt表
					echo '选项为文件<br>';
		    		$arr = array(
			    		'question_id' 	=> $question_id,
			    		'opt' 			=> $val['key'],
			    		'answer' 		=> '',
			    		'file_id' 		=> $file_id,
			    		'true_answer'   => $true_answer[$i],
		    			);
		    		$res = $question_opt->add($arr);
		    		if(!$res) {
		    			$this->error('question_opt表插入失败2');
		    		}
		    		$i++;
					echo 'question_opt表插入成功<br>';
		    	}
		    }
		}
		echo '成功!!!!!!!!!!!!!<br>';
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
		$question_id = $_GET['id'];
		$this->assign('question_id',$question_id);

		//查询小题内容
		$question = M('satisfaction_investigate_question');
		$question_res = $question->where('id='.$question_id)->find();
		$this->assign('data',$question_res);
		// var_dump($question_res);
		//查文件信息
		$image = M('satisfaction_investigate_question_images');
		$files = M('files');
		$image_res = $image->where('question_id='.$question_id)->select();
		foreach($image_res as &$val) {
			$res = $files->where('id='.$val['file_id'])->find();
			$val['file'] = $res['title'];
		}

		$this->assign('question_images',$image_res);
		// var_dump($image_res);



		$opt = M('satisfaction_investigate_question_opt');
		$files = M('files');
		$opt_res = $opt->where('question_id='.$question_id)->select();
		//判断选项是否为文件
		foreach($opt_res as &$val) {
			if($val['file_id']) {
				$file_res = $files->where('id='.$val['file_id'])->find();
				$val['photo_path'] = $file_res['file_path'];
			}
		}
		// echo '<pre>';
		// var_dump($opt);
		// exit();

		$this->assign('opt',$opt_res);
		// var_dump($opt_res);

		//查询正确答案
		$opt_res = $opt->where('question_id='.$question_id.' and true_answer=1')->find();
		$this->assign('true_answer',$opt_res['opt']);
		// var_dump($opt_res['opt']);


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
		$question_id = $_GET['id'];
		$this->assign('question_id',$question_id);

		//查询小题内容
		$question = M('satisfaction_investigate_topic');
		$question_res = $question->where('id='.$question_id)->find();
		$this->assign('data',$question_res);

		//如果是选择题,查选项表
		$opt = M('satisfaction_investigate_opt');
		$files = M('files');
		$opt_res = $opt->where('question_id='.$question_id)->select();

		// echo '<pre>';
		// var_dump($opt);
		// exit();

		$this->assign('opt',$opt_res);

		//查询正确答案
		$true_answer = $opt->where('question_id='.$question_id.' and true_answer=1')->find();
		$this->assign('true_answer',$true_answer['opt']);

		//判断选项是文字还是图片
		if($opt_res[0]['file_id']) {
			//如果选项是文字
			$this->display('edit_text_opt');

		}

		if($opt_res[0]['file_id']) {
			//如果选项是图片
			//判断选项是否为文件
			// foreach($opt_res as &$val) {
			// 	if($val['file_id']) {
			// 		$file_res = $files->where('id='.$val['file_id'])->find();
			// 		$val['photo_path'] = $file_res['file_path'];
			// 	}
			// }
			$this->display('edit_pic_opt');
		}
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

		$question 		 = M('satisfaction_investigate_question');
    	$question_opt 	 = M('satisfaction_investigate_question_opt');
		$question_images = M('satisfaction_investigate_question_images');
    	$files 			 = M('files');

		//删除question_image表,及其关联的file表和文件
		$question_images_res = $question_images->where('question_id='.$id)->select();

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
		$question_opt_res = $question_opt->where('question_id='.$id)->select();
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
		$res = $question->where('id='.$id)->delete();
		if(!$res) {
			$this->error('question表删除失败');
		}

		redirect(U('Admin/Satisfaction/index'));
	}

}