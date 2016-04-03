<?php

class AliPayAction extends Action{

    public function index(){

        require('./lib/alipay_notify.class.php');
        $alipayNotify  = new AlipayNotify(C('ALIPAY_CONFIG'));
        $verify_result = $alipayNotify->verifyNotify();

        M('post_json')->add(['post_json'=>json_encode($_POST)]);

        if(!M('alipay')->add($_POST))M('alipay')->where(['trade_no'=>$_POST['trade_no']])->save($_POST);

        if($_POST['trade_status'] == 'TRADE_FINISHED'){

            $rs = M('user_order')->where(['trade_no'=>$_POST['out_trade_no']])->find();

            if($rs['pay_for'] == 1){
                M('active_user')->where(['trade_no'=>$_POST['out_trade_no']])->save(['state'=>2]);
            }

            M('user_order')->where(['trade_no'=>$_POST['out_trade_no']])->save(['state'=>2]);
        }

        /*if($verify_result){
            //验证成功
            //$out_trade_no = $_POST['out_trade_no'];
            //$trade_no     = $_POST['trade_no'];
            $trade_status = $_POST['trade_status'];
            switch($trade_status){
                case 'TRADE_FINISHED':
                    return_json(0,'finished');
                    break;
                case 'TRADE_SUCCESS':
                    return_json(0,'success');
                    break;
                default:
                    return_json(0,'default');
            }
        }else{
            return_json(-1);
        }*/
    }

    public function validate(){

    }


}