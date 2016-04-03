<?php

class Class_spaceAction extends Action{

    public function ln_get_class($data){

        D('User')->check_token($data['uid'],$data['token']);

        $rs = D('Class_space')->get_class_by_uid($data['uid']);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_get_class_space_list($data){
        D('User')->check_token($data['uid'],$data['token']);
        check_null(40018,true,$data['class_id']);
        $rs = D('Class_space')->get_class_space_list($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_brings_flower($data){
        D('User')->check_token($data['uid'],$data['token']);
        check_null(40020,true,$data['class_space_id']);
        if(D('Class_space')->check_my_flower($data['class_space_id'],$data['uid'])){
            return_json(40019);
        }
        $rs = D('Class_space')->brings_flower($data);
        if($rs)
            return_json(0);
        else
            return_json(-1);
    }

    public function ln_create_class_space($data){
        D('User')->check_token($data['uid'],$data['token']);

        check_null(40018,true,$data['class_id']);
        $data['type'] = $data['type'] == null ? 1 : $data['type'];
        $data['time_insert'] = time();
        $data['audio_id'] != '' && $data['audio_id']=StrCode($data['audio_id'],'DECODE');

        if($_FILES['class_space_attr']['size']>0)
            $data['attr'] = $this->upload_sd_attachment();

        $rs = D('Class_space')->create_class_space($data);
        if($rs)
            return_json(0);
        else
            return_json(-1);
    }

    public function ln_reply_class_space($data){

        D('User')->check_token($data['uid'],$data['token']);

        check_null(40020,true,$data['class_space_id']);

        $data['content'] = $data['reply_content'];
        $data['time_insert'] = time();

        $data['pid'] = $data['reply_id'] != '' ? $data['reply_id'] : 0;

        if($data['reply_id'] != ''){
            $data['pid'] = $data['reply_id'];
            $data['reply_for_uid'] = D('Class_space')->get_uid_by_reply_id($data['reply_id']);
        }

        $rs = D('Class_space')->reply_class_space($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(-1);
    }

    public function ln_get_class_space_reply($data){

        D('User')->check_token($data['uid'],$data['token']);

        check_null(40020,true,$data['class_space_id']);

        $rs = D('Class_space')->get_class_space_reply($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(-1);
    }

    public function ln_get_class_space_student($data){

        D('User')->check_token($data['uid'],$data['token']);

        check_null(40018,true,$data['class_id']);

        $rs = D('Class_space')->get_class_space_student($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(-1);
    }

    public function ln_get_student_homework_audio($data){

        D('User')->check_token($data['uid'],$data['token']);
        check_null(40018,true,$data['student_id']);
        check_null(40018,true,$data['class_id']);
        $rs = D('Class_space')->get_student_homework_audio(StrCode($data['student_id'],'DECODE'),$data['class_id']);
        if($rs)
            return_json(0,$rs);
        else
            return_json(-1);
    }

//-----------------------------------other-----------------------------------------
    public function upload_sd_attachment(){
        $path = 'Public/Uploads'.'/';
        if(!is_dir($path))mkdir($path,0777,true);
        $path.= 'Class_space'.'/';
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
        $filepath = $zip_path.$arr[0];
        if(!is_dir($filepath))mkdir($filepath,0777,true);
        $zip->extractTo($filepath);
        $handle=opendir($filepath);
        while (false !== ($file = readdir($handle))){
            if ($file != "." && $file != "..") {
                $array_file[] = $filepath.'/'.$file;
            }
        }
        $zip ->close();
        unlink($zip_path.$zip_name);
        return $array_file;
    }
}