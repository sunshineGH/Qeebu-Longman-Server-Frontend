<?php
class DepartmentModel extends Model
{
	private $department_cows = 'a.`department_id`, a.`department_name`, a.`local_id`, a.`building_id`, a.`floor_id`';

//-------------------------------------insert--------------------------------------
	public function add_department($data)
	{
		$data['time_insert'] = time();
		$data['time_update'] = time();
		$data['time_delete'] = -1;

		return M('audience_department','','DB_MEETING')->add($data);
	}
//-------------------------------------update--------------------------------------

	public function upt_department($data){
		$data['time_update'] = time();

		$map = [
			'department_id'=>$data['department_id']
		];
		return M('audience_department','','DB_MEETING')->where($map)->save($data);
	}

	public function move_audience_to_other_department($data){
		$data['time_update'] = time();
		return M('audience_department_rds','','DB_MEETING')->where('rds_id='.$data['rds_id'])->save($data);
	}
//
	public function copy_audience_to_other_department($data){
		$data['time_update'] = time();
		$data['time_insert'] = time();
		$data['time_delete'] = 0;
		$rs = M('audience_department_rds','','DB_MEETING')->add($data);

		$data1['time_update'] = time();
		$data1['department_id']=$data['department_id'];
		$this->upt_department($data1);
		return $rs;

	}
//-------------------------------------delete--------------------------------------

	public function del_department($data){
		/*
		 * 1、删除部门
		 * 2、删除部门下面的人
		 *
		 * 4、递归
		 * */

		return $this->delete_user_by_department_id($data['department_id']);
	}

	public function delete_user_by_department_id($department_id){

		//删除底下成员
		$audience_ids = $this->get_audience_id_by_department_id($department_id);
		foreach($audience_ids as $val){
			$val['delete_user'] = $val['audience_id'];
			$this->delete_audience_from_current_department($val);
		}

		//删除他们底下的成员
		$map = [
			'department_pid'=>$department_id,
			'_string'	   =>'time_insert > time_delete'
		];
		$rs = M('audience_department','','DB_MEETING')->where($map)->select();
		if($rs){
			foreach ($rs as $val) {
				$this->delete_user_by_department_id($val['department_id']);
			}
		}
		return $this->del_department_by_id($department_id);
	}

	public function del_department_by_id($department_id){

		$map = [
			'department_id'=>$department_id,
			'_string'	   =>'time_insert > time_delete'
		];
		$rs = M('audience_department','','DB_MEETING')->where($map)->save(
			['time_delete'=>time()]
		);
		return $rs;
	}

	public function delete_audience_from_current_department($data){

		//判断部门下面的人是否在其他分组，不在则将他加入到未分组中
		$map = [
			'audience_id'=>$data['delete_user'],
			'_string'	 =>'time_insert > time_delete'
		];
		$count = M('audience_department_rds','','DB_MEETING')->field('rds_id')->where($map)->count();


		$map=[
			'rds_id'=>$data['rds_id'],
			'_string'	 =>'time_insert > time_delete'
		];
		if($count > 1){
			$rs = M('audience_department_rds','','DB_MEETING')->where($map)->save(['time_delete'=>time()]);
			$this->upt_department(['department_id'=> $data['department_id']]);
		}else{
			$rs = M('audience_department_rds','','DB_MEETING')->where($map)->save([
				'time_update'	=>time(),
				'department_id' =>2 //移动到未分组
			]);
			$this->upt_department(['department_id'=> 2]);
		}
		return $rs;

	}

//-------------------------------------select--------------------------------------

	public function get_department_name_by_audience_id($audience_id){
		$department_id = M('audience_department_rds','','DB_MEETING')->where('audience_id='.$audience_id.' and time_insert>time_delete')->getField('department_id');
		return $this->get_department_name_by_id($department_id);
	}

	public function get_department_id_by_audience_id($audience_id){
		return M('audience_department_rds','','DB_MEETING')->where('audience_id='.$audience_id.' and time_insert>time_delete')->getField('department_id');

	}

	public function get_department_by_uid($uid){
		$department_cows = 'a.`department_id`, a.`department_name`, a.`local_id`, a.`building_id`, a.`floor_id`';

		$map = ['audience_id'=>$uid];
		$audience_department = M('audience','','DB_MEETING')->where($map)->getField('audience_department');

		$map = ['department_id'=>$audience_department];
		return M('audience_department', '', 'DB_MEETING')->field($department_cows)->where($map)->find();
	}

	public function get_department_personel($uid){
		$sql = 'select personnel_id from department_personnel as a';
		$sql .= ' join audience as b on b.audience_department=a.department_id';
		$sql .= ' where b.audience_id=' . $uid;
		$sql .= ' limit 1';
		$rs = M('department_personnel', '', 'DB_MEETING')->query($sql);
		return $rs[0]['personnel_id'];
	}

	public function get_audience_num($department_id){
		$num = $this->get_son_department_num($department_id);
		$num += $this->get_son_audience($department_id);
		return $num;
	}

	public function get_son_department_num($department_id){
		return M('audience_department', '', 'DB_MEETING')->where('time_insert > time_delete and department_pid = ' . $department_id)->count();
	}

	public function get_son_audience($department_id)
	{
		return M('audience_department_rds', '', 'DB_MEETING')->where('time_insert > time_delete and department_id = ' . $department_id)->count();
	}

	public function get_department_name_by_id($department_id)
	{
		return M('audience_department','','DB_MEETING')->where('department_id='.$department_id)->getField('department_name');
	}
//查询当前用户是否已经在当前部门
	public function get_audience_department_rds_id($data){
		return M('audience_department_rds','','DB_MEETING')->where('department_id='.$data['department_id'].' and audience_id='.$data['audience_id'].' and time_insert>time_delete')->find();
	}

	public function get_audience_id_by_department_id($department_id){

		$map = [
			'department_id'=>$department_id,
			'_string'	   =>'time_insert>time_delete'
		];
		return M('audience_department_rds','','DB_MEETING')->where($map)->select();

	}






}

?>