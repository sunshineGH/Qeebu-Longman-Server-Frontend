<?php

class ExercisesAction extends Action{

    public function ln_get_topic($data){

        D('User')->check_token($data['uid'],$data['token']);

        $data['type'] = $data['exercises_type'] == ''? 1 : $data['exercises_type'];

        $rs   = D('Exercises')->get_topic($data);

        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_get_question($data){

        D('User')->check_token($data['uid'],$data['token']);

        $data['topic_id'] = StrCode(check_null(40016,true,$data['topic_id']),'DECODE');

        $rs = D('Exercises')->get_questions($data['topic_id']);

        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);

    }
}