<?php
class ProjectAction extends Action{
	//项目列表	
	public function get_projects(){
		if(!Database($_REQUEST["cid"]))exit;
		switch(''){
			case $_REQUEST["count"]:$_REQUEST['count']=10;
			case $_REQUEST['page']:$_REQUEST['page']=1;
		}
		$rs=D('Project')->get_project_list($_REQUEST);
		if($rs){
			return_json('0',$rs);
		}else{
			return_json('40001');
		}	
	}
	//项目查询
	public function search_project(){
		if(!Database($_REQUEST["cid"]))exit;
		switch(''){
			case $_REQUEST["conditions"]:return_json(40038);return;
		}
		$rs=D('Project')->search_projects($_REQUEST);
		if($rs){
			return_json('0',$rs);
		}else{
			return_json('40001');
		}
	}
/*
 * @name
 * 创建一个新的项目
 *
 * @arg
 * cid                  company id
 * uid                  用户ID
 * project_name         项目名称
 * project_description  项目描述
 * project_startTime    开始时间
 * project_endTime      开始时间
 * project_leader       项目负责人id
 *
 * @update
 * 2014/12/15 10:42 zhanghao
 * */
    public function create_new_project(){
        if(!Database($_REQUEST["cid"]))exit;
        $_REQUEST['uid']				   =check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['project_name']          =check_null(40081,true,$_REQUEST['project_name']);
        $_REQUEST['project_description']   =check_null(40082,true,$_REQUEST['project_description']);
        $_REQUEST['project_startTime']     =check_null(40083,true,$_REQUEST['project_startTime']);
        $_REQUEST['project_endTime']       =check_null(40084,true,$_REQUEST['project_endTime']);
        $_REQUEST['project_leader']        =check_null(40085,true,$_REQUEST['project_leader']);
        $_REQUEST['time_insert']           =time();
        $_REQUEST['time_update']           =-1;
        $_REQUEST['time_delete']           =-1;

        if(!D('Project')->create_project($_REQUEST)){
            return_json('-1');
        }
        return_json('0');
    }

}
?>