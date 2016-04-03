<?php
class DatabaseAction extends CommonAction{
/*
 *获取sqlite地址
 ***/
public function get_sqlite_path(){
	$rs=D('Database')->get_sqlite_path();
	if($rs)
		return_json(0,$rs);
	else
		return_json(40001);
}

	public function drop_database_l(){
		Database(100);
		for($i=1058;$i<=1410;$i++){
			$sql = 'drop database teamin_'.$i;
			$r=M('audience','','DB_MEETING')->query($sql);
			dump($r);
		}
	}

	public function delete_company(){
		$cid = $_GET['cid'];
		if(!$cid){
			echo 'miss cid';
			exit();
		}
		//删除关系
		M('audience_with_company','','')->db('CONFIG1')->where('cid='.$cid)->delete();
		dump(M('audience_with_company','','')->_sql());
		//删除公司
		M('company','','')->db('CONFIG1')->where('company_id='.$cid)->delete();
		dump(M('company','','')->_sql());
		//删除公司数据库
		M('company','','')->db('CONFIG1')->query('drop database teamin_'.$cid);
		dump(M('company','','')->_sql());
		//删除公司app关联
		M('company_app_rds','','')->db('CONFIG1')->where('cid='.$cid)->delete();
		dump(M('company_app_rds','','')->_sql());
		//删除push_notice

		$rs=M('push_notice','','')->db('CONFIG1')->where('company_id='.$cid)->select();
		foreach($rs as $val){
			M('push_notice_user','','')->db('CONFIG1')->where('push_notice_id='.$val['id'])->delete();
			dump(M('push_notice_user','','')->_sql());
		}
		M('push_notice','','')->db('CONFIG1')->where('company_id='.$cid)->delete();
		dump(M('push_notice','','')->_sql());
	}

	public function create_tables(){
		$rs = M('push_notice_user','','')->db('CONFIG1')->query('show databases like "teamin_%"');
		foreach($rs as $val){
			$sql = "CREATE TABLE IF NOT EXISTS `".$val['Database (teamin_%)']."`.`meeting_push` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `meeting_id` int(11) NOT NULL,
			  `audience_id` int(11) NOT NULL,
			  `push_time` int(11) NOT NULL,
			  `befor_time` int(11) NOT NULL,
			  `time_update` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$rs=M('push_notice_user','','')->db('CONFIG1')->query($sql);
		}
	}

	public function create_tables_task_share_log(){

		$rs = M('push_notice_user','','')->db('CONFIG1')->query('show databases like "teamin_%"');
		foreach($rs as $val){
			$sql = "CREATE TABLE IF NOT EXISTS  `".$val['Database (teamin_%)']."`.`task_share_log` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
			  `task_id` int(11) NOT NULL,
			  `task_share_name` varchar(100) NOT NULL,
			  `task_share_members_id` text NOT NULL,
			  `task_is_department` tinyint(4) NOT NULL,
			  `task_at_members_id` text NOT NULL,
			  `task_members_department_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$rs=M('push_notice_user','','')->db('CONFIG1')->query($sql);
		}
	}

	public function create_tables_task_execution(){

		$rs = M('push_notice_user','','')->db('CONFIG1')->query('show databases like "teamin_%"');
		foreach($rs as $val){

			$sql = "CREATE TABLE IF NOT EXISTS `".$val['Database (teamin_%)']."`.`task_execution` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `task_id` int(11) NOT NULL,
						  `uid` int(11) NOT NULL,
						  `time_insert` int(11) NOT NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `task_uid_id` (`task_id`,`uid`)
						) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

			$rs=M('push_notice_user','','')->db('CONFIG1')->query($sql);
		}
	}

	public function create_tables_sign_in(){

		$rs = M('push_notice_user','','')->db('CONFIG1')->query('show databases like "teamin_%"');
		foreach($rs as $val){
			$sql = "CREATE TABLE IF NOT EXISTS `".$val['Database (teamin_%)']."`.`sign_in` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `uid` int(11) NOT NULL,
				  `local` varchar(200) CHARACTER SET utf8 NOT NULL,
				  `long` varchar(100) CHARACTER SET utf8 NOT NULL,
				  `lat` varchar(100) CHARACTER SET utf8 NOT NULL,
				  `range` int(11) NOT NULL,
				  `start_time` int(11) NOT NULL,
				  `end_time` int(11) NOT NULL,
				  `date` date NOT NULL,
				  `open_time` int(11) NOT NULL,
				  `time_insert` int(11) NOT NULL,
				  `time_update` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
			M('push_notice_user','','')->db('CONFIG1')->query($sql);

			$sql ="
				CREATE TABLE IF NOT EXISTS `".$val['Database (teamin_%)']."`.`sign_in_user` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `sign_in_id` int(11) NOT NULL,
				  `uid` int(11) NOT NULL,
				  `long` varchar(100) CHARACTER SET utf8 NOT NULL,
				  `lat` varchar(100) CHARACTER SET utf8 NOT NULL,
				  `local` varchar(100) CHARACTER SET utf8 NOT NULL,
				  `date` date NOT NULL,
				  `state` int(11) NOT NULL,
				  `time_insert` int(11) NOT NULL,
				  `time_update` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
			$rs=M('push_notice_user','','')->db('CONFIG1')->query($sql);
		}
	}

	public function update_table_sign_in(){
		$rs = M('push_notice_user','','')->db('CONFIG1')->query('show databases like "teamin_%"');
		foreach($rs as $val){
			$sql = "ALTER TABLE  `".$val['Database (teamin_%)']."`.`sign_in_user` CHANGE  `time_start`  `start_time` INT( 11 ) NOT NULL";
			$rs=M('push_notice_user','','')->db('CONFIG1')->query($sql);
			echo $sql;
		}
	}

	public function update_tables(){
		$rs = M('push_notice_user','','')->db('CONFIG1')->query('show databases like "teamin_%"');
		foreach($rs as $val){
			$sql = "DROP TABLE  ".$val['Database (teamin_%)'].".`leave`";
			M('push_notice_user','','')->db('CONFIG1')->query($sql);
			echo $sql;
			$sql = "CREATE TABLE IF NOT EXISTS `".$val['Database (teamin_%)']."`.`leave` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `audience_id` int(11) NOT NULL,
				  `approval_uid` int(11) NOT NULL,
				  `start_time` int(11) NOT NULL,
				  `end_time` int(11) NOT NULL,
				  `type_id` int(11) NOT NULL,
				  `reason` text NOT NULL,
				  `approval_reason` text NOT NULL,
				  `state` tinyint(1) NOT NULL,
				  `time_insert` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$rs=M('push_notice_user','','')->db('CONFIG1')->query($sql);
			echo $sql;
		}
	}



	public function update_leave_type(){
		$rs = M('push_notice_user','','')->db('CONFIG1')->query('show databases like "teamin_%"');
		foreach($rs as $val){
			$sql = "DROP TABLE  ".$val['Database (teamin_%)'].".`leave_type`";
			M('push_notice_user','','')->db('CONFIG1')->query($sql);
			echo $sql;
			$sql = "CREATE TABLE IF NOT EXISTS  `".$val['Database (teamin_%)']."`.`leave_type` (
			  `type_id` int(11) NOT NULL AUTO_INCREMENT,
			  `type_name` varchar(50) NOT NULL,
			  PRIMARY KEY (`type_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;";
			$rs=M('push_notice_user','','')->db('CONFIG1')->query($sql);
			echo $sql.'<br/>';
			$sql = "INSERT INTO  `".$val['Database (teamin_%)']."`.`leave_type` (`type_id`, `type_name`) VALUES
			(1, '事假'),
			(2, '病假'),
			(3, '产假'),
			(4, '年假'),
			(5, '婚假'),
			(6, '丧假'),
			(7, '护理假');";
			$rs=M('push_notice_user','','')->db('CONFIG1')->query($sql);
			echo $sql.'<br/>';
		}
	}



}
?>