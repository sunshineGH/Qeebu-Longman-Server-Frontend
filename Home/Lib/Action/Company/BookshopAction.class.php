<?php

class BookshopAction extends Action{

    public function ln_get_category($data){

        D('User')->check_token($data['uid'],$data['token']);
        $rs = D('Bookshop')->get_category();
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_get_data($data){

        D('User')->check_token($data['uid'],$data['token']);
        check_null(40037,true,$data['bookshop_category_id']);
        $rs = D('Bookshop')->get_data($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }
}