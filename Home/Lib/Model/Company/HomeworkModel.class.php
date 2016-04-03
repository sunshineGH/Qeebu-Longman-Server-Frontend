<?php

class HomeworkModel extends Model{

//----------------------------------init------------------------------------------------
    public function get_homework($id,$field=''){
        if($field=='')
            $rs = M('homework')->where(['id'=>$id])->find();
        else
            $rs = M('homework')->field($field)->where(['id'=>$id])->find();
        return $rs;
    }

//---------------------------------insert-----------------------------------------------
    public function create_class_homework($data){

        $rs = M('class_homework')->add($data);

        if(!$rs)return false;

        if($data['attr']!=null && count($data['attr'])>0){
            foreach($data['attr'] as $val){
                $file_info = D('Files')->get_file_info($val);
                $file_data = [
                    'path_type'=>1,
                    'file_type'=>$file_info['type'],
                    'file_size'=>$file_info['size'],
                    'file_path'=>$val
                ];
                if($file_data['file_type'] == 'mp3'){
                    $file_data['file_title'] = $data['audio_title'];
                    $file_data['file_time']  = $data['audio_time'];
                }
                $file_id   = D('Files')->add_file($file_data);
                $this->add_class_homework_attr($rs,$file_id,$data);
                unset($file_data);
            }
        }
        return $rs;
    }

    public function add_class_homework_attr($class_homework_id,$file_id,$data){
        return M('class_homework_attr')->add([
            'class_homework_id'=> $class_homework_id,
            'file_id'          => $file_id,
            'audio_title'      => $data['audio_title']  ==''?'':$data['audio_title'],
            'audio_time'       => $data['audio_time']   ==''?'':$data['audio_time']
        ]);
    }

    public function submit_class_homework($data){

        $rs = M('class_homework_submit')->add($data);

        if(!$rs)return false;

        if($data['attr']!= ''){
            foreach($data['attr'] as $val){
                $file_info = D('Files')->get_file_info($val);
                $file_data = [
                    'path_type'=>1,
                    'file_type'=>$file_info['type'],
                    'file_size'=>$file_info['size'],
                    'file_path'=>$val
                ];
                if($file_data['file_type'] == 'mp3'){
                    $file_data['file_title'] = $data['audio_title'];
                    $file_data['file_time']  = $data['audio_time'];
                }
                $this->add_class_homework_submit_attr($rs,$val,$data,$file_info['type']);
            }
        }
        A('Company/Push_notification')->home_work_submit_push($data);
        return $rs;
    }

    public function add_class_homework_submit_attr($class_homework_submit_id,$file_path,$data,$type){
        return M('class_homework_submit_attr')->add([
            'class_homework_submit_id'=>$class_homework_submit_id,
            'uid'           =>$data['uid'],
            'class_id'      =>$data['class_id'],
            'file_id'       =>$file_path,
            'type'          =>$type == 'mp3'?2 :1,
            'audio_title'   =>$type != 'mp3'?'':$data['audio_title'],
            'audio_time'    =>$type != 'mp3'?'':$data['audio_time'],
            'time_insert'   =>time()
        ]);
    }

//---------------------------------delete-----------------------------------------------
//---------------------------------update-----------------------------------------------
//---------------------------------select-----------------------------------------------

    public function get_homework_list($data){

        $user_field = 'uid,nickname,photo';

        $map['class_id']     = $data['class_id'];
        $map['homework_date']= $data['homework_date'];
        //homework_date

        $rs = M('class_homework')
            ->where($map)
            ->select();

        foreach($rs as &$val){
            $user = D('User')->get_user('', $val['uid'], $user_field);
            $user['photo']!= '' && $user['photo'] = C('DOMAIN_NAME').__ROOT__.'/'.$user['photo'];
            $val           = array_merge($user,$val);
            $attr          = $this->get_homework_attr($val['id']);
            $val['images'] = $attr['images'];
            $val['audio']  = $attr['audio'];

            if(count($val['images']) == 1){
                $size = getimagesize($val['images'][0]);
                $val['image_for_one_width'] = $size[0];
                $val['image_for_one_height'] = $size[1];
            }else{
                $val['image_for_one_width'] = 0;
                $val['image_for_one_height'] = 0;
            }
            unset($val['uid']);
            unset($val['class_id']);
            unset($user);
        }
        return $rs;
    }

    public function get_homework_attr($homework_id){
        $re = [
            'images'=>[],
            'audio' =>[]
        ];

        $map['class_homework_id'] = $homework_id;
        $rs = M('class_homework_attr')->where($map)->select();

        foreach($rs as $val){
            $file = D('Files')->get_file($val['file_id']);
            if(!$file)continue;

            $type = end(explode('.',$file));
            if($type == 'mp3'){
                $re['audio'][] = [
                    'audio_path' =>$file,
                    'audio_title'=>$val['audio_title'],
                    'audio_time' =>$val['audio_time']
                ];
            }else{
                $re['images'][]= $file;
            }
        }
        return $re;
    }

    public function get_homework_by_type($data){

        $data['page'] = $data['page'] == 0 ? 1 : $data['page'];
        $data['count']= $data['count']== 0 ? 1 : $data['count'];

        $level = D('User')->get_user('',$data['uid'],'speaking_level,grammar_level');

        if($data['type'] == 1){
            //grammar
            if($level['grammar_level'] == 0)return [];
            $level = $level['grammar_level'];
        }else{
            //speaking
            if($level['speaking_level'] == 0)return [];
            $level = $level['speaking_level'];
        }

        $map=[
            'level'=>$level,
            'type' =>$data['type']
        ];
        $rs = M('exercises_topic')
            ->where($map)
            ->order('id desc')
            ->limit((($data['page']-1)*$data['count']).",".$data['count'])
            ->select();
        return $rs;
    }

    public function get_class_homework_detail($data){

        $submit_field= 'uid,content,time_insert,class_id,id';
        $role = D('User')->get_user('',$data['uid'],'role')['role'];
        $map['class_homework_id'] = $data['class_homework_id'];
        $role == 1 && $map['uid'] = $data['uid'];
        $rs['submit'] = $this->get_class_homework_submit_by_map($map,$submit_field);

        if($role == 1){
            $class_submit = $this->get_class_homework_submit_by_map(['class_homework_id'=>$data['class_homework_id']],'uid');
        }else{
            $class_submit = $rs['submit'];
        }

        $classmate_arr = [];
        $classmate  = D('User')->get_classmate_by_class_id($data['class_id'],'uid,nickname,photo');
        foreach($classmate as $val){
            $classmate_arr[$val['uid']] = $val;
        }
        $rs['homework_finish_info'] = $this->homework_finish_info($class_submit,$classmate_arr);


        foreach($rs['submit'] as &$val){
            $val = array_merge([
                'nickname' => $classmate_arr[$val['uid']]['nickname'],
                'photo'    => $classmate_arr[$val['uid']]['photo'] == '' ? '' : C('DOMAIN_NAME').__ROOT__.'/'.$classmate_arr[$val['uid']]['photo']
            ],$val);
            $attr = $this->get_submit_homework_attr($val['id']);
            $val['images'] = $attr['images'];
            $val['audio']  = $attr['audio'];

            if(count($val['images']) == 1){
                $size = getimagesize($val['images'][0]);
                $val['image_for_one_width'] = $size[0];
                $val['image_for_one_height'] = $size[1];
            }else{
                $val['image_for_one_width'] = 0;
                $val['image_for_one_height'] = 0;
            }

            unset($val['class_id']);
        }
        return $rs;
    }

    public function get_class_homework_submit_by_map($where,$field=''){
        return M('class_homework_submit')->field($field)->where($where)->order('uid')->select();
    }

    public function homework_finish_info($class_submit,$classmate_arr){

        $finished   = array_unique(i_array_column($class_submit,'uid'));
        $class_uid  = array_unique(i_array_column($classmate_arr,'uid'));

        $unfinished     = array_diff($class_uid,$finished);
        $rs             = [
            'finished'  =>[],
            'unfinished'=>[]
        ];

        foreach($finished as $val){
            $rs['finished'][]   = [
                'nickname' => $classmate_arr[$val]['nickname'],
                'photo'    => $classmate_arr[$val]['photo'] == '' ? '' : C('DOMAIN_NAME').__ROOT__.'/'.$classmate_arr[$val]['photo']
            ];
        }

        foreach($unfinished as $val){
            $rs['unfinished'][] = [
                'nickname' => $classmate_arr[$val]['nickname'],
                'photo'    => $classmate_arr[$val]['photo'] == '' ? '' : C('DOMAIN_NAME').__ROOT__.'/'.$classmate_arr[$val]['photo']
            ];
        }
        return $rs;
    }

    public function get_submit_homework_attr($class_homework_submit_id){
        $re = [
            'images'=>[],
            'audio' =>[]
        ];
        $map['class_homework_submit_id'] = $class_homework_submit_id;
        $rs = M('class_homework_submit_attr')->where($map)->select();
        foreach($rs as $val){
            if($val['type'] == '2'){
                $re['audio'][] = [
                    'audio_path' => C('PUBLIC_URL').$val['file_id'],
                    'audio_title'=> $val['audio_title'],
                    'audio_time' => $val['audio_time']
                ];
            }else{
                $re['images'][]= C('PUBLIC_URL').$val['file_id'];
            }
        }
        return $re;
    }
}