<?php

class UserModel extends Model{

//----------------------------------init---------------------------------------------
    public function get_user($username='',$uid='',$field=''){
        $map = [];
        if($username != ''){
            $map['username'] = $username;
        }else if($uid!=''){
            $map['uid'] = $uid;
        }
        if($field == '')
            return M('user')->where($map)->find();
        else
            return M('user')->where($map)->field($field)->find();
    }

//---------------------------------insert-----------------------------------------

    public function create_user($data){
        $rs = M('user')->add($data);
        return $rs;
    }

    public function create_verify($username){
        $data = [
            'username'   =>$username,
            'verify'     =>''.rand(1,9).rand(1,9).rand(1,9).rand(1,9),
            'time_insert'=>time()
        ];
        $this->save_verify($data);
        return $data['verify'];
    }

    public function save_verify($data){

        $this->del_verify_by_username($data['username']);

        return M('verify')->add($data);
    }


//---------------------------------delete---------------------------------------
    public function del_verify_by_username($username){
        $map['username'] = $username;
        return M('verify')->where($map)->delete();
    }

    public function del_old_photo_by_uid($uid){
        unlink($this->get_user('',$uid,'photo')['photo']);
    }
//---------------------------------update---------------------------------------
    public function upt_password($username,$password){
        $map['username'] = $username;
        $data['password']= $password;
        return M('user')->where($map)->save($data);
    }

    public function upt_user_info($data){

        $data['photo'] != '' && $this->del_old_photo_by_uid($data['uid']);
        //$data['birthday'] != '' && $data['birthday'] = strtotime($data['birthday']);
        return M('user')->where(['uid' => $data['uid']])->save($data);
    }

    public function upt_user_time_update($uid){
        return M('class_user')->where(['uid'=>$uid])->save(['time_update'=>time()]);
    }

    public function save_im_username($uid,$username){
        $im_username = self_md5($username);
        A('Company/IM')->registerToken($im_username,$im_username);
        M('user')->where(['uid' => $uid])->save(['im_username'=>$im_username]);
    }

//---------------------------------select---------------------------------------


    public function get_user_by_uid_array($uid_array,$field = ''){
        $map['uid'] = ['in',implode(',',$uid_array)];
        if($field == '')
            return M('user')->where($map)->select();
        else
            return M('user')->where($map)->field($field)->select();
    }

    public function get_teacher_by_uid($uid){
        $teacher_filed    = 'uid as teacher_id,nickname,photo,type,desc';
        $rs               = D('User')->get_user('',$uid,$teacher_filed);
        $rs['photo'] != '' && $rs['photo'] = C('PUBLIC_URL').$rs['photo'];
        $rs['teacher_id'] = StrCode($rs['teacher_id']);
        return $rs;
    }

    public function get_uid_by_class_id($data){

        $rs = M('class_user')
            ->where(['class_id'=>$data['class_id']])
            ->field('user_id')
            ->select();

        return i_array_column($rs,'user_id');
    }

    //获取用户信息 by class_id
    public function get_classmate_by_class_id($class_id,$field=''){
        $all        = $this->get_uid_by_class_id(['class_id'=>$class_id]);
        $users      = $this->get_user_by_uid_array($all,$field);
        return $users;
    }

    public function get_verify($username){
        $map['username'] = $username;
        return M('verify')->where($map)->find();
    }

    public function check_token($uid,$token){

        if($uid == null)return_json(40013);
        if($token == null)return_json(40012);

        $field    = 'username,password';
        $rs       = $this->get_user('',$uid,$field);
        $ns_token = self_md5($uid.$rs['username'].$rs['password']);
        if($token != $ns_token){
            return_json(40012);
        }
    }

    public function check_teacher($uid){
        if($uid == null)return_json(40013);

        $field    = 'role';
        $rs       = $this->get_user('',$uid,$field);
        if($rs['role'] != 2){
            return_json(40015);
        }
    }

    public function get_teacher_detail($data){
        return M('teacher_class_detail')->where(['uid'=>$data['uid']])->find();
    }


//-----------------------------------------sync--------------------------------------
    public function sync_class_by_course_id($last,$class_arr){
        $table = 'class';
        $fields= 'id,class_num';
        $field = 'id';
        $where = ['id'=>['in',implode(',',$class_arr)]];
        return D('Sync')->get_database_synchronization($table,$fields,$field,$last,$where);
    }

    public function sync_student_by_course_id($last,$class_arr){

        $course_user = $this->sync_course_user_by_course_id($class_arr,$last);
        $arr         = $this->get_user_ids_by_data($course_user);

        $re = [
            'insert'=>$arr['insert']==[] ? null : $this->format_user_data($arr['insert']),
            'update'=>$arr['update']==[] ? null : $this->format_user_data($arr['update']),
        ];

        if($arr['delete'] != null){
            foreach($arr['delete'] as &$delete_val){
                $delete_val['uid'] = StrCode($delete_val['student_id']);
                unset($delete_val['student_id']);
            }
            $re['delete'] = $arr['delete'];
        }else{
            $re['delete'] = null;
        }
        return $re;
    }

    public function sync_course_user_by_course_id($class_arr,$last){
        $table = 'class_user';
        $fields= 'user_id';
        $field = 'user_id';
        $where = ['class_id'=>['in',implode(',',$class_arr)]];
        return D('Sync')->get_database_synchronization($table,$fields,$field,$last,$where);
    }

    public function sync_course_teacher_by_course_id($course_id,$last){
        $table = 'class_user';
        $fields= 'user_id as teacher_id';
        $field = 'user_id as teacher_id';
        $where = ['course_id'=>$course_id,'role'=>2];
        return D('Sync')->get_database_synchronization($table,$fields,$field,$last,$where);

    }

    public function format_user_data($user_arr){
        $user_field = '`uid`, `nickname`, `parents_name`, `tel`, `role`, `photo`, `type`,`im_username`';
        $user = $this->get_user_by_uid_array($user_arr,$user_field);
        foreach($user as &$val){
            $val['photo']  != '' && $val['photo'] = C('DOMAIN_NAME').__ROOT__.'/'.$val['photo'];
            $val['uid']     = StrCode($val['uid']);
            $val['im_name'] = $val['im_username'];
        }
        return $user;
    }

    public function get_user_ids_by_data($rs){
        $insert = [];
        $update = [];
        $delete = [];

        if($rs['insert'] != null){
            $insert = array_merge($insert,i_array_column($rs['insert'],'user_id'));
        }

        if($rs['update'] != null){
            $update = array_merge($update,i_array_column($rs['update'],'user_id'));
        }

        if($rs['delete'] != null){
            $delete = array_merge($delete,i_array_column($rs['delete'],'user_id'));
        }
        return [
            'insert' => array_unique($insert),
            'update' => array_unique($update),
            'delete' => array_unique($delete)
        ];
    }

    public function sync_class_student_rds_by_course_id($last,$class_arr){
        $table = 'class_user';
        $fields= 'id,class_id,user_id as student_id';
        $field = 'id';
        $where = ['class_id'=>['in',implode(',',$class_arr)],'role'=>1];
        $rs = D('Sync')->get_database_synchronization($table,$fields,$field,$last,$where);

        foreach($rs['insert'] as &$val_insert){
            $val_insert['student_id'] = StrCode($val_insert['student_id']);
        }

        foreach($rs['update'] as &$val_update){
            $val_update['student_id'] = StrCode($val_update['student_id']);
        }

        return $rs;
    }

    public function sync_class_teacher_rds_by_course_id($last,$class_arr){
        $table = 'class_user';
        $fields= 'id,class_id,user_id as teacher_id';
        $field = 'id';
        $where = ['class_id'=>['in',implode(',',$class_arr)],'role'=>2];
        $rs = D('Sync')->get_database_synchronization($table,$fields,$field,$last,$where);
        foreach($rs['insert'] as &$val_insert){
            $val_insert['teacher_id'] = StrCode($val_insert['teacher_id']);
        }
        foreach($rs['update'] as &$val_update){
            $val_update['teacher_id'] = StrCode($val_update['teacher_id']);
        }
        return $rs;
    }

    public function im_register_for_user($uid){
        $rs = M('user')->where(['uid'=>$uid])->select();
        foreach($rs as $val){
            A('Company/IM')->registerToken(self_md5($val['username']),self_md5($val['username']));
            M('user')->where([
                'uid'=>$val['uid']
            ])->save([
                'im_username'=>self_md5($val['username'])
            ]);
        }
        return $rs;
    }
//获取我的积分和优惠劵
    public function get_my_integration_num($uid){
        $map = [
            'uid' => $uid
        ];
        $rs = M('user_integration')
            ->where($map)
            ->field('integration')
            ->select();
        return ''.(int)array_sum(i_array_column($rs,'integration'));
    }

    public function get_my_coupon_num($uid){
        $map = [
            'uid' => $uid
        ];
        return M('user_coupon')->where($map)->count();
    }

    public function get_all_student_uid(){
        $map = [
            'role'=>1
        ];
        $rs = M('class_user')->field('distinct user_id')->where($map)->select();
        return i_array_column($rs,'user_id');
    }

}