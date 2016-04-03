<?php

class Special_caseAction extends Action{
//-------------------------------------insert-------------------------------------------
    public function ln_create_special_case($data){

        D('User')->check_token($data['uid'],$data['token']);

        check_null(40018,true,$data['class_id']);
        check_null(40036,true,$data['special_case_date']);
        check_null(40025,true,$data['class_time_id']);
        $data['time_insert'] = time();

        $rs=D("Special_case")->create_special_case($data);
        if($rs){
            return_json(0);
        }else{
            return_json(-1);
        }
    }
//------------------------------------delete--------------------------------------------
//------------------------------------update--------------------------------------------
    public function ln_upt_special_case_state($data){

        D('User')->check_token($data['uid'],$data['token']);

        check_null(40027,true,$data['special_case_id']);
        $data['time_update'] = time();

        unset($data['uid']);

        $rs=D("Special_case")->upt_special_case_state($data);
        if($rs){
            return_json(0);
        }else{
            return_json(-1);
        }
    }
//------------------------------------select--------------------------------------------
    public function ln_get_special_case_list($data){

        D('User')->check_token($data['uid'],$data['token']);

        $rs=D("Special_case")->get_special_case_list_by_class_id($data);
        if($rs){
            return_json(0,$rs);
        }else{
            return_json(40001);
        }
    }
}