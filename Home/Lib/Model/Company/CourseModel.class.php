<?php

class CourseModel extends Model{

//----------------------------------------init----------------------------------------
    public function get_course($id = 0,$field='',$where=[]){
        $map = [];
        if($id != 0){
            $map['id'] = $id;
        }
        if($where != []){
            $map = array_merge($map,$where);
        }
        if($field=='')
            $rs = M('course')->where($map)->find();
        else
            $rs = M('course')->field($field)->where($map)->find();
        return $rs;
    }

    public function get_course_detail_by_id($id,$field){
        if($field == '')
            $rs = M('course_detail')->where(['id'=>$id])->find();
        else
            $rs = M('course_detail')->where(['id'=>$id])->field($field)->find();
        return $rs;
    }
//---------------------------------------insert----------------------------------------
    public function trial_application($data){
        $rs = M('trial_application')->add([
            'course_id'  =>$data['course_id'],
            'uid'        =>$data['uid'],
            'time_insert'=>time()
        ]);
        return $rs;
    }

//---------------------------------------delete----------------------------------------
//---------------------------------------update----------------------------------------
//---------------------------------------select----------------------------------------
//获取我的课程
//权重1
    public function get_my_course($uid){

        $course_field       = '`id`, `name`, `place`, `desc`, `level`, `speaker_level`, `grammar_level`, `supporting_course_id`';
        $course_belong_field= 'id,name';

        $my_course  = [];
        $student_course_ids = $this->get_course_id_by_student_id($uid);

        foreach($student_course_ids as $val){

            //client
            $course   = $this->get_course($val,$course_field);
            $class_id = $this->get_class_id_by_course_id_and_uid($val,$uid);

            //teacher
            $class                = D('Classes')->get_class($class_id,'`zh_teacher`, `en_teacher`');
            $course['zh_teacher'] = D('User')->get_user('',$class['zh_teacher'],'nickname')['nickname'];
            $course['en_teacher'] = D('User')->get_user('',$class['en_teacher'],'nickname')['nickname'];

            //next_time
            $class_time                 = D('Classes')->get_next_class_time_by_uid_class_id(['uid'=>$uid,'class_id'=>$class_id]);
            $class_time_info            = D('Classes')->get_class_time($class_time['class_time_id'],'course_detail_id,time_start,time_end');
            $class_detail               = $this->get_course_detail_by_id($class_time_info['course_detail_id'],'course_homework,course_schedule');
            $course['next_time_begin']  = $class_time_info['time_start'];
            $course['next_time_end']    = $class_time_info['time_end'];
            $course['homework']         = $class_detail['course_homework'] == ''?'':$class_detail['course_homework'];
            $course['schedule']         = $class_detail['course_schedule'] == ''?'':$class_detail['course_schedule'];
            //配套课程
            if($course['supporting_course_id'] == 0){
                $course['belong'] = null;
            }else{
                $course['belong'] = $this->get_course_belong($course['supporting_course_id'],$course_belong_field);
            }
            $course['id']         = StrCode($course['id']);
            $my_course[]=$course;
        }
        $rs['my_course']           = $my_course;
        $rs['recommended_courses'] = $this->recommended_courses_by_level(2);
        return $rs;
    }

    public function get_my_course_new($uid){

        $course_field        = '`id`, `name`, `desc`, `supporting_course_id`';

        $my_course  = [];
        $student_course_ids = $this->get_course_id_by_student_id($uid);

        foreach($student_course_ids as $val){
            //client
            $course   = $this->get_course($val,$course_field);
            $class_id = $this->get_class_id_by_course_id_and_uid($val,$uid);

            //老师
            $class                = D('Classes')->get_class($class_id,'`zh_teacher`, `en_teacher`,`hm_teacher`,`school_address_id`');
            $course['zh_teacher'] = D('User')->get_teacher_by_uid($class['zh_teacher']);
            $course['en_teacher'] = D('User')->get_teacher_by_uid($class['en_teacher']);
            $course['hm_teacher'] = D('User')->get_teacher_by_uid($class['hm_teacher']);
            $course['place']      = M('school_address')->where(['id'=>$class['school_address_id']])->getField('name');
            $course['place'] == '' && $course['place'] = '宝山校区';

            //本次的目标和作业
            $class_time                         = D('Classes')->get_next_class_time_by_uid_class_id(['uid'=>$uid,'class_id'=>$class_id]);
            $class_time_info                    = D('Classes')->get_class_time($class_time['class_time_id'],'course_detail_id,time_start,time_end');
            $class_detail                       = $this->get_course_detail_by_id($class_time_info['course_detail_id'],'course_homework,course_schedule,course_detail');
            $course['current_course_objectives']= $class_detail['course_detail']   == ''?'':$class_detail['course_detail'];
            $course['current_course_homework']  = $class_detail['course_homework'] == ''?'':$class_detail['course_homework'];

            //下次课程时间和进度
            $class_time                 = D('Classes')->get_next_class_time_by_uid_class_id(['uid'=>$uid,'class_id'=>$class_id]);
            $class_time_info            = D('Classes')->get_class_time($class_time['class_time_id'],'course_detail_id,time_start,time_end');
            $class_detail               = $this->get_course_detail_by_id($class_time_info['course_detail_id'],'course_homework,course_schedule');
            $course['next_time_begin']  = $class_time_info['time_start'];
            $course['next_time_end']    = $class_time_info['time_end'];
            $course['schedule']         = $class_detail['course_schedule'] == ''?'':$class_detail['course_schedule'];

            //拓展训练
            $course['supporting_course'] = $this->get_supporting_course_unit($course['supporting_course_id']);
            unset($course['supporting_course_id']);
            $course['id']         = StrCode($course['id']);
            $my_course[]=$course;
        }
        return $my_course;
    }


    public function get_user_center_my_course($uid){

        $course_field       = '`id`, `name`, `place`';
        $my_course  = [];
        $student_course_ids = $this->get_course_id_by_student_id($uid);

        foreach($student_course_ids as $val){
            $course                = $this->get_course($val,$course_field);
            $course['id']          = StrCode($course['id']);
            $class_id              = $this->get_class_id_by_course_id_and_uid($val,$uid);
            $class_time            = D('Classes')->get_class($class_id,'time_begin,time_end');
            $course['time_begin']  = $class_time['time_begin'];
            $course['time_end']    = $class_time['time_end'];
            $my_course[]=$course;
        }
        return $my_course;
    }

//获取课程详情
//权重1
    public function get_course_detail($id,$uid){

        if(!$this->check_student_course($uid,$id))return_json(40015);
        $course_field       = 'id,name,desc';

        //client
        $course   = $this->get_course($id,$course_field);
        $class_id = $this->get_class_id_by_course_id_and_uid($id,$uid);

        //老师
        $class                = D('Classes')->get_class($class_id,'`zh_teacher`, `en_teacher`,`hm_teacher`');
        $course['zh_teacher'] = D('User')->get_teacher_by_uid($class['zh_teacher']);
        $course['en_teacher'] = D('User')->get_teacher_by_uid($class['en_teacher']);
        $course['hm_teacher'] = D('User')->get_teacher_by_uid($class['hm_teacher']);

        //current
        $class_time                         = D('Classes')->get_next_class_time_by_uid_class_id(['uid'=>$uid,'class_id'=>$class_id]);
        $class_time_info                    = D('Classes')->get_class_time($class_time['class_time_id'],'course_detail_id,time_start,time_end');
        $class_detail                       = $this->get_course_detail_by_id($class_time_info['course_detail_id'],'course_homework,course_schedule,course_detail');

        $course['current_course_objectives']= $class_detail['course_detail']   == ''?'':$class_detail['course_detail'];
        $course['current_course_homework']  = $class_detail['course_homework'] == ''?'':$class_detail['course_homework'];
        //next
        $course['next_course_time_begin']   = $class_time_info['time_start'];
        $course['next_course_time_end']     = $class_time_info['time_end'];
        $course['next_course_schedule']     = $class_detail['course_schedule'] == ''?'':$class_detail['course_schedule'];

        $course['id'] = StrCode($id);

        return $course;
    }

//获取推荐课程详情
    public function get_supporting_course_detail($data){

        $id        = $data['course_id'];
        $uid       = $data['uid'];

        $course_field         = 'id,name,desc,supporting_course_id';
        $support_course_field = 'id,name,star_level,level';

        //client
        $course_p                   = $this->get_course($id,$course_field);
        $course                     = $this->get_supporting_course($course_p['supporting_course_id'],$support_course_field);
        $class_id                   = $this->get_class_id_by_course_id_and_uid($id,$uid);

        //老师
        $class                = D('Classes')->get_class($class_id,'`zh_teacher`, `en_teacher`,`hm_teacher`');
        $course['zh_teacher'] = D('User')->get_teacher_by_uid($class['zh_teacher']);
        $course['en_teacher'] = D('User')->get_teacher_by_uid($class['en_teacher']);
        $course['hm_teacher'] = D('User')->get_teacher_by_uid($class['hm_teacher']);

        $course['students_number']         = $this->get_course_student_count($id);
        $course['desc']                    = $course_p['desc'];
        $course['parents_name']            = $course_p['name'];
        $course['supporting_course']       = $this->get_supporting_course_unit($course['id']);
        $course['id']                      = StrCode($id);
        return $course;
    }

    public function get_supporting_course_unit($supporting_course_id){
        $map = ['supporting_course_id'=>$supporting_course_id];
        $rs = M('supporting_course_unit')
            ->where($map)
            ->field('id,unit,name')
            ->order('unit')
            ->select();
        foreach($rs as &$val){
            $val['id'] = StrCode($val['id']);
        }
        return $rs;
    }

    public function get_supporting_course_unit_detail($data){

        $map['id'] = $data['unit_id'];

        $rs = M('supporting_course_unit_module')
            ->where(['unit_id'=>$data['unit_id']])
            ->select();
        $themes_id  = i_array_column($rs,'theme_id');
        $module_id  = i_array_column($rs,'module_id');
        $themes     = $this->get_supporting_course_theme($themes_id);
        $modules    = $this->get_supporting_course_module($module_id);
        $rs_1 = [];

        //分组
        foreach($rs as $val){
            $rs_1[$val['theme_id']][] = $val;
        }

        //组装
        $re = [];
        foreach($rs_1 as $key=>$val){
            $re_val = $themes[$key];
            foreach($val as $val1){
                $module = $modules[$val1['module_id']];
                if($val1['topic_type'] == 1){
                    if($val1['file_id']!=0){
                        $file = D('Files')->get_file_detail($val1['file_id'],'file_path,title,file_time,file_size');
                        if(!$file){
                            unset($val1);
                            continue;
                        }
                        $module['file_path']          = init_public_url($file['file_path']);
                        $module['name']               = $file['title'];
                        $module['exercises_topic_id'] = '';
                    }else{
                        $module['file_path']          = '';
                        $module['exercises_topic_id'] = StrCode($val1['exercises_topic_id']);
                    }
                    $module['topic_zip_file_path']  = '';
                }else{
                    $module['file_path']          = '';
                    $module['exercises_topic_id'] = '';
                    $module['topic_zip_file_path']= D('Files')->get_file($val1['topic_zip_file_id']);
                }
                $module['topic_type'] = $val1['topic_type'];
                $re_val['modules'][]=$module;
            }
            $re[] = $re_val;
        }
        return $re;
    }

    public function get_supporting_course_theme($themes_id_arr){
        $re = [];
        $map = ['id'=>['in',implode(',',$themes_id_arr)]];
        $rs = M('supporting_course_theme')
            ->where($map)
            ->select();
        foreach($rs as $val){
            $val['image'] = D('Files')->get_file($val['image']);
            $re[$val['id']] = $val;
        };
        return $re;
    }

    public function get_supporting_course_module($modules_id_arr){
        $re = [];
        $map = ['id'=>['in',implode(',',$modules_id_arr)]];
        $rs = M('supporting_course_module')
            ->where($map)
            ->select();
        foreach($rs as $val){
            $val['image'] = D('Files')->get_file($val['image']);
            $re[$val['id']] = $val;
        };
        return $re;
    }

    public function get_supporting_course_video($supporting_course_times_id){
        $map = ['supporting_course_times_id'=>$supporting_course_times_id];
        $rs = M('supporting_course_video')
            ->where($map)
            ->field('file_id')
            ->select();
        if(!$rs)return [];

        foreach($rs as &$val1){
            $file = D('Files')->get_file_detail($val1['file_id'],'file_path,title,file_time,file_size');
            if(!$file){
                unset($val1);
                continue;
            }
            $val1['file_path'] = $file['file_path'];
            $val1['file_title']= $file['title'];
            $val1['file_time'] = $file['file_time'];
            $val1['file_size'] = $file['file_size'];
        }
        return $rs;
    }

//获取推荐课程详情
    public function get_recommended_courses_detail($id){
        $course_field = 'id,name,place,desc,5 as star_level,level';

        $course = $this->get_course($id,$course_field);

        //teacher
        $course['zh_teacher'] = null;
        $course['en_teacher'] = null;
        $course['hm_teacher'] = null;

        $course['students_number'] = $this->get_course_student_count($course['id']);
        $course['id'] = StrCode($id);

        return $course;
    }

//获取课程安排


//获取配套课程
    public function get_supporting_course($id,$field){
        if($field == '')
            $rs = M('supporting_course')->where(['id'=>$id])->find();
        else
            $rs = M('supporting_course')->where(['id'=>$id])->field($field)->find();
        return $rs;
    }

//获取class_id
    public function get_class_id_by_course_id_and_uid($val,$uid){

        $map = [
            'user_id'=>$uid,
            'course_id' =>$val
        ];
        $rs = M('class_user')->where($map)->getField('class_id');
        return $rs;
    }


    public function get_course_id_by_student_id($uid){

        $map['user_id'] = $uid;

        $rs = M('class_user')
            ->field('distinct course_id')
            ->where($map)
            ->select();
        return i_array_column($rs,'course_id');
    }

    public function check_student_course($uid,$course_id){
        $rs=M('class_user')->where([
            'user_id'   =>$uid,
            'course_id' =>$course_id
        ])->find();
        return $rs;
    }

    public function get_course_time($id,$field=''){
        $map['id'] = $id;
        if($field=='')
            $rs = M('course_time')->where($map)->find();
        else
            $rs = M('course_time')->field($field)->where($map)->find();
        return $rs;
    }

    public function get_course_time_by_course_id($id,$field=''){
        $map['course_id'] = $id;
        if($field=='')
            $rs = M('course_time')->where($map)->select();
        else
            $rs = M('course_time')->field($field)->where($map)->select();
        return $rs;
    }

    public function get_course_student_count($id){
        $map['course_id'] = $id;
        $rs = M('class_user')->where($map)->count();
        return $rs;
    }

    public function get_course_belong($id,$field=''){
        $map['id'] = $id;
        $rs = M('supporting_course')->field($field)->where($map)->find();
        if(!$rs)return null;

        $rs['open_course'] = 'Study how to talk with Us';
        $rs['id'] = StrCode($rs['id']);
        return $rs ? $rs : null;
    }

    public function recommended_courses_by_level($level){
        $courses=[];
        $map['level'] = $level;
        $field = 'id,name,place,level';
        $rs = M('recommended_courses')->where($map)->select();
        foreach($rs as $val){
            $course = $this->get_course($val['course_id'],$field);
            $course['id'] = StrCode($course['id']);
            $courses[]=$course;
        }
        return $courses;
    }

    public function get_course_id_by_user_id($uid){
        $map['user_id'] = $uid;
        $rs = M('class_user')->where($map)->field('course_id')->select();
        return array_unique(i_array_column($rs,'course_id'));
    }
}