<?php
class SqliteModel extends Model{
	//通讯录数据库字段
	private $audience_list_cows='`audience_id`,`audience_tel`,`audience_email`,`audience_name`,`audience_position`,`audience_portrait`,`audience_sex`,`audience_age`,`audience_qq`,`audience_weixin`,`audience_imUsername` ,`audience_imPassword`,`department_name` as `audience_department`';
	private $department_list_cows = '`department_id`, `department_name`, `department_pid`, `floor_id`, `leader_id`';
	private $audience_department_rds_list_cows= '`rds_id`,`department_id`,`audience_id`';
	//事物跟踪表数据库字段
	private $event_list_cows	='a.event_id,a.event_name,a.event_description,a.project_id,a.event_image,a.event_start_time,a.event_end_time,a.event_current_audience,a.event_current_date,a.event_current_state,a.event_current_responds,a.event_launch_id,a.event_examination_id,a.event_execution_id,a.event_acceptance_id,b.audience_name as event_launch_name,c.audience_name as event_examination_name,b.audience_portrait as event_launch_portrait,c.audience_portrait as event_examination_portrait,f.project_name';
	private $event_log_cows 	='a.log_id,a.log_type,a.audience_id,a.event_id,a.event_state,a.event_responds,a.time_update as event_time_update,b.audience_name';
	private $event_notice_cows	='a.`notice_id`, b.audience_name ,a.`event_id`,a.`event_state`,a.`time_insert`,a.`time_delete`';
	private $event_project_cows	='project_id,project_name';
/**
 * get_database
 * 同步数据库
 *
 * @param  	string 	$table 		表
 * @param  	string 	$fields 	字段集
 * @param  	string 	$field 		字段
 * @param  	int		$last 		最后同步时间
 * @return 	array
 */
    public function get_database_synchronization($table,$fields,$field,$last,$where=[]){

        $sql['insert']='select '.$fields.' from '.$table.' where time_insert>'.$last.' and time_delete < time_insert';
        $sql['update']='select '.$fields.' from '.$table.' where time_insert<'. $last.' and time_update>='.$last.' and time_delete < time_insert ';
        $sql['delete']='select '.$field .' from '.$table.' where time_insert<='. $last.' and time_delete > '.$last;
        $add_list 	= M("","","DB_MEETING")->query($sql['insert']);
        $update_list= M("","","DB_MEETING")->query($sql['update']);
        $delete_list= M("","","DB_MEETING")->query($sql['delete']);

        if($add_list==null || count($add_list)==0)$re['insert']=null;
        else $re['insert'] = $add_list;
        if($update_list==null || count($update_list)==0)$re['update']=null;
        else $re['update'] = $update_list;
        if($delete_list==null || count($delete_list)==0)$re['delete']=null;
        else $re['delete'] = $delete_list;

        return $re;
    }

	function get_database($category){
		return M('sqlite','','DB_MEETING')->field('sqlite_path,time_insert as timestamp')->where('sqlite_category='.$category)->order('sqlite_id desc')->find();	
	}	
	
	function get_audience_database_synchronization($last){

		$audience_list_cows = '`audience_id`,`audience_tel`,`audience_email`,`audience_name`,`audience_position`,`audience_portrait`,`audience_sex`,`audience_age`,`audience_qq`,`audience_weixin`,`audience_imUsername` ,`audience_imPassword`';

		$sql['insert']='select '.$audience_list_cows.' from audience a where audience_state!=0 and a.time_insert>'.$last.' and a.time_delete < a.time_insert';
		$sql['update']='select '.$audience_list_cows.' from audience a where audience_state!=0 and a.time_insert<'.$last.' and a.time_update>='.$last.' and a.time_delete < a.time_insert ';
		$sql['delete']='select '.$audience_list_cows.' from audience a where audience_state!=0 and a.time_insert<='.$last.' and a.time_delete > '.$last;
		$add_list 	= M('','','DB_MEETING')->query($sql['insert']);
		$update_list= M('','','DB_MEETING')->query($sql['update']);
		$delete_list= M('','','DB_MEETING')->query($sql['delete']);

		if($add_list==null || count($add_list)==0)
			$re['insert']=null;
		else{
			$arr = array();
			foreach($add_list as $val){
				$department = D('Department')->get_department_name_by_audience_id($val['audience_id']);
				$val['audience_department'] = $department?$department:'';
				$arr[]= $val;
			}
			$re['insert'] = $arr;
		}

		if($update_list==null || count($update_list)==0){
			$re['update']=null;
		}else{
			$arr = array();
			foreach($update_list as $val){
				$department = D('Department')->get_department_name_by_audience_id($val['audience_id']);
				$val['audience_department'] = $department?$department:'';
				$arr[]= $val;
			}
			$re['update'] = $arr;
		}

		if($delete_list==null || count($delete_list)==0){
			$re['delete']=null;
		}else{
			$arr = array();
			foreach($delete_list as $val){
				$val['audience_department'] = '';
				$arr[]= $val;
			}
			$re['delete'] = $arr;
		}
		
		return $re;	
	}
	
	function get_department_database_synchronization($last){
		$sql['insert']='select '.$this->department_list_cows.' from audience_department where time_insert>'.$last.' and time_delete < time_insert';
		$sql['update']='select '.$this->department_list_cows.' from audience_department where time_insert<'.$last.' and time_update>='.$last.' and time_delete < time_insert ';
		$sql['delete']='select '.$this->department_list_cows.' from audience_department where time_insert<='.$last.' and time_delete > '.$last;
		$add_list 	= M('','','DB_MEETING')->query($sql['insert']);
		$update_list= M('','','DB_MEETING')->query($sql['update']);
		$delete_list= M('','','DB_MEETING')->query($sql['delete']);

		if($add_list==null || count($add_list)==0)
			$re['insert']=null;
		else
		{
			foreach($add_list as $val)
			{
				$val['audience_num'] = ''.D('Department')->get_audience_num($val['department_id']);
				$val['have_child']   = $val['audience_num'] == '0' ? '0' : '1';
				$re['insert'][]= $val;
			}
			$val = null ; unset($val);
		}

		if($update_list==null || count($update_list)==0)
			$re['update']=null;
		else
		{
			foreach($update_list as $val)
			{
				$val['audience_num'] = ''.D('Department')->get_audience_num($val['department_id']);
				$val['have_child']   = $val['audience_num'] == '0' ? '0' : '1';
				$re['update'][]= $val;
			}
		}

		if($delete_list==null || count($delete_list)==0)
			$re['delete']=null;
		else
			$re['delete'] = $delete_list;
		
		return $re;	
	}
	
	function get_audience_department_rds_database_synchronization($last){
		
		$sql['insert']='select '.$this->audience_department_rds_list_cows.' from audience_department_rds where time_insert>'.$last.' and time_delete < time_insert';
		$sql['update']='select '.$this->audience_department_rds_list_cows.' from audience_department_rds where time_insert<'.$last.' and time_update>='.$last.' and time_delete < time_insert ';
		$sql['delete']='select '.$this->audience_department_rds_list_cows.' from audience_department_rds where time_insert<='.$last.' and time_delete > '.$last;
		$add_list 	= M('','','DB_MEETING')->query($sql['insert']);
		$update_list= M('','','DB_MEETING')->query($sql['update']);
		$delete_list= M('','','DB_MEETING')->query($sql['delete']);
		if($add_list==null || count($add_list)==0)$re['insert']=null;
		else $re['insert'] = $add_list;
		if($update_list==null || count($update_list)==0)$re['update']=null;
		else $re['update'] = $update_list;
		if($delete_list==null || count($delete_list)==0)$re['delete']=null;
		else $re['delete'] = $delete_list;
		
		return $re;	
	}
	
	function get_event_database_synchronization($data){

		$last=$data['timestamp'];
		$sql1 ='select '.$this->event_list_cows.' from event a';
		$sql1.=' join audience b on a.event_launch_id=b.audience_id';
		$sql1.=' join audience c on a.event_examination_id=c.audience_id';
		$sql1.=' join project f on a.project_id=f.project_id';

		$sql2 =$sql1.' where a.event_launch_id='.$data['audience_id'];
		$sql3 =$sql1.' where a.event_examination_id='.$data['audience_id'];
		$sql4 =$sql1.' where a.event_execution_id='.$data['audience_id'];
		$sql5 =$sql1.' where a.event_acceptance_id='.$data['audience_id'];
		$insert=' and a.time_insert>'.$last.' and a.time_delete < a.time_insert';
		$update=' and a.time_insert<'.$last.' and a.time_update>='.$last.' and a.time_delete < a.time_insert ';
		$delete=' and a.time_insert<='.$last.' and a.time_delete > '.$last;
		$sql['insert']=$sql2.$insert.' union '.$sql3.$insert.' union '.$sql4.$insert.' union '.$sql5.$insert;
		$sql['update']=$sql2.$update.' union '.$sql3.$update.' union '.$sql4.$update.' union '.$sql5.$update;
		$sql['delete']=$sql2.$delete.' union '.$sql3.$delete.' union '.$sql4.$delete.' union '.$sql5.$delete;
		$add_list 	= M('','','DB_MEETING')->query($sql['insert']);
		$update_list= M('','','DB_MEETING')->query($sql['update']);
		$delete_list= M('','','DB_MEETING')->query($sql['delete']);

        foreach($add_list as $val)
        {
            if($val['event_execution_id'] != 0)
            {
                $re = D('Audience')->get_audience($val['event_execution_id']);
                $val['event_execution_name']    =$re['audience_name'];
                $val['event_execution_portrait']=$re['audience_portrait'];
            }else{
                $val['event_execution_name']='';
                $val['event_execution_portrait']='';
            }
            if($val['event_acceptance_id'] != 0)
            {
                $re = D('Audience')->get_audience($val['event_acceptance_id']);
                $val['event_acceptance_name']=$re['audience_name'];
                $val['event_acceptance_portrait']=$re['audience_portrait'];
            }else{
                $val['event_acceptance_name']='';
                $val['event_acceptance_portrait']='';
            }
            $arr[]=$val;
        }
        $add_list = $arr;
        $arr=null;unset($arr);

        foreach($update_list as $val)
        {
            if($val['event_execution_id'] != 0)
            {
                $re = D('Audience')->get_audience($val['event_execution_id']);
                $val['event_execution_name']    =$re['audience_name'];
                $val['event_execution_portrait']=$re['audience_portrait'];
            }else{
                $val['event_execution_name']='';
                $val['event_execution_portrait']='';
            }
            if($val['event_acceptance_id'] != 0)
            {
                $re = D('Audience')->get_audience($val['event_acceptance_id']);
                $val['event_acceptance_name']=$re['audience_name'];
                $val['event_acceptance_portrait']=$re['audience_portrait'];
            }else{
                $val['event_acceptance_name']='';
                $val['event_acceptance_portrait']='';
            }
            $arr[]=$val;
        }
        $update_list = $arr;
        $arr=null;unset($arr);

		if($add_list==null || count($add_list)==0)$re['insert']=null;
		else $re['insert'] = $add_list;
		if($update_list==null || count($update_list)==0)$re['update']=null;
		else $re['update'] = $update_list;
		if($delete_list==null || count($delete_list)==0)$re['delete']=null;
		else $re['delete'] = $delete_list;
		
		return $re;	
	}
	
	function get_event_log_database_synchronization($data){		
		
		$last=$data['timestamp'];
		$sql1 ='select '.$this->event_log_cows.' from event_log a';
		$sql1.=' join audience b on a.audience_id=b.audience_id';
		$sql1.=' join event c on a.event_id=c.event_id';
		$sql2 =$sql1.' where c.event_launch_id='.$data['audience_id'];
		$sql3 =$sql1.' where c.event_examination_id='.$data['audience_id'];
		$sql4 =$sql1.' where c.event_execution_id='.$data['audience_id'];
		$sql5 =$sql1.' where c.event_acceptance_id='.$data['audience_id'];
		
		$insert=' and a.time_insert>'.$last.' and a.time_delete < a.time_insert';
		$update=' and a.time_insert<'.$last.' and a.time_update>='.$last.' and a.time_delete < a.time_insert ';
		$delete=' and a.time_insert<='.$last.' and a.time_delete > '.$last;
		$sql['insert']=$sql2.$insert.' union '.$sql3.$insert.' union '.$sql4.$insert.' union '.$sql5.$insert;
		$sql['update']=$sql2.$update.' union '.$sql3.$update.' union '.$sql4.$update.' union '.$sql5.$update;
		$sql['delete']=$sql2.$delete.' union '.$sql3.$delete.' union '.$sql4.$delete.' union '.$sql5.$delete;
		
		$add_list 	= M('','','DB_MEETING')->query($sql['insert']);
		$update_list= M('','','DB_MEETING')->query($sql['update']);
		$delete_list= M('','','DB_MEETING')->query($sql['delete']);
		if($add_list==null || count($add_list)==0)$re['insert']=null;
		else $re['insert'] = $add_list;
		if($update_list==null || count($update_list)==0)$re['update']=null;
		else $re['update'] = $update_list;
		if($delete_list==null || count($delete_list)==0)$re['delete']=null;
		else $re['delete'] = $delete_list;
		return $re;
	}
	
	function get_event_notice_database_synchronization($data){
		$last=$data['timestamp'];
		$sql1 ='select '.$this->event_notice_cows.' from event_notice a';
		$sql1.=' join audience b on a.audience_id=b.audience_id';
		$sql1.=' where a.audience_id='.$data['audience_id'];
		$sql['insert']=$sql1.' and a.time_insert>'.$last.' and a.time_delete < a.time_insert';
		$sql['update']=$sql1.' and a.time_insert<'.$last.' and a.time_update>='.$last.' and a.time_delete < a.time_insert ';
		$sql['delete']=$sql1.' and a.time_insert<='.$last.' and a.time_delete > '.$last;
		$add_list 	= M('','','DB_MEETING')->query($sql['insert']);
		$update_list= M('','','DB_MEETING')->query($sql['update']);
		$delete_list= M('','','DB_MEETING')->query($sql['delete']);
		if($add_list==null || count($add_list)==0)$re['insert']=null;
		else $re['insert'] = $add_list;
		if($update_list==null || count($update_list)==0)$re['update']=null;
		else $re['update'] = $update_list;
		if($delete_list==null || count($delete_list)==0)$re['delete']=null;
		else $re['delete'] = $delete_list;
		return $re;
	}

//获取用户audience_info Register version
	function new_get_audience_database_synchronization($last){

		$audience_list_cows='`audience_id`,`audience_tel`,`audience_email`,`audience_name`,"" as `audience_position`,`audience_portrait`,`audience_sex`,`audience_age`,`audience_qq`,`audience_weixin`,"" as `audience_imUsername` ,"" as `audience_imPassword`,"" as `audience_department`,audience_character';

		$map_insert['a.cid'] = $_REQUEST['cid'];
		$map_insert['a.audience_character'] = array('in','1,2,3');
		$map_insert['a.time_insert'] = array('gt',$last);
		$map_insert['_string']     = 'a.time_delete < a.time_insert';
		$add_list=M('audience_with_company','','')->alias('a')
			->join('join audience as b on a.uid=b.audience_id')
			->field($audience_list_cows)
			->where($map_insert)
			->db('CONFIG1')
			->select();

		$map_update['a.cid'] = $_REQUEST['cid'];
		$map_update['a.audience_character'] = array('in','1,2,3');
		$map_update['a.time_insert'] = array('elt',$last);
		$map_update['a.time_update'] = array('egt',$last);
		$map_update['_string']     = 'a.time_delete < a.time_insert';
		$update_list=M('audience_with_company','','')->alias('a')
			->join('join audience as b on a.uid=b.audience_id')
			->field($audience_list_cows)
			->where($map_update)
			->db('CONFIG1')
			->select();

		$map_delete['a.cid'] = $_REQUEST['cid'];
		$map_delete['a.audience_character'] = array('in','1,2,3');
		$map_delete['a.time_insert'] = array('elt',$last);
		$map_delete['a.time_delete'] = array('gt',$last);
		$delete_list=M('audience_with_company','','')->alias('a')
			->join('join audience as b on a.uid=b.audience_id')
			->field('audience_id')
			->where($map_delete)
			->db('CONFIG1')
			->select();

		if($add_list==null || count($add_list)==0)
			$re['insert']=null;
		else{
			$arr = array();
			foreach($add_list as $val){
				$val['audience_id'] = StrCode($val['audience_id']);
				$arr[]=$val;
			}
			$re['insert'] = $arr;
		}
		if($update_list==null || count($update_list)==0)$re['update']=null;
		else{
			$arr = array();
			foreach($update_list as $val){
				$val['audience_id'] = StrCode($val['audience_id']);
				$arr[]=$val;
			}
			$re['update'] = $arr;
		}
		if($delete_list==null || count($delete_list)==0)$re['delete']=null;
		else{
			$arr = array();
			foreach($delete_list as $val){
				$val['audience_id'] = StrCode($val['audience_id']);
				$arr[]=$val;
			}
			$re['delete'] = $arr;
		}

		return $re;
	}

	function new_get_audience_department_rds_database_synchronization($last){

		$sql['insert']='select '.$this->audience_department_rds_list_cows.' from audience_department_rds where time_insert>'.$last.' and time_delete < time_insert';
		$sql['update']='select '.$this->audience_department_rds_list_cows.' from audience_department_rds where time_insert<'.$last.' and time_update>='.$last.' and time_delete < time_insert ';
		$sql['delete']='select '.$this->audience_department_rds_list_cows.' from audience_department_rds where time_insert<='.$last.' and time_delete > '.$last;

		$add_list 	= M('audience_department_rds','','DB_MEETING')->query($sql['insert']);
		$update_list= M('audience_department_rds','','DB_MEETING')->query($sql['update']);
		$delete_list= M('audience_department_rds','','DB_MEETING')->query($sql['delete']);

		if($add_list==null || count($add_list)==0)$re['insert']=null;
		else{
			foreach($add_list as $val){
				$val['audience_id'] = StrCode($val['audience_id']);
				$arr[]=$val;
			}
			$re['insert'] = $arr;
		}

		if($update_list==null || count($update_list)==0)$re['update']=null;
		else{
			foreach($update_list as $val){
				$val['audience_id'] = StrCode($val['audience_id']);
				$arr[]=$val;
			}
			$re['update'] = $arr;
		}
		if($delete_list==null || count($delete_list)==0)
			$re['delete']=null;
		else{
			foreach($delete_list as $val){
				$val['audience_id'] = StrCode($val['audience_id']);
				$arr[]=$val;
			}
			$re['delete'] = $arr;
		}

		return $re;
	}
}
?>