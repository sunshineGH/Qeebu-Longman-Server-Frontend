<?php

class ioAction extends Action{

    public $req = [];
    protected function _initialize(){
        $this->req = json_decode(urldecode($_REQUEST['req']),true);
        if (!$this->req){
            return_json(-1);
        }
    }

    public function index(){
        $this->_initialize();

        switch($this->req['action']){

            case 'course':
                $this->course_action();
                break;
            case 'user':
                $this->user_action();
                break;
            case 'library':
                $this->library_action();
                break;
            case 'exercises':
                $this->exercises_action();
                break;
            case 'class_space':
                $this->class_space_action();
                break;
            case 'class_homework':
                $this->class_homework_action();
                break;
            case 'leave':
                $this->leave_action();
                break;
            case 'news':
                $this->news_action();
                break;
            case 'classes':
                $this->classes_action();
                break;
            case 'sign_in':
                $this->sign_in_action();
                break;
            case 'user_order':
                $this->user_order_action();
                break;
            case 'special_case':
                $this->special_case_action();
                break;
            case 'bookshop':
                $this->bookshop_action();
                break;
            case 'satisfaction_investigate':
                $this->Satisfaction_investigate_action();
                break;
            case 'push_notification':
                $this->push_notification_action();
                break;
        }
    }

    //用户管理
    public function user_action(){

        switch($this->req['type']){
            case 'login':
                A('Company/User')->ln_login($this->req['data']);
                break;
            case 'login_out':
                A('Company/User')->ln_login_out($this->req['data']);
                break;
            case 'register':
                A('Company/User')->ln_register($this->req['data']);
                break;
            case 'get_verify':
                A('Company/User')->ln_get_verify($this->req['data']);
                break;
            case 'check_verify':
                A('Company/User')->ln_check_verify($this->req['data']);
                break;
            case 'upt_password':
                A('Company/User')->ln_upt_password($this->req['data']);
                break;
            case 'upt_user_info':
                A('Company/User')->ln_upt_user_info($this->req['data']);
                break;
            case 'sync_user':
                A('Company/User')->ln_sync_user($this->req['data']);
                break;
            case 'get_teacher_detail':
                A('Company/User')->ln_get_teacher_detail($this->req['data']);
                break;
        }
    }

    //课程管理
    public function course_action(){
        switch($this->req['type']){
            case 'get_my_course':
                A('Company/Course')->get_my_course($this->req['data']);
                break;
            case 'get_user_center_my_course':
                A('Company/Course')->get_user_center_my_course($this->req['data']);
                break;
            case 'get_course_detail':
                A('Company/Course')->get_course_detail($this->req['data']);
                break;
            case 'get_recommended_courses':
                A('Company/Course')->get_recommended_courses_detail($this->req['data']);
                break;
            case 'get_supporting_course':
                A('Company/Course')->get_supporting_course_detail($this->req['data']);
                break;
            case 'get_supporting_course_unit_detail':
                A('Company/Course')->ln_get_supporting_course_unit_detail($this->req['data']);
                break;
            case 'trial_application':
                A('Company/Course')->ln_trial_application($this->req['data']);
                break;
            case 'get_my_course_new':
                A('Company/Course')->get_my_course_new($this->req['data']);
                break;
        }
    }

    //图书管理
    public function library_action(){

        switch($this->req['type']){
            case 'get_channel':
                A('Company/Library')->ln_get_channel($this->req['data']);
                break;
            case 'get_data':
                A('Company/Library')->ln_get_data($this->req['data']);
                break;
            case 'get_library_channel':
                A('Company/Library')->ln_get_library_channel($this->req['data']);
                break;
            case 'get_library_sqlite':
                A('Company/Library')->ln_get_library_sqlite($this->req['data']);
                break;
        }
    }

    //课后练习
    public function exercises_action(){
        switch($this->req['type']){
            case 'get_topic':
                A('Company/Exercises')->ln_get_topic($this->req['data']);
                break;
            case 'get_question':
                A('Company/Exercises')->ln_get_question($this->req['data']);
                break;
        }
    }

    //班级空间
    public function class_space_action(){
        switch($this->req['type']){
            case 'get_classes':
                A('Company/Class_space')->ln_get_class($this->req['data']);
                break;
            case 'get_class_space_list':
                A('Company/Class_space')->ln_get_class_space_list($this->req['data']);
                break;
            case 'brings_flower':
                A('Company/Class_space')->ln_brings_flower($this->req['data']);
                break;
            case 'create_class_space':
                A('Company/Class_space')->ln_create_class_space($this->req['data']);
                break;
            case 'reply_class_space':
                A('Company/Class_space')->ln_reply_class_space($this->req['data']);
                break;
            case 'get_class_space_reply':
                A('Company/Class_space')->ln_get_class_space_reply($this->req['data']);
                break;
            case 'get_class_space_student':
                A('Company/Class_space')->ln_get_class_space_student($this->req['data']);
                break;
            case 'get_student_homework_audio':
                A('Company/Class_space')->ln_get_student_homework_audio($this->req['data']);
                break;
        }
    }

    //作业提交
    public function class_homework_action(){
        switch($this->req['type']){
            case 'get_classes':
                A('Company/Homework')->ln_get_class($this->req['data']);
                break;
            case 'get_class_homework_list':
                A('Company/Homework')->ln_get_class_homework_list($this->req['data']);
                break;
            case 'create_class_homework':
                A('Company/Homework')->ln_create_class_homework($this->req['data']);
                break;
            case 'submit_class_homework':
                A('Company/Homework')->ln_submit_class_homework($this->req['data']);
                break;
            case 'get_class_homework_detail':
                A('Company/Homework')->ln_get_class_homework_detail($this->req['data']);
                break;
        }
    }

    //请假管理

    public function leave_action(){
        switch($this->req['type']){
            case 'create_leave':
                A('Company/Leave')->ln_create_leave($this->req['data']);
                break;
            case 'get_class_time':
                A('Company/Leave')->ln_get_class_time($this->req['data']);
                break;
            case 'get_leave_list':
                A('Company/Leave')->ln_get_leave_list($this->req['data']);
                break;
            case 'get_free_class':
                A('Company/Leave')->ln_get_free_class($this->req['data']);
                break;
            case 'choose_free_class':
                A('Company/Leave')->ln_choose_free_class($this->req['data']);
                break;
            case 'upt_leave_state':
                A('Company/Leave')->ln_upt_leave_state($this->req['data']);
                break;
            case 'choose_my_class':
                A('Company/Leave')->ln_choose_my_class($this->req['data']);
                break;
        }
    }

    public function news_action(){
        switch($this->req['type']){
            case 'get_news_list':
                A('Company/News')->ln_get_news_list($this->req['data']);
                break;
            case 'get_news_detail':
                A('Company/News')->ln_get_news_detail($this->req['data']);
                break;
            case 'active_sign_up':
                A('Company/News')->ln_active_sign_up($this->req['data']);
                break;
            case 'get_active_state':
                A('Company/News')->ln_get_active_state($this->req['data']);
                break;
        }
    }

    public function classes_action(){
        switch($this->req['type']){
            case 'get_class_list':
                A('Company/Classes')->ln_get_class_list($this->req['data']);
                break;
            case 'get_class_arrangement':
                A('Company/Classes')->ln_get_class_arrangement($this->req['data']);
                break;
            case 'classes_user_detail':
                A('Company/Classes')->ln_classes_user_detail($this->req['data']);
                break;
            case 'get_classes_date':
                A('Company/Classes')->ln_get_classes_date($this->req['data']);
                break;
        }
    }

    public function sign_in_action(){
        switch($this->req['type']){
            case 'sign_in':
                A('Company/Sign_in')->ln_sign_in($this->req['data']);
                break;
        }
    }

    public function user_order_action(){
        switch($this->req['type']){
            case 'get_my_order':
                A('Company/Order')->ln_get_my_order($this->req['data']);
                break;
            case 'get_my_order_red_point':
                A('Company/Order')->ln_get_my_order_red_point($this->req['data']);
                break;
            case 'cancel_order':
                A('Company/Order')->ln_cancel_order($this->req['data']);
                break;
        }
    }

    //特殊情况处理
    public function special_case_action(){
        switch($this->req['type']){
            case 'create_special_case':
                A('Company/Special_case')->ln_create_special_case($this->req['data']);
                break;
            case 'get_special_case_list':
                A('Company/Special_case')->ln_get_special_case_list($this->req['data']);
                break;
            case 'upt_special_case_state':
                A('Company/Special_case')->ln_upt_special_case_state($this->req['data']);
                break;
        }
    }

    //在线书城
    public function bookshop_action(){
        switch($this->req['type']){
            case 'get_category':
                A('Company/Bookshop')->ln_get_category($this->req['data']);
                break;
            case 'get_data':
                A('Company/Bookshop')->ln_get_data($this->req['data']);
                break;
        }
    }

    //满意度调查
    public function satisfaction_investigate_action(){
        switch($this->req['type']){
            case 'get_topic':
                A('Company/Satisfaction_investigate')->ln_get_topic($this->req['data']);
                break;
            case 'get_question':
                A('Company/Satisfaction_investigate')->ln_get_question($this->req['data']);
                break;
            case 'submit_user_answer':
                A('Company/Satisfaction_investigate')->ln_submit_user_answer($this->req['data']);
                break;
        }
    }

    //推送消息
    public function push_notification_action(){
        switch($this->req['type']){
            case 'reg_device_token':
                A('Company/Push_notification')->ln_reg_device_token($this->req['data']);
                break;
            case 'get_notice_numbers':
                A('Company/Push_notification')->ln_get_notice_numbers($this->req['data']);
                break;
            case 'get_notice_list':
                A('Company/Push_notification')->ln_get_notice_list($this->req['data']);
                break;
        }
    }
}