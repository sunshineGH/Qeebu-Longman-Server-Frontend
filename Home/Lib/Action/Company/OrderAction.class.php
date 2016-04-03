<?php

class OrderAction extends Action{

    public function ln_get_my_order($data){

        D('User')->check_token($data['uid'],$data['token']);

        $rs = D('Order')->get_my_order($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_get_my_order_red_point($data){

        D('User')->check_token($data['uid'],$data['token']);
        check_null(40017,true,$data['timestamp']);

        $rs = D('Order')->get_my_order_red_point($data);
        if($rs)
            return_json(0,[],time());
        else
            return_json(40001,[],time());
    }

    public function ln_cancel_order($data){

        D('User')->check_token($data['uid'],$data['token']);

        check_null(40033,true,$data['trade_no']);

        $rs = D('Order')->cancel_order($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(-1);

    }

}