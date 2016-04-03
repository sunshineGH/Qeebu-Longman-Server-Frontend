<?php

class ClassesModel extends Model{

//-------------------------------------init---------------------------------------------
    public function get_class($id,$field=''){
        if($field=='')
            $rs = M('class')->where(['id'=>$id])->find();
        else
            $rs = M('class')->field($field)->where(['id'=>$id])->find();
        return $rs;
    }

    public function get_class_user($id,$field=''){
        if($field=='')
            $rs = M('class_user')->where(['id'=>$id])->find();
        else
            $rs = M('class_user')->field($field)->where(['id'=>$id])->find();
        return $rs;
    }
//------------------------------------------insert--------------------------------------
//------------------------------------------delete--------------------------------------
    public function delete_free_class($user_id,$course_detail_id){
        $map = [
            'user_id'          =>$user_id,
            'course_detail_id' =>$course_detail_id
        ];
        return M('free_class')->where($map)->delete();
    }

    public function delete_class_by_id($id){
        $this->delete_class_time_by_class_id($id);
        $this->delete_class_time_student_by_class_id($id);
        return M('class')->where(['class_id'=>$id])->delete();
    }

    public function delete_class_time_by_class_id($id){
        return M('class_time')->where(['class_id'=>$id])->delete();
    }

    public function delete_class_time_student_by_class_id($id){
        return M('class_time_student')->where(['class_id'=>$id])->delete();
    }
//------------------------------------------update--------------------------------------
    public function upt_class_user_by_user_id($user_id){
        return M('class_user')
            ->where(['user_id'=>$user_id])
            ->save(['time_update'=>time()]);
    }
//------------------------------------------select--------------------------------------
    public function get_class_id_by_teacher_id($uid){
        $map = [
            'user_id'=>$uid,
            'role'   =>'2',
            '_string'=>'time_insert>time_delete'
        ];
        $rs = M('class_user')->field('distinct class_id')->where($map)->select();
        return i_array_column($rs,'class_id');
    }

    public function get_class_num_by_id($id){
        return M('class')->where(['id'=>$id])->getField('class_num');
    }
//获取我的班级管理列表
//权重1
    public function get_class_by_uid($uid){
        $class_field = 'id,class_num,zh_teacher,en_teacher,hm_teacher';
        $re          = false;
        $class_ids   = $this->get_class_id_by_teacher_id($uid);
        foreach($class_ids as $val){
            $class = $this->get_class($val,$class_field);
            $class['zh_teacher']    = D('User')->get_user('',$class['zh_teacher'],'nickname')['nickname'];
            $class['en_teacher']    = D('User')->get_user('',$class['en_teacher'],'nickname')['nickname'];
            $class['hm_teacher']    = D('User')->get_user('',$class['hm_teacher'],'nickname')['nickname'];
            $class['student_count'] = $this->get_class_student_count_by_class_id($val);
            $re == false ? $re = [$class] : $re[]=$class;
        }
        return $re;
    }

    public function get_class_student_count_by_class_id($class_id){
        $map = [
            'class_id'=>$class_id,
            '_string' =>'time_insert>time_delete',
            'role'    =>'1'
        ];
        return M('class_user')->where($map)->count();
    }
//获取当天的课程安排
//权重1
    public function get_class_arrangement($data){
        $map = [
            'time_start'=>[
                ['egt',strtotime($data['class_date'].'00:00:00')],
                ['elt',strtotime($data['class_date'].'23:59:59')],
            ],
            'state'     =>1,
        ];
        $rs = M('class_time_student')
            ->field('class_id,class_time_id')
            ->group('class_id')
            ->where($map)
            ->select();

        foreach($rs as &$val){
            $class_time                     = $this->get_class_time($val['class_time_id'],'course_detail_id,time_start,time_end');
            $val['class_num']               = $this->get_class($val['class_id'],'class_num')['class_num'];
            $val['time_start']              = $class_time['time_start'];
            $val['time_end']                = $class_time['time_end'];
            $val['class_hot']               = 'family class';
            $class_detail                   = D('Course')->get_course_detail_by_id($class_time['course_detail_id'],'course_homework,course_schedule');
            $val['course_schedule']         = $class_detail['course_schedule'];
            $val['course_homework']         = $class_detail['course_homework'];
            $val['makeMissedStudentNumber'] = 0;//补课
            $val['tempStudentNumber']       = 0;//插班
        }
        return $rs;
    }
//获取当前班级本次课程的参加课程详情
    public function classes_user_detail($data){

        $users = [];
        $user_filed = 'uid,`nickname`, `parents_name`, `sex`, `tel`, `photo`,`birthday`, `coming_date`, `coming_style`, `level_test`, `speaking_level`, `grammar_level`, `im_username`,`role`,`type`,`desc`';

        if($data['class_date']){
            $map = [
                'class_id'   =>$data['class_id'],
                'time_start'=>[
                    ['egt',strtotime($data['class_date'].'00:00:00')],
                    ['elt',strtotime($data['class_date'].'23:59:59')],
                ],
                'state' =>1,
            ];
            $rs = M('class_time_student')->where($map)->field('user_id,coming_style as student_style')->select();
            $uid_arr= i_array_column($rs,'user_id');

            $teachers = $this->get_class($data['class_id'],'zh_teacher,en_teacher,hm_teacher');
            $uid_arr[] = $teachers['zh_teacher'];
            $uid_arr[] = $teachers['en_teacher'];
            $uid_arr[] = $teachers['hm_teacher'];

            $user = D('User')->get_user_by_uid_array($uid_arr,$user_filed);

            foreach($user as $val1){
                $uid = $val1['uid'];
                $val1['photo'] = init_public_url($val1['photo']);
                $val1['uid'] = StrCode($val1['uid']);
                $val1['user_absence_times'] = '3';
                $val1['continue_state']     = '已续班';
                $users[$uid] = $val1;
            }
            foreach($rs as &$val){
                $student = $users[$val['user_id']];
                if(!$student)continue;
                $val = array_merge($val,$student);
                unset($val['user_id']);
            }
            $rs[] = array_merge(['student_style'=>0],$users[$teachers['zh_teacher']]);
            $rs[] = array_merge(['student_style'=>0],$users[$teachers['en_teacher']]);
            $rs[] = array_merge(['student_style'=>0],$users[$teachers['hm_teacher']]);
        }else{
            $map = ['class_id'   =>$data['class_id']];
            $rs = M('class_user')
                ->where($map)
                ->field('user_id,0 as student_style')
                ->select();
            $uid_arr= i_array_column($rs,'user_id');

            $user = D('User')->get_user_by_uid_array($uid_arr,$user_filed);
            foreach($user as $val1){
                $val1['photo'] = init_public_url($val1['photo']);
                $val1['uid'] = StrCode($val1['uid']);
                $val1['user_absence_times'] = '3';
                $val1['continue_state']     = '已续班';
                $users[$val1['uid']] = $val1;
            }
            foreach($rs as &$val){
                $student = $users[$val['user_id']];
                if(!$student)continue;
                $val = array_merge($val,$student);
                unset($val['user_id']);
            }
        }
        return $rs;
    }


//获取空闲的班级
//权重1
    public function get_free_class($data){

        $class_time = $this->get_class_time($data['class_time_id'],'course_detail_id,class_id');
        $class_ids  = $this->get_class_id_by_course_detail_id($class_time['course_detail_id']);

        $rs = [];
        foreach($class_ids as $val){

            if($val == $class_time['class_id'])continue;
            $class['class_num']         = $this->get_class_num_by_id($val);
            if(!$class['class_num']){
                $this->delete_class_by_id($val);
                continue;
            }
            $this_class_time            = $this->get_class_time_by_course_detail_id_class_id($class_time['course_detail_id'],$val);
            $class['max_num']           = $this_class_time['max_num'];
            $class['student_count']     = $this->get_class_time_student_count($this_class_time['id']);
            $class['class_time_id']     = $this_class_time['id'];
            $class['time_start']        = $this_class_time['time_start'];
            $class['time_end']          = $this_class_time['time_end'];
            $class['class_id']          = StrCode($val);
            $rs[]=$class;
        }
        return $rs;
    }

//选择空闲的class班级给家长让家长选择
//权重1
    public function choose_free_class($data){

        $class_ids        = explode(',',$data['class_time_id']);
        $course_detail_id = $this->get_class_time($class_ids[0],'course_detail_id')['course_detail_id'];

        //老师选择空余课
        $rs = false;
        $this->delete_free_class($data['student_id'],$course_detail_id);
        //老师选班专用
        if($data['for']=='' || $data['for']!='choose_my_class'){
            foreach($class_ids as $val){
                $rs = M('free_class')->add([
                    'user_id'          =>$data['student_id'],
                    'class_time_id'    =>$val,
                    'course_detail_id' =>$course_detail_id
                ]);
            }
            A('Company/Push_notification')->leave_teacher_pass_push($data);
        }else{
            $class_time 	= $this->get_class_time($data['class_time_id']);
            //1.安排班级 - 查看座位数
            $student_count  = $this->get_class_time_student_count($data['class_time_id']);
            if($student_count >= $class_time['max_num'] ){
                return_json(40028);
            }else{
                //新增用户
                M('class_time_student')->add([
                    'class_id' 		=> $class_time['class_id'],
                    'class_time_id' => $data['class_time_id'],
                    'user_id'		=> $data['uid'],
                    'time_start'	=> $class_time['time_start'],
                    'state'			=> 1,
                ]);
                //原来的置为请假状态
                M('class_time_student')->where([
                    'class_time_id' => $data['old_class_time_id'],
                    'user_id'	    => $data['uid']
                ])->save(['state'=> 2]);
            }
            //家长选班专用
            $data['course_detail_id'] = $course_detail_id;
            $rs = M('leave_history')->add($data);
            A('Company/Push_notification')->leave_history_push($data);
        }
        D('Leave')->upt_leave_state([
            'leave_id'           =>$data['leave_id'],
            'state'              =>$data['state'],
            'not_available_class'=>$data['not_available_class']
        ]);
        return $rs;
    }

//获取老师选给我的空闲的class
//权重1
    public function get_my_free_class($data){
        $course_detail_id = $this->get_class_time($data['class_time_id'],'course_detail_id')['course_detail_id'];
        $map = [
            'user_id'          =>$data['uid'],
            'course_detail_id' =>$course_detail_id
        ];
        $rs = M('free_class')->field('class_time_id')->where($map)->select();
        if(!$rs)return [];
        foreach($rs as &$val){
            $this_class_time            = $this->get_class_time($val['class_time_id']);
            $val['class_num']           = $this->get_class($this_class_time['class_id'],'class_num')['class_num'];
            $val['max_num']             = $this_class_time['max_num'];
            $val['student_count']       = $this->get_class_time_student_count($this_class_time['id']);
            $val['time_start']          = $this_class_time['time_start'];
            $val['time_end']            = $this_class_time['time_end'];
        }
        return $rs;
    }

//获取当前时间之后的所有上课时间
    public function get_classes_time_by_uid_class_id($data){
        $map = [
            'class_id'  =>$data['class_id'],
            'user_id'   =>$data['uid'],
            'time_start'=>[
                ['gt',strtotime(date('Y-m-d').'00:00:00')]
            ],
            'state'     =>1
        ];
        $rs = M('class_time_student')->field('class_time_id,time_start')->where($map)->order('time_start')->select();
        return $rs;
    }

//获取当前时间之后的所有上课时间信息
    public function get_next_class_time_by_uid_class_id($data){
        $map = [
            'class_id'  =>$data['class_id'],
            'user_id'   =>$data['uid'],
            'time_start'=>[
                ['gt',strtotime(date('Y-m-d').'00:00:00')]
            ]
        ];
        $rs = M('class_time_student')->where($map)->find();
//        dump(M('class_time_student')->_sql());
        return $rs;
    }

//获取当前uid所有上课时间
    public function get_classes_date($data){
        $rs       = M('class_user')->where(['user_id'=>$data['uid']])->select();
        $class_id = i_array_column($rs,'class_id');
        $map = [
            'class_id'=>['in',$class_id]
        ];
        $time_start = M('class_time')->where($map)->field('time_start')->select();
        return i_array_column($time_start,'time_start');
    }

    public function get_class_time($id,$field=''){
        if($field=='')
            $rs = M('class_time')->where(['id'=>$id])->find();
        else
            $rs = M('class_time')->field($field)->where(['id'=>$id])->find();
        return $rs;
    }

    public function get_class_id_by_course_detail_id($course_detail_id,$where=[]){

        $map = [
            'course_detail_id'=> $course_detail_id,
            'time_start'      => ['gt',time()]
        ];
        if($where!=[]){
            $map = array_map($map,$where);
        }
        $rs = M('class_time')->where($map)->field('distinct class_id')->select();
        return i_array_column($rs,'class_id');
    }

    public function get_class_time_by_course_detail_id_class_id($course_detail_id,$class_id){
        $map = [
            'course_detail_id'=>$course_detail_id,
            'class_id'        =>$class_id
        ];
        return M('class_time')->where($map)->find();
    }

    public function get_class_time_student_count($class_time_id){
        $map = [
            'class_time_id'=>$class_time_id,
            'state'        =>1,
        ];
        return M('class_time_student')->field('distinct user_id')->where($map)->count();
    }

//--------------------------start class_user_model---------------------------------------
    public function get_class_id_by_user_id($uid){
        $map = [
            'user_id' => $uid,
            '_string' => 'time_insert > time_delete'
        ];
        $rs = M('class_user')->where($map)->field('distinct class_id')->select();
        if(!$rs)return false;
        return i_array_column($rs,'class_id');
    }

    public function get_class_by_id_arr($arr,$field){
        $map['id'] = ['in',implode(',',$arr)];
        return M('class')->where($map)->field($field)->select();
    }
//------------------------end class_user_model------------------------------------------

}