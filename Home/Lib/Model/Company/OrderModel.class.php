<?php

class OrderModel extends Model{

    public function get_my_order($data){

        $data['page'] = $data['page'] == '' ? 1 : $data['page'];
        $data['count']= $data['count']== '' ? 10: $data['count'];

        $rs = M('user_order')
            ->where(['uid'=>$data['uid']])
            ->order('id desc')
            ->limit(($data['page']-1)*$data['count'].','.$data['count'])
            ->select();

        foreach($rs as &$val){
            if($val['pay_for'] == '1'){
                $active = D('News')->get_active($val['pay_for_id'],'title,price,content,');
                $val['title'] = $active['title'];
                $val['price'] = $active['price'];
                if($val['state'] == 1){
                    (time()-$val['time_insert']-15*60) >= 0 && $val['state'] = '3';
                }
                $text = strip_tags($active['content']);
                $text = str_replace("&nbsp;","",$text);
                $text = str_replace("&","",$text);
                $text = str_replace("<","",$text);
                $text = str_replace(">","",$text);
                $text = str_replace("\n","",$text);
                $text = str_replace("\r","",$text);
                $text = str_replace("\t","",$text);
                $text = str_replace(" ","",$text);
                $val['intro'] = mb_substr($text,0,40,'UTF-8');
                $val['url']   = C('DOMAIN_NAME').__URL__.'?req='.urlencode('{"action":"news","type":"get_news_detail","data":{"id":"'.$val['id'].'"}}');

                $val['goods_num'] = '1';
                unset($val['content']);
                unset($val['uid']);
            }
        }

        return $rs;
    }

    public function get_my_order_red_point($data){

        $map = [
            'uid'=>$data['uid'],
            'state'=>1,
            'time_insert'=>[
                ['gt',$data['timestamp']],
                ['lt',$data['timestamp']+9000]
            ]
        ];
        $rs = M('user_order')
            ->where($map)
            ->find();
        return $rs;
    }

    public function cancel_order($data){
        //删除座位
        M('active_user')->where(['trade_no'=>$data['trade_no']])->delete();

        //取消订单
        $rs = M('user_order')->where(['trade_no'=>$data['trade_no']])->delete();

        return $rs;
    }

}