<?php
/**
 * Created by PhpStorm.
 * User: imdba
 * Date: 2015/3/4
 * Time: 10:22
 */
class TeamAction extends Action{

//-------------------------------------select--------------------------------------
//查询我的team
    public function get_my_team(){

        $_REQUEST['uid'] = check_null(40002,true,$_REQUEST['uid']);

        $rs = D('Team')->get_my_team($_REQUEST);

        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }
//获取个人邀请信息列表
    public function get_team_invite_list(){

        $_REQUEST['uid'] = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['cid'] = $_REQUEST['cid']?StrCode($_REQUEST['cid'],'DECODE'):0;
        $rs = D('Team')->get_team_invite_list($_REQUEST);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }
//查询数据库
    public function check_database_init(){
        $_REQUEST['cid'] = check_null(40038,true,$_REQUEST['cid']);
        $rs              = D('Team')->check_($_REQUEST['cid']);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

//-------------------------------------insert--------------------------------------
//创建team
    public function create_team(){

        $_REQUEST['uid']         =check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['company_name']=check_null(40104,true,$_REQUEST['company_name']);

        $_FILES['company_logo']!=null && $this->image_upload();

        $rs = D('Team')->create_team($_REQUEST);

        if($rs){
            $rs['link']= 'http://huiyi.qeebu.cn/teamin/Team/join_team?tokens=XMKUHNDLUIOQWERBNJKL&c=AS8bfrASrrrZXCqDrq&k='.$rs['company_id'].'-'.StrCode($_REQUEST['uid']);
            return_json(0,$rs);
        }else
            return_json(-1);
    }

//申请加入team
    public function apply_team(){

        $data = [
            'uid'               => check_null(40002,true,$_REQUEST['uid']),
            'cid'               => check_null(40038,true,$_REQUEST['cid']),
            'cid_mark'          => StrCode($_REQUEST['cid']),
            'time'              => time(),
            'audience_character'=> 4
        ];

        //查询是否已经加入公司
        if(D('Team')->get_team_member_with_cid_uid($data)){
            return_json('40108');
            return;
        }

        $rs = D('Team')->apply_team($data);
        if($rs)
            return_json(0);
        else
            return_json(-1);
    }

//邀请加入team
    public function invite_audience(){

        $data = [
            'cid'                => check_null(40038,true,$_REQUEST['cid']),
            'uid'                => check_null(40002,true,$_REQUEST['uid']),
            'cid_mark'           => $_REQUEST['cid'],
            'time_insert'        => time(),
            'audience_character' => 6,
            'invite_admin'       => StrCode($_REQUEST['invite_admin'],'DECODE')
        ];

        if(D('Team')->get_team_member_with_cid_uid($data)){
            //查询是否已经加入公司
            return_json('40108');
            return;
        }

        $rs = D('Team')->apply_team($data);
        if($rs)
            return_json(0);
        else
            return_json(-1);
    }


//-------------------------------------update--------------------------------------
//管理员修改team
    public function update_team(){

        $_REQUEST['uid'] = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['cid'] = check_null(40038,true,$_REQUEST['cid']);

        if($_FILES['company_logo']['name']!=''){
            $this->image_upload();
        }

        $rs = D('Team')->update_team($_REQUEST);

        if($rs)
            return_json(0);
        else
            return_json(-1);
    }
//设置用户当前cid
    public function set_current_team(){

        $_REQUEST['uid'] = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['cid'] = check_null(40038,true,$_REQUEST['cid']);
        $_REQUEST['time_update'] = time();

        $rs = D('Team')->set_current_team($_REQUEST);

        if($rs)
            return_json(0);
        else
            return_json(-1);
    }
//是否同意当前申请或者邀请
    public function update_audience_character(){

        $data = [
            'uid'=>check_null(40002,true,$_REQUEST['uid']),
            'cid'=>check_null(40038,true,$_REQUEST['cid']),
            'audience_character'=>check_null(40105,true,$_REQUEST['audience_character'])
        ];
        $data['invite_admin'] = isset($_REQUEST['invite_admin']) ? StrCode($_REQUEST['invite_admin'],'DECODE'):0;

        if($_REQUEST['audience_character'] == 1){
            return_json(-1);return;
        }

        $rs = D('Team')->update_audience_character($data);
        if($rs)
            return_json(0);
        else
            return_json(-1);
    }

//-------------------------------------delete--------------------------------------
//离开当前team
    public function leave_team(){

        $_REQUEST['uid'] = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['cid'] = check_null(40038,true,$_REQUEST['cid']);

        $rs = D('Team')->leave_team($_REQUEST);

        if($rs)
            return_json(0);
        else
            return_json(-1);

    }
//删除当前成员
    public function delete_from_team(){

        $_REQUEST['uid'] = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['cid'] = check_null(40038,true,$_REQUEST['cid']);
        $_REQUEST['delete_user'] = check_null(40110,true,StrCode($_REQUEST['delete_user'],'DECODE'));
        if(!Database($_REQUEST['cid']))return;

        //1.判断权限
        $rs1= D('Team')->get_team_admin_by_uid($_REQUEST['cid'],$_REQUEST['uid']);
        $rs2= D('Team')->get_team_admin_by_uid($_REQUEST['cid'],$_REQUEST['delete_user']);
        if(!$rs1){
            return_json(40107);return;
        }else if($rs2['audience_character']==1){
            return_json(40107);return;
        }
        //2.删除delete_user
        $rs = D('Team')->delete_from_team($_REQUEST);
        if($rs)
            return_json(0);
        else
            return_json(-1);
    }

//-----------------------------------web------------------------------------------------
//web端加入team
    public function join_team(){
        //采用cookie控制每台设备仅能注册一个账号,下次进入时,使用当前账号
        $key = $_GET['k'];
        $arr=explode('-',$key);
        $_REQUEST['uid'] = check_null(40002,true,$arr['1']);
        $_REQUEST['cid'] = check_null(40038,true,$arr['0']);

        $data['user_name'] = D('Audience')->new_get_audience_name_by_uid($_REQUEST['uid']);
        $data['team_name'] = D('Team')->get_team_name_by_cid($_REQUEST['cid']);
        $data['k']         = StrCode($_REQUEST['cid']);

        $rs=M('teamin_app','','')->db(1,'DB_CONFIG2')->where('app_project="teamin" and app_openUrl="NewTeamIn"')->find();
        $this->assign('data',$data);
        $this->assign('rs',$rs);
        $this->display();
    }

    public function for_login_web(){

        $data = [
            'username'=>check_null(40014,true,$_REQUEST['username']),
            'pwd'     =>check_null(40012,true,$_REQUEST['pwd'])
        ];

        //判断当前用户名是否存在
        $audience = D('Audience')->new_get_audience($data);
        $uid = $audience['audience_id'];

        $key = $_REQUEST['k'];
        $cid = StrCode($key,'DECODE');

        if(!$audience){
            return_json(40015);
            return;
        }else if ($audience['audience_pwd'] != $data["pwd"]) {
            return_json(40008);
            return;
        }else if(D('Team')->get_team_member_with_cid_uid(['uid'=>$uid,'cid'=>$cid])){
            return_json(0,1);
            return;
        }else{
            $this->insert_team_just_from_web($uid,$cid,$key);
            return_json(0,0);
        }
    }

    public function join_team_come_from_web(){

        $key = $_REQUEST['k'];
        $_REQUEST['cid'] = StrCode($key,'DECODE');

        $_REQUEST['audience_username'] = check_null(40041,true,$_REQUEST['conditions']);
        $_REQUEST['audience_name']     = check_null(40111,true,$_REQUEST['audience_name']);
        $_REQUEST['audience_pwd']      = check_null(40112,true,$_REQUEST['audience_pwd']);

        $rs = D('Audience')->join_team_come_from_web($_REQUEST);
        if($rs){
            $url = 'http://huiyi.qeebu.cn/teamin/Team/welcome?tokens=XMKUHNDLUIOQWERBNJKL&c=AS8bfrASrrrZXCqDrq&k='.StrCode($_REQUEST['cid']).'_'.StrCode($_REQUEST['uid']);
            return_json(0,$url);
        }else
            return_json(-1);
    }

    public function check_verify_from_web(){
        $_REQUEST['conditions']  = check_null(40039,true,$_REQUEST['conditions']);
        $_REQUEST['verify']	     = check_null(40097,true,$_REQUEST['verify']);
        $rs = D('Audience')->get_verify($_REQUEST);
        if($rs['verify_key'] == $_REQUEST['verify']) {
            $key = $_REQUEST['k'];
            $cid = StrCode($key,'DECODE');
            $re = D('Audience')->find_audience_id_by_username($_REQUEST['conditions']);
            if($re){
                if(D('Team')->get_team_member_with_cid_uid(['uid'=>$re['audience_id'],'cid'=>$cid])){
                    return_json(1,1);
                }else{
                    $this->insert_team_just_from_web($re['audience_id'],$cid,$key);
                    return_json(1,0);
                }
            }else{
                return_json(0);
            }
        }else{
            return_json(40099,[
                $rs,$_REQUEST['verify']
            ]);
        }
    }

    public function validate(){

        $data['tel']       = StrCode($_GET['c'],'DECODE');
        $data['k']         = $_GET['k'];
        $this->assign('data',$data);
        $this->display();
    }

    public function register(){
        $data['tel']       = StrCode($_GET['c'],'DECODE');
        $data['k']         = $_GET['k'];
        $this->assign('data',$data);
        $this->display();
    }

    public function if_invite(){
        $this->display();
    }

    public function welcome(){
        $key = $_GET['k'];
        $arr = explode('-',$key);
        $_REQUEST['cid'] = check_null(40038,true,$arr['0']);

        $data['team_name'] = D('Team')->get_team_name_by_cid($_REQUEST['cid']);

        $arr2=M('teamin_app','','')->db(1,'DB_CONFIG2')->where('app_project="teamin" and app_openUrl="NewTeamIn"')->find();
        $data['ios']    = $arr2['app_downloadUrl'];
        $data['android']= $arr2['android_url'];

        $this->assign('data',$data);
        $this->display();
    }

    public function insert_team_just_from_web($uid,$cid,$key){
        $data1 = [
            'uid' 		=> $uid,
            'cid' 		=> $cid,
            'cid_mark'	=> $key,
            'audience_character' => 3,
            'character_incomming'=> 6,
            'time_insert'=>time(),
            'time_update'=>0,
            'time_delete'=>0
        ];
        $r=D('Company/Team')->get_team_with_cid_uid($data1);
        $map = [
            'cid'=>$data1['cid'],
            'uid'=>$data1['uid']
        ];
        if($r){
            M('audience_with_company','','')->db('CONFIG1')->where($map)->delete();
        }
        M('audience_with_company','','')->db('CONFIG1')->add($data1);
        Database($key);
        $data2 = [
            'audience_id' => $uid,
            'department_id'=>2,
            'time_insert' => time(),
            'time_update' => time(),
            'time_delete' => -1
        ];
        M('audience_department_rds','','DB_MEETING')->add($data2);
    }
//-----------------------------------web end---------------------------------------
//--------------------------public function --------------------------------------

    public function image_upload(){

        !isset($_REQUEST['cid']) && $_REQUEST['cid'] = 0;

        $upload_path = 'Public/Uploads'.'/';
        !is_dir($upload_path) && mkdir($upload_path,0777,true);
        $upload_path.= 'company'.'/';
        !is_dir($upload_path) && mkdir($upload_path,0777,true);
        $upload_path.= $_REQUEST["cid"] .'/';
        !is_dir($upload_path) && mkdir($upload_path,0777,true);

        import('ORG.Net.UploadFile');

        $config = [
            'savePath' 	=> $upload_path,
            'maxSize'  	=> 3145728,
            'allowExts'	=> ['jpg', 'gif', 'png', 'jpeg'],

            'thumb'				=> true,
            'thumbPrefix'		=> 'm_',
            'thumbMaxWidth'		=> '480',
            'thumbMaxHeight'	=> '320',
            'thumbRemoveOrigin' => true
        ];

        $upload = new UploadFile($config);
        if(!$upload->upload()){
            $_REQUEST['company_logo'] = '';
        }else{
            $uploadList = $upload->getUploadFileInfo();
            $_REQUEST['company_logo'] = $config['savePath'].$config['thumbPrefix'].$uploadList[0]['savename'];
        }
    }

}