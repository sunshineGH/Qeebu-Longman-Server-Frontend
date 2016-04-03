<?php

class TestAction extends CommonAction{


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
        dump($_FILES);

        //以下为文件写入
        $upload_path = 'Public/Uploads'.'/';
        !is_dir($upload_path) && mkdir($upload_path,0777,true);
        $upload_path.= 'exercise'.'/';
        !is_dir($upload_path) && mkdir($upload_path,0777,true);

        import('ORG.Net.UploadFile');

        $config = [
            'savePath'  => $upload_path,
            'maxSize'   => 31457280000,
        ];

        $upload = new UploadFile($config);
        if(!$upload->upload()){
            echo '文件上传失败<br>';
            echo $upload->getErrorMsg();
            return 0;
        }else{
            echo '文件上传成功<br>';
            $uploadList = $upload->getUploadFileInfo();
            foreach($uploadList as $key=>&$val){

                //写入file表
                $arr = array(
                    'title'     => $val['name'],
                    'path_type' => 1,
                    'file_type' => $val['extension'],
                    'file_size' => $val['size'],
                    'file_path' =>$val['savepath'].$val['savename'],
                    'file_time' => time(),
                    'file_title'=> $val['name'],
                    );

                dump($arr);

                $files = M('files');
                $file_id = $files->add($arr);
                if(!$file_id){
                    echo 'file表插入失败';
                    return 0;
                }
            }
        }


    }
}