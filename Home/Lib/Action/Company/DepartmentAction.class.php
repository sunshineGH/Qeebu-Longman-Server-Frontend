<?php

class DepartmentAction extends Action{

    public function add_department(){
        if(!Database($_REQUEST['cid']))return;

        $_REQUEST['department_name']  	= check_null(40100,true,$_REQUEST['department_name']);
        $_REQUEST['department_pid']  	= check_null(40101,true,$_REQUEST['department_pid']);

        $rs = D('Department')->add_department($_REQUEST);

        if($rs)
            return_json(0,$rs);
        else
            return_json(-1);
    }

    public function upt_department(){
        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['department_id'] = check_null(40103,true,$_REQUEST['department_id']);

        //移动部门的时候不能将自己移动到自己上

        if(isset($_REQUEST['department_pid']) && $_REQUEST['department_id']==$_REQUEST['department_pid']){
            return_json(40103);return;
        }

        $rs = D('Department')->upt_department($_REQUEST);

        if($rs)
            return_json(0);
        else
            return_json(-1);
    }

    public function del_department(){

        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['department_id'] = check_null(40103,true,$_REQUEST['department_id']);

        $rs = D('Department')->del_department($_REQUEST);

        if($rs)
            return_json(0);
        else
            return_json(-1);
    }

    public function move_audience_to_other_department(){

        if(!Database($_REQUEST['cid']))return;

        $data = [
            'rds_id'        =>check_null(40109,true,$_REQUEST['rds_id']),
            'uid'           =>check_null(40002,true,$_REQUEST['uid']),
            'audience_id'   =>check_null(40002,true,$_REQUEST['uid']),
            'department_id' =>check_null(40103,true,$_REQUEST['department_id'])
        ];

        if(D('Department')->get_audience_department_rds_id($data)){
            return_json(40113);
            exit();
        }

        $rs = D('Department')->move_audience_to_other_department($data);

        if($rs)
            return_json(0);
        else
            return_json(-1);

    }

    public function copy_audience_to_other_department(){

        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['department_id'] = check_null(40103,true,$_REQUEST['department_id']);
        $_REQUEST['audience_id']   = check_null(40002,true,$_REQUEST['uid']);

        if(D('Department')->get_audience_department_rds_id($_REQUEST)){
            return_json(40113);
            exit();
        }

        $rs = D('Department')->copy_audience_to_other_department($_REQUEST);

        if($rs){
            return_json(0);
        }else{
            return_json(-1);
        }
    }

    public function delete_audience_from_current_department(){

        if(!Database($_REQUEST['cid']))return;

        $data = [
            'cid'           =>check_null(40038,true,$_REQUEST['cid']),
            'uid'           =>check_null(40002,true,$_REQUEST['uid']),
            'rds_id'        =>check_null(40109,true,$_REQUEST['rds_id']),
            'delete_user'   =>check_null(40110,true,StrCode($_REQUEST['delete_user'],'DECODE')),
            'department_id' =>check_null(40103,true,$_REQUEST['department_id'])
        ];
        $rs1= D('Company/Team')->get_team_admin_by_uid($data['cid'],$data['uid']);
        $rs2= D('Company/Team')->get_team_admin_by_uid($data['cid'],$data['delete_user']);
        if(!$rs1){
            return_json(40107);return;
        }else if($rs2['audience_character']==1){
            return_json(40107);return;
        }
        $rs = D('Department')->delete_audience_from_current_department($data);

        if($rs){
            return_json(0);
        }else{
            return_json(-1);
        }
    }
}