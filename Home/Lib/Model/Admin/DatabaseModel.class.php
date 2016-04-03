<?php
class DatabaseModel extends Model{
/*
 *get_sqlite_path
 *获取最新一条database
 ***/
public function get_sqlite_path(){
	return M('sqlite_db','','')->db(1,'DB_CONFIG1')->order('sqlite_id desc')->getField('sqlite_path');	
}
	
	
}
?>