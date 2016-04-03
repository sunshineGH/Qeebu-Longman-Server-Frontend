<?php

class CourseAction extends Action{

    public function get_my_course($data){

        D('User')->check_token($data['uid'],$data['token']);
        $rs = D('Course')->get_my_course($data['uid']);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function get_my_course_new($data){

        D('User')->check_token($data['uid'],$data['token']);
        $rs = D('Course')->get_my_course_new($data['uid']);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function get_user_center_my_course($data){

        D('User')->check_token($data['uid'],$data['token']);
        $rs = D('Course')->get_user_center_my_course($data['uid']);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }


    public function get_course_detail($data){

        $data['course_id'] = check_null(40014,true,$data['course_id']);
        $data['course_id'] = StrCode($data['course_id'],'DECODE');

        D('User')->check_token($data['uid'],$data['token']);
        $rs = D('Course')->get_course_detail($data['course_id'],$data['uid']);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function get_supporting_course_detail($data){

        $data['course_id'] = check_null(40014,true,$data['course_id']);
        $data['course_id'] = StrCode($data['course_id'],'DECODE');

        D('User')->check_token($data['uid'],$data['token']);
        //if(!D('Course')->check_student_course($data['uid'],$data['course_id']))return_json(40015);

        $rs = D('Course')->get_supporting_course_detail($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_get_supporting_course_unit_detail($data){

        check_null(40031,true,$data['unit_id']);
        $data['unit_id'] = StrCode($data['unit_id'],'DECODE');
        D('User')->check_token($data['uid'],$data['token']);
        $rs = D('Course')->get_supporting_course_unit_detail($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function get_recommended_courses_detail($data){

        $data['course_id'] = StrCode(check_null(40014,true,$data['course_id'],'DECODE'));
        D('User')->check_token($data['uid'],$data['token']);

        $rs = D('Course')->get_recommended_courses_detail($data['course_id']);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

//申请试听
    public function ln_trial_application($data){

        D('User')->check_token($data['uid'],$data['token']);

        $data['course_id'] = StrCode(check_null(40014,true,$data['course_id'],'DECODE'));

        $rs = D('Course')->trial_application($data);
        if($rs)
            return_json(0);
        else
            return_json(-1);
    }
}