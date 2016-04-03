<?php
class Special_caseModel extends Model{

//------------------------------insert-------------------------------------------
    public function create_special_case($data){

        $rs= M('special_case')->add($data);
        A('Company/Push_notification')->special_case_push($data);
        return $rs;
    }
//-----------------------------------update--------------------------------------------
//-----------------------------------delete-------------------------------------------
//-----------------------------------select-------------------------------------------
    public function get_special_case_list_by_class_id($data){

        $page = $data['page'] == '' ? 1 : $data['page'];
        $count= $data['count']== '' ? 10: $data['count'];

        $role = D('User')->get_user('',$data['uid'],'role')['role'];
        if($role == 1){
            $map['uid'] = $data['uid'];
        }

        $rs = M('special_case')
            ->where($map)
            ->order('id desc')
            ->limit((($page-1)*$count).",".$count)
            ->select();

        foreach($rs as &$val){
            $user = D('User')->get_user('',$val['uid'],'nickname,photo,tel,im_username');
            $val['photo']		 = $user['photo'] == '' ? '': C('DOMAIN_NAME').__ROOT__.'/'.$val['photo'];
            $val['nickname'] 	 = $user['nickname'];
            $val['im_username']  = $user['im_username'];
            $val['tel']          = $user['tel'];
            $val['uid'] 	 	 = StrCode($val['uid']);
            $val['class_num']    = D('Classes')->get_class($val['class_id'],'class_num')['class_num'];
            unset($val['class_id']);
            unset($user);
        }
        return $rs;
    }

    public function upt_special_case_state($data){
        return M('special_case')->where(['id'=>$data['special_case_id']])->save($data);
    }
}