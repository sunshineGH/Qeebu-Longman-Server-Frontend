<?php

class Satisfaction_investigateAction extends Action{

    public function ln_get_topic($data){

        D('User')->check_token($data['uid'],$data['token']);

        $data['type'] = $data['exercises_type'] == ''? 1 : $data['exercises_type'];

        $rs = D('Satisfaction_investigate')->get_topic($data);

        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_get_question($data){

        D('User')->check_token($data['uid'],$data['token']);

        $data['topic_id'] = StrCode(check_null(40016,true,$data['topic_id']),'DECODE');

        $rs = D('Satisfaction_investigate')->get_questions($data['topic_id']);

        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_submit_user_answer($data){

        D('User')->check_token($data['uid'],$data['token']);

        check_null(40016,true,$data['topic_id']);
        check_null(40038,true,is_array($data['satisfaction_investigate_answer']));
        $data['topic_id'] = StrCode($data['topic_id'],'DECODE');
        $data['answer']   = json_encode($data['satisfaction_investigate_answer']);

        $rs = D('Satisfaction_investigate')->submit_user_answer($data);
        if($rs)
            return_json(0);
        else
            return_json(-1);
    }
}