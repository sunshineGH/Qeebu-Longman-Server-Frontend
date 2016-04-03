<?php

class ClassesAction extends Action{

    public function ln_get_class_list($data){

        D('User')->check_token($data['uid'],$data['token']);

        $rs = D('Classes')->get_class_by_uid($data['uid']);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_get_class_arrangement($data){

        D('User')->check_token($data['uid'],$data['token']);

        $data['class_date'] = check_null(40029,true,$data['class_date']);

        $rs = D('Classes')->get_class_arrangement($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_classes_user_detail($data){

        D('User')->check_token($data['uid'],$data['token']);

        $data['class_id']   = check_null(40018,true,$data['class_id']);
        $rs = D('Classes')->classes_user_detail($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_get_classes_date($data){
        D('User')->check_token($data['uid'],$data['token']);
        $rs = D('Classes')->get_classes_date($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }


}