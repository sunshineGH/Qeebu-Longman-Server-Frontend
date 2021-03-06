<?php

class NoticeAction extends Action{

    public function push_notice()
    {
        if(!Database($_REQUEST["cid"]))exit;
        $_REQUEST['uid']           = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['notice_title']  = check_null(40096,true,$_REQUEST['title']);
        $_REQUEST['notice_detail'] = check_null(40005,true,$_REQUEST['content']);
        D('Notice')->push_notice($_REQUEST)?return_json(0):return_json(-1);
    }

    public function get_chown(){
        if(!Database($_REQUEST["cid"]))exit;
        $_REQUEST['uid']           = check_null(40002,true,$_REQUEST['uid']);
        return_json(-1);
    }

    public function get_notice_list(){
        if(!Database($_REQUEST["cid"]))exit;
        $_REQUEST['uid']        = check_null(40002,true,$_REQUEST['uid']);

        $rs = D('Notice')->get_notice_list($_REQUEST);

        if($rs)
            return_json(0,$rs,time());
        else
            return_json(40001);
    }

    public function get_red_point()
    {
        if(!Database($_REQUEST["cid"]))exit;
        $_REQUEST['uid']        = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['timestamp']  = check_null(40042,true,$_REQUEST['timestamp']);

        $rs = D('Notice')->get_red_point($_REQUEST);
        if($rs)
            return_json(0);
        else
            return_json(40001);
    }
}