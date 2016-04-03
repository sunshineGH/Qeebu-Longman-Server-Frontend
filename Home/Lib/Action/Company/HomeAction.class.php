<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/3/10
 * Time: 11:32
 */
class HomeAction extends Action{

    public function set_home_info(){

        $_REQUEST['uid']       = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['cid']       = $_REQUEST['cid'] == '' ? 0 : StrCode($_REQUEST['cid'],'DECODE');
        $_REQUEST['timestamp'] = check_null(40042,true,$_REQUEST['timestamp']);

        //判断用户信息
        $audience = D('Audience')->get_home_audience_info($_REQUEST);
        if(!$audience){
            return_json(40001);return;
        }

        $company = D('Team')->get_team_by_cid($_REQUEST['cid']);

        //audience_character
        $admin_info  = D('Team')->get_team_admin_by_uid($_REQUEST['cid'],$_REQUEST['uid']);
        $rs['if_is_admin'] = $admin_info == false ? 0 : 1;

        //if_alive
        if($_REQUEST['cid'] != 0 && $_REQUEST['cid'] != null){
            $re   = D('Team')  ->get_team_alive($_REQUEST);
            $rs['if_alive']   = $re ? '1' : '0';
        }else{
            $rs['if_alive'] = '1';
        }

        $rs['team_alive'] = false;

        $rs['audience_info']     = $audience;

        if($company){
            $rs['company_name']      = $company['company_name'];


            if($company['company_logo']!=''){
                $rs['company_logo']=C('DOMAIN_NAME').__ROOT__.'/'.$company['company_logo'];
            }else{
                $rs['company_logo']='';
            }

        }else{
            $rs['company_logo'] = '';
            $rs['company_name'] = '';
        }

        $rs['point_info']        = '0';
        $rs['notice_point_info'] = '0';

        return_json(0,$rs,time());
    }

    public function get_meeting_red_point(){

        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['uid']       = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['timestamp'] = check_null(40042,true,$_REQUEST['timestamp']);

        $rs = D('Meeting')->get_red_point($_REQUEST);
        if($rs)
            return_json(0);
        else
            return_json(40001);
    }

    public function get_team_red_point(){
        $data=[
            'uid'             =>check_null(40002,true,$_REQUEST['uid']),
            'notice_timestamp'=>check_null(40042,true,$_REQUEST['notice_timestamp']),
            'solve_timestamp' =>check_null(40042,true,$_REQUEST['solve_timestamp'])
        ];

        $rs = D('Team')->get_red_point($data);
        return_json(0,$rs);
    }

}