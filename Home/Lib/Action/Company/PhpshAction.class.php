<?php
ini_set('max_execution_time', '0');
class PhpshAction extends Action{
    //nohup 进程 修改需要请示
    public function push_meeting_push(){

        $_REQUEST['cid'] = 102;
        //idg database
        if(!Database(102))return;


        while(true){
            $time = time();
            $push_list = D('Push')->get_meeting_push($time);
            if(!$push_list){
                sleep(5);
            }else{
                foreach($push_list as $key=>$val){

                    $meeting = D('Meeting')->get_meeting_start_time_meeting_name($key);
                    $message = '会议提醒:您的会议'.$meeting['meeting_title'].'将在'.date('Y-m-d H:i',$meeting['meeting_startTime']).'开始,请您提前做好开会准备!';

                    foreach($val as $key1=>$val1){

                        $post_data = [
                            'sound'  => 2,
                            'message'=> $message,
                            'push_key_words' => 'home'
                        ];
                        $tokens = D('Push')->get_meeting_uri($key,$message,$val1);
                        A('Company/Push')->push_notice($tokens,$post_data);
                        unset($key1);unset($val1);
                    }
                    unset($key);unset($val);
                }
                unset($push_list);
                unset($meeting);
                unset($message);
                unset($post_data);
                unset($tokens);
            }
        }

    }

}
?>