<?php

class SupplydemandModel extends Model{

    private $get_supplydemand_list_cows = 'sd_id,sd_publish,sd_title,sd_local,sd_type,sd_state,type_id,time_insert';
    private $my_supplydemand_cows       = 'a.sd_id,a.time_insert,sd_title,sd_local,sd_type,sd_state,type_id';


    public function create_supplydemand($data){

        $data['time_insert'] = time();
        $rs = M('supplydemand','','DB_MEETING')->add($data);

        if($data['sd_attachment'] != ''){
            foreach($data['sd_attachment'] as $val) {

                $this->add_supplydemand_attachment([
                    'attachment_path'=>$val,
                    'supplydemand_id'=>$rs
                ]);
            }
        }
        return $rs;
    }

    public function add_supplydemand_attachment($data){
        return M('supplydemand_attachment','','DB_MEETING')->add($data);
    }

    public function get_supplydemand_list($data){

        $field = 'sd_id,sd_publish,sd_title,sd_local,sd_type,sd_state,type_id,time_insert';

        $data['page'] = $data['page']  == null? 1:$data['page'];
        $data['count']= $data['count'] == null?10:$data['count'];


        $map['1'] = 1;

        if($data['company_name']!=null){
            $audience_id = D('Audience')->new_get_audience_id_by_audience_name($data['company_name']);
            $map['sd_publish'] = $audience_id;
        }
        $data['without_me']  !=null && $map['sd_publish']= ['neq',$data['uid']];
        $data['sd_type']     !=null && $map['sd_type']   = $data['sd_type'];
        $data['state']       !=null && $map['sd_state']  = ['in',$data['state']];
        $data['title']       !=null && $map['sd_title']  = ['like',$data['title']];
        $data['detail']      !=null && $map['sd_detail']    = ['like',$data['detail']];
        $data['local']       !=null && $map['sd_local']  = ['like',$data['local']];
        $data['type_id']     !=null && $map['type_id']   = ['in',$data['type_id']];

        $re = M('supplydemand','','DB_MEETING')
            ->field($field)
            ->where($map)
            ->order('sd_id desc')
            ->limit(($data['page']-1)*$data['count'].','.$data['count'])
            ->select();

        foreach($re as $val){
            $company= D('Audience')->new_get_audience_by_uid($val['sd_publish']);
            $val['company_name']    = $company['audience_name'];
            $val['audience_tel']    = $company['audience_tel'];
            $val['audience_email']  = $company['audience_email'];
	        $val['sd_local']        = '';
            $val['is_contact']      = $this->check_contact($data['uid'],$val['sd_id']);
            $val['contact_num']     = $this->get_contact_num($val['sd_id']);
            $val['type_name']       = $this->get_type_name($val['type_id']);
            $val['type_name']       = $val['type_name'] == null ? '' : $val['type_name'];
            $val['supplydemand_attachment'] = $this->get_one_supplydemand_attachment($val['sd_id']);
            unset($val['sd_publish']);
            $arr[] = $val;
        }
        $val=null;unset($val);
        return $arr;
    }

    public function get_one_supplydemand_attachment($supplydemand_id){

        return M('supplydemand_attachment','','DB_MEETING')
            ->where(['supplydemand_id'=>$supplydemand_id])
            ->field('attachment_id,attachment_path')
            ->getField('attachment_path');
    }

    public function get_supplydemand_attachment($supplydemand_id){

        $rs = M('supplydemand_attachment','','DB_MEETING')
            ->where(['supplydemand_id'=>$supplydemand_id])
            ->field('attachment_id,attachment_path')
            ->select();
        return $rs ? $rs : null;
    }

    public function get_type_name($id)
    {
        return M('supplydemand_type','','DB_MEETING')
            ->where(['type_id'=>$id])
            ->getField('type_name');
    }

    public function get_supplydemand_details($data){
        if ($data['need_audience_info'] != 1) {
            $rs = M('supplydemand', '', 'DB_MEETING')
                ->field('sd_detail,sd_state')
                ->where(['sd_id'=>$data['sd_id']])
                ->find();
        }else{
            $rs = M('supplydemand','','DB_MEETING')
                ->field('sd_detail,sd_state,sd_publish')
                ->where(['sd_id'=>$data['sd_id']])
                ->find();
            $audience = D('Audience')->new_get_audience_by_uid($rs['sd_publish']);
            $rs['audience_name'] = $audience['audience_name'];
            $rs['audience_tel']  = $audience['audience_tel'];
            unset($rs['sd_publish']);
        }
        $rs['supplydemand_attachment'] = $this->get_supplydemand_attachment($data['sd_id']);
        return $rs;
    }

    public function check_contact($uid,$sd_id){

        $map = [
            'audience_id'=>$uid,
            'sd_id'      =>$sd_id
        ];
        $rs = M('supplydemand_audience','','DB_MEETING')->where($map)->count();
        if($rs)
            return 1;
        else
            return 0;
    }

    public function get_contact_num($sd_id){

        return M('supplydemand_audience','','DB_MEETING')
            ->where(['sd_id'=>$sd_id])
            ->count();
    }

    public function contact_supplydemand($data){
        return M('supplydemand_audience','','DB_MEETING')->add($data);
    }

    public function check_reg($data){

        $map = [
            'sd_id'      => $data['sd_id'],
            'audience_id'=> $data['audience_id']
        ];

        return M('supplydemand_audience','','DB_MEETING')
            ->where($map)
            ->find();
    }

    public function get_supplydemand_audience_list($data){

        return M('supplydemand_audience','','DB_MEETING')
            ->where(['sd_id'=>$data['sd_id']])
            ->order('sd_id desc')
            ->select();
    }

    public function update_supplydemand($data){

        $data['time_update']=time();
        if($data['delete_image'] != ''){
            $arr = explode(',',$data['delete_image']);
            foreach($arr as $val){
                $this->del_supplydemand_attachment($val);
            }
        }
        return M('supplydemand','','DB_MEETING')
            ->where(['sd_id'=>$data['sd_id']])
            ->save($data);
    }

    public function del_supplydemand_attachment($id){

        $re   = $this->get_attachment_by_id($id);
        $path = str_replace(C('DOMAIN_NAME').__ROOT__.'/','',$re['attachment_path']);
        unlink($path);

        return M('supplydemand_attachment','','DB_MEETING')
            ->where(['attachment_id'=>$id])
            ->delete();
    }

    public function get_attachment_by_id($id){
        return M('supplydemand_attachment','','DB_MEETING')
            ->where(['attachment_id'=>$id])
            ->find();
    }

    public function my_supplydemand($data){

        $data['page'] = $data['page']  == null? 1:$data['page'];
        $data['count']= $data['count'] == null?10:$data['count'];

        $sql = 'select '.$this->my_supplydemand_cows.' from supplydemand as a';

        if($data['my_apply'] != null )
        {
            $sql.= ' join supplydemand_audience as b on a.sd_id = b.sd_id';
            $sql.= ' where b.audience_id='.$data['uid'];

        }
        else
            $sql.= ' where a.sd_publish='.$data['uid'];

        $data['type']     != null && $sql.= ' and a.sd_type='.$data['type'];
        $data['state']    != null && $sql.=' and a.sd_state in ('.$data['state'].')';
        $data['my_apply'] != null && $sql.= ' group by a.sd_title';

        $sql.= ' order by sd_id desc';
        $sql.= ' limit '.($data['page']-1)*$data['count'].','.$data['count'];

        $rs = M('supplydemand','','DB_MEETING')->query($sql);

        foreach($rs as &$val){
            $val['supplydemand_attachment'] = $this->get_one_supplydemand_attachment($val['sd_id']);
            $val['type_name']               = $this->get_type_name($val['type_id']);
        }

        return $rs;
    }

    public function get_supplydemand_type(){

        return M('supplydemand_type','','DB_MEETING')
            ->field('type_id,type_name')
            ->select();
    }
}
