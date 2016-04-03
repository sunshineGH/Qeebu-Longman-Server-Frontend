<?php
/**
 * Created by zhanghao.
 * User: Zhanghao
 * tel: 18609059892
 * qq: 261726511
 */
class Daily_logAction extends Action{

//-----------------------------------init------------------------------------------
    public $req_data = [];
    protected function _initialize(){
        $this->req_data = json_decode($_REQUEST['req'],true);
        if (!$this->req_data){
            return_json(-1);
            exit();
        }
    }

    public function index(){
        $this->_initialize();
        switch($this->req_data['type']){
            case 'create':
                $this->create_daily_log();
                break;
            case 'get':
                $this->get_daily_log();
                break;
            case 'del':
                $this->del_daily_log();
                break;
            case 'praise':
                $this->praise_daily_log();
                break;
            case 'praise_reply':
                $this->praise_daily_log_reply();
                break;
            case 'reply':
                $this->reply_daily_log();
                break;
            case 'get_reply':
                $this->get_daily_log_reply();
                return;
            case 'get_daily_log_praise':
                $this->get_daily_log_praise();
                return;

            case 'get_me_replies':
                $this->get_me_replies();
                return;


            case 'get_me_remind':
                $this->get_me_remind();
                return;

            case 'get_me_praise':
                $this->get_me_praise();
                return;

            //创建一个新的私人标签
            case 'create_tag':
                $this->create_tag();
                return;

            //获取个人标签
            case 'get_tag':
                $this->get_tag();
                return;


            default:
                return_json(-1);
                break;
        }
    }

//-------------------------------------insert------------------------------------
    public function create_daily_log(){

        if(!Database($this->req_data['data']["cid"]))exit;

        $data['cid']                = StrCode($this->req_data['data']['cid'],'DECODE');
        $data['log_create_uid']	    = check_null(40002,true,$this->req_data['data']['uid']);
        $data['log_content']        = check_null(40007,true,$this->req_data['data']['content']);
        $data['log_local']          = isset($this->req_data['data']['local'])      ? $this->req_data['data']['local'] : '';
        $data['log_long']           = isset($this->req_data['data']['long'])       ? $this->req_data['data']['long']  : '';
        $data['log_lat']            = isset($this->req_data['data']['lat'])        ? $this->req_data['data']['lat']   : '';
        $data['log_remind']         = isset($this->req_data['data']['daily_log_remind']) ? $this->req_data['data']['daily_log_remind']   : '';
        $data['log_tags']           = isset($this->req_data['data']['daily_log_tags']) ? $this->req_data['data']['daily_log_tags']   : '';
        $data['share_range']        = isset($this->req_data['data']['share_range']) ? $this->req_data['data']['share_range']   : '';

        if($data['log_create_uid'] == 0){return_json(40002);return;}

        if($_FILES['log_attr']['name']!='')
            $data['attr'] = $this->upload_sd_attachment();

        $rs = D('Daily_log')->create_daily_log($data);
        if($rs)
            return_json('0');
        else
            return_json('-1');
    }

    public function praise_daily_log(){

        if(!Database($this->req_data['data']["cid"]))exit;

        $data['uid']	 = check_null(40002,true,$this->req_data['data']['uid']);
        $data['log_id']  = check_null(40075,true,$this->req_data['data']['log_id']);

        if(D('Daily_log')->find_praise($data)){
            return_json(40123);return;
        }

        $rs = D('Daily_log')->praise_daily_log($data);
        if($rs)
            return_json('0');
        else
            return_json('-1');
    }

    public function praise_daily_log_reply(){

        if(!Database($this->req_data['data']["cid"]))exit;

        $data['uid']	 = check_null(40002,true,$this->req_data['data']['uid']);
        $data['reply_id']= check_null(40124,true,$this->req_data['data']['reply_id']);

        if(D('Daily_log')->find_praise_reply($data)){
            return_json(40123);return;
        }

        $rs = D('Daily_log')->praise_daily_log_reply($data);
        if($rs)
            return_json('0');
        else
            return_json('-1');
    }


    public function reply_daily_log(){

        if(!Database($this->req_data['data']["cid"]))exit;

        $data['uid']	     = check_null(40002,true,$this->req_data['data']['uid']);
        $data['log_id']      = check_null(40075,true,$this->req_data['data']['log_id']);
        $data['content']     = check_null(40007,true,$this->req_data['data']['content']);
        $data['pid']         = isset($this->req_data['data']['pid']) ? $this->req_data['data']['pid'] : 0;
        $data['reply_remind']= isset($this->req_data['data']['reply_remind']) ? $this->req_data['data']['reply_remind'] : null;

        $rs = D('Daily_log')->reply_daily_log($data);
        if($rs)
            return_json('0');
        else
            return_json('-1');
    }

    public function create_tag(){

        if(!Database($this->req_data['data']["cid"]))exit;

        $data['uid']	      = check_null(40002,true,$this->req_data['data']['uid']);
        $data['tag_name']	  = check_null(40125,true,$this->req_data['data']['tag_name']);
        $data['tag_real_name']= strtolower($data['tag_name']);

        if(D('Daily_log')->get_tag_by_tag_name_and_uid($data['tag_name'],$data['uid'])){
            return_json(40126);
            return;
        }
        $rs = D('Daily_log')->create_tag($data);

        if($rs)
            return_json('0');
        else
            return_json('-1');

    }
//-------------------------------------update------------------------------------



//-------------------------------------delete------------------------------------
    public function del_daily_log(){

        if(!Database($this->req_data['data']["cid"]))exit;

        $data['uid']	= check_null(40002,true,$this->req_data['data']['uid']);
        $data['log_id'] = check_null(40075,true,$this->req_data['data']['log_id']);

        $uid = D('Daily_log')->get_daily_log_create_uid($data['log_id']);
        if($uid != $data['uid']){
            return_json(40120);return;
        }

        $rs = D('Daily_log')->del_daily_log($data);
        if($rs)
            return_json('0');
        else
            return_json('-1');

    }
//-------------------------------------select------------------------------------

    public function get_daily_log(){

        if(!Database($this->req_data['data']["cid"]))exit;

        $data['uid']	    = check_null(40002,true,$this->req_data['data']['uid']);

        $rs = D('Daily_log')->get_daily_log($this->req_data['data']);
        if($rs)
            return_json('0',$rs);
        else
            return_json('40001');
    }

    public function get_daily_log_reply(){

        if(!Database($this->req_data['data']["cid"]))exit;
        $data['uid']	    = check_null(40002,true,$this->req_data['data']['uid']);
        $data['log_id']     = check_null(40075,true,$this->req_data['data']['log_id']);

        $rs = D('Daily_log')->get_reply_by_log_id($data['log_id'],$data['uid']);
        if($rs)
            return_json('0',$rs);
        else
            return_json('40001');

    }

    public function get_daily_log_praise(){

        if(!Database($this->req_data['data']["cid"]))exit;

        $data['uid']	    = check_null(40002,true,$this->req_data['data']['uid']);
        $data['log_id']     = check_null(40075,true,$this->req_data['data']['log_id']);

        $rs = D('Daily_log')->get_daily_log_praise($this->req_data['data']);
        if($rs)
            return_json('0',$rs);
        else
            return_json('40001');

    }

    public function get_me_remind(){

        if(!Database($this->req_data['data']["cid"]))exit;
        $data['uid']	    = check_null(40002,true,$this->req_data['data']['uid']);

        $rs = D('Daily_log')->get_me_remind($this->req_data['data']);
        if($rs)
            return_json('0',$rs);
        else
            return_json('40001');
    }

    public function get_me_replies(){

        if(!Database($this->req_data['data']["cid"]))exit;
        $data['uid']	    = check_null(40002,true,$this->req_data['data']['uid']);

        $rs = D('Daily_log')->get_me_replies($this->req_data['data']);
        if($rs)
            return_json('0',$rs);
        else
            return_json('40001');
    }

    public function get_me_praise(){

        if(!Database($this->req_data['data']["cid"]))exit;
        $data['uid']	    = check_null(40002,true,$this->req_data['data']['uid']);

        $rs = D('Daily_log')->get_me_praise($this->req_data['data']);
        if($rs)
            return_json('0',$rs);
        else
            return_json('40001');
    }

    public function get_tag(){

        if(!Database($this->req_data['data']["cid"]))exit;
        $data['uid']	    = check_null(40002,true,$this->req_data['data']['uid']);

        $rs = D('Daily_log')->get_tags_by_uid($data['uid']);
        if($rs)
            return_json('0',$rs);
        else
            return_json('40001');

    }

//-----------------------------------other-----------------------------------------

    public function upload_sd_attachment(){
        $path = 'Public/Uploads'.'/';
        if(!is_dir($path))mkdir($path,0777,true);
        $path.= 'Daily_log'.'/';
        if(!is_dir($path))mkdir($path,0777,true);
        $path.= $_REQUEST["cid"] .'/';
        if(!is_dir($path))mkdir($path,0777,true);

        import('ORG.Net.UploadFile');
        $config['savePath'] = $path;

        $upload = new UploadFile($config);
        if(!$upload->upload()){
            $this->error($upload->getErrorMsg());
        }

        $uploadList = $upload->getUploadFileInfo();
        return $this->get_zip_detail($path,$uploadList[0]['savename']);
    }

    public function get_zip_detail($zip_path,$zip_name){
        $zip = new ZipArchive;
        $zip -> open($zip_path.$zip_name);
        $arr = explode('.',$zip_name);
        $filepath = $zip_path.$arr[0];
        if(!is_dir($filepath))mkdir($filepath,0777,true);
        $zip->extractTo($filepath);
        $handle=opendir($filepath);
        while (false !== ($file = readdir($handle)))
        {
            if ($file != "." && $file != "..") {
                $array_file[] = C('DOMAIN_NAME').__ROOT__.'/'.$filepath.'/'.$file;
            }
        }
        $zip ->close();
        unlink($zip_path.$zip_name);
        return $array_file;
    }

}