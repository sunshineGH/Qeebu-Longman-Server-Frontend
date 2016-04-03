<?php

class Push_notificationModel extends Model{


    public $model = [
        //student
        '1'=>'国际认证考试',
        '2'=>'俱乐部',
        '3'=>'作业通知',
        '4'=>'请假审核',
        //teacher
        '5'=>'课程安排',
        '6'=>'作业提交',
        '7'=>'请假',
        '8'=>'特殊情况处理'
    ];
//--------------------------------------insert----------------------------------------
    public function reg_device_token($data){
        $this->delete_uri_by_device_token($data['device_token']);
        $this->delete_uri_by_uid($data['uid']);
        return M('push_uri')->add($data);
    }

    public function add_push_notice($data){
        return M('push_notice')->add([
            'time_insert'=>time(),
            'uid'        =>$data['uid'],
            'content'    =>$data['content'],
            'model_id'   =>$data['model_id']
        ]);
    }
//--------------------------------------update----------------------------------------
    public function update_badge_number($badge,$data){
        return M('push_uri')->where(['uid'=>$data['uid']])->save(['badge'=>$badge]);
    }
//--------------------------------------delete----------------------------------------
    public function delete_uri_by_device_token($device_token){
        $map = ['device_token'=>$device_token];
        return M('push_uri')->where($map)->delete();
    }

    public function delete_uri_by_uid($uid){
        $map = ['uid'=>$uid];
        return M('push_uri')->where($map)->delete();
    }
//--------------------------------------select----------------------------------------

    public function get_notice_numbers($data){

        $role = D('Company/User')->get_user('',$data['uid'],'role')['role'];

        if($role == 1){
            return [
                $this->get_notice_num_list_data($data['uid'],1,$data['model_timestamp'][1]),
                $this->get_notice_num_list_data($data['uid'],2,$data['model_timestamp'][2]),
                $this->get_notice_num_list_data($data['uid'],3,$data['model_timestamp'][3]),
                $this->get_notice_num_list_data($data['uid'],4,$data['model_timestamp'][4])
            ];
        }else{
            return [
                $this->get_notice_num_list_data($data['uid'],5,$data['model_timestamp'][5]),
                $this->get_notice_num_list_data($data['uid'],6,$data['model_timestamp'][6]),
                $this->get_notice_num_list_data($data['uid'],7,$data['model_timestamp'][7]),
                $this->get_notice_num_list_data($data['uid'],8,$data['model_timestamp'][8])
            ];
        }
    }

    public function get_notice_num_list_data($uid,$model_id,$last_time){
        $rs['model_id']    = $model_id;
        $rs['model_name']  = $this->model[$model_id];
        $rs['notice_count']= $this->get_notice_count_by_uid_model_id($model_id,$uid,$last_time);
        $last_content      = $this->get_notice_last_by_uid_model_id($model_id,$uid);
        $rs['content']     = $last_content['content']     ? $last_content['content']    :'';
        $rs['time_insert'] = $last_content['time_insert'] ? $last_content['time_insert']:'0';
        return $rs;
    }

    public function get_notice_last_by_uid_model_id($model_id,$uid){
        $map = [
            'uid'=>$uid,
            'model_id'=>$model_id
        ];
        return M('push_notice')->where($map)->order('id desc')->find();
    }

    public function get_notice_count_by_uid_model_id($model_id,$uid,$last_time){
        $map = [
            'uid'=>$uid,
            'model_id'=>$model_id,
            'time_insert'=>['gt',$last_time]
        ];
        $rs = M('push_notice')->where($map)->count();
        return $rs;
    }

    public function get_notice_list($data){
        $map = [
            'uid'=>$data['uid'],
            'model_id'=>$data['model_id']
        ];
        $rs = M('push_notice')->where($map)->select();
        foreach($rs as &$val){
            unset($val['uid']);
            unset($val['id']);
            unset($val['model_id']);
        }
        return $rs;
    }

    public function get_tokens($uid_arr){
        $map = ['uid'=>['in',implode(',',$uid_arr)]];
        $rs = M('push_uri')->where($map)->select();

        return $rs;
    }



}