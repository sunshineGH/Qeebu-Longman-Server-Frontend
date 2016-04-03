<?php

class NoticeModel extends Model{

    public function push_notice($data){
        $data['timestamp'] = time();
        $rs = M('notice','','DB_MEETING')->add($data);

        $post_data['message']        = "【".$data['notice_title']."】".$data['notice_detail'];
        $post_data['message']        = mb_substr($post_data['message'],0,30);
        $post_data['message']        = nl2br($post_data['message']);
        $post_data['message']        = strip_tags( $post_data['message']);

        if($_REQUEST!=102){
            $cid = is_numeric($_REQUEST['cid']) ? $_REQUEST['cid'] : StrCode($_REQUEST['cid'],'DECODE');
            $company_name = D('Team')->get_team_name_by_cid($cid);
            $post_data['message'] = '来自['.$company_name.']的公告: '.$post_data['message'];
        }

        $data['uid'] = $data['cid'] == 102 ? $data['uid'] : StrCode($data['uid']);

        $audience_id = D('Team')->get_audience_by_cid(StrCode($data['cid'],'DECODE'));
        D('Push')->create_push_notice('公告信息','5',$post_data['message'],time(),$audience_id);


        //push init
        $post_data['sound']          = $data['cid'].'-'.$data['uid']; //default
        $post_data['push_key_words'] = 'home';

        if($_REQUEST['cid']==102)
            $tokens = D('Uri')->get_devicetokens($post_data['push_key_words']);
        else
            $tokens = D('Uri')->get_uri_by_user_arr($audience_id);

        if($_REQUEST['cid']!=102)$post_data['sound']=1;
        A('Company/Push')->push_notice($tokens,$post_data);

        return $rs;
    }


    public function get_notice_list($data)
    {
        $data['page'] = $data['page']  == null? 1:$data['page'];
        $data['count']= $data['count'] == null?10:$data['count'];

        $sql = 'select * from notice';
        $sql.= ' order by notice_id desc';
        $sql.= ' limit '.($data['page']-1)*$data['count'].','.$data['count'];
        return M('notice','','DB_MEETING')->query($sql);
    }

    public function get_red_point($data)
    {
        $sql = 'select notice_id from notice';
        $sql.= ' where timestamp > '.$data['timestamp'];
        $sql.= ' limit 1';
        $rs  = M('notice','','DB_MEETING')->query($sql);
        return $rs?'1':'0';
    }

    public function get_notice_point_info($data){
        $map = [
            'uid'        =>$data['uid'],
            'time_insert'=>['gt',$data['push_timestamp']],
        ];

        $rs = M('push_notice_user','','')->db('CONFIG1')->where($map)->find();
        if($rs){
            return '1';
        }else{
            if($data['cid']!=0 && $data['cid']!=null){
                Database($data['cid']);
                $rs = M('push_notice_user','','DB_MEETING')->where($map)->find();
                if($rs){
                    return '1';
                }
            }
        }

        return '0';




    }

}
