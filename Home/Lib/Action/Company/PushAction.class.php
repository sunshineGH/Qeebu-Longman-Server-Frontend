<?php
require(C('PHPMQTTCLIENT'));

class PushAction extends Action{

	private $passphrase = '1234';

    private $sound = [
        'module'=>1,//模块消息
        'team'  =>2 //team消息
    ];
//-----------------------------------------insert--------------------------------------
    public function reg_devicetoken(){

        $_REQUEST["uid"]		=check_null(40002,true,$_REQUEST["uid"]);
        $_REQUEST["mobiletype"] =check_null(40064,true,$_REQUEST["mobiletype"]);
        $_REQUEST["devicetoken"]=check_null(40018,true,$_REQUEST["devicetoken"]);
        $_REQUEST["version"] 	=check_null(40066,true,$_REQUEST["version"]);
        $_REQUEST['timestamp']  =time();
        if($_REQUEST['devicetoken']=='(null)'){return_json(40018);return;}

        $rs = D('Push')->add_devicetoken($_REQUEST);

        if($rs){
            return_json(0);
        }else{
            return_json(-1);
        }
    }
//-----------------------------------------delete--------------------------------------


    public function solve_push_notice(){

        $data = [
            'cid'           =>$_REQUEST['cid'],
            'audience_id'   =>check_null(40002,true,$_REQUEST["uid"]),
            'push_notice_id'=>check_null(40128,true,$_REQUEST["push_notice_id"]),
            'push_type'     =>check_null(40129,true,$_REQUEST["push_type"]),
        ];
        D('Push')->solve_push_notice($data);
        return_json(0);

    }

    public function check_push_notice(){

        $data = [
            'cid'=>check_null(40038,true,$_REQUEST["cid"]),
            'uid'=>check_null(40002,true,$_REQUEST["uid"]),
        ];
        $rs = D('Push')->check_push_notice($data);
        if($rs)
            return_json(0);
        else
            return_json(-1);
    }
//-----------------------------------------update--------------------------------------
    public function update_push_badge(){
        if(!Database($_REQUEST["cid"]))exit;
        $_REQUEST["uid"]			=check_null(40002,true,$_REQUEST["uid"]);
        $_REQUEST["devicetoken"] 	=check_null(40018,true,$_REQUEST["devicetoken"]);

        $rs=D('Push')->init_badge($_REQUEST);
        if($rs){
            return_json(0);
        }else{
            return_json(-1);
        }
    }
//-----------------------------------------select--------------------------------------
    public function get_my_notice(){
        if(!Database($_REQUEST['cid']))return;
        $data = [
            'uid'      =>check_null(40002,true,$_REQUEST["uid"]),
            'timestamp'=>check_null(40042,true,$_REQUEST["timestamp"])
        ];
        $rs = D('Push')->get_my_push_notice($data);
        if($rs){
            return_json(0,$rs,time());
        }else{
            return_json(40001,[],time());
        }

    }

    public function get_my_home_notice(){

        $data = [
            'uid'      =>check_null(40002,true,$_REQUEST["uid"]),
            'timestamp'=>check_null(40042,true,$_REQUEST["timestamp"])
        ];

        if($_REQUEST['cid']!=''){
            if(!Database($_REQUEST['cid']))return;
            $rs = D('Push')->get_my_home_notice($data);
            if(!$rs){
                if($data['timestamp']==0){
                    $rs = D('Push')->get_init_data();
                    return_json(0,$rs,time());
                }else{
                    return_json(40001,[],time());
                }
            }else{
                return_json(0,$rs,time());
            }
        }else{
            if($data['timestamp']==0){
                $rs = D('Push')->get_init_data();
                return_json(0,$rs,time());
            }else{
                return_json(40001,[],time());
            }
        }
    }

    public function get_my_team_notice(){
        $data = [
            'uid'      =>check_null(40002,true,$_REQUEST["uid"]),
            'timestamp'=>check_null(40042,true,$_REQUEST["timestamp"])
        ];
        $rs = D('Push')->get_my_team_push_notice($data);
        if($rs)
            return_json(0,$rs,time());
        else
            return_json(40001,[],time());
    }

    public function get_my_team_news(){
        $data = [
            'uid'      =>check_null(40002,true,$_REQUEST["uid"]),
            'timestamp'=>check_null(40042,true,$_REQUEST["timestamp"])
        ];
        $rs = D('Push')->get_my_team_push_news($data);
        if($rs)
            return_json(0,$rs,time());
        else
            return_json(40001,[],time());
    }

    public function meeting_push($meeting_id,$message,$arr=[]){

        if($_REQUEST['cid']!=102){
            $this->meeting_push_reg($meeting_id,$message,$arr);
            return;
        }

        $post_data = [
            'sound'=>$this->sound['module'],
            'push_key_words'=>'home',
            'message'       =>$message
        ];

        $tokens = D('Push')->get_meeting_uri($meeting_id);

        $this->push_notice($tokens,$post_data);
    }

    public function meeting_push_reg($meeting_id,$message,$arr=[]){

        $cid = is_numeric($_REQUEST['cid']) ? $_REQUEST['cid'] : StrCode($_REQUEST['cid'],'DECODE');
        $company_name = D('Team')->get_team_name_by_cid($cid);

        $meeting_name = D('Meeting')->get_title_by_id($meeting_id);
        $message = '来自['.$company_name.']的提醒: '.$message.'会议名称:['.$meeting_name.'],请注意查看!';

        $post_data = [
            'sound'  => $this->sound['module'],
            'message'=> $message,
            'push_key_words' => 'home'
        ];

        $tokens = D('Push')->get_meeting_uri($meeting_id,$message,$arr);

        $this->push_notice($tokens,$post_data);
    }

    public function task_push($task_id,$message,$user_arr){

        $req_data = json_decode($_REQUEST['req'],true);
        $cid      = StrCode($req_data['data']['cid'],'DECODE');
        $company_name = D('Team')->get_team_name_by_cid($cid);

        $task_content = D('Task')->get_task_content_by_id($task_id);
        $message = '来自['.$company_name.']的提醒: '.$message.',任务名称: ['.$task_content.'],请注意查看!';

        $post_data = [
            'sound'  => $this->sound['module'],
            'message'=> $message,
            'push_key_words' => 'home'
        ];

        $tokens = D('Push')->get_task_uri($task_id,$message,$user_arr);
        $this->push_notice($tokens,$post_data);
    }

    public function leave_push($message,$user_arr){

        $req_data = json_decode($_REQUEST['req'],true);
        $cid      = StrCode($req_data['data']['cid'],'DECODE');
        $company_name = D('Team')->get_team_name_by_cid($cid);

        $message = '来自['.$company_name.']的提醒: '.$message;

        $post_data = [
            'sound'  => $this->sound['module'],
            'message'=> $message,
            'push_key_words' => 'home'
        ];

        $tokens = D('Push')->get_leave_uri($message,$user_arr);
        $this->push_notice($tokens,$post_data);
    }

    public function daily_log_push($log_id,$message){

        $req_data = json_decode($_REQUEST['req'],true);
        $cid      = StrCode($req_data['data']['cid'],'DECODE');
        $company_name = D('Team')->get_team_name_by_cid($cid);

        $message = '来自['.$company_name.']的提醒: '.$message.',请注意查看!';

        $post_data = [
            'sound'  => $this->sound['module'],
            'message'=> $message,
            'push_key_words' => 'home'
        ];

        $tokens = D('Push')->get_daily_log_uri($log_id,$message);
        $this->push_notice($tokens,$post_data);
    }

    public function daily_log_reply_push($reply_id,$message){

        $req_data = json_decode($_REQUEST['req'],true);
        $cid      = StrCode($req_data['data']['cid'],'DECODE');
        $company_name = D('Team')->get_team_name_by_cid($cid);

        $message = '来自['.$company_name.']的提醒: '.$message.',请注意查看!';

        $post_data = [
            'sound'  => $this->sound['module'],
            'message'=> $message,
            'push_key_words' => 'home'
        ];

        $tokens = D('Push')->get_daily_log_reply_uri($reply_id,$message);
        $this->push_notice($tokens,$post_data);
    }

    public function daily_log_remind_push($log_id=0,$reply_id=0,$message,$without=0){

        $req_data = json_decode($_REQUEST['req'],true);
        $cid      = StrCode($req_data['data']['cid'],'DECODE');
        $company_name = D('Team')->get_team_name_by_cid($cid);

        $message = '来自['.$company_name.']的提醒: '.$message.',请注意查看!';

        $post_data = [
            'sound'  => $this->sound['module'],
            'message'=> $message,
            'push_key_words' => 'home'
        ];

        $tokens = D('Push')->get_daily_log_remind_uri($log_id,$reply_id,$message,$without);
        $this->push_notice($tokens,$post_data);
    }

    public function team_push($team_id,$message,$state,$user_arr=[],$uid,$admin_id,$old_company_name=''){

        $company_name = D('Team')->get_team_name_by_cid($team_id);
        $message = '来自['.$company_name.']的提醒: '.$message.',请注意查看!';

        $tokens = D('Push')->get_team_uri($team_id,$message,$state,$user_arr,$uid,$admin_id,$old_company_name);

        $post_data = [
            'sound'  => $this->sound['team'],
            'message'=> $message,
            'push_key_words' => 'home'
        ];

        $this->push_notice($tokens,$post_data);

    }

    public function leave_team_push($team_id,$message,$uid){
        $company_name = D('Team')->get_team_name_by_cid($team_id);
        $message = '来自['.$company_name.']的提醒: '.$message.',请注意查看!';
        $tokens = D('Push')->get_leave_team_uri($team_id,$message,$uid);
        $post_data = [
            'sound'         => $this->sound['team'],
            'message'       => $message,
            'push_key_words'=> 'home'
        ];
        $this->push_notice($tokens,$post_data);
    }

//------------------------------public function----------------------------------------

    public function push_notice($tokens,$post_data){

        foreach($tokens as $val){

            $val['mobiletype']  = isset($val['mobiletype']) ? $val['mobiletype'] : $val['ad_or_ios'];
            $val['devicetoken'] = isset($val['devicetoken'])? $val['devicetoken']: $val['token'];

            if($val['mobiletype'] == 1){
                $post_data['tokens'][] = array(
                    'token' => $val['devicetoken'],
                    'badge' => (int)$val['badge'] + 1
                );
                $arr['app_for'] = 'home';
                $arr['uid']     = $val['uid'];
                D('Push')->update_badge_number(((int)$val['badge'] + 1),$arr);
            }else{
                $push['message'] 	= 'meeting:'.$post_data['message'];
                $push['devicetoken']= $val['devicetoken'];
                $this->ANDROID_pushmsg($push);
            }
        }
        $this->push_more($post_data);

    }

    //push more for ios push
    public function push_more($post_data){
        if($_REQUEST['cid']!='102')
            $post_data['push_key_words'] = 'teamin';

        $post_data=json_encode($post_data,JSON_UNESCAPED_UNICODE);
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,"http://115.29.41.83/Public/apnsphp/sample_push_many.php");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('json'=>$post_data));
        curl_exec($ch);
        curl_close($ch);
    }

    //push one for android push
    public function ANDROID_pushmsg($push){
        $conn        = new SAMConnection();
        $conn_result = $conn->connect(SAM_MQTT, [SAM_HOST=>'127.0.0.1',SAM_PORT=>1883]);
        if (!$conn_result) return false;
        $msgCpu  = new SAMMessage($push['message']);
        $title   = C('ANDROID_PUSH_KEYWORDS');
        $target  = $title.'/'.$push['devicetoken'];
        $correlId= $conn->send('topic://'.$target,$msgCpu);
        $re=true;
        if (!$correlId){
            $re=false;
        }
        $conn->disconnect();
        return $re;
    }

    //android push test
    public function ad_test(){
        $token = $_GET['token'] ? $_GET['token'] : 'e7cb79bdc6c92f51';

        $conn        = new SAMConnection();
        $conn_result = $conn->connect(SAM_MQTT,array(SAM_HOST=>'127.0.0.1',SAM_PORT=>1883));

        if (!$conn_result){
            var_dump(2);
        }
        $msgCpu  = new SAMMessage('You have a message!');
        $title = C('ANDROID_PUSH_KEYWORDS');
        $target  = $title.'/'.$token;
        $correlId= $conn->send('topic://'.$target,$msgCpu);
        $re=true;
        if (!$correlId){
            $re=false;
        }
        // close connection
        $conn->disconnect();

        var_dump($msgCpu);
        var_dump($title);
        var_dump($correlId);
        var_dump($re);
    }

    //push one for ios
    public function push_one($data){
        if(!Database($data["cid"]))exit;
        $re	                = D('Push')->get_data_by_uid($data['uid'],$data['app_for']);
        $push['message']	= "[".$data['title']."]".$data['content'];
        $push['devicetoken']= $re['devicetoken'];
        $push['badge']      = (int)$re['badge'] + 1;
        $push['sound']      = $data['sound']!=''?$data['sound']:'default';
        $push['app_for']    = $data['app_for'];

        D('Push')->update_badge_number($push['badge'],$data);

        if($this->push_ios($push)){
            return true;
        }else{
            return false;
        }
    }

    //push many
    public function push_many($title,$content,$keywords){
        //push init
        if($_REQUEST['cid']!='102')
            $keywords = 'teamin';
        else
            $post_data['push_key_words'] = $keywords;
        $post_data['message']        = "【".$title."】".$content;
        $post_data['message']        = mb_substr($post_data['message'],0,30);
        $post_data['message']        = nl2br($post_data['message']);
        $post_data['message']        = strip_tags( $post_data['message']);

        $tokens = D('Uri')->get_devicetokens($keywords);

        foreach($tokens as $val){
            if($val['mobiletype'] == 1){
                $post_data['tokens'][] = array(
                    'token' => $val['devicetoken'],
                    'badge' => (int)$val['badge'] + 1
                );
                $arr['app_for'] = 'home';
                $arr['uid']     = $val['uid'];
                D('Push')->update_badge_number(((int)$val['badge'] + 1),$arr);
            }
        }
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
            )
        );
        $ck_path = 'Public/Core/Pems/'.$push['app_for'].'_ck.pem';

        $ctx = stream_context_create();
        stream_context_set_option($ctx,"ssl","local_cert",$ck_path);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);

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

    public function push_test_1(){

        $push = [
            'message'=>'1122344',
            'badge'  =>'1',
            'sound'  =>'default',
            'app_for'=>'cx',
            'devicetoken'=>'0274ae9ad74c36c1c6f0287dea7e371a17aa84a523a72f5b5956187b8b6d2c1b'
        ];

        $body = array(
            "aps" => array(
                "category"          => "alert",
                'content-available' => 1,
                "alert"             => $push['message'],
                "badge"             => $push['badge'],
                "sound"             => $push['sound']
            )
        );
        $ck_path = 'Public/Core/Pems/'.$push['app_for'].'_ck.pem';

        $ctx = stream_context_create();
        stream_context_set_option($ctx,"ssl","local_cert",$ck_path);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);

        $fp = stream_socket_client("ssl://gateway.push.apple.com:2195", $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp)
            die('链接失败!');

        $payload = json_encode($body);
        $msg = chr(0) . pack("n",32) . pack("H*", str_replace(' ', '', $push['devicetoken'])) . pack("n",strlen($payload)) . $payload;
        fwrite($fp, $msg);
        fclose($fp);
        echo '发送成功';

    }

    //old function not use but not delete
    public function get_uris(){

        //数据初始化
        switch(''){
            case $_POST['message']:$this->no_error(-1,"no message");return;
            case $_POST['mobiletype']:$this->no_error(-1,"no type");return;
        }
        $timestamp	=time();
        $data['title']		=$_POST['title'];
        $data['towho']		=$_POST['sel'];
        $data['message']   	=$_POST["message"];
        $data['mobiletype']	=$_POST["mobiletype"];
        $data['sort']		=(int)$_POST['sort'];
        if (preg_replace('/[\n\r\t]/','',preg_replace('/\s(?=\s)/','',trim($data['message'])))==''){
            $this->no_error(-1,"uncorrct message");
            return;
        }
        $data['time_insert']=$timestamp;
        $data['time_update']=$timestamp;
        $data['time_delete']=-1;
        $data['title']=$_POST['title'];

        //添加
        if(!M('push_message','','DB_MEETING')->add($data)){
            $this->no_error(-2,"Add message fail");
            return;
        }
        add_time_center('push');

        //获取需要推送的设备号
        $sql['uri']="select * from uri where 1=1";
        switch($data['mobiletype']){
            case 'IOS':$sql['uri'].=" and mobiletype='IOS'";break;
            case 'ANDROID':$sql['uri'].=" and mobiletype='ANDROID'";break;
        }
        if($data['towho']>0)
            $sql['uri'].=" and group_id={$data['towho']}";
        $uris=M('uri','','DB_MEETING')->query($sql['uri']);
        if(!$uris){
            $this->no_error(-3,'find no device');
            return;
        }

        //信息推送
        //init 推送信息
        $push['message']="[".$data['title']."]".$data['message'];
        if(mb_strlen($push['message'])>60)
            $push['message']=mb_substr($push['message'],0,30,'UTF-8')."......";
        //init 声音
        $push['sound'] = 'default';
        //init 成功条数
        $right = 0;
        foreach($uris as $val){
            //init devicetoken
            $push['devicetoken']=$val['devicetoken'];
            if($val['mobiletype'] == "IOS"){
                //init badge
                $cond["devicetoken"] = $val['devicetoken'];
                $timestamp = (int)(M("uri","",'DB_MEETING')->where($cond)->getField("timestamp"));
                $push['badge']=M("push_message","",'DB_MEETING')->where("mobiletype!='ANDROID' and time_insert >= {$timestamp}")->count();

                $re=$this->IOS_pushmsg($push);
                if($re)
                    $right++;
            }
            if($val['mobiletype'] == "ANDROID"){
                $re=$this->ANDROID_pushmsg($push);
                if($re)
                    $right++;
            }
        }
        $this->no_error(1,"sucess number: ".$right."/".count($uris));
    }

    //push one test
    public function push_test(){
        $push['message']	= '您有一条新的消息';
        $push['devicetoken']= 'dab9cc1bcd32487727652b4444e2a2d167fdb44356aaf4b2fd9db160554cbc68';
        $push['sound']      = 'default';
        $push['app_for']    = 'teamin';
        $push['badge']      = 1;

        if($this->push_ios($push)){
            dump(1);
            return true;
        }else{
            dump(0);
            return false;
        }
    }
}
?>