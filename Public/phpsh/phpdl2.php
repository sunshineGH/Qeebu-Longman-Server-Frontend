<?php
	ini_set('max_execution_time', '0');
	class db{
		public $conn;  
	    public static $sql;  
		public static $instance=null;
		private function __construct(){  
	        require_once('db.config.php');  
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
	         
	        self::$sql = "select * from {$table}";  
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
	   
	     public function delete($table){     
	          
			self::$sql = "delete from {$table}";  
	        return mysql_query(self::$sql);  
	          
	     }  
	      
	    public static function getLastSql(){  
	        echo self::$sql;  
	    }  
	      
	}  
	  
	$db = db::getInstance();	
	while(true){
		$list = $db->select('tme_email');
		if($list){
			$db->delete('tme_email');
		}else if(empty($list)){
			sleep(30);
		}
	}
	
	
?>