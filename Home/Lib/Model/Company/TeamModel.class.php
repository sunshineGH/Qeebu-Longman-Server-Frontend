<?php

class TeamModel extends Model{
//-------------------------------------select--------------------------------------

    public function get_red_point($data){
        $notice = [];
        $map = [
            'audience_id' => $data['uid'],
            'time_insert' => ['gt',$data['timestamp']],
            'state'		  => ['in','5,7,8,9,14'],
        ];
        $push_notice_ids = M('team_push_notice_user','','')
            ->db('CONFIG1')
            ->field('push_notice_id')
            ->where($map)
            ->order('time_insert desc')
            ->select();
        $push_notice_ids = i_array_column($push_notice_ids,'push_notice_id');
        $push_notice_ids = array_unique($push_notice_ids);
        foreach($push_notice_ids as $val){
            $re = M('team_push_notice','','')
                ->db('CONFIG1')
                ->field('id')
                ->where(['id'=>$val])
                ->find();
            if(!$re)continue;
            $notice[]=$re;
        }

        $solve = [];
        $map = [
            'audience_id' => $data['uid'],
            'time_insert' => ['gt',$data['timestamp']],
            'state'		  => ['in','4,6'],
        ];
        $push_notice_ids = M('team_push_notice_user','','DB_MEETING')
            ->db('CONFIG1')
            ->field('push_notice_id')
            ->where($map)
            ->order('time_insert desc')
            ->select();
        $push_notice_ids = i_array_column($push_notice_ids,'push_notice_id');
        $push_notice_ids = array_unique($push_notice_ids);
        foreach($push_notice_ids as $val){
            $re = M('team_push_notice','','DB_MEETING')
                ->db('CONFIG1')
                ->where(['id'=>$val])
                ->getField('id');
            if(!$re)continue;
            $solve[]=$re;
        }

        return [
            'notice'=>$notice!=[] ?'1':'0',
            'solve' =>$solve!=[]  ?'1':'0'
        ];
    }

    public function get_team_create_info($cid){

        $map['audience_character'] = 1;
        $map['cid']                = $cid;
        $uid = M('audience_with_company','','')->db('CONFIG1')->where($map)->getField('uid');
        return M('audience','','')->db('CONFIG1')->where(['audience_id'=>$uid])->find();
    }

    public function get_team_alive($data){

        $map['uid']     = $data['uid'];
        $map['cid']     = $data['cid'];
        $map['audience_character'] = ['in','1,2,3'];
        $map['_string'] = 'time_insert>time_delete';

        return M('audience_with_company','','')->db('CONFIG1')->where($map)->count(1);
    }

    public function get_team_admin_by_uid($cid,$uid){

        $map['uid'] = $uid;
        $map['cid'] = $cid;
        $map['audience_character'] = ['in','1,2'];

        return M('audience_with_company','','')->db('CONFIG1')->where($map)->find();
    }

    public function get_team_admin_by_cid($cid){

        $map['cid'] = $cid;
        $map['audience_character'] = ['in','1,2'];

        return M('audience_with_company','','')
            ->db('CONFIG1')->where($map)->select();
    }


//-------------------------------------insert--------------------------------------
    public function create_team($data){

        $data['time_insert'] = time();

        //get cid
        $cid = M('company','','')->db('CONFIG1')->add($data);
        if(!$cid)return false;

        //wait for create database
        $data['company_id'] = $cid;
        M('wait_create_team','','')->db('CONFIG1')->add($data);

        //create default admin
        $data1 = [
            'audience_character'=>1,
            'time_insert'       =>time(),
            'time_update'       =>time(),
            'cid'               =>$cid,
            'uid'               =>$data['uid']
        ];
        M('audience_with_company','','')->db('CONFIG1')->add($data1);

        //设置当前team为创建好的team
        $this->set_current_team($data1);

        //处理返回数据
        $data3 = $this->get_team_by_cid($cid);

        $rs['company_id']   = StrCode($cid,'ENCODE');
        $rs['company_name'] = $data3['company_name'];
        $rs['company_desc'] = $data3['company_desc'];
        $rs['company_logo'] = $data3['company_logo'];
        $rs['is_open']      = $data3['is_open'];
        $rs['team_creater'] = $this->get_team_creater($cid);

        $rs['audience_character'] = 1;

        return $rs;
    }

//-------------------------------------update--------------------------------------

    public function update_team($data){
        $data['time_update'] = time();

        $old_data = $this->get_team_by_cid($data['cid']);

        $map = ['company_id'=>$data['cid']];
        $rs  = M('company','','')->db('CONFIG1')->where($map)->save($data);

        $company = $this->get_team_by_cid($data['cid']);
        Database($data['cid']);
        D('Department')->upt_department([
            'department_id'     =>1,
            'department_name'   =>$company['company_name']
        ]);

        if($company['company_name'] != $old_data['company_name']){
            $message = '将原team名称'.$old_data['company_name'].'更改为'.$data['company_name'];
            $audience_character = 14;
            $user_arr = $this->get_team_member_with_cid($data['cid'],$data['uid']);
            A('Company/Push')->team_push($data['cid'],$message,$audience_character,$user_arr,$data['uid'],$data['uid'],$old_data['company_name']);
        }

        return $rs;
    }

    public function update_audience_character($data){

        $audience_with_company = M('audience_with_company','','')->db('CONFIG1');

        $map = [
            'cid'    =>$data['cid'],
            'uid'    =>$data['uid'],
        ];
        $data['time_update'] = time();

        $rs = $audience_with_company->where($map)->find();

        if($data['audience_character'] == 3 && $rs['audience_character']!=3){

            $data['character_incomming'] = $rs['audience_character'];
            $data['time_insert']         = time();
            //组织架构
            Database($data['cid']);
            M('audience_department_rds','','DB_MEETING')->add([
                'audience_id'  => $data['uid'],
                'department_id'=> 2,
                'time_insert'  => time(),
                'time_delete'  => -1
            ]);
            D('Push')->delete_push_notice_by_update_audience_character($data);
        }
        $rs = $audience_with_company->where($map)->save($data);

        //推送
        $audience_name = D('Audience')->new_get_audience_name_by_uid($data['uid']);
        $admin_name    = D('Audience')->new_get_audience_name_by_uid($data['invite_admin']);
        switch($data['audience_character']){
            case 3:
                if($data['character_incomming'] == 4){
                    $audience_character = 9;
                    $user_arr = [$data['uid']];
                    $message  = '您的申请已经被['.$admin_name.']同意';
                }else{
                    $audience_character = 8;
                    $user_arr = [$data['invite_admin']];
                    $message  = '您的邀请已经被['.$audience_name.']同意';
                }
                break;
            case 5:
                $message = '您的申请被['.$admin_name.']拒绝';
                $audience_character = 5;
                $user_arr = [$data['uid']];
                break;
            case 7:
                $message = '您的邀请已经被['.$audience_name.']拒绝';
                $audience_character = 7;
                $user_arr = [$data['invite_admin']];
                break;
            default:
                $message = '您已经被设定为管理员';
                $audience_character = 2;
                $user_arr = [$data['uid']];
        }
        A('Company/Push')->team_push($data['cid'],$message,$audience_character,$user_arr,$data['uid'],$data['invite_admin']);

        return $rs;
    }
//-------------------------------------delete--------------------------------------

    public function delete_from_team($data){
        //1、删除当前用户的组织架构
        $arr1['time_update'] = time();
        $arr2['time_delete'] = time();
        $rs = M('audience_department_rds','','DB_MEETING')->where('audience_id='.$data['delete_user'].' and time_insert>time_delete')->select();
        foreach($rs as $val){
            //1、更改当前部门表的时间
            M('audience_department','','DB_MEETING')->where('department_id='.$val['department_id'])->save($arr1);
            //2、删除此条数据
            M('audience_department_rds','','DB_MEETING')->where('rds_id='.$val['rds_id'])->save($arr2);
        }
        //2、删除当前teamin
        return M('audience_with_company','','')->db('CONFIG1')->where('uid='.$data['delete_user'].' and cid='.$data['cid'].' and time_insert>time_delete')->save($arr2);
    }


    public function get_my_team($data){
        $data['page'] = $data['page']  == null? 1:$data['page'];
        $data['count']= $data['count'] == null?10:$data['count'];

        $sql = 'select '.'cid,audience_character'.' from audience_with_company';
        $sql.= ' where uid='.$data['uid'];

        if($data['audience_character'] == 4)
            $sql.=' and audience_character = 4';
        else
            $sql.=' and audience_character in (1,2,3) and time_insert>time_delete';

        $sql.= ' order by cid desc';
        $sql.= ' limit '.($data['page']-1)*$data['count'].','.$data['count'];

        $rs = M('audience_with_company','','')->db('CONFIG1')->query($sql);

        foreach($rs as &$val) {
            if ($val['cid'] != '') {
                $company = $this->get_team_by_cid($val['cid']);
                $val['company_id']   = StrCode($val['cid'],'ENCODE');
                $val['company_name'] = $company['company_name'] == null ? '' :$company['company_name'];
                $val['company_desc'] = $company['company_desc'];
                $val['company_logo'] = $company['company_logo'] == '' ? '' : C('DOMAIN_NAME').__ROOT__.'/'.$company['company_logo'];
                $val['is_open']      = $company['is_open'];
                $val['team_creater'] = $this->get_team_creater($val['cid']);
                $val['link']         = 'http://huiyi.qeebu.cn/teamin/Team/join_team?tokens=XMKUHNDLUIOQWERBNJKL&c=AS8bfrASrrrZXCqDrq&k='.$val['company_id'].'-'.StrCode($_REQUEST['uid']);
                unset($val['cid']);
            }
        }
        return $rs;
    }

    public function get_team_by_cid($cid){
        return M('company','','')
            ->db('CONFIG1')
            ->field('company_name,company_desc,company_logo,is_open')
            ->where('company_id='.$cid)
            ->find();
    }

    public function get_team_name_by_cid($cid){
        return M('company','','')->db('CONFIG1')->where('company_id='.$cid)->getField('company_name');
    }

    public function get_team_creater($cid){
        $uid = M('audience_with_company','','')->db('CONFIG1')->where('audience_character=1 and cid='.$cid)->getField('uid');
        return M('audience','','')->db('CONFIG1')->where('audience_id='.$uid)->getField('audience_name');
    }

    public function set_current_team($data){
        $map['audience_id'] = $data['uid'];
        $data['current_cid'] = $data['cid'];
        return M('audience','','')->db('CONFIG1')->where($map)->save($data);
    }

    public function apply_team($data){
        $data['check'] = 2;
        $rs = $this->get_team_with_cid_uid($data);

        if($rs){
            $data['time_update'] = time();
            $map = [
                'cid'=>$data['cid'],
                'uid'=>$data['uid']
            ];
            $re = M('audience_with_company','','')->db('CONFIG1')->where($map)->save($data);
        }else{
            $data['time_insert'] = time();
            $re = M('audience_with_company','','')->db('CONFIG1')->add($data);
        }
        $this->apply_push($data);
        return $re;

    }

    public function apply_push($data){

        $audience_name = D('Audience')->new_get_audience_name_by_uid($data['uid']);
        $admin_name    = D('Audience')->new_get_audience_name_by_uid($data['invite_admin']);

        if($data['audience_character'] == 4){
            $audience = D('Team')->get_team_admin_by_cid($data['cid']);
            $user_arr = i_array_column($audience,'uid');
            $message = '['.$audience_name.']申请加入您的Team';
        }else{
            $user_arr = [$data['uid']];
            $message = '['.$admin_name.']邀请您加入team';
        }
        A('Company/Push')->team_push($data['cid'],$message,$data['audience_character'],$user_arr,$data['uid'],$data['invite_admin']);
    }

    public function get_team_with_cid_uid($data){
        //如果是邀请或者申请需要判断是否重复
        $map = [
            'cid'=>$data['cid'],
            'uid'=>$data['uid']
        ];
        return M('audience_with_company','','')->db('CONFIG1')->where($map)->find();
    }

    public function get_team_member_with_cid_uid($data){
        $map = [
            'uid'=>$data['uid'],
            'cid'=>$data['cid'],
            'audience_character'=>['in','1,2,3']
        ];
        $data['check'] != 2 && $map['_string'] = 'time_insert>time_delete';
        return M('audience_with_company','','')->db('CONFIG1')->where($map)->find();
    }

    public function get_team_member_with_cid($cid,$uid=0){
        $map = [
            'cid'               =>$cid,
            'uid'               =>['neq',$uid],
            'audience_character'=>['lt',4]
        ];

        $rs = M('audience_with_company','','')
            ->db('CONFIG1')
            ->field('uid')
            ->where($map)
            ->select();
        return i_array_column($rs,'uid');
    }

    public function get_team_count($uid){
        $rs = M('audience_with_company','','')
            ->db('CONFIG1')
            ->where('uid='.$uid.' and audience_character in (1,2,3) and time_insert>time_delete')
            ->count(1);
        return $rs ? $rs : 0;
    }

    public function get_team_admin($data){
        return M('audience_with_company','','')->db('CONFIG1')->where('uid='.$data['uid'].' and cid='.$data['cid'])->find();
    }
/*
 * 1、根据uid判断当前是否为当前cid的管理员
 * 2、如果不是管理员，只拿与自己相关的邀请和同意信息
 * 3、如果是管理员，则拿与自己相关的数据并且增加当前和cid相关的所有需要审核的数据
 * 4、根据不同的cid拿相关的企业的名称,company_name
 * 5、根据不同的uid拿相关的audience_name
 * 6、状态码[
 *      1=>'创建者',
 *      2=>'管理员',
 *      3=>'成员',
 *      4=>'申请加入team',
 *      5=>'管理员【拒绝】用户申请'【显示在audience下】',
 *      6=>'管理员邀请用户加入team',
 *      7=>'用户【拒绝】管理员邀请 【显示在管下】',
 *      8=>'用户【同意】管理员邀请'【显示在admin下】,
 *      9=>'管理员【同意】用户申请'【显示在audience下】,
 *      //10=>'用户【拒绝】管理员邀请【uid和admin_id相反,显示在admin下】',
 *      //11=>'管理员【拒绝】用户申请【uid和admin_id相反,显示在audience下】'
 *      //12=>'用户【同意】管理员邀请【uid和admin_id相反,显示在audience下】',
 *      //13=>'管理员【同意】用户申请【uid和admin_id相反,显示在admin下】'
 *      14=>'管理员修改team名称'
 *  ]
 * */
    public function get_team_invite_list($data){

        $data['page']  = $data['page']  == null?  1 :$data['page'];
        $data['count'] = $data['count'] == null? 10 :$data['count'];

        $model = M('audience_with_company','','')->db('CONFIG1');
        $field = 'uid,audience_character,cid,character_incomming,time_update';


        //先把自己的数据拿了
        if($data['for'] == 1){
            $where  = 'uid='.$data['uid'].' and audience_character in (6)';
            $rs = $model->field($field)->where($where)->limit(($data['page']-1)*$data['count'].",".$data['count'])->select();
        }else{
            $where1 = 'uid='.$data['uid'].' and audience_character in (5,7)';
            $where2 = 'uid='.$data['uid'].' and audience_character = 3 and character_incomming in (4,6)';
            $rs1 = $model->field($field)->where($where1)->limit(($data['page']-1)*$data['count'].",".$data['count'])->select();

            $rs2 = $model->field($field)->where($where2)->limit(($data['page']-1)*$data['count'].",".$data['count'])->select();
            foreach($rs2 as $val){
                $val['audience_character'] = $val['character_incomming'] == 4 ? 9 : 8;
                $arr[]=$val;
            }
            $rs2 = $arr;
            unset($arr);
            $rs = array_merge((array)$rs1,(array)$rs2);
        }

        //是否为管理员,是管理员就把自己的数据也合并到rs数组中
        $admin = $this->get_team_admin($data);
        if($admin['audience_character'] == 1){
            if($data['for'] == 1){
                $where  = 'cid='.$data['cid'].' and audience_character in (4)';
                $rs3 = $model->field($field)->where($where)->limit(($data['page']-1)*$data['count'].",".$data['count'])->select();
                $rs = array_merge((array)$rs,(array)$rs3);
            }else{
                $where1 = 'cid='.$data['cid'].' and audience_character in (5,6,7)';
                $where2 = 'cid='.$data['cid'].' and audience_character = 3 and character_incomming in (4,6)';
                $rs1 = $model->field($field)->where($where1)->limit(($data['page']-1)*$data['count'].",".$data['count'])->select();
                foreach($rs1 as $val1){
                    $val1['audience_character'] = $val1['audience_character'] == 5 ? 10 : $val1['audience_character'];
                    $val1['audience_character'] = $val1['audience_character'] == 7 ? 11 : $val1['audience_character'];
                    $arr1[]=$val1;
                }
                $rs1 = $arr1;unset($val1);
                $rs2 = $model->field($field)->where($where2)->limit(($data['page']-1)*$data['count'].",".$data['count'])->select();
                foreach($rs2 as $val){
                    $val['audience_character'] = $val['character_incomming'] == 4 ? 9 : 8;
                    $arr[]=$val;
                }
                $rs2 = $arr;
                unset($arr);
                $rs = array_merge((array)$rs,(array)$rs1,(array)$rs2);
            }
        }

        foreach($rs as &$val){
            $audience                       = D('Audience')->new_get_audience_by_uid($val['uid']);
            $val['audience_name']           = $audience['audience_name'];
            $val['audience_portrait']       = $audience['audience_portrait']==''? '' :C('DOMAIN_NAME').__ROOT__.'/'.$audience['portrait'];
            $val['audience_contact_info']   = $audience['audience_username'];
            $val['audience_name']           = D('Audience')->new_get_audience_name_by_uid($val['uid']);
            $val['company_name']            = $this->get_team_name_by_cid($val['cid']);
            $creater                        = $this->get_team_create_info($val['cid']);
            $val['company_creater_name']    = $creater['audience_name'];
            $val['company_creater_portrait']= C('DOMAIN_NAME').__ROOT__.'/'.$creater['audience_portrait'];
            $val['company_member_count']    = $this->get_team_member_count($val['cid']);
            $val['uid']                     = StrCode($val['uid']);
            $val['cid']                     = StrCode($val['cid']);
            unset($val['character_incomming']);
        }
        return $rs;
    }

    public function get_team_member_count($cid){
        $rs = M('audience_with_company','','')->db('CONFIG1')->where('cid='.$cid.' and audience_character in (1,2,3) and time_insert>time_delete')->count(1);
        return $rs ? ''.$rs : '';
    }

    public function get_audience_by_cid($cid){

        $map = [
            'cid'=>$cid,
            'audience_character'=>[
                ['gt',0],
                ['lt',4]
            ],
            '_string'=>'time_insert>time_delete'
        ];

        $rs = M('audience_with_company','','')
            ->db('CONFIG1')
            ->field('uid')
            ->where($map)
            ->select();
        return i_array_column($rs,'uid');
    }

    public function leave_team($data){

        $map = [
            'uid'=>$data['uid'],
            'cid'=>$data['cid']
        ];
        $rs = M('audience_with_company','','')
            ->db('CONFIG1')
            ->where($map)
            ->save(['time_delete'=>time()]);
        $username = D('Audience')->new_get_audience_name_by_uid($data['uid']);
        A('Company/Push')->leave_team_push($data['cid'],$username.'退出了该Team',$data['uid']);
        return $rs;
    }
}