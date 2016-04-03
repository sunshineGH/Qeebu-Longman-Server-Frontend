<?php

class HomeworkAction extends Action{

//---------------------------------insert-----------------------------------------------
    //布置趣味性作业
    public function ln_create_class_homework($data){

        D('User')->check_token($data['uid'],$data['token']);

        check_null(40018,true,$data['class_id']);
        $data['type'] = $data['type'] == null ? 1 : $data['type'];
        $data['time_insert'] = time();
        $data['homework_date'] = date('Y-m-d');
        if($_FILES['class_homework_attr']['size']>0)
            $data['attr'] = D('Files')->upload_sd_attachment('Homework');

        $rs = D('Homework')->create_class_homework($data);
        if($rs)
            return_json(0);
        else
            return_json(40001);
    }

    //提交作业
    public function ln_submit_class_homework($data){

        D('User')->check_token($data['uid'],$data['token']);

        check_null(40018,true,$data['class_id']);
        check_null(40023,true,$data['class_homework_id']);

        $data['time_insert'] = time();
        if($_FILES['class_homework_attr']['size']>0)
            $data['attr'] = D('Files')->upload_sd_attachment('Homework');

        $rs = D('Homework')->submit_class_homework($data);
        if($rs)
            return_json(0);
        else
            return_json(40001);
    }
//---------------------------------delete-----------------------------------------------
//---------------------------------update-----------------------------------------------
//---------------------------------select-----------------------------------------------
    //获取班级
    public function ln_get_class($data){

        D('User')->check_token($data['uid'],$data['token']);

        $rs = D('Class_space')->get_class_by_uid($data['uid']);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }
    //获取当前课后作业
    public function ln_get_class_homework_list($data){

        D('User')->check_token($data['uid'],$data['token']);

        check_null(40017,true,$data['timestamp']);
        $data['homework_date'] = date('Y-m-d',$data['timestamp']);

        $rs = D('Homework')->get_homework_list($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }
    //获取课后作业
    public function ln_get_homework($data){

        D('User')->check_token($data['uid'],$data['token']);

        $data['type'] = $data['type'] == ''? 1 : $data['type'];

        $rs   = D('Homework')->get_homework_by_type($data);

        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }
    //获取课后作业详情
    public function ln_get_class_homework_detail($data){

        D('User')->check_token($data['uid'],$data['token']);

        check_null(40023,true,$data['class_homework_id']);

        $rs = D('Homework')->get_class_homework_detail($data);

        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }
//-----------------------------------other-----------------------------------------
    public function upload_sd_attachment(){
        $path = 'Public/Uploads'.'/';
        if(!is_dir($path))mkdir($path,0777,true);
        $path = date('Y-m-d').'/';
        if(!is_dir($path))mkdir($path,0777,true);
        $path.= 'Homework'.'/';
        if(!is_dir($path))mkdir($path,0777,true);
        import('ORG.Net.UploadFile');
        $config['savePath'] = $path;
        $upload = new UploadFile($config);
        if(!$upload->upload()){
            return [];
        }
        $uploadList = $upload->getUploadFileInfo();
        return $this->get_zip_detail($path,$uploadList[0]['savename']);
    }
    public function get_zip_detail($zip_path,$zip_name){
        $array_file = [];
        $zip = new ZipArchive;
        $zip -> open($zip_path.$zip_name);
        $arr = explode('.',$zip_name);
        $file_path = $zip_path.$arr[0];
        if(!is_dir($file_path))mkdir($file_path,0777,true);
        $zip->extractTo($file_path);
        $handle=opendir($file_path);
        while (false !== ($file = readdir($handle))){
            if ($file != "." && $file != "..") {
                $array_file[] = $file_path.'/'.$file;
            }
        }
        $zip ->close();
        unlink($zip_path.$zip_name);
        return $array_file;
    }

}