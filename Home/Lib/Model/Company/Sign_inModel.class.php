<?php

class Sign_inModel extends Model{

    public function for_sign_in($data){

        $time = time();

        $rs = M('sign_in')->add([
            'uid'           =>$data['uid'],
            'sign_in_date'  =>date('Y-m-d',$time),
            'time_insert'   =>$time
        ]);
        if($rs){
            M('user')->where(['uid'=>$data['uid']])->save(['last_sign_in_date'=>date('Y-m-d',$time)]);
            M('user_integration')->add([
                'uid'=>$data['uid'],
                'coming_style'=>'sign in',
                'integration' =>'5',
                'time_insert' =>$time
            ]);
        }
        return $rs;
    }


    public function get_last_sign_in_date($uid){
        return M('user')->where(['uid'=>$uid])->getField('last_sign_in_date');
    }
}