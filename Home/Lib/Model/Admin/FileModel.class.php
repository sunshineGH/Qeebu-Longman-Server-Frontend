<?php
class FileModel extends Model{

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function uploadfile(){
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
		}else{
			echo '文件上传成功<br>';
		    $uploadList = $upload->getUploadFileInfo()[0];

	    	//写入file表
	    	$arr = array(
	    		'title' 	=> $uploadList['name'],
	    		'path_type' => 1,
	    		'file_type' => $uploadList['extension'],
	    		'file_size' => round($uploadList['size']/1048576),
	    		'file_path' => $uploadList['savepath'].$uploadList['savename'],
	    		'file_time' => time(),
	    		'file_title'=> $uploadList['name'],
	    		);
	    	// echo '<pre>';
	    	// var_dump($arr);
	    	// exit();
	    	$files = M('files');
	    	$file_id = $files->add($arr);
	    	if(!$file_id){
	    		echo 'file表插入失败';
		    	return 0;
	    	}
	    }


	    echo '文件上传成功';

	    return $file_id;
	}

}
?>