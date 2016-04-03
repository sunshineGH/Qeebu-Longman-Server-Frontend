<?php
class ProjectModel extends Model{
	
	public function get_project_name_by_pid($pid){
		return M('project','','DB_MEETING')->where('project_id='.$pid)->getField('project_name');	
	}	
	
	public function search_projects($data){
		$sql ='select * from project';
		$sql.=' where INSTR (project_name,"'.$data['conditions'].'")';
		$sql.=' order by project_endTime desc';
		$sql.=' limit 20';
		return M('project','','DB_MEETING')->query($sql);
	}
	
	public function get_project_list($data){
		$sql = "select * from project";
		$sql.= " where 1=1";
		$sql.= " order by project_endTime desc";
		$sql.= " limit ".(($data['page']-1)*$data['count']).",".$data['count'];
		return M("project","",'DB_MEETING')->query($sql);
	}

    public function create_project($data){
        return M('project','','DB_MEETING')->add($data);
    }
}
?>