<?php

class FilesModel extends Model{

    public function upload_sd_attachment($model){
        $path = 'Public/Uploads'.'/';
        if(!is_dir($path))mkdir($path,0777,true);
        $path.= date('Y-m-d').'/';
        if(!is_dir($path))mkdir($path,0777,true);
        $path.= $model.'/';
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


    public function get_file($id){
        $rs = M('files')->where(['id'=>$id])->find();
        if($rs['file_path']=='')return '';
        if($rs['path_type']==1)
            return C('DOMAIN_NAME').__ROOT__.'/'.$rs['file_path'];
        else if($rs['path_type'] == 2)
            return $rs['file_path'];
        else
            return '';
    }

    public function get_file_detail($id,$filed){
        if($filed == '')
            $rs = M('files')->where(['id'=>$id])->find();
        else
            $rs = M('files')->field($filed)->where(['id'=>$id])->find();
        if($rs['file_path']=='')return false;
        if($rs['path_type']==1)
            $rs['file_path'] = C('PUBLIC_URL').$rs['file_path'];
        unset($rs['path_type']);
        return $rs;
    }

    public function get_file_info($file_path){
        return [
            'type'=>end(explode('.',$file_path)),
            'size'=>round(filesize($file_path)/1048576,2)
        ];
    }

    public function add_file($data){
        return M('files')->add($data);
    }


}