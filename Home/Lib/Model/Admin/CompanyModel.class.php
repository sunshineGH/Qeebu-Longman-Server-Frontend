<?php
class CompanyModel extends Model{

	public $open_company_cows = '`company_id`, `company_name`';
/*
 *check_disabled 查询当前公司id是否可用
 *arg
 *cid int
 *****/
	public function check_disabled($cid){
		return M('company','','')->db(1,'DB_CONFIG1')->where('company_id='.$_REQUEST["cid"])->getField('company_disabled');
	}

	public function get_open_company($data){

		$data['page'] 		 = $data['page']  == null? 1:$data['page'];
		$data['count']		 = $data['count'] == null?10:$data['count'];

		$sql = 'select '.$this->open_company_cows.' from company';
		$sql.= ' where is_open=1';

		$data['company_name'] != '' && $sql.=' and company_name like "%'.$data['company_name'].'%"';

		$sql.= ' order by company_id desc';
		$sql.= ' limit '.($data['page']-1)*$data['count'].','.$data['count'];

		$rs = M('company','','')->db(1,'DB_CONFIG1')->query($sql);

		foreach($rs as $val){
			$val['team_creater'] = D('Company/Team')->get_team_creater($val['company_id']);
			$val['team_creater'] == false && $val['team_creater'] = '匿名';
			$val['company_id']	 = StrCode($val['company_id']);
			$arr[]=$val;
		}

		return $arr;
	}

	public function get_my_company($data){

		$data['page'] 		 = $data['page']  == null? 1:$data['page'];
		$data['count']		 = $data['count'] == null?10:$data['count'];

		$sql = 'select cid from audience_with_company';
		$sql.= ' where uid = '.$data['uid'];

		$data['company_name'] != '' && $sql.=' and company_name like "%'.$data['company_name'].'%"';

		$sql.= ' order by company_id desc';
		$sql.= ' limit '.($data['page']-1)*$data['count'].','.$data['count'];

		return M('company','','')->db(1,'DB_CONFIG1')->query($sql);

	}


	
	
}
?>