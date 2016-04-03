<?php
class NewsAction extends Action{

//获取新闻列表
    public function ln_get_news_list($data){

        D('User')->check_token($data['uid'],$data['token']);

        $rs = D('News')->get_news_list($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function ln_active_sign_up($data){

        D('User')->check_token($data['uid'],$data['token']);
        check_null(40032,true,$data['active_id']);
        check_null(40033,true,$data['trade_no']);

        $active        = D('News')->get_active($data['active_id'],'max_num,reg_time_end,price');
        $data['state'] = $active['price']!=0 ? 1 : 2;

        //1.判断是否人数已满
        if(!D('News')->check_if_have_sign_up_set($data['active_id'],$active['max_num'])){
            return_json(40034);
        }
        //2.判断是否当前报名时间是否截止
        if($active['reg_time_end'] <= time())return_json(40035);
        //3.报名
        $rs = D('News')->sign_up($data);
        if($rs)
            return_json(0);
        else
            return_json(-1);
    }

    /**
     * 获取当前用户报名状态
     * state = [
     *     '0' => '未报名',
     *     '1' => '待支付',
     *     '2' => '已报名成功',
     * ];
     */
    public function ln_get_active_state($data){

        D('User')->check_token($data['uid'],$data['token']);
        check_null(40032,true,$data['active_id']);

        $active = D('News')->get_active($data['active_id'],'max_num,reg_time_end');
        //1.判断是否人数已满
        if(!D('News')->check_if_have_sign_up_set($data['active_id'],$active['max_num'])){
            return_json(40034);
        }
        //2.判断是否当前报名时间是否截止
        if($active['reg_time_end'] <= time())return_json(40035);

        //3.判断当前用户的订单是否已经过期
        $rs = D('News')->get_active_user(0,'time_insert,state',['user_id'=>$data['uid'],'active_id'=>$data['active_id']]);
        if($rs['state'] == 1){
            if($rs['time_insert'] <= (time()-60*15)){
                D('News')->delete_active_user($data);
                $rs['state'] = 0;
            }
        }
        return_json(0,['active_state'=>''.(int)$rs['state']]);
    }
//获取新闻详情
    public function ln_get_news_detail($data){
        $rs = D('News')->get_news_detail($data);
        $this->assign('rs',$rs);
        $this->display('ln_get_news_detail');
    }
}