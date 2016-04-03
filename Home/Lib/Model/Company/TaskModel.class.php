<?php
/**
 * Created by zhanghao.
 * User: Zhanghao
 * tel: 18609059892
 * qq: 261726511
 */
class TaskModel extends Model{

//---------------------------------param------------------------------------------


//------------------------------------------------insert----------------------------------------------------------
    public function create_new_project($data){

        $data['time_insert'] = time();
        $data['time_update'] = 0;
        $data['time_delete'] = 0;

        $rs = M('task_project','','DB_MEETING')->add($data);

        if(!$rs)return false;

        $creator = [
            'project_id'=>$rs,
            'uid'       =>$data['uid'],
            'part'      =>0
        ];
        $this->add_project_members($creator);

        if($data['project_members_id']!=''){
            $project_members = explode(',',$data['project_members_id']);
            foreach($project_members as $val){

                $audience = [
                    'project_id'=>$rs,
                    'uid'       =>StrCode($val,'DECODE'),
                    'part'      =>2
                ];

                $this->add_project_members($audience);
            }
        }

        return $rs;
    }

    public function create_new_task($data){
        unset($data['task_id']);
        unset($data['task_hint']);

        $data['time_insert'] = time();
        $data['time_update'] = 0;
        $data['time_delete'] = 0;
        $rs = M('task','','DB_MEETING')->add($data);
        if(!$rs)return false;

        $this->add_task_members([
            'task_id'=>$rs,
            'uid'    =>$data['task_creater_id'],
            'part'   =>0
        ]);

        if($data['task_members_id']!=''){
            $task_members = explode(',',$data['task_members_id']);
            foreach($task_members as $val){
                $this->add_task_members([
                    'task_id'=>$rs,
                    'uid'    =>StrCode($val,'DECODE'),
                    'part'   =>2
                ]);
            }
        }

        //push notice
        A('Company/Push')->task_push($rs,'您有一个新的任务');

        //create a daily_log
        if($data['task_project_id'] == 1){
            $message = '我发布了个人任务:【'.$data['task_content'].'】';
        }else{
            $message = '我发布了任务:【'.$data['task_content'].'】,任务成员:';
        }
        $data['task_is_share_on'] == 1 && $this->task_share_log($rs,$data,$message,'add');


        return $rs;
    }



    public function add_project_members($data){
        $data['time_insert']= time();
        $data['time_update']= 0;
        $data['time_delete']= 0;
        return M('task_project_members','','DB_MEETING')->add($data);
    }

    public function add_task_members($data){
        $data['time_insert']= time();
        $data['time_update']= 0;
        $data['time_delete']= 0;
        return M('task_task_members','','DB_MEETING')->add($data);
    }
//------------------------------------------------update----------------------------------------------------------

    public function done_myself_task($data){
        return M('task_execution','','DB_MEETING')->add($data);
    }

    public function update_project($data){

        $map['project_id']   = $data['project_id'];
        $data['time_update'] = time();
        $rs = M('task_project','','DB_MEETING')->where($map)->save($data);

        $data['project_members_id'] == '' && $data['project_members_id'] = [];
        $data['project_members_id']=array_map(function($val){
            return StrCode($val,'DECODE');
        },explode(',',$data['project_members_id']));

        $old_project_members = $this->get_project_members_by_project_id($data['project_id']);
        $old_project_members == false && $old_project_members = [];
        $old_project_members = array_map(function($val){
            return StrCode($val,'DECODE');
        },explode(',',$old_project_members));

        //判断
        $del = array_merge(array_diff($old_project_members,$data['project_members_id']));
        if($del && $del!=[]){
            $this->delete_project_members_by_user_arr($data['project_id'],$del);
        }

        $add = array_merge(array_diff($data['project_members_id'],$old_project_members));
        if($add && $add!=[]){
            foreach($add as $val){
                $this->add_project_members([
                    'project_id'=>$data['project_id'],
                    'part'      =>2,
                    'uid'       =>$val
                ]);
            }
        }

        return $rs;
    }



    public function update_task($data){
        $data['uid'] = StrCode($data['uid'],'DECODE');
        $data['time_update']     = time();
        $data['task_creater_id'] = StrCode($data['task_creater_id'],'DECODE');

        $map['task_id'] = $data['task_id'];

        $rs = M('task','','DB_MEETING')->where($map)->save($data);

        $old_task_members = $this->get_audience_by_task_id($data['task_id']);
        $new_task_members = array_map(function($v){
            return StrCode($v,'DECODE');
        },explode(',',$data['task_members_id']));
        $old_task_members = $old_task_members ? $old_task_members : [];
        $new_task_members = $new_task_members ? $new_task_members : [];

        $del = array_diff($old_task_members,$new_task_members);
        $add = array_diff($new_task_members,$old_task_members);
        $upt = array_intersect($old_task_members,$new_task_members);

        if($del!=[] && $del!=false){

            foreach($del as $val){
                $this->del_task_members_by_uid([
                    'task_id'=>$data['task_id'],
                    'uid'    =>$val,
                    'part'   =>2
                ]);
            }
            A('Company/Push')->task_push($data['task_id'],'您有一个任务被取消',$del);
        }
        if($upt!=[] && $upt!=false){

            foreach($upt as $val){
                $this->upt_task_members_by_uid([
                    'task_id'=>$data['task_id'],
                    'uid'    =>$val,
                    'part'   =>2
                ]);
            }
            if($data['task_time_done']>$data['task_time_begin']){
                foreach($upt as $val){
                    $this->done_myself_task([
                        'uid'    => $val,
                        'task_id'=> $data['task_id'],
                        'time_insert'=>time()
                    ]);
                }
                A('Company/Push')->task_push($data['task_id'],'您有一个任务被完成',$upt);
            }else
                A('Company/Push')->task_push($data['task_id'],'您有一个任务被修改',$upt);
        }
        if($add!=[] && $add!=false){
            foreach($add as $val){
                $this->add_task_members([
                    'task_id'=>$data['task_id'],
                    'uid'    =>$val,
                    'part'   =>2
                ]);
            }
            A('Company/Push')->task_push($data['task_id'],'您有一个新的任务',$add);
        }

        //create_new_daily_log
        if($data['task_project_id'] == 1){
            if($data['task_time_done']>$data['task_time_begin']) {
                $this->done_myself_task([
                    'uid'    => $data['uid'],
                    'task_id'=> $data['task_id'],
                    'time_insert'=>time()
                ]);
                $message = '我完成了个人任务:【'.$data['task_content'].'】';
            }else{
                $message = '我修改了个人任务:【'.$data['task_content'].'】';
            }
        }else{
            if($data['task_time_done']>$data['task_time_begin']) {
                $message = '我完成了任务:【'.$data['task_content'].'】,任务成员:';
            }else{
                $message = '我修改了任务:【'.$data['task_content'].'】,任务成员:';
            }
        }
        if($data['task_is_share_on'] == 1){
            $this->task_share_log($data['task_id'],$data,$message,'upt');
        }else{
            $this->task_share_log($data['task_id'],$data,$message,'del');
        }

        return $rs;
    }

    public function upt_task_members_by_uid($data){
        $map = [
            'task_id' => $data['task_id'],
            'part'    => $data['part'],
            'uid'     => $data['uid'],
            '_string' => 'time_insert>time_delete'
        ];
        $data['time_update'] = time();
        return M('task_task_members','','DB_MEETING')->where($map)->save($data);
    }

    public function delete_project_members_by_user_arr($project_id,$user_arr){

        foreach($user_arr as $val){

            $data = ['time_delete'=>time()];

            //1.删除他在这个项目中创建任务 待解决
            //2.删除项目中有他的任务
            $map = [
                'uid' =>$val,
                'part'=>2,
                '_string'=>'time_insert>time_delete'
            ];
            M('task_project_members','','DB_MEETING')->where($map)->save($data);

            $tasks = $this->get_task_by_project_id($project_id);
            $task_ids= i_array_column($tasks,'task_id');
            if(!$task_ids)continue;
            $map = [
                'uid' =>$val,
                'part'=>2,
                'task_id'=>['in',implode(',',$task_ids)],
                '_string'=>'time_insert>time_delete'
            ];
            M('task_task_members','','DB_MEETING')->where($map)->save($data);
        }
    }
//------------------------------------------------delete----------------------------------------------------------
    public function delete_members_by_project_id($project_id){
        $map['project_id'] = $project_id;
        $map['part']       = 2;
        $map['_string']    = 'time_delete < time_insert';
        $data['time_delete'] = time();
        return M('task_project_members','','DB_MEETING')->where($map)->save($data);
    }

    public function delete_members_by_task_id($task_id){
        $map = [
            'task_id' => $task_id,
            '_string' => 'time_delete < time_insert',
            'part'    => 2
        ];
        $data['time_delete'] = time();
        return M('task_task_members','','DB_MEETING')->where($map)->save($data);
    }

    public function del_task_members_by_uid($data){
        $map = [
            'task_id' => $data['task_id'],
            'part'    => $data['part'],
            'uid'     => $data['uid'],
            '_string' => 'time_insert>time_delete'
        ];
        $data['time_delete'] = time();
        return M('task_task_members','','DB_MEETING')->where($map)->save($data);
    }
//------------------------------------------------select----------------------------------------------------------
    public function get_task_execution($task_id){
        $map = [
            'task_id' => $task_id
        ];
        $rs = M('task_execution','','DB_MEETING')->where($map)->select();
        return $rs;
    }

    public function get_task_execution_detail($data){
        $execution = $this->get_task_execution($data['task_id']);
        $members   = $this->get_task_members_by_task_id($data['task_id']);
        $members   = explode(',',$members);

        $rs['task_execution'] = count($execution).'/'.count($members);

        if($execution){
            $execution=array_map(function($v){
                return StrCode($v);
            },i_array_column($execution,'uid'));
        }

        $rs['task_members_execution'] = [];

        foreach($members as $val){

            $member['uid'] = $val;

            $audience = D('Audience')->new_get_audience_by_uid(StrCode($member['uid'],'DECODE'));
            $member['audience_name']     = $audience['audience_name'];
            $member['audience_portrait'] = $audience['audience_portrait'];
            $member['is_finish']         = in_array($val,$execution) ? '1' : '0';

            $rs['task_members_execution'][]=$member;
            unset($member);
        }
        return $rs;

    }

    public function get_project_id_by_uid($uid){

        $map = [
            'uid'    =>$uid,
            '_string'=>'time_insert>time_delete'
        ];

        $map['uid']  = $uid;
        $rs = M('task_project_members','','DB_MEETING')
            ->field('distinct project_id')
            ->where($map)
            ->select();

        $project_ids    = i_array_column($rs,'project_id');
        $project_ids[]  = 1;
        return $project_ids;
    }

    public function sync_project($data){

        $re = [
            'insert' => null,
            'update' => null,
            'delete' => null
        ];

        $fields = '`project_id`, `project_name`, `project_detail`, `project_time_begin`, `project_time_done`, `project_time_end`,`project_color`';
        $field  = '`project_id`';

        $project_ids = $this->get_project_id_by_uid($data['uid']);

        $map_insert['project_id']  = array('in',implode(',',$project_ids));
        $map_insert['time_insert'] = array('gt',$data['timestamp']);
        $map_insert['_string']     = 'time_delete < time_insert';
        $add_list=M('task_project','','DB_MEETING')->field($fields)->where($map_insert)->select();

        $map_update['project_id']  = array('in',implode(',',$project_ids));
        $map_update['time_insert'] = array('elt',$data['timestamp']);
        $map_update['time_update'] = array('egt',$data['timestamp']);
        $map_update['_string']     = 'time_delete < time_insert';
        $upt_list=M('task_project','','DB_MEETING')->field($fields)->where($map_update)->select();

        $map_delete['project_id']  = array('in',implode(',',$project_ids));
        $map_delete['time_insert'] = array('elt',$data['timestamp']);
        $map_delete['time_delete'] = array('gt',$data['timestamp']);
        $del_list=M('task_project','','DB_MEETING')->field($field)->where($map_delete)->select();

        if($add_list==null || count($add_list)==0)
            $re['insert'] = null;
        else{
            foreach($add_list as $val){
                $val['project_members_id'] = $this->get_project_members_by_project_id($val['project_id']);
                if($val['project_id']!=1)
                    $val['project_creater_id'] = $this->get_project_creater_id_by_project_id($val['project_id']);
                else
                    $val['project_creater_id'] = StrCode($data['uid']);
                $val['project_members_id'] == false && $val['project_members_id']='';
                $val['project_creater_id'] == false && $val['project_creater_id']='';

                $re['insert'][] = $val;
            }
        }

        if($upt_list==null || count($upt_list)==0)
            $re['update'] = null;
        else{
            foreach($upt_list as $val){
                $val['project_members_id'] = $this->get_project_members_by_project_id($val['project_id']);
                if($val['project_id']!=1)
                    $val['project_creater_id'] = $this->get_project_creater_id_by_project_id($val['project_id']);
                else
                    $val['project_creater_id'] = StrCode($data['uid']);
                $val['project_members_id'] == false && $val['project_members_id']='';
                $val['project_creater_id'] == false && $val['project_creater_id']='';
                $re['update'][] = $val;
            }
        }
        if($del_list==null || count($del_list)==0)
            $re['delete'] = null;
        else
            $re['delete'] = $del_list;
        return $re;
    }

    public function sync_task($data){

        $fields = '`task_id`, `task_content`, `task_creater_id`, `task_project_id`, `task_time_begin`, `task_time_done`, `task_time_end`';
        $fields.=',`task_notice_time`, `task_notice_repeat`, `task_desc`, `task_importance`, `task_urgency`';
        $field  = '`task_id`';

        $re = [
            'insert' => null,
            'update' => null,
            'delete' => null
        ];

        //获取相关项目列表
        $project_ids = $this->get_project_id_by_uid($data['uid']);
        $project_ids = array_filter($project_ids,function($val){
             if($val==1)
                 return false;
             return true;
        });

        //insert
        $map_insert = [
            'task_project_id' =>['in',implode(',',$project_ids)],
            'time_insert'=>['gt',$data['timestamp']],
            '_string'    =>'time_delete < time_insert',
        ];
        $add_list=M('task','','DB_MEETING')->field($fields)->where($map_insert)->select();
        //mine
        $map_insert['task_creater_id'] = $data['uid'];
        $map_insert['task_project_id'] = 1;
        $add_list_mine = M('task','','DB_MEETING')->field($fields)->where($map_insert)->select();
        //merge
        $add_list = array_merge((array)$add_list,(array)$add_list_mine);

        //update
        $map_update = [
            'task_project_id' =>['in',implode(',',$project_ids)],
            'time_insert'=>['elt',$data['timestamp']],
            'time_update'=>['gt',$data['timestamp']],
            '_string'    =>'time_delete < time_insert',
        ];
        $upt_list=M('task','','DB_MEETING')->field($fields)->where($map_update)->select();
        //mine
        $map_update['task_creater_id'] = $data['uid'];
        $map_update['task_project_id'] = 1;
        $upt_list_min=M('task','','DB_MEETING')->field($fields)->where($map_update)->select();
        //merge
        $upt_list = array_merge((array)$upt_list,(array)$upt_list_min);

        //delete
        $map_delete = [
            'task_project_id' =>['in',implode(',',$project_ids)],
            'time_insert'=>['elt',$data['timestamp']],
            'time_delete'=>['gt',$data['timestamp']],
        ];
        $del_list=M('task','','DB_MEETING')->field($field)->where($map_delete)->select();

        $map_delete['task_creater_id'] = $data['uid'];
        $map_delete['task_project_id'] = 1;
        $del_list_min=M('task','','DB_MEETING')->field($field)->where($map_delete)->select();

        $del_list = array_merge((array)$del_list,(array)$del_list_min);

        if($add_list==null || count($add_list)==0)
            $re['insert'] = null;
        else{
            foreach($add_list as $val){
                $val['task_members_id'] = $this->get_task_members_by_task_id($val['task_id']);
                if($val['task_project_id']=='1')
                    $val['task_creater_id'] = StrCode($data['uid']);
                else
                    $val['task_creater_id'] = $this->get_task_creater_id_by_task_id($val['task_id']);
                $val['task_members_id'] == false && $val['task_members_id']='';
                $time = $this->get_task_data_by_id($val['task_id'],$data['uid']);
                $val['task_read_time']  = $time['time_read']   == ''?'0':$time['time_read'].'';
                $val['task_insert_time']= $time['time_insert'] == ''?'0':$time['time_insert'].'';
                $val['task_update_time']= $time['time_update'] == ''?'0':$time['time_update'].'';
                $re['insert'][] = $val;
            }
        }

        if($upt_list==null || count($upt_list)==0)
            $re['update'] = null;
        else{
            foreach($upt_list as $val){
                $val['task_members_id'] = $this->get_task_members_by_task_id($val['task_id']);
                $val['task_members_id'] == false && $val['task_members_id']='';
                if($val['task_project_id']=='1')
                    $val['task_creater_id'] = StrCode($data['uid']);
                else
                    $val['task_creater_id'] = $this->get_task_creater_id_by_task_id($val['task_id']);
                $time = $this->get_task_data_by_id($val['task_id'],$data['uid']);
                $val['task_read_time']  = $time['time_read'] == ''?'0':$time['time_read'].'';
                $val['task_insert_time']= $time['time_insert'] == ''?'0':$time['time_insert'].'';
                $val['task_update_time']= $time['time_update'] == ''?'0':$time['time_update'].'';

                $re['update'][] = $val;
            }
        }
        if($del_list==null || count($del_list)==0)
            $re['delete'] = null;
        else
            $re['delete'] = $del_list;
        return $re;

    }

    public function get_task_data_by_id($task_id,$uid){

        $map = [
            'task_id'=>$task_id,
            'uid'    =>$uid,
            'part'   =>2,
            '_string'=>'time_insert>time_delete'
        ];

        $rs = M('task_task_members','','DB_MEETING')
            ->where($map)
            ->find();
        return $rs;
    }

    public function get_task_content_by_id($task_id){
        $map['task_id']=$task_id;
        return M('task','','DB_MEETING')->where($map)->getField('task_content');
    }

    public function get_project_members_by_project_id($project_id){

        $map = [
            'project_id'=>$project_id,
            'part'      =>2,
            '_string'   =>'time_insert>time_delete'
        ];
        $rs = M('task_project_members','','DB_MEETING')->field('uid')->where($map)->select();
        if(!$rs)
            return '';
        $project_members = implode(',',array_map(function($val){return StrCode($val['uid']);},$rs));
        return $project_members;
    }

    public function get_project_creater_id_by_project_id($project_id){

        $map = [
            'project_id'=>$project_id,
            'part'      =>0,
            '_string'   =>'time_insert>time_delete'
        ];
        $rs = M('task_project_members','','DB_MEETING')->field('uid')->where($map)->getField('uid');
        if(!$rs)
            return false;

        return $rs;
    }

    public function get_task_members_by_task_id($task_id){

        $map = [
            'task_id'=>$task_id,
            '_string'=>'time_insert>time_delete',
            'part'   =>2,
        ];
        $rs = M('task_task_members','','DB_MEETING')->field('uid')->where($map)->select();
        if(!$rs)
            return false;

        return implode(',',array_map(function($val){return StrCode($val['uid']);},$rs));
    }

    public function get_task_creater_id_by_task_id($task_id){

        $map = [
            'task_id' => $task_id,
            '_string' => 'time_insert>time_delete',
            'part'    => 0,
        ];
        $rs = M('task_task_members','','DB_MEETING')->where($map)->getField('uid');
        return StrCode($rs);
    }

    public function get_task_project_admin($data,$part=null){
        $map['project_id'] = $data['project_id'];
        $map['_string']    = 'time_insert>time_delete';

        $data['uid'] != '' && $map['uid'] = $data['uid'];

        if(0===$part)
            $map['part'] = 0;
        else if(1===$part)
            $map['part'] = 1;
        else
            $map['part'] = array('in',array(0,1));
        return M('task_project_members','','DB_MEETING')->where($map)->select();
    }

    public function update_task_read_time($data){

        $data1 = [
            'time_read'  => time(),
            'time_update'=> time()
        ];

        $map['task_id'] = $data['task_id'];
        M('task','','DB_MEETING')
            ->where(['task_id'=>$data['task_id']])
            ->save($data1);

        return M('task_task_members','','DB_MEETING')->where($data)->save($data1);

    }

    public function get_audience_by_task_id($id){
        $map['task_id'] = $id;
        $map['part']    = 2;
        $map['_string'] = 'time_insert>time_delete';
        $rs = M('task_task_members','','DB_MEETING')->field('distinct uid')->where($map)->select();
        return i_array_column($rs,'uid');
    }
//------------------------------------project-------------------------------------
    public function get_project_name_by_id($project_id){
        return M('task_project','','DB_MEETING')->where(['project_id'=>$project_id])->getField('project_name');
    }

    public function get_project_by_id($project_id){
        return M('task_project','','DB_MEETING')->where(['project_id'=>$project_id])->find();
    }

    public function get_project_by_id_all($project_id){
        $project = M('task_project','','DB_MEETING')
            ->where(['project_id'=>$project_id])
            ->find();

        $project['project_create_id']   = $this->get_project_creater_id_by_project_id($project_id);
        $project['project_create_name'] = D('Audience')->new_get_audience_name_by_uid(StrCode($project['project_create_id'],'DECODE'));
        $project['task_num']            = $this->get_task_num_by_project_id($project_id);
        $project['project_members_id']  = $this->get_project_members_by_project_id($project_id);
        return $project;

    }

//------------------------------------Task----------------------------------------
    public function get_task_id_by_uid($uid,$project_id,$no_resolved){

        if($project_id == 0 && $no_resolved==0){
            $map = [
                'uid'=>$uid,
                '_string'=>'time_insert>time_delete'
            ];
            $rs = M('task_task_members','','DB_MEETING')->field('distinct task_id')->where($map)->select();
        }else{
            $map = [
                'uid'=>$uid,
                '_string'=>'a.time_insert>a.time_delete',
            ];
            $project_id !=0 && $map['b.task_project_id']=$project_id;
            $no_resolved!=0 && $map['b.task_time_done'] ='0';
            $rs = M('task_task_members','','DB_MEETING')
                ->alias('a')
                ->join('join task b on a.task_id=b.task_id')
                ->field('distinct a.task_id')
                ->where($map)
                ->select();
        }
        if(!$rs)return false;
        return i_array_column($rs,'task_id');
    }

    public function get_task_by_id($task_id){
        $map = [
            'task_id'=>$task_id
        ];
        $field = '`task_id`, `task_content`, `task_creater_id`, `task_project_id`, `task_time_begin`, `task_time_done`, `task_time_end`, `task_notice_time`, `task_notice_repeat`, `task_desc`, `task_importance`, `task_urgency`, `time_insert`,`time_update`';
        return M('task','','DB_MEETING')->field($field)->where($map)->find();
    }

    public function get_task_by_project_id($project_id,$uid=0){
        $map = [
            'task_project_id'=>$project_id
        ];
        $project_id == 1 && $map['task_creater_id'] = $uid;
        $field = '`task_id`, `task_content`, `task_creater_id`, `task_project_id`, `task_time_begin`, `task_time_done`, `task_time_end`, `task_notice_time`, `task_notice_repeat`, `task_desc`, `task_importance`, `task_urgency`';
        return M('task','','DB_MEETING')->field($field)->where($map)->select();
    }

    public function get_task_num_by_project_id($project_id){
        $map = ['task_project_id'=>$project_id];
        return M('task','','DB_MEETING')->where($map)->count();
    }

    public function get_private_task_num_by_uid($uid){
        $map = ['task_project_id'=>1,'task_creater_id'=>$uid];
        return M('task','','DB_MEETING')->where($map)->count();
    }

    public function get_my_task($data){

        $rs = [];
        $task_ids = $this->get_task_id_by_uid($data['uid'],$data['project_id'],$data['no_resolved']);

        foreach($task_ids as $val){

            $task = $this->get_task_by_id($val);

            $task['task_members_id'] = $this->get_task_members_by_task_id($val);
            $task['task_members_id'] == false && $task['task_members_id']='';

            $project = $this->get_project_by_id($task['task_project_id']);
            $task['task_project_name']    = $project['project_name'];
            $task['task_project_color']   = $project['project_color'];
            $task['task_project_end_time']= $project['project_time_end'];
            $task['task_project_members_id']  = $this->get_project_members_by_project_id($task['task_project_id']);

            $task['task_creater_name'] = D('Audience')->new_get_audience_name_by_uid($task['task_creater_id']);


            //未加密
            $members = explode(',',$task['task_members_id']);

            $task['task_is_creator']      = $task['task_creater_id'] == $data['uid'] ? '1' : '0';
            $execution                    = $this->get_task_execution($task['task_id']);

            $execution_uid                = array_unique((array)i_array_column($execution,'uid'));
            $task['task_execution']       = count($execution_uid).'/'.count($members);

            $task['task_is_finish']       = in_array($data['uid'],$execution_uid) ? '1' : '0';

            //加密
            $task['task_creater_id'] = StrCode($task['task_creater_id']);
            $task['task_is_members']      = in_array($task['task_creater_id'],$members) ? '1' : '0';



            $time = $this->get_task_data_by_id($val,$data['uid']);
            $task['task_read_time']  = $time['time_read']   == ''?'0':$time['time_read'].'';
            $task['task_insert_time']= $time['time_insert'] == ''?'0':$time['time_insert'].'';
            $task['task_update_time']= $time['time_update'] == ''?'0':$time['time_update'].'';
            $task['time'] = $task['time_insert']>=$task['time_update']?$task['time_insert']:$task['time_update'];

            $task_share = $this->get_task_share_log_by_task_id($task['task_id']);
            if($task_share){
                $task = array_merge($task,[
                    'task_is_share_on'          => '1',
                    'task_at_members_id'        => $task_share['task_at_members_id'],
                    'task_is_department'        => $task_share['task_is_department'],
                    'task_share_members_id'     => $task_share['task_share_members_id'],
                    'task_members_department_id'=> $task_share['task_members_department_id'],
                    'task_share_name'           => $task_share['task_share_name']
                ]);
            }else{
                $task = array_merge($task,[
                    'task_is_share_on'          => '0',
                    'task_at_members_id'        => '',
                    'task_is_department'        => '0',
                    'task_share_members_id'     => '0',
                    'task_members_department_id'=> '',
                    'task_share_name'           => ''
                ]);
            }
            $rs[] = $task;
            unset($task);
            unset($task_share);
            unset($time);
        }
        usort($rs,function($a,$b){
            if($a['time'] == $b['time']){
                return 0;
            }
            return $a['time'] > $b['time'] ? -1 : 1;
        });

        return $rs;
    }

    public function get_my_project($data){
        $rs = [];
        $project_ids = $this->get_project_id_by_uid($data['uid']);
        foreach($project_ids as $val){
            $project = $this->get_project_by_id($val);

            if($val == 1){
                $project['project_create_id']   = StrCode($data['uid']);
                $audience = D('Audience')->new_get_audience_by_uid($data['uid']);
                $project['project_create_name']     = $audience['audience_name'];
                $project['project_create_portrait'] = $audience['audience_portrait'];
                $project['task_num']            = $this->get_private_task_num_by_uid($data['uid']);
                $project['project_members_id']  = $project['project_create_id'];
            }else{
                $project['project_create_id']   = StrCode($this->get_project_creater_id_by_project_id($val));
                $audience = D('Audience')->new_get_audience_by_uid(StrCode($project['project_create_id'],'DECODE'));
                $project['project_create_name']     = $audience['audience_name'];
                $project['project_create_portrait'] = $audience['audience_portrait'];
                $project['task_num']            = $this->get_task_num_by_project_id($val);
                $project['project_members_id']  = $this->get_project_members_by_project_id($val);
            }
            $project['time'] = $project['time_insert']>=$project['time_update']?$project['time_insert']:$project['time_update'];

            unset($project['time_insert']);
            unset($project['time_update']);
            unset($project['time_delete']);
            $rs[] = $project;
            unset($project);

        }

        usort($rs,function($a,$b){
            if($a['time'] == $b['time']){
                return 0;
            }
            return $a['time'] > $b['time'] ? -1 : 1;
        });

        return $rs;
    }

    public function get_task_by_project($data){

        $rs = [];

        $tasks = $this->get_task_by_project_id($data['project_id'],$data['uid']);

        foreach($tasks as $val){
            $val['task_members_id'] = $this->get_task_members_by_task_id($val['task_id']);
            $val['task_members_id'] == false && $val['task_members_id']='';

            $project = $this->get_project_by_id($val['task_project_id']);
            $val['task_project_name']        = $project['project_name'];
            $val['task_project_color']       = $project['project_color'];
            $val['task_project_end_time']    = $project['project_time_end'];
            $val['task_creater_name']        = D('Audience')->new_get_audience_name_by_uid($val['task_creater_id']);
            $val['task_project_members_id']  = $this->get_project_members_by_project_id($val['task_project_id']);
            
            $time = $this->get_task_data_by_id($val['task_id'],$data['uid']);
            $val['task_read_time']  = $time['time_read']   == ''?'0':$time['time_read'].'';
            $val['task_insert_time']= $time['time_insert'] == ''?'0':$time['time_insert'].'';
            $val['task_update_time']= $time['time_update'] == ''?'0':$time['time_update'].'';

            //角色判断

            //未加密
            $members = explode(',',$val['task_members_id']);
            $val['task_is_creator']      = $val['task_creater_id'] == $data['uid'] ? '1' : '0';
            $execution = $this->get_task_execution($val['task_id']);
            $execution_uid = i_array_column($execution,'uid');
            $val['task_execution']       = count($execution_uid).'/'.count($members);
            $val['task_is_finish']       = in_array($data['uid'],$execution_uid) ? '1' : '0';

            //加密
            $val['task_creater_id'] = StrCode($val['task_creater_id']);
            $val['task_is_members'] = in_array($val['task_creater_id'],$members) ? '1' : '0';


            $task_share = $this->get_task_share_log_by_task_id($val['task_id']);
            if($task_share){
                $val = array_merge($val,[
                    'task_is_share_on'          => '1',
                    'task_at_members_id'        => $task_share['task_at_members_id'],
                    'task_is_department'        => $task_share['task_is_department'],
                    'task_share_members_id'     => $task_share['task_share_members_id'],
                    'task_members_department_id'=> $task_share['task_members_department_id'],
                    'task_share_name'           => $task_share['task_share_name']
                ]);
            }else{
                $val = array_merge($val,[
                    'task_is_share_on'          => '0',
                    'task_at_members_id'        => '',
                    'task_is_department'        => '0',
                    'task_share_members_id'     => '0',
                    'task_members_department_id'=> '',
                    'task_share_name'           => ''
                ]);
            }



            $rs[] = $val;
            unset($val);
            unset($task_share);
        }

        return $rs;
    }

    public function get_task_share_log_by_task_id($task_id){
        return M('task_share_log','','')->where(['task_id'=>$task_id])->find();
    }
//----------------------------------other------------------------------------------
    public function task_share_log($task_id,$data,$message,$action){


        if($action == 'del'){
            M('task_share_log','','DB_MEETING')->where('task_id='.$task_id)->delete();
            return;
        }

        $new_data = [
            'task_id'                   => $task_id,
            'task_is_share_on'          => '1',
            'task_at_members_id'        => $data['task_at_members_id'],
            'task_is_department'        => $data['task_is_department'],
            'task_share_members_id'     => $data['task_share_members_id'],
            'task_members_department_id'=> $data['task_members_department_id'],
            'task_share_name'           => $data['task_share_name']
        ];
        $log_reminds = [];
        $msg         = '';
        $i           = 0;
        if($new_data['task_at_members_id']!=''){

            foreach(explode(',',$new_data['task_at_members_id']) as $val){
                $log_remind = [];
                $audience_name = D('Audience')->new_get_audience_name_by_uid(StrCode($val,'DECODE'));
                $strlen = mb_strlen($audience_name,'utf8');

                $log_remind['uid']      = $val;
                $log_remind['at_local'] = $i;

                $i += $strlen+1;
                $log_remind['at_length']= $strlen+1;

                $log_reminds[] = $log_remind;
                $msg .= '@'.$audience_name.' ';
                $i++;
            }
        }

        if($data['task_project_id'] != 1){
            $task_members_array=array_map(function($v){
                return D('Audience')->new_get_audience_name_by_uid(StrCode($v,'DECODE'));
            },explode(',',$data['task_members_id']));
            $msg = $msg.$message.implode(',',$task_members_array);
        }else{
            $msg = $msg.$message;
        }

        if($data['task_share_members_id']==''){
            $req          = json_decode($_REQUEST['req'],true);
            $cid          = StrCode($req['data']['cid'],'DECODE');
            $audience_ids = D('Audience')->get_uid_by_cid($cid);
            $share_range = array_map(function($v){
                return StrCode($v);
            },$audience_ids);
        }else{
            $share_range = explode(',',$data['task_share_members_id']);
        }

        D('Company/Daily_log')->create_daily_log([
            'cid'            => StrCode($this->req_data['data']['cid'],'DECODE'),
            'log_create_uid' => $data['uid'],
            'log_content'    => $msg,
            'share_range'    => $share_range,
            'log_remind'     => $log_reminds,
        ]);
        if($action == 'add')
            M('task_share_log','','DB_MEETING')->add($new_data);
        elseif($action == 'upt'){
            M('task_share_log','','DB_MEETING')->where('task_id='.$task_id)->delete();
            M('task_share_log','','DB_MEETING')->add($new_data);
        }
    }
}