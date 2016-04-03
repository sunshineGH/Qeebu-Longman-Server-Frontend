<?php
header("content-type:text/html;charset=utf-8");
class MessageAction extends Action{

    public function send_password(){
        $user=M('audience','','DB_MEETING')->field('`mobile_no`,`nickname`,`username`,`password`')->select();
        $right=0;
        foreach($user as $val){
            $message="Dear {$val['nickname']}!您好!您本次IDG资本移动OA的用户名为:{$val['username']},密码为:{$val['password']},祝你本次上海之行愉快!";
            if($val['mobile_no']!=''){
                $re= $this->mt($val['mobile_no'],$message);
                if($re)
                    $right++;
            }
        }
        echo_status('1','success number :'.$right.'/'.count($user));
    }

    public function sms(){
        switch(''){
            case $_POST['message']:echo_status('-3','missing jsonstr');return;
            case $_SESSION['logged_in']:echo_status(14,'no logged_in');return;
        }
        $message   = $_POST["message"];
        // construct phone_no string
        $mobileNos = D('Audience')->get_mobile_nos();
        if ($mobileNos == ''){
            echo_status(2,'no mobile no');
            return;
        }
        //$mobileNos = '18810461431';,15500023516';
        $result    = $this->mt($mobileNos,$message);
        if ($result)
            echo_status(1,'OK');
        else
            echo_status(8,'send failed');
    }

    public function retrieve_password(){
        if (!isset($_REQUEST["jsonstr"])){
            echo_status('-3','missing jsonstr');
            return;
        }
        $jsonstr = str_replace('\"','"',$_REQUEST["jsonstr"]);
        $jsonreq = json_decode($jsonstr);

        if (!property_exists($jsonreq, "sso_id") or !property_exists($jsonreq, "phone_no")){
            echo_status('-2','missing jsonstr.param');
            return;
        }
        $username = $jsonreq->{"sso_id"};
        $phone_no = $jsonreq->{"phone_no"};
        $password = create_random_password(6);

        $Dao      = D('Audience');
        if(!$existornot = $Dao->has_user_exist($username)){
            echo_status(C('ACTION_STATUS_3.nul_no'),
                'user do not exist');
            return;
        }
        $result = $Dao->set_pwd($username,$password);
        if ($result){
            // send message
            $is_sent = $this->mt($phone_no,'Your New Password:'.$password);

            if ($is_sent){
                echo_status(C('ACTION_STATUS_0.no'),
                    c('ACTION_STATUS_0.msg'));
            } else {
                echo_status(C('ACTION_STATUS_9.fal_no'),
                    'send failed');
            }
        } else {
            echo_status(C('ACTION_STATUS_10.fal_no'),
                C('ACTION_STATUS_10.fal_msg'));
        }
    }



    public function test_mt(){
        $this->mt('18609059892','You have a message');
    }


    /*******************
    mt
    @descr
    send message
    @arg
    $phone_no, ex.'15388650501,15321858155'
     */
    public function mt($phone_no, $content){

        $sn = 'SDK-BBX-010-21735';
        $pwd= '$572-e$2';

        $flag = 0;
        $params = '';
        $argv = array(
            'sn'      =>$sn,
            //'pwd'     =>strtoupper(md5(C('MESSAGE_SN').C('MESSAGE_PWD'))),
            'pwd'     =>strtoupper(md5($sn.$pwd)),
            'mobile'  =>$phone_no,
            'content' =>iconv("UTF-8","gb2312//IGNORE",$content.'【IDG资本】'),
            'ext'     =>'',
            'stime'   =>'',
            'rrid'    =>''
        );

        foreach ($argv as $key=>$value){
            if ($flag!=0){
                $params .= "&";
                $flag    = 1;
            }
            $params .= $key."=";
            $params .= urlencode($value);
            $flag = 1;
        }
        $length = strlen($params);
        $fp     = fsockopen('sdk105.entinfo.cn',8060,
            $errno,$errstr,10) or exit($errstr."--->".$errno);
        $header = "POST /webservice.asmx/mt HTTP/1.1\r\n";
        $header .= "Host:sdk105.entinfo.cn\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".$length."\r\n";
        $header .= "Connection: Close\r\n\r\n";

        $header .= $params."\r\n";
        //echo $header."<br/>";
        fputs($fp,$header);
        $inheader= 1;
        while (!feof($fp)){
            $line = fgets($fp, 1024);
            if ($inheader && ($line == "\n" || $line == "\r\n")){
                $inheader = 0;
            }
            if ($inheader == 0){
                //echo $line;
            }
        }
        $line = str_replace("<string xmlns=\"http://tempuri.org/\">","",$line);
        $line = str_replace("</string>","",$line);
        $result = explode("-",$line);
        if (count($result) > 1)
            return false;
        //echo 'send failed: '.$line;
        else
            return true;
        //echo 'send successful'.$line;
    }

    /***********
    mo_ctl
     */

    public function mo_rep(){
        //echo "hello,world".date("Y-m-d H:i:s",time());
        $this->mo();
    }

    public function mo_agent(){
        //$this->display();
    }

    public function mo_ctl(){
        $switch = $_REQUEST["switch"];
        if ($switch == 'on'){
            $Dao = D("Auxvar");
            if ($Dao->get_value("mo_ctl_bit") == 0)
            {

                D("Auxvar")->set_value("mo_ctl_bit",1);

                // to run timer in background we will use proc_open
                $descriptorspec = array(
                    0 => array("pipe","r"),//stdin
                    //1 => array("pipe","w"),//stdout
                    1 => array("file","./TMP/STDOUT.txt","a"), // stdout > file
                    2 => array("file","./TMP/STUFF.txt","a") //stderr
                );
                $process = proc_open(
                    C('MESSAGE_MO_CMD1'),
                    $descriptorspec, $pipes) or die('error');

                echo_status(C('ACTION_STATUS_0.no'),
                    C('ACTION_STATUS_0.msg'));
            } else
                echo_status(C('ACTION_STATUS_14.no'),
                    C('ACTION_STATUS_14.msg'));
        } elseif ($switch == 'off') {
            if(D("Auxvar")->get_value("mo_ctl_bit") != 0)
            {
                if(D("Auxvar")->set_value("mo_ctl_bit",0) != false){

                    //---------------------------------

                    $descriptorspec = array(
                        0 => array("pipe","r"),//stdin
                        1 => array("file","./TMP/STDOUT.txt","a"), // stdout > file
                        2 => array("file","./TMP/STUFF.txt","a") //stderr
                    );
                    $process = proc_open(
                        C('MESSAGE_MO_CMD2'),
                        $descriptorspec, $pipes) or die('error');

                    //---------------------------------

                    echo_status(C('ACTION_STATUS_0.no'),
                        C('ACTION_STATUS_0.msg'));
                }else
                    echo_status(C('ACTION_STATUS_14.no'),
                        C('ACTION_STATUS_14.msg'));
            } else
                echo_status(C('ACTION_STATUS_14.no'),
                    C('ACTION_STATUS_14.msg'));
        }
    }
    /***********
    mo
     */
    public function mo(){
        //echo "mo-".strtotime("now");
        $flag = 0;
        $argv = array(
            'sn'=>C('MESSAGE_SN'),
            'pwd'=>strtoupper(md5(C('MESSAGE_SN').C('MESSAGE_PWD')))
        );
        // construct post-string
        $params = '';
        foreach ($argv as $key=>$value){
            if ($flag != 0){
                $params .= "&";
                $flag    = 1;
            }
            $params .= $key."=";
            $params .= urlencode($value);
            $flag    = 1;
        }
        $length = strlen($params);
        // create socket connection
        $fp = fsockopen(C('MESSAGE_HOST'),C('MESSAGE_PORT'),
            $errno, $errstr, 10) or exit($errstr."--->".$errno);
        // construct post header
        $header = "POST /webservice.asmx/mo HTTP/1.1\r\n";
        $header .= "Host:".C('MESSAGE_HOST')."\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".$length."\r\n";
        $header .= "Connection: Close\r\n\r\n";
        // add post-string
        $header .= $params."\r\n";
        // send post data
        $line = '';
        fputs($fp, $header);
        while(!feof($fp)){
            $line .= fgets($fp, 1024);
        }
        list(,$line)=explode("\r\n\r\n",$line,2);
        //<string xmlns="http://tmpuri.org/">-5</string>
        $line = str_replace("<string xmlns=\"http://tempuri.org/\">","",$line);
        $line = str_replace("</string>","",$line);
        $line = preg_replace('/<[^>]*?>/','',$line);
        $line = trim($line);

        $num  = count(explode("\n",$line));
        $y    = 1;

        $Dao  = D("Question");
        for ($i = 0; $i < $num; $i++)
        {
            $reply = explode(",",$line);
            /*
            echo $y."th<br/>";
            echo "<b>reply:</b>".$reply[1]."<br/>";
            echo "<b>replay man:</b>".$reply[2]."<br/>";
            echo "<b>reply content:</b>".urldecode($reply[3])."<br/>";
            */
            $time = substr($reply[4], 0, 19);
            //echo "<b>reply time:</b>".$time."<br/><br/><br/><br/>";
            $Dao->message_add_qresult(urldecode($reply[2]),
                urldecode($reply[3]),strtotime($time));
            $y++;
        }
    }


    /***********
    register
     */
    public function register(){
        $flag = 0;
        $params = '';
        $argv = array(
            'sn'      =>'SDK-BBX-010-15319',
            'pwd'     =>'b5201+8+',
            'province'=>iconv("UTF-8","gb2312//IGNORE","北京"),
            'city'    =>iconv("UTF-8","gb2312//IGNORE","北京"),
            'trade'   =>'IT',
            'entname' =>iconv("UTF-8","gb2312//IGNORE","北京奇步互动技术有限公司"),
            'linkman' =>iconv("UTF-8","gb2312//IGNORE","王志江"),
            'phone'   =>'62240901',
            'mobile'  =>'18810461431',
            'email'   =>'578312197@qq.com',
            'fax'     =>'88888888',
            'address' =>iconv("UTF-8","gb2312//IGNORE","北京朝阳区太阳宫中路（地铁十号线太阳宫地铁D出口向南200米即到）"),
            'postcode'=>'100028',
            'sign'    =>''
        );

        foreach ($argv as $key => $value){
            if ($flag != 0){
                $params .= "&";
                $flag   =  1;
            }
            $params .= $key."=";
            $params .= urlencode($value);
            $flag = 1;
        }
        $length = strlen($params);

        $fp     = fsockopen("sdk105.entinfo.cn",8060,$errno,$errstr,10) or exit($errstr."--->".$errno);

        $header = "POST /webservice.asmx/Register HTTP/1.1\r\n";
        $header .= "Host:sdk105.entinfo.cn\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".$length."\r\n";
        $header .= "Connection: Close\r\n\r\n";

        $header .= $params."\r\n";
//echo $header."<br/>";
        fputs($fp,$header);
        $inheader = 1;
        while(!feof($fp)){
            $line = fgets($fp, 1024);
            if ($inheader && ($line == "\n" || $line == "\r\n")) {
                $inheader = 0;
            }
            if ($inheader == 0) {
                //echo $line;
            }
        }
        //<string xmlns="http://tempuri.org/">-5</string>
        $line=str_replace("<string xmlns=\"http://tempuri.org/\">","",$line);
        $line=str_replace("</string>","",$line);
        $result=explode(" ",$line);
        if ($result[0]=="0")
            echo "register success!";
        elseif ($result[0]=="-1")
            echo "repeat register";
        else
            echo "register failed";
    }


}

?>
