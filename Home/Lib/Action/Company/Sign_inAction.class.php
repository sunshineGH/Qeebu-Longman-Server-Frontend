<?php

class Sign_inAction extends Action{

    public function ln_sign_in($data){

        D('User')->check_token($data['uid'],$data['token']);

        $rs = D('Sign_in')->for_sign_in($data);
        $user['integration_num']  = D('User')->get_my_integration_num($data['uid']);
        $user['coupon_num']       = D('User')->get_my_coupon_num($data['uid']);
        if($rs){
            return_json(0,$user);
        }else{
            return_json(-1,$user);
        }
    }
}