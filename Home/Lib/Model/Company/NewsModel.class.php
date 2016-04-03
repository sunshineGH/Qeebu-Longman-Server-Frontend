<?php
class NewsModel extends Model{

//--------------------------------------Common-------------------------------------
    public function get_active($id,$field){
        if($field)
            return M('active')->where(['id'=>$id])->find();
        else
            return M('active')->where(['id'=>$id])->field($field)->find();
    }

    public function get_active_user($id=0,$field='',$where=[]){

        $map = [];
        $id != 0     && $map['id'] = $id;
        $where != [] && $map = array_merge($map,$where);

        if($field)
            return M('active_user')->where($map)->find();
        else
            return M('active_user')->where($map)->field($field)->find();
    }
//--------------------------------------select---------------------------------------

    public function get_news_list($data){

        $data['count']   = $data['count'] == 0 ? 10 : $data['count'];
        $data['page']    = $data['page']  == 0 ? 1  : $data['page'];
        $data['category']= $data['category'] == '' ? 1 : $data['category'];

        $rs = M('active')
            ->where(['category'=>$data['category']])
            ->field('id,file_id,title,reg_time_begin,reg_time_end,max_num,price,time_insert,content')
            ->limit((($data['page']-1)*$data['count']).",".$data['count'])
            ->select();
        foreach($rs as &$val){
            $val['file_path'] = D('Files')->get_file($val['file_id']);
            $val['url']       = C('DOMAIN_NAME').__URL__.'?req='.urlencode('{"action":"news","type":"get_news_detail","data":{"id":"'.$val['id'].'"}}');

            $text = strip_tags($val['content']);
            $text = str_replace("&nbsp;","",$text);
            $text = str_replace("&","",$text);
            $text = str_replace("<","",$text);
            $text = str_replace(">","",$text);
            $text = str_replace("\n","",$text);
            $text = str_replace("\r","",$text);
            $text = str_replace("\t","",$text);
            $text = str_replace(" ","",$text);
            $val['intro'] = mb_substr($text,0,40,'UTF-8');
            unset($val['file_id']);
            unset($val['content']);
        }
        return $rs;
    }

    public function get_news_detail($data){
        $rs = M('active')->where(['id'=>$data['id']])->find();
        $rs['file_path'] = D('Files')->get_file($rs['file_id']);
        $rs['time_insert'] = date('Y-m-d',$rs['time_insert']);
        return $rs;
    }

    public function get_news($id,$field){

    }

    /**
     *
     * Sign up to participate in activities
     *
     * @return int , the insert id
     */
    public function sign_up($data){
        if($data['trade_no'] != ''){
            M('user_order')->add([
                'trade_no'   => $data['trade_no'],
                'uid'        => $data['uid'],
                'pay_for'    => 1,
                'pay_for_id' => $data['active_id'],
                'state'      => 1,
                'time_insert'=> time()
            ]);
        }
        $rs = M('active_user')->add([
            'user_id'    => $data['uid'],
            'active_id'  => $data['active_id'],
            'trade_no'   => $data['trade_no'],
            'time_insert'=> time(),
            'state'      => $data['state'],
        ]);
        return $rs;
    }

    /**
     *
     * Query whether there are seats
     * there are returns true, otherwise it returns false
     *
     * @return bool
     */
    public function check_if_have_sign_up_set($active_id,$max_num = 0){
        if($max_num == 0){
            $max_num = $this->get_active($active_id,'max_num')['max_num'];
        }
        $sign_up_num = $this->get_active_sign_up_num($active_id);
        return $max_num >= $sign_up_num;
    }
    /**
     *
     * Query sign up all student num
     *
     * @return number
     */
    public function get_active_sign_up_num($active_id){
        return (int)M('active_user')->where(['active_id'=>$active_id])->count();
    }


    /**
     *
     * Query sign up success student num
     *
     * @return number
     */
    public function get_active_success_sign_up_num($active_id){
        return (int)M('active_user')->where(['active_id'=>$active_id,'state'=>2])->count();
    }

    public function delete_active_user($data){
        return M('active_user')->where(['user_id'=>$data['uid'],'active_id'=>$data['active_id']])->delete();
    }
}

