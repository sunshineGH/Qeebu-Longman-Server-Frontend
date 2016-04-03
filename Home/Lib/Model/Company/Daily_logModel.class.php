<?php
/**
 * Created by zhanghao.
 * User: Zhanghao
 * tel: 18609059892'
 * qq: 261726511
 */
class Daily_logModel extends Model{

//---------------------------------param------------------------------------------


//------------------------------------------------insert----------------------------------------------------------

    public function create_daily_log($data){
        $data['time_insert'] = time();
        $rs = M('daily_log','','DB_MEETING')->add($data);
        if(!$rs)
            return false;
        if($data['attr']!=null && $data['attr']!=[]){
            foreach($data['attr'] as $val){
                $this->add_daily_log_attr($rs,$val);
            }
        }
        if($data['log_remind']!=null && $data['log_remind']!=[]){
            foreach($data['log_remind'] as $val){
                $this->add_daily_log_remind($rs,$val);
            }
            A('Company/Push')->daily_log_remind_push($rs,0,'有一条新的日志@了您');
        }
        if($data['log_tags'] !=null && $data['log_tags'] != []){
            foreach($data['log_tags'] as $val){
                $this->add_daily_log_tags($rs,$val);
            }
        }

        if($data['share_range']==''  || $data['share_range'] == [] || !is_array($data['share_range'])){
            $data['share_range'] = D('Audience')->get_uid_by_cid($data['cid']);
            foreach($data['share_range'] as $val){
                $this->add_daily_log_share_range($rs,$val);
            }
        }else{
            $share_range  = $data['share_range'];
            $share_range[]= StrCode($data['log_create_uid']);
            if($data['log_remind']!=null && $data['log_remind']!=[]){
                foreach($data['log_remind'] as $val){
                    $share_range[]=$val['uid'];
                }
            }
            $share_range   = array_unique($share_range);

            foreach($share_range as $val){
                $this->add_daily_log_share_range($rs,StrCode($val,'DECODE'));
            }
        }

        return $rs;
    }

    public function create_tag($data){
        $rs = M('daily_log_tags_private','','DB_MEETING')->add($data);
        return $rs;
    }

    public function add_daily_log_remind($log_id,$data){
        $data = [
            'log_id'    =>$log_id,
            'uid'       =>StrCode($data['uid'],'DECODE'),
            'at_local'  =>$data['at_local'],
            'at_length' =>$data['at_length'],
            'time_insert'=>time()
        ];
        return M('daily_log_remind','','DB_MEETING')->add($data);
    }

    public function add_daily_log_tags($log_id,$data){
        $data = [
            'log_id'        =>$log_id,
            'tag_name'      =>$data['tag_name'],
            'tag_real_name' =>$data['tag_real_name'],
        ];
        return M('daily_log_tags','','DB_MEETING')->add($data);
    }

    public function add_daily_log_share_range($log_id,$data){
        $data = [
            'log_id'        =>$log_id,
            'uid'           =>$data,
        ];
        return M('daily_log_share_range','','DB_MEETING')->add($data);
    }

    public function add_daily_log_attr($log_id,$path){
        $data = [
            'log_id'=>$log_id,
            'path'  =>$path,
            'time_insert'=>time()
        ];
        return M('daily_log_attr','','DB_MEETING')->add($data);
    }

    public function praise_daily_log($data){

        $data['praise_for']  = $this->get_uid_by_log_id($data['log_id']);
        $data['group_cows']  = 'l-'.$data['log_id'];
        $data['time_insert'] = time();
        $rs = M('daily_log_praise','','DB_MEETING')->add($data);
        A('Company/Push')->daily_log_push($data['log_id'],'您的日志被点赞了');
        return $rs;
    }

    public function praise_daily_log_reply($data){

        $data['praise_for']  = $this->get_reply_uid_by_reply_id($data['reply_id']);
        $data['group_cows']  = 'r-'.$data['reply_id'];
        $data['time_insert'] = time();
        A('Company/Push')->daily_log_reply_push($data['reply_id'],'您的评论被点赞了');

        return M('daily_log_praise','','DB_MEETING')->add($data);

    }

    public function reply_daily_log($data){
        $data['time_insert'] = time();
        if($data['pid'] != 0){
            $data['reply_for_uid'] = $this->get_reply_uid_by_reply_id($data['pid']);
            A('Company/Push')->daily_log_reply_push($data['pid'],'您的评论有一条新的回复信息');
        }else{
            $data['reply_for_uid'] = $this->get_uid_by_log_id($data['log_id']);
            A('Company/Push')->daily_log_push($data['log_id'],'您的日志有一条新的回复信息');
        }
        $rs = M('daily_log_reply','','DB_MEETING')->add($data);

        if($rs && $data['reply_remind']!=null && $data['reply_remind']!=[]){

            foreach($data['reply_remind'] as $val){
                $this->add_reply_remind($rs,$val);
            }
            A('Company/Push')->daily_log_remind_push(0,$rs,'有一条新的评论@了您',$data['reply_for_uid']);
        }

        return $rs;
    }

    public function add_reply_remind($reply_id,$data){
        $data = [
            'reply_id'    =>$reply_id,
            'uid'       =>StrCode($data['uid'],'DECODE'),
            'at_local'  =>$data['at_local'],
            'at_length' =>$data['at_length'],
            'time_insert'=>time()
        ];
        return M('daily_log_remind','','DB_MEETING')->add($data);
    }

//------------------------------------------------update----------------------------------------------------------
//------------------------------------------------delete----------------------------------------------------------


    public function del_daily_log($data){

        $map = [
            'log_id'=>$data['log_id']
        ];
        $rs = M('daily_log','','DB_MEETING')->where($map)->delete();
        M('daily_log_attr','','DB_MEETING')->where($map)->delete();
        M('daily_log_remind','','DB_MEETING')->where($map)->delete();
        M('daily_log_praise','','DB_MEETING')->where($map)->delete();
        M('daily_log_share_range','','DB_MEETING')->where($map)->delete();
        M('daily_log_tags','','DB_MEETING')->where($map)->delete();
        M('daily_log_reply','','DB_MEETING')->where($map)->delete();

        return $rs;
    }
//------------------------------------------------select----------------------------------------------------------

    public function get_daily_log($data){

        $data['uid'] = StrCode($data['uid'],'DECODE');

        $map['1'] = 1;

        if(isset($data['start_id'])){
            $map['a.log_id'][] = array('lt',$data['start_id']);
        }
        if(isset($data['end_id'])){
            $map['a.log_id'][] = array('gt',$data['end_id']);
        }
        if(isset($data['watching']) && $data['watching']!=[]){
            $map_create_uid = '';
            foreach($data['watching'] as $val){
                $map_create_uid.= ','.StrCode($val,'DECODE');
            }
            $map_create_uid=preg_replace("/,/",'',$map_create_uid,1);

            $map['a.log_create_uid'] = ['exp',' in ('.$map_create_uid.') '];
        }

        $page     = isset($data['page']) ? $data['page'] : 1;
        $count    = isset($data['count'])? $data['count']: 10;

        $map['b.uid'] = $data['uid'];
        $rs = M('daily_log','','DB_MEETING')->alias('a')
            ->join('join daily_log_share_range as b on a.log_id = b.log_id')
            ->field('a.*')
            ->where($map)
            ->order('log_id desc')
            ->limit(($page-1)*count,$count)
            ->select();

        $arr = [];

        foreach($rs as $val){

            $val['reply_num'] = $this->get_reply_num_by_log_id($val['log_id']);

            if(isset($data['tags']) && trim($data['tags'])!=''){
                $val['tags']  = $this->get_daily_log_tags_by_log_id($val['log_id'],strtolower($data['tags']));
                if($val['tags'] == []) continue;
            } else{
                $val['tags']  = $this->get_daily_log_tags_by_log_id($val['log_id']);
            }

            $audience                 = D('Audience')->new_get_audience_by_uid($val['log_create_uid']);
            $praise                   = $this->find_praise(['uid'=>$data['uid'],'log_id'=>$val['log_id']]);

            $val['audience_name']     = $audience['audience_name'] ? $audience['audience_name'] : '';
            $val['audience_portrait'] = $audience['audience_portrait'] ? C('DOMAIN_NAME').__ROOT__.'/'.$audience['audience_portrait']:'';
            $val['praise_num']        = $this->get_praise_num_by_log_id($val['log_id']);
            $val['attr']              = $this->get_daily_log_attr_by_log_id($val['log_id']);
            $val['remind']            = $this->get_daily_log_remind_by_log_id($val['log_id']);
            $val['log_create_uid']    = StrCode($val['log_create_uid']);
            $val['my_praise']         = $praise == false ? '0' : '1';

            $arr[]=$val;
        }
        return $arr;
    }

    public function get_daily_log_by_log_id($log_id){
        $map['log_id'] = $log_id;
        $rs = M('daily_log','','DB_MEETING')
            ->where($map)
            ->find();
        $audience                = D('Audience')->new_get_audience_by_uid($rs['log_create_uid']);

        $rs['audience_name']     = $audience['audience_name'] ? $audience['audience_name'] : '';
        $rs['audience_portrait'] = $audience['audience_portrait'] ? C('DOMAIN_NAME').__ROOT__.'/'.$audience['audience_portrait']:'';
        $rs['reply_num']         = $this->get_reply_num_by_log_id($rs['log_id']);
        $rs['praise_num']        = $this->get_praise_num_by_log_id($rs['log_id']);
        $rs['attr']              = $this->get_daily_log_attr_by_log_id($rs['log_id']);
        $rs['remind']            = $this->get_daily_log_remind_by_log_id($rs['log_id']);
        $rs['log_create_uid']    = StrCode($rs['log_create_uid']);

        $map1 = [
            'uid'   =>$rs['log_create_uid'],
            'log_id'=>$rs['log_id']
        ];
        $praise                  = $this->find_praise($map1);
        $rs['my_praise']         = $praise == false ? '0' : '1';

        return $rs;
    }

    public function get_daily_log_content_by_log_id($log_id){
        $map['log_id'] = $log_id;
        return M('daily_log','','DB_MEETING')->where($map)->getField('log_content');
    }

    public function get_daily_log_attr_by_log_id($log_id){
        $map['log_id'] = $log_id;
        $rs = M('daily_log_attr','','DB_MEETING')
            ->field('id,path')
            ->where($map)
            ->select();
        return $rs ? $rs : [];
    }

    public function get_daily_log_remind_by_log_id($log_id){
        $map['log_id'] = $log_id;
        $rs = M('daily_log_remind','','DB_MEETING')
            ->field('id,log_id,uid,at_local,at_length')
            ->where($map)
            ->select();

        if(!$rs)
            return [];

        foreach($rs as &$val){
            $val['uid'] = StrCode($val['uid']);
        }

        return $rs;
    }

    public function get_daily_log_tags_by_log_id($log_id,$tags=''){
        $map['log_id'] = $log_id;
        $rs = M('daily_log_tags','','DB_MEETING')
            ->field('id,log_id,tag_name,tag_real_name')
            ->where($map)
            ->select();

        if(!$rs)
            return [];

        if($tags!=''){
            $tag_in = false;
            foreach($rs as $val1){
                if($val1['tag_real_name'] == $tags){
                    $tag_in = true;
                }
            }
            if($tag_in == false){
                return [];
            }
        }

        return $rs;
    }

    public function get_reply_remind_by_log_id($reply_id){
        $map['reply_id'] = $reply_id;
        $rs = M('daily_log_remind','','DB_MEETING')
            ->where($map)
            ->select();

        if(!$rs)
            return [];

        foreach($rs as &$val){
            $val['uid'] = StrCode($val['uid']);
        }

        return $rs;
    }

    public function get_daily_log_create_uid($log_id){
        $map['log_id'] = $log_id;
        return M('daily_log','','DB_MEETING')
            ->where($map)
            ->getField('log_create_uid');
    }

    public function get_uid_by_log_id($log_id){
        $map['log_id'] = $log_id;
        return M('daily_log','','DB_MEETING')
            ->where($map)
            ->getField('log_create_uid');
    }


//reply start

    public function get_reply_uid_by_reply_id($reply_id){
        $map['id'] = $reply_id;
        return M('daily_log_reply','','DB_MEETING')->where($map)->getField('uid');
    }

    public function get_reply_num_by_log_id($log_id){
        $map['log_id'] = $log_id;
        //$map['pid']  = 0;
        $rs = M('daily_log_reply','','DB_MEETING')
            ->where($map)
            ->count();
        return $rs ? $rs : 0;

    }

    public function get_reply_by_log_id($log_id,$uid){

        $map['log_id'] = $log_id;
        $rs = M('daily_log_reply','','DB_MEETING')
            ->where($map)
            ->select();
        if(!$rs) return [];
        foreach($rs as &$val){

            $val['praise_num'] = $this->get_praise_num_by_reply_id($val['id']);

            $audience = D('Audience')->new_get_audience_by_uid($val['uid']);
            $val['audience_name']     = $audience['audience_name'] ? $audience['audience_name'] : '';
            $val['audience_portrait'] = $audience['audience_portrait'] ? C('DOMAIN_NAME').__ROOT__.'/'.$audience['audience_portrait']:'';

            $map1['uid']      = $uid;
            $map1['reply_id'] = $val['id'];
            $praise           = $this->find_praise_reply($map1);
            $val['my_praise'] = $praise == false ? '0' : '1';

            $val['remind']    = $this->get_reply_remind_by_log_id($val['id']);

            $val['uid'] = StrCode($val['uid']);
            $val['reply_for_uid'] = StrCode($val['reply_for_uid']);
        }

        return $rs;
    }

    public function get_reply_by_id($id){

        $map['id'] = $id;

        $rs = M('daily_log_reply','','DB_MEETING')
            ->where($map)
            ->find();
        $audience = D('Audience')->new_get_audience_by_uid($rs['uid']);
        $rs['audience_name']     = $audience['audience_name']     ? $audience['audience_name'] : '';
        $rs['audience_portrait'] = $audience['audience_portrait'] ? C('DOMAIN_NAME').__ROOT__.'/'.$audience['audience_portrait']:'';
        $rs['uid'] = StrCode($rs['uid']);
        return $rs;
    }

    public function get_me_replies($data){

        $map['reply_for_uid'] = StrCode($data['uid'],'DECODE');

        if(isset($data['start_id'])){
            $map['log_id'][] = array('lt',$data['start_id']);
        }
        if(isset($data['end_id'])){
            $map['log_id'][] = array('gt',$data['end_id']);
        }
        $page  = isset($data['page']) ? $data['page'] : 1;
        $count = isset($data['count'])? $data['count']: 10;

        $rs = M('daily_log_reply','','DB_MEETING')
            ->where($map)
            ->limit(($page-1)*count,$count)
            ->select();

        foreach($rs as &$val){

            $audience = D('Audience')->new_get_audience_by_uid($val['uid']);
            $val['audience_name']     = $audience['audience_name'] ? $audience['audience_name'] : '';
            $val['audience_portrait'] = $audience['audience_portrait'] ? C('DOMAIN_NAME').__ROOT__.'/'.$audience['audience_portrait']:'';


            if($val['pid'] != 0)
                $val['reply_for_reply'] = $this->get_reply_by_id($val['pid']);
            else
                $val['reply_for_reply'] = null;

            $val['reply_for_log'] = $this->get_daily_log_by_log_id($val['log_id']);
            $val['remind']    = $this->get_reply_remind_by_log_id($val['id']);
            $val['uid'] = StrCode($val['uid']);
            $val['reply_for_uid'] = StrCode($val['reply_for_uid']);
        }
        return $rs;
    }

    public function get_me_remind($data){

        $map['uid'] = StrCode($data['uid'],'DECODE');

        if(isset($data['start_id'])){
            $map['id'][] = array('lt',$data['start_id']);
        }
        if(isset($data['end_id'])){
            $map['id'][] = array('gt',$data['end_id']);
        }
        $page  = isset($data['page']) ? $data['page'] : 1;
        $count = isset($data['count'])? $data['count']: 10;

        $rs = M('daily_log_remind','','DB_MEETING')
            ->where($map)
            ->limit(($page-1)*count,$count)
            ->select();

        foreach($rs as $val){

            $uid = 0;

            if($val['reply_id'] != 0){
                $reply          = $this->get_reply_by_id($val['reply_id']);
                $val['uid']     = $reply['uid'];
                $val['content'] = $reply['content'];

                $uid = StrCode($reply['uid'],'DECODE');
                $log_id = $reply['log_id'];
            }else{
                $log_id = $val['log_id'];
                $val['content'] = '';
            }
            $val['log_id'] = $log_id;
            $val['reply_for_log'] = $this->get_daily_log_by_log_id($log_id);

            $uid = $uid == 0 ? StrCode($val['reply_for_log']['log_create_uid'],'DECODE') : $uid;
            $audience                 = D('Audience')->new_get_audience_by_uid($uid);
            $val['audience_name']     = $audience['audience_name'] ? $audience['audience_name'] : '';
            $val['audience_portrait'] = $audience['audience_portrait'] ? C('DOMAIN_NAME').__ROOT__.'/'.$audience['audience_portrait']:'';

            $val['remind'] = $val['reply_id']!=0 ? $this->get_reply_remind_by_log_id($val['reply_id']) : [];

            unset($val['uid']);
            unset($val['reply_id']);
            unset($val['at_local']);
            unset($val['at_length']);
            unset($val['time_insert']);

            $arr []= $val;
        }

        return $arr;
    }

    public function get_me_praise($data){

        $map['praise_for'] = StrCode($data['uid'],'DECODE');

        if(isset($data['time_insert'])){
            $map['time_insert'] = array('lt',$data['time_insert']);
        }

        $page  = isset($data['page']) ? $data['page'] : 1;
        $count = isset($data['count'])? $data['count']: 10;

        $rs = M('daily_log_praise','','DB_MEETING')
            ->field('distinct `group_cows`')
            ->where($map)
            ->order('time_insert desc')
            ->limit(($page-1)*count,$count)
            ->select();


        foreach($rs as $val){

            $arr = explode('-',$val['group_cows']);

            if($arr[0] == 'r'){
                $reply          = $this->get_reply_by_id($arr[1]);
                $val['content'] = $reply['content'];
                $val['remind']  = $this->get_reply_remind_by_log_id($reply['id']);
                $log_id = $reply['log_id'];
                $val['praise']  = $this->get_praise_person_by_reply_id($arr[1]);
                $val['time_insert'] = M('daily_log_praise','','DB_MEETING')->where('reply_id='.$arr[1])->order('time_insert desc')->getField('time_insert');
            }else{
                $log_id = $arr[1];
                $val['content'] = '';
                $val['remind']  = [];
                $val['praise']  = $this->get_praise_person_by_log_id($arr[1]);
                $val['time_insert'] = M('daily_log_praise','','DB_MEETING')->where('log_id='.$arr[1])->order('time_insert desc')->getField('time_insert');
            }
            $val['daily_log'] = $this->get_daily_log_by_log_id($log_id);



            unset($val['group_cows']);
            unset($val['uid']);
            unset($val['reply_id']);
            unset($val['at_local']);
            unset($val['at_length']);

            $arr1 []= $val;
        }

        return $arr1;
    }


//reply end

//---praise start ---
    public function get_daily_log_praise($data){
        $map['log_id'] = $data['log_id'];
        $rs = M('daily_log_praise','','DB_MEETING')->where($map)->select();
        if(!$rs)return [];
        foreach($rs as &$val){
            $val['uid'] = StrCode($val['uid']);
        }
        return $rs;
    }

    public function find_praise($data){
        return M('daily_log_praise','','DB_MEETING')
            ->where($data)
            ->find();
    }

    public function find_praise_reply($data){
        $rs = M('daily_log_praise','','DB_MEETING')
            ->where($data)
            ->find();
        return $rs;
    }

    public function get_praise_num_by_log_id($log_id){
        $map['log_id']      = $log_id;
        $rs = M('daily_log_praise','','DB_MEETING')
            ->where($map)
            ->count();
        return $rs ? $rs : 0;
    }

    public function get_praise_person_by_log_id($log_id){
        $map['log_id'] = $log_id;
        $rs = M('daily_log_praise','','DB_MEETING')
            ->field('uid')
            ->order('time_insert desc')
            ->where($map)
            ->select();
        if(!$rs)return [];
        foreach($rs as &$val){
            $val['name'] = D('Audience')->new_get_audience_name_by_uid($val['uid']);
            $val['uid']  = StrCode($val['uid']);
        }
        return $rs;
    }

    public function get_praise_person_by_reply_id($reply_id){
        $map['reply_id'] = $reply_id;
        $rs = M('daily_log_praise','','DB_MEETING')
            ->field('uid')
            ->order('time_insert desc')
            ->where($map)
            ->select();
        if(!$rs)return [];
        foreach($rs as &$val){
            $val['name'] = D('Audience')->new_get_audience_name_by_uid($val['uid']);
            $val['uid']  = StrCode($val['uid']);
        }
        return $rs;
    }

    public function get_praise_num_by_reply_id($reply_id){
        $map['reply_id'] = $reply_id;
        $rs = M('daily_log_praise','','DB_MEETING')
            ->where($map)
            ->count();
        return $rs ? $rs : 0;
    }




//---praise end ---

//--- tag start ---
    public function get_tag_by_tag_name_and_uid($tag_name,$uid){
        $map['tag_name'] = $tag_name;
        $map['uid']      = $uid;
        return M('daily_log_tags_private','','DB_MEETING')->where($map)->find();
    }

    public function get_tags_by_uid($uid){
        $field = 'id,tag_name,tag_real_name';
        $map['uid']      = $uid;
        $rs = M('daily_log_tags_private','','DB_MEETING')->field($field)->where($map)->select();
        return $rs;
    }

}