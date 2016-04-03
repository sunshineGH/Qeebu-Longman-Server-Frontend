<?php


class BookAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(){
		$rs = pageing('bookshop',7,$_GET['search'],'id desc','1','name');
		$category = M('bookshop_category');
		foreach($rs['data'] as &$val)
		{
			$category_name = $category->field('name')->where('id='.$val['bookshop_category_id'])->find();
			$val['category_name'] = $category_name['name'];
		}
		$this->assign('rs',$rs);
		$this->display('index');
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
		    return 0;
		}

		$uploadList = $upload->getUploadFileInfo()[0];

		$path = $path.$uploadList['savepath'].$uploadList['savename'];
		$image_path = $uploadList['savepath'].$uploadList['savename'];


		$str = <<<EEE
		<script>
			parent.document.getElementById('image_path').setAttribute('value',"$image_path");
			parent.document.getElementById('photo').setAttribute('src',"$path");
		</script>
EEE;

		echo $str;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$category = M('bookshop_category');
		$category = $category->field('id,name')->select();
		$this->assign('category',$category);

		$this->display('create');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function edit()
	{
		$id = $_GET['id'];
		$path = 'http://huiyi.qeebu.cn/longman/';

		$bookshop = M('bookshop');
		$bookshop = $bookshop->where('id='.$id)->find();
		$bookshop['image_path'] = $path.$bookshop['image_path'];
		// var_dump($bookshop);
		$this->assign('data',$bookshop);

		$category = M('bookshop_category');
		$category = $category->field('id,name')->select();
		$this->assign('category',$category);

		$this->display();
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
			'title' 				=> $_POST['title'],
			'bookshop_category_id' 	=> $_POST['bookshop_category_id'],
			'price' 				=> $_POST['price'],
			'author' 				=> $_POST['author'],
			'desc' 					=> $_POST['desc'],
			'file_id' 				=> $file_id,
			'image_path' 			=> $_POST['image_path'],
			);
		$library = M('bookshop');
		$res = $library->add($arr);
		echo $library->getLastSql();
		if(!$res) {
			$this->error('添加失败');
		}


		redirect(U('Admin/Book/index'));

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
		$library = M('bookshop');
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

		redirect(U('Admin/Book/index'));
	}

}