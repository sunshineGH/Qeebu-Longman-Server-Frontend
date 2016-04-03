<?php
	ini_set('max_execution_time', '0');
	class db{
		public $conn;  
	    	public static $sql;  
		public static $instance=null;
		private function __construct(){  
	        
		$db['host'] = "localhost";
		$db['user'] = "root";
		$db['password'] = "8d7108df6a";//0e4b14a0d0
		$db['database'] = "teamin_1411";
		$this->conn = mysql_connect($db['host'],$db['user'],$db['password']);  
	        if(!mysql_select_db($db['database'],$this->conn)){  
	            echo "失败";  
	        };  
	        mysql_query('set names utf8',$this->conn);         
	    }
		private function __clone(){}
		public static function getInstance(){  
	        if(is_null(self::$instance)){  
	            self::$instance = new db;  
	        }  
	        return self::$instance;  
	    }
		 
	    public function select($table){	        
	        self::$sql = "select * from meeting_push where push_time ";//< ".time();  
	        $result=mysql_query(self::$sql,$this->conn);
	        $resuleRow = array();  
	        $i = 0;  
	        while($row=mysql_fetch_assoc($result)){  
	            foreach($row as $k=>$v){
	                $resuleRow[$i][$k] = $v;  
	            }  
	            $i++;  
	        }  
	        return $resuleRow;
	    }
	   
	     public function delete($table,$id,$val){     
	        $where = $id."=".$val;
			self::$sql = "delete from {$table} where {$where}";  
	        return mysql_query(self::$sql);	          
	     }	      
	    public static function getLastSql(){  
	        echo self::$sql;  
	    }

		public function create_database($data){
			$fp1 = fopen("/data/htdocs/teamin/Public/Database/init_base.sql","w");
			if(!$fp1)echo 1;
			fwrite($fp1,"create database teamin_".$data['company_id'].';');
			fclose($fp1);

			$fp3 = fopen("/data/htdocs/teamin/Public/Database/init_data.sql","w");
			if(!$fp3)echo 3;
			fwrite($fp3,"use teamin_".$data['company_id'].';');
			fclose($fp3);

			shell_exec('/data/htdocs/teamin/Public/Database/new_company.sh teamin_'.$data['company_id']);

			$re['code'] = 1;
			$re['msg']  = $data['company_id'];
			return $re;
		}

		public function init_database_data($data){
			$database = 'teamin_'.$data['company_id'];
			$name  = $data['company_name'];
			$table = 'audience_department';
			$table2= 'audience_department_rds';
			$files = '`department_id`, `department_name`, `department_pid`, `audience_num`, `have_child`, `floor_id`, `leader_id`, `time_insert`, `time_update`, `time_delete`';
			$time  = time();

			$sql = "INSERT INTO {$database}.{$table} (`department_id` ,`department_name` ,`department_pid` ,`audience_num`,`have_child` ,`floor_id` ,`leader_id` ,`time_insert` ,`time_update` ,`time_delete`)VALUES (2 ,  '未分组',  '1',  '0',  '0',  '1',  '1',  '{$time}',  '{$time}',  '0');";
			echo $sql;
			mysql_query($sql);
			$sql = "INSERT INTO {$database}.{$table} (`department_id` ,`department_name` ,`department_pid` ,`audience_num`,`have_child` ,`floor_id` ,`leader_id` ,`time_insert` ,`time_update` ,`time_delete`)VALUES (1 ,  '{$name}',  '0',  '0',  '0',  '1',  '1',  '{$time}',  '{$time}',  '0');";
			echo $sql;
			mysql_query($sql);
			$sql = "INSERT INTO {$database}.{$table2} (`rds_id`, `audience_id`, `department_id`, `time_insert`, `time_update`, `time_delete`)VALUES (NULL ,{$data['uid']},  '2',{$time},{$time},  0);";
			echo $sql;
			mysql_query($sql);
		}
	}	  
	$db = db::getInstance();	
	while(true){
		$list = $db->select('wait_create_team');
		echo "<pre>";
		var_dump($list);
exit();
		foreach($list as $val){
			
		}
		if($list){
			foreach($list as $value){
				$data = $db->create_database($value);
				if($data['code']==1){
					$db->init_database_data($value);
					$dataDel = $db->delete('wait_create_team','company_id',$data['msg']);
				}
			}			
		}else if(empty($list)){
			sleep(30);
		}
	}
	
	
?>
