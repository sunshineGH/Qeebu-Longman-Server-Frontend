<?php

class Push_notificationAction extends Action{

/*
1.国际认证考试：
有新的考试活动发布了，快来报名吧~

2.俱乐部
有新的俱乐部活动发布了，快来报名吧~

3.作业发布
有一条新的作业通知

4.请假审核
您的请假申请已经通过，请确认补课时间

teacher
5.课程安排
（1）XX课程到了关键节点，请做好教学准备
（2）XX课程的作为状态有了变化，请查看

6.作业提交
 XXX提交了什么什么XXX日期的XXX作业[done]

7.请假审核（老师）
（1）XXX提交了请假申请，请尽快审核[done]
（2）XXX确认了补课时间[done]
（3）XXX没有确定补课时间，请重新安排

8.特殊情况处理
 XXX提交了转班/延期/退费/转校申请[done]
 * */

//--------------------------------------insert----------------------------------------
    public function ln_reg_device_token($data){

        D('User')->check_token($data['uid'],$data['token']);
        check_null(40039,true,$data["phone_system"]);
        check_null(40040,true,$data['device_token']);
        check_null(40041,true,$data['version']);
        $data['last_open_time'] = time();
        $data['time_insert']    = time();
        $data['badge']          = 0;

        $rs = D('Push_notification')->reg_device_token($data);
        if($rs){
            return_json(0);
        }else{
            return_json(-1);
        }
    }

    public function special_case_push($data){

        $type = ['转班','转校','延期','退费'];
        $nickname  = D('Company/User')->get_user('',$data['uid'],'nickname')['nickname'];
        $message   = $nickname.'提交了'.$type[$data['type']].'申请';

        $teachers  = $this->get_class($data['class_id'],'zh_teacher,hm_teacher');
        $uid_arr[] = $teachers['zh_teacher'];
        $uid_arr[] = $teachers['hm_teacher'];
        $tokens    = D('Push_notification')->get_tokens($uid_arr);
        foreach($uid_arr as $val){
            D('Push_notification')->add_push_notice([
                'uid'     =>$val,
                'content' =>$message,
                'model_id'=>8
            ]);
        }
        $this->push_notice($tokens,['message'=>$message]);
    }

    public function leave_push($data){

        $nickname  = D('Company/User')->get_user('',$data['uid'],'nickname')['nickname'];
        $message   = $nickname.'提交了请假申请，请尽快审核';

        $teachers  = $this->get_class($data['class_id'],'zh_teacher,hm_teacher');
        $uid_arr[] = $teachers['zh_teacher'];
        $uid_arr[] = $teachers['hm_teacher'];
        $tokens    = D('Push_notification')->get_tokens($uid_arr);
        foreach($uid_arr as $val){
            D('Push_notification')->add_push_notice([
                'uid'     =>$val,
                'content' =>$message,
                'model_id'=>7
            ]);
        }
        $this->push_notice($tokens,['message'=>$message]);
    }
    //leave_history_push
    public function leave_history_push($data){

        $nickname  = D('Company/User')->get_user('',$data['uid'],'nickname')['nickname'];
        $message   = $nickname.'确认了补课时间';

        $teachers  = $this->get_class($data['class_id'],'zh_teacher,hm_teacher');
        $uid_arr[] = $teachers['zh_teacher'];
        $uid_arr[] = $teachers['hm_teacher'];
        $tokens    = D('Push_notification')->get_tokens($uid_arr);
        foreach($uid_arr as $val){
            D('Push_notification')->add_push_notice([
                'uid'      => $val,
                'content'  => $message,
                'model_id' => 7
            ]);
        }
        $this->push_notice($tokens,['message'=>$message]);
    }
    //leave_teacher_pass_push
    public function leave_teacher_pass_push($data){
        $message   = '您的请假申请已经通过，请确认补课时间';
        $uid_arr[] = $data['student_id'];
        $tokens    = D('Push_notification')->get_tokens($uid_arr);
        foreach($uid_arr as $val){
            D('Push_notification')->add_push_notice([
                'uid'      => $val,
                'content'  => $message,
                'model_id' => 7
            ]);
        }
        $this->push_notice($tokens,['message'=>$message]);
    }

    //home_work_submit_push
    public function home_work_submit_push($data){

        $nickname = D('Company/User')->get_user('',$data['uid'],'nickname')['nickname'];
        $homework = D('Company/Homework')->get_homework('class_homework_id','title,homework_date');
        $message  = $nickname.'提交了'.$homework['homework_date'].'的'.$homework['title'].'作业';

        $teachers  = $this->get_class($data['class_id'],'zh_teacher,hm_teacher');
        $uid_arr[] = $teachers['zh_teacher'];
        $uid_arr[] = $teachers['hm_teacher'];
        $tokens    = D('Push_notification')->get_tokens($uid_arr);
        foreach($uid_arr as $val){
            D('Push_notification')->add_push_notice([
                'uid'     =>$val,
                'content' =>$message,
                'model_id'=>6
            ]);
        }
        $this->push_notice($tokens,['message'=>$message]);
    }

    public function active_push($category){
        $message   ='';
        if($category == 1){
            $message = '有新的俱乐部活动发布了，快来报名吧~';
        }else if($category == 2){
            $message = '有新的考试活动发布了，快来报名吧~';
        }

        $uid_arr   = D('User')->get_all_student_uid();
        $tokens    = D('Push_notification')->get_tokens($uid_arr);
        foreach($uid_arr as $val){
            D('Push_notification')->add_push_notice([
                'uid'      => $val,
                'content'  => $message,
                'model_id' => $category == 1 ? 2 : 1
            ]);
        }
        $this->push_notice($tokens,['message'=>$message]);
    }

//--------------------------------------delete----------------------------------------
    public function ln_delete_device_token($data){

        D('User')->check_token($data['uid'],$data['token']);

        $rs = D('Push_notification')->delete_uri_by_uid($data['uid']);

        if($rs){
            return_json(0);
        }else{
            return_json(-1);
        }
    }
//--------------------------------------select----------------------------------------
    public function ln_get_notice_numbers($data){

        D('User')->check_token($data['uid'],$data['token']);

        $rs = D('Push_notification')->get_notice_numbers($data);
        if($rs){
            return_json(0,$rs);
        }else{
            return_json(-1);
        }
    }

    public function ln_get_notice_list($data){

        D('User')->check_token($data['uid'],$data['token']);
        check_null('40042',true,$data['model_id']);

        $rs = D('Push_notification')->get_notice_list($data);
        if($rs){
            return_json(0,$rs);
        }else{
            return_json(40001);
        }
    }

//--------------------------------------others----------------------------------------

    public function push_test(){
        $push['message']	= '您有一条新的消息';
        $push['devicetoken']= 'cae3e5df984432d8c3b19da2a8de9ded54ea4397238e1bc0c0909cd57a81b091';
        $push['sound']      = 'LongMan';
        $push['app_for']    = 'longman';
        $push['badge']      = 1;

        if($this->push_ios($push)){
            dump(1);
            return true;
        }else{
            dump(0);
            return false;
        }
    }

    public function push_test1(){
        $post_data = [
            'sound'  => 'LongMan',
            'message'=> 'you have message!'
        ];
        $post_data['tokens'][] = [
            'token'=>'cae3e5df984432d8c3b19da2a8de9ded54ea4397238e1bc0c0909cd57a81b091',
            'badge'=>'1'
        ];
        $this->push_more($post_data);
    }

    public function push_ios($push){
        $body = array(
            "aps" => array(
                "category"          => "alert",
                'content-available' => 1,
                "alert"             => $push['message'],
                "badge"             => $push['badge'],
                "sound"             => $push['sound']
            ),
            'coming_by' =>'LongMan',
        );
        $ck_path = 'Public/Pems/lening_ck.pem';

        $ctx = stream_context_create();
        stream_context_set_option($ctx,'ssl','local_cert',$ck_path);
        stream_context_set_option($ctx,'ssl','passphrase','1234');

        $fp = stream_socket_client("ssl://gateway.push.apple.com:2195", $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            return false;
        }
        $payload = json_encode($body);
        $msg = chr(0) . pack("n",32) . pack("H*", str_replace(' ', '', $push['devicetoken'])) . pack("n",strlen($payload)) . $payload;
        fwrite($fp, $msg);
        fclose($fp);
        return true;
    }

    public function push_notice($tokens,$post_data){
        foreach($tokens as $val){
            if($val['phone_system'] == 1){
                $post_data['tokens'][] = array(
                    'token' => $val['device_token'],
                    'badge' => (int)$val['badge'] + 1
                );
                $arr['uid']     = $val['uid'];
                D('Push_notification')->update_badge_number(((int)$val['badge'] + 1),$arr);
            }else{
                $push['message'] 	= 'meeting:'.$post_data['message'];
                $push['devicetoken']= $val['device_token'];
                $this->ANDROID_pushmsg($push);
            }
        }
        $this->push_more($post_data);
    }

    //push more for ios push
    public function push_more($post_data){
        $post_data['push_key_words'] = 'LongMan';
        $post_data['sound']          = 'LongMan';
        $post_data=json_encode($post_data,JSON_UNESCAPED_UNICODE);
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,"http://115.29.41.83/Public/apnsphp/sample_push_many.php");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, array('json'=>$post_data));
        curl_exec($ch);
        curl_close($ch);
    }

    //push one for android push
    public function ANDROID_pushmsg($push){
        $conn        = new SAMConnection();
        $conn_result = $conn->connect(SAM_MQTT, [SAM_HOST=>'127.0.0.1',SAM_PORT=>1883]);
        if (!$conn_result) return false;
        $msgCpu  = new SAMMessage($push['message']);
        $title   = 'lening';
        $target  = $title.'/'.$push['devicetoken'];
        $correlId= $conn->send('topic://'.$target,$msgCpu);
        $re=true;
        if (!$correlId){
            $re=false;
        }
        $conn->disconnect();
        return $re;
    }

}