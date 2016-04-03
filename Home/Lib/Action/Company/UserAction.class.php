<?php

class UserAction extends Action{

    protected $upt_data;

//------------------------------------------insert-------------------------------------
    public function ln_register($data){

        $reg_data = [
            'username'=>check_null(40002,true,$data['username']),
            'password'=>self_md5(check_null(40003,true,$data['password'])),
            'nickname'=>check_null(40007,true,$data['nickname']),
            'role'    =>1,
        ];
        $reg_data['tel'] = $reg_data['username'];

        $user = D('User')->get_user($reg_data['username']);
        if($user)return_json(40006);

        $rs = D('User')->create_user($reg_data);
        if(!$rs)return_json(-1);

        D('User')->im_register_for_user($rs);
        $user = D('User')->get_user($reg_data['username']);
        $user['token'] = self_md5($user['uid'].$user['username'].$user['password']);

        unset($user['username']);
        unset($user['password']);
        return_json(0,$user);
    }
//------------------------------------------delete-------------------------------------
//------------------------------------------update-------------------------------------
    public function ln_upt_password($data){

        $username = check_null(40002,true,$data['username']);
        $password = self_md5(check_null(40003,true,$data['password']));

        $user = D('User')->get_user($username);
        if(!$user)return_json(40004);

        D('User')->upt_password($username,$password);
        return_json(0);
    }

    public function ln_upt_user_info($data){

        D('User')->check_token($data['uid'],$data['token']);

        unset($data['password']);
        unset($data['role']);
        unset($data['username']);

        $_FILES['photo']['name']!='' && $data['photo'] = $this->image_upload();

        $rs = D('User')->upt_user_info($data);
        if($rs){
            unset($data['uid']);
            unset($data['token']);
            $data['photo']!=''&& $data['photo'] = C('PUBLIC_URL').$data['photo'];
            $re = end($data);
            D('Company/User')->upt_user_time_update($data['uid']);
            return_json(0,$re);
        }else
            return_json(-1);
    }
//------------------------------------------select-------------------------------------
    public function ln_login($data){

        $username = check_null(40002,true,$data['username']);
        $password = self_md5(check_null(40003,true,$data['password']));

        $user = D('User')->get_user($username);

        if(!$user)return_json(40004);

        if($user['password'] != $password)return_json(40005);
        $user['token'] = self_md5($user['uid'].$user['username'].$user['password']);
        $user['photo']!= ''&& $user['photo'] = C('DOMAIN_NAME').__ROOT__.'/'.$user['photo'];

        $user['integration_num']  = D('User')->get_my_integration_num($user['uid']);
        $user['coupon_num']       = D('User')->get_my_coupon_num($user['uid']);
        $user['last_sign_in_date']= $user['last_sign_in_date'] == '0000-00-00' ? '0' : strtotime($user['last_sign_in_date']);
        unset($user['username']);
        unset($user['password']);
        return_json(0,$user);
    }

    public function ln_login_out($data){

        A('Company/Push_notification')->ln_delete_device_token($data);

        return_json(0);

    }

    public function ln_get_verify($data){

        $message = '您好,欢迎您注册乐宁教育APP,您的验证码为: ';
        $username = check_null(40002,true,$data['username']);

        preg_match('/^[0-9]{11}$/',$username,$matches);
        if($matches==array() || count($matches)==0)return_json(40011);

        if($data['check_in'] == 1){
            $user = D('User')->get_user($username);
            if($user)return_json(40006);
        }else if($data['check_in'] == 2){
            $user = D('User')->get_user($username);
            if(!$user)return_json(40004);
        }

        $verify = D('User')->create_verify($username);
        if(for_sms($username,$message.$verify)){
            return_json(0);
        }else{
            return_json(40008);
        }
    }

    public function ln_check_verify($data){

        $username = check_null(40002,true,$data['username']);
        $verify   = check_null(40009,true,$data['verify']);

        $rs = D('User')->get_verify($username);
        if($verify == $rs['verify']){
            D('User')->del_verify_by_username($username);
            return_json(0);
        }else{
            return_json(40010);
        }
    }

    public function ln_sync_user($data){

        D('User')->check_token($data['uid'],$data['token']);
        $last = check_null(40017,true,$data['timestamp']);

        $class_arr = D('Classes')->get_class_id_by_user_id($data['uid']);

        $rs['classes']             = D('User')->sync_class_by_course_id($last,$class_arr);
        $rs['student']             = D('User')->sync_student_by_course_id($last,$class_arr);

        $rs['classes_student_rds'] = D('User')->sync_class_student_rds_by_course_id($last,$class_arr);
        $rs['classes_teacher_rds'] = D('User')->sync_class_teacher_rds_by_course_id($last,$class_arr);

        $rs['timestamp']           = time();
        return_json(0,$rs,time());
    }

    public function ln_get_my_integration_and_coupon($data){

        D('User')->check_token($data['uid'],$data['token']);

        $rs = D('User')->get_my_integration_and_coupon($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_get_teacher_detail($data){
        D('User')->check_token($data['uid'],$data['token']);
        $rs = D('User')->get_teacher_detail($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

//---------------------------------Other Tools---------------------------------------
    public function image_upload(){

        $upload_path = 'Public/Uploads'.'/';
        !is_dir($upload_path) && mkdir($upload_path,0777,true);
        $upload_path.= 'photo'.'/';
        !is_dir($upload_path) && mkdir($upload_path,0777,true);

        import('ORG.Net.UploadFile');

        $config = [
            'savePath' 	=> $upload_path,
            'maxSize'  	=> 3145728,
            'allowExts'	=> ['jpg', 'gif', 'png', 'jpeg']
        ];

        $upload = new UploadFile($config);
        if(!$upload->upload()){
            return '';
        }else{
            $uploadList = $upload->getUploadFileInfo();
            return $config['savePath'].$uploadList[0]['savename'];
        }
    }

//---------------------------------IM 即时通讯-------------------------------------------

    public function im_register_for_user(){
        $rs = M('user')->where(['uid'=>$_GET['uid']])->select();
        foreach($rs as $val){
            A('Company/IM')->registerToken(self_md5($val['username']),self_md5($val['username']));
            M('user')->where([
                'uid'=>$val['uid']
            ])->save([
                'im_username'=>self_md5($val['username'])
            ]);
        }
    }

    public function im_register(){
        $rs = M('user')->select();
        foreach($rs as $val){
            A('Company/IM')->registerToken(self_md5($val['username']),self_md5($val['username']));
            M('user')->where([
                'uid'=>$val['uid']
            ])->save([
                'im_username'=>self_md5($val['username'])
            ]);
        }
    }
}