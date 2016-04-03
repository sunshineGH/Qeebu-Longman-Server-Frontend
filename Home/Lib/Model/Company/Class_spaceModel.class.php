<?php

class Class_spaceModel extends Model{

    //use in homework and class_space top button
    public function get_class_by_uid($uid){

        $class_field = 'id,class_num';
        $re          = false;

        $class_ids = $this->get_class_id_by_user_id($uid);

        if($class_ids){
            $re = $this->get_class_by_id_arr($class_ids,$class_field);
        }
        return $re;
    }

    public function get_class_id_by_user_id($uid){

        $map['user_id'] = $uid;
        $map['_string'] = 'time_insert > time_delete';

        $rs = M('class_user')->where($map)->field('distinct class_id')->select();
        if(!$rs)return false;
        return i_array_column($rs,'class_id');
    }

    public function get_class_by_id_arr($arr,$field){
        $map['id'] = ['in',implode(',',$arr)];
        return M('class')->where($map)->field($field)->select();
    }

    public function get_class_space_list($data){

        $user_field = 'uid,nickname,photo,type as teacher_type';

        $data['page'] = $data['page'] == 0 ? 1 : $data['page'];
        $data['count']= $data['count']== 0 ? 10 : $data['count'];

        $map['class_id'] = $data['class_id'];

        $rs = M('class_space')
            ->where($map)
            ->order('id desc')
            ->limit((($data['page']-1)*$data['count']).",".$data['count'])
            ->select();

        $teacher = [];
        foreach($rs as &$val){
            if($teacher[$val['uid']] != null){
                $user = $teacher[$val['uid']];
            }else {
                $user = D('User')->get_user('', $val['uid'], $user_field);
                $teacher[$val['uid']] = $user;
            }
            $user['photo'] != '' && $user['photo'] = C('DOMAIN_NAME').__ROOT__.'/'.$user['photo'];
            $val = array_merge($user,$val);
            $attr = $this->get_class_space_attr($val['id']);
            $val['images'] = $attr['images'];
            $val['audio_title'] = $attr['audio']['audio_title']?$attr['audio']['audio_title']:'';
            $val['audio_time']  = $attr['audio']['audio_time']?$attr['audio']['audio_time'].'':'';
            $val['audio_path']  = $attr['audio']['audio_path']?$attr['audio']['audio_path']:'';


            $val['have_my_flower']        = $this->check_my_flower($val['id'],$data['uid']);
            $val['flowers']               = $this->get_flower_num($val['id']);
            if($val['flowers'] > 0){
                $val['brings_flowers_people'] = $this->get_flower_people($val['id']);
            }else{
                $val['brings_flowers_people'] = '';
            }

            unset($val['uid']);
            unset($val['class_id']);
            unset($user);
        }
        return $rs;
    }

    public function get_class_space_attr($class_space_id){
        $re = [
            'images'=>[],
            'audio' =>[]
        ];

        $map['class_space_id'] = $class_space_id;
        $rs = M('class_space_attr')->where($map)->select();

        foreach($rs as $val){
            $file = D('Files')->get_file_detail($val['file_id']);
            if(!$file)continue;
            if($file['file_type'] == 'mp3'){
                $re['audio'] = [
                    'audio_path'=>$file['file_path'],
                    'audio_title'=>$file['file_title'],
                    'audio_time' =>$file['file_time']
                ];
            }else{
                $re['images'][]= $file['file_path'];
            }
        }
        return $re;
    }

    public function get_flower_num($class_space_id){
        $map['class_space_id'] = $class_space_id;
        return M('class_space_flower')->where($map)->count();
    }

    public function get_flower_people($class_space_id){
        $map['class_space_id'] = $class_space_id;
        $rs = M('class_space_flower')->field('uid')->where($map)->select();
        $uid_array = i_array_column($rs,'uid');
        $name = D('User')->get_user_by_uid_array($uid_array,'nickname');
        return implode(',',i_array_column($name,'nickname'));
    }

    public function check_my_flower($class_space_id,$uid){
        $map = [
            'class_space_id'=>$class_space_id,
            'uid'           =>$uid
        ];
        return M('class_space_flower')->where($map)->count();
    }

    public function brings_flower($data){
        return M('class_space_flower')->add($data);
    }

    public function create_class_space($data){

        $rs = M('class_space')->add($data);

        if(!$rs)return false;

        if($data['attr']!=null && count($data['attr'])>0){
            foreach($data['attr'] as $val){
                $file_info = D('Files')->get_file_info($val);
                $file_id = D('Files')->add_file([
                    'path_type'=>1,
                    'file_type'=>$file_info['type'],
                    'file_size'=>$file_info['size'],
                    'file_path'=>$val
                ]);
                $this->add_class_space_attr($rs,$file_id);
            }
        }

        if($data['audio_id']!=''){
            $this->add_class_space_attr($rs,$data['audio_id']);
        }

        return $rs;
    }

    public function add_class_space_attr($class_space_id,$file_id){
        return M('class_space_attr')->add([
            'class_space_id'=>$class_space_id,
            'file_id'       =>$file_id
        ]);
    }

    public function reply_class_space($data){
        return M('class_space_reply')->add($data);
    }

    public function get_class_space_reply($data){

        $field       = 'role';
        $user_field  = 'uid,nickname,photo';
        $rs       = D('User')->get_user('',$data['uid'],$field);


        $map['class_space_id'] = $data['class_space_id'];
        $rs['role'] == 1 && $map['uid'] = $data['uid'];
        $rs = M('class_space_reply')->field('id,uid,pid,content,time_insert')->where($map)->select();

        if($rs['role'] == 1){
            $re = M('class_space_reply')->field('uid,pid,content,time_insert')->where([
                'class_space_id'=>$data['class_space_id'],
                'reply_for_uid' =>$data['uid']
            ])->select();
            if($re)
                $rs = array_merge($rs,(array)$re);
        }

        foreach($rs as &$val){
            $user = D('User')->get_user('', $val['uid'], $user_field);
            $val['uid']       = StrCode($val['uid']);
            $val['nickname']  = $user['nickname'];
            $user['photo'] != '' && $user['photo'] = C('DOMAIN_NAME').__ROOT__.'/'.$user['photo'];
            $val['photo']     = $user['photo'];
            if($val['pid'] == 0)
                $val['p_name'] = '';
            else
                $val['p_name'] = D('User')->get_user('', $val['pid'], 'nickname')['nickname'];
        }
        return $rs;
    }

    public function get_uid_by_reply_id($reply_id){
        return M('class_space_reply')->where(['id'=>$reply_id])->getField('uid');
    }

    public function get_class_space_student($data){
        $uids = D('User')->get_uid_by_class_id($data);
        $rs   = D('User')->get_user_by_uid_array($uids,'uid,nickname,photo');
        foreach($rs as &$val){
            $val['photo'] != '' && $val['photo'] = C('DOMAIN_NAME').__ROOT__.'/'.$val['photo'];
            $val['audio_count'] = $this->get_student_audio_count_by_uid($val['uid']);
            $val['student_id']=StrCode($val['uid']);
            unset($val['uid']);
        }
        return $rs;
    }

    public function get_student_audio_count_by_uid($uid){
        $map = [
            'uid'=>$uid,
            'type'=>2,
        ];
        return M('class_homework_submit_attr')->where($map)->count();
    }

    public function get_student_homework_audio($uid,$class_id=''){
        $map = [
            'uid'=>$uid,
            'type'=>2,
            'class_id'=>$uid
        ];
        $field = 'file_id,time_insert,audio_title,audio_time';
        $rs = M('class_homework_submit_attr')->field($field)->where($map)->select();
        foreach($rs as &$val){
            $val['file_url'] = D('Files')->get_file($val['file_id']);
            $val['file_id']  = StrCode($val['file_id']);
        }
        return $rs;
    }

    
}