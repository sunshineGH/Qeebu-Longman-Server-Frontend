<?php

class UriModel extends Model{

    public $model_admin = null;

    public function get_model(){
        if($this->model_admin)
            return $this->model_admin;
        else{
            $this->model_admin = M('push_uri','','')->db('CONFIG1');
            return $this->model_admin;
        }
    }
//--------------------------------------insert-------------------------------------
    public function add_token($data){

        $save_data = [
            'uid'           => $data['uid'],
            'token'         => $data['devicetoken'],
            'ad_or_ios'     => $data['mobiletype'],
            'version'       => $data['version'],
            'badge'         => 0,
            'last_open_time'=> time(),
            'time_insert'   => time()
        ];
        $uri = $this->find_token_by_token($save_data['token']);
        if($uri){
            $this->del_devicetoken_by_devicetoken($save_data['token']);
        }

        $uri = $this->find_token_by_uid($save_data['uid']);
        if($uri){
            $this->del_devicetoken_by_uid($save_data['uid']);
        }

        $rs = $this->get_model()->add($save_data);
        return $rs;
    }
//--------------------------------------delete-------------------------------------
    public function del_devicetoken_by_devicetoken($token){
        $map['token'] = $token;
        return $this->get_model()->where($map)->delete();
    }

    public function del_devicetoken_by_uid($uid){
        $map['uid'] = $uid;
        return $this->get_model()->where($map)->delete();
    }

//--------------------------------------update-------------------------------------
    public function update_badge_number($badge,$data){
        $map = ['uid'=>$data['uid']];
        $save_data = ['badge'=>$badge];
        return $this->get_model()->where($map)->save($save_data);
    }
//--------------------------------------select-------------------------------------



    public function find_token_by_token($token){
        $map = ['token'=>$token];
        return $this->get_model()->where($map)->find();
    }

    public function find_token_by_uid($uid){
        $map = ['uid'=>$uid];
        return $this->get_model()->where($map)->find();
    }

    public function get_uri_by_user_arr($user_arr){
        $map = ['uid'=>['in',implode(',',$user_arr)]];
        return $this->get_model()->where($map)->select();
    }

    public function get_admin_uri_by_cid($cid){
        $audience   = D('Team')->get_team_admin_by_cid($cid);
        $audience_id= i_array_column($audience,'uid');
        return $this->get_uri_by_user_arr($audience_id);
    }

    public function get_uri_by_uid($uid){
        return [$this->find_token_by_uid($uid)];
    }




    public function get_devicetokens($key_words){

        $map['app_for'] = $key_words;

        return M('uri','','DB_MEETING')->where($map)->select();
    }


}