<?php
class CompanyAction extends CommonAction{

    public function index(){
        echo 1;
    }

    public function get_open_company(){

        $rs = D('Company')->get_open_company($_REQUEST);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function get_my_company(){

        $_REQUEST['uid']  =check_null(40002,true,$_REQUEST['uid']);

        $rs = D('Company/Audience')->get_my_company($_REQUEST);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);

    }


}
?>