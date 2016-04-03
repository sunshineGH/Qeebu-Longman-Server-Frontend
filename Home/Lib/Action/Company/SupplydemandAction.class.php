<?php
/**
 * Created by PhpStorm.
 * User: zhanghao
 * Date: 2014/12/22
 * Time: 17:02
 */
class SupplydemandAction extends Action{

/*
 * create_supplydemand
 * @name
 * 创建一条供需关系信息
 *
 * @arg
 * cid          int     公司id   must     40038
 * uid          int     创建人id must      40002
 * sd_type      int     供需状态  must     40086
 * sd_title     varchar 名称     must     40087
 * sd_detail    text    详情     must     40088
 * local        varchar 地点     must     40092
 *
 * */

    public function create_supplydemand(){

        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['sd_publish'] = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['sd_type']    = check_null(40086,true,$_REQUEST['sd_type']);
        $_REQUEST['sd_title']   = check_null(40087,true,$_REQUEST['sd_title']);
        $_REQUEST['sd_detail']  = check_null(40088,true,$_REQUEST['sd_detail']);
        $_REQUEST['sd_local']   = '';//check_null(40092,true,$_REQUEST['local']);
        $_REQUEST['sd_state']   = 0 ;

        $_REQUEST['sd_title']   = str_replace('\\U','\\u',$_REQUEST['sd_title']);
        $_REQUEST['sd_local']   = str_replace('\\U','\\u',$_REQUEST['sd_local']);
        $_REQUEST['sd_detail']  = str_replace('\\U','\\u',$_REQUEST['sd_detail']);

        if($_FILES['sd_attachment']['name']!='')
            $_REQUEST['sd_attachment'] = $this->upload_sd_attachment();

        $rs = D('Supplydemand')->create_supplydemand($_REQUEST);
        if($rs){
            return_json(0);
        }else{
            return_json(-1);
        }

    }
/*
 * get_supplydemand_list
 * @name
 * 获取供应信息列表
 *
 * @arg
 * cid int 公司id must 40038
 * uid int 创建人id must 40002
 * sd_type int 供需状态 must 40086
 *
 * @optional
 * state        int     状态 optional
 * title        varchar 标题
 * local        varchar 地点
 * company_name varchar 公司名称
 * detail       varchar 详情
 *
 * //http://huiyi.qeebu.cn/teamin/Supplydemand/get_supplydemand_list?sd_type=0&state=1&without_me=1&cid=VApSVw%3D%3D&page=1&uid=Vg%3D%3D
 * */
    public function get_supplydemand_list(){
        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['uid'] = check_null(40002,true,$_REQUEST['uid']);

        $rs = D('Supplydemand')->get_supplydemand_list($_REQUEST);
        if(!$rs)
            return_json('40001');
        else
            return_json(0,$rs);
    }
/*
 * 获取供应信息详情
 *
 * @arg
 * cid int 公司id must 40038
 * uid int 创建人id must 40002
 * sd_id int 供需状态 must 40089
 *
 * @update
 * zhanghao 2014/12/22 21:00
 * */
    public function get_supplydemand_details(){
        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['uid']    = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['sd_id']  = check_null(40089,true,$_REQUEST['sd_id']);

        if(!$rs = D('Supplydemand')->get_supplydemand_details($_REQUEST))
            return_json('40001');
        else
            return_json(0,$rs);
    }
/*
 * @name
 * 我要联系
 *
 * @arg
 * cid int 公司id must 40038
 * uid int 创建人id must 40002
 * sd_id int 供需状态 must 40089
 *
 * @update
 * zhanghao 2014/12/23 18:11
 * */
    public function contact_supplydemand(){
        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['audience_id']= check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['sd_id']      = check_null(40089,true,$_REQUEST['sd_id']);
        $_REQUEST['time_insert']=time();

        if(D('Supplydemand')->check_reg($_REQUEST))
            return_json('40090');
        else{
            D('Supplydemand')->contact_supplydemand($_REQUEST)?return_json(0):return_json('-1');
        }
    }

/*
 * get_supplydemand_typ
 * @name
 * 获取供需关系类型
 *
 * @arg
 * cid int 公司id must 40038
 * uid int must 40002
 * */
    public function get_supplydemand_type(){
        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['uid'] = check_null(40002,true,$_REQUEST['uid']);

        $rs = D('Supplydemand')->get_supplydemand_type($_REQUEST);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }
/*
 * @name
 * 获取联系人列表
 *
 * @arg
 * cid int 公司id must 40038
 * uid int 创建人id must 40002
 * sd_id int 供需状态 must 40089
 *
 * @update
 * zhanghao 2014/12/22 21:00
 * */
    public function get_supplydemand_audience_list(){

        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['uid']   = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['sd_id'] = check_null(40086,true,$_REQUEST['sd_id']);

        $rs = D('Supplydemand')->get_supplydemand_audience_list($_REQUEST);

        if(!$rs){
            return_json('40001');return;
        }

        $arr=[];
        foreach($rs as $val) {
            $audience = D('Audience')->new_get_audience_by_uid($val['audience_id']);
            if(!$audience)continue;

            $val['audience_name']    = $audience['audience_name']==null?'Default':$audience['audience_name'];
            $val['audience_company'] = '公司名称';//$audience['audience_company'];
            $val['audience_tel']     = $audience['audience_tel']==null?'Default':$audience['audience_tel'];
            $val['audience_local']   = '公司地址';//$audience['audience_local'];
            $audience=null;unset($audience);

            $arr[]=$val;
        }
        if(!$arr)
            return_json(40001);
        else
            return_json(0,$arr);

    }
/*
 * @name
 * 审批
 *
 * @arg
 * cid int   公司id must 40038
 * uid int   创建人id must 40002
 * sd_id int 供需状态 must 40089
 * state int 状态 must 40091
 *
 * @optionnal
 * sd_detail 详情 optionnal
 * sd_local 地点  optionnal
 * sd_type 供需类型 optionnal
 * @update
 * zhanghao 2014/12/22 21:00
 * */
    public function update_supplydemand(){

        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['uid']     = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['sd_id']   = check_null(40086,true,$_REQUEST['sd_id']);
        $_REQUEST['sd_state']= check_null(40091,true,$_REQUEST['state']);

        D('Supplydemand')->update_supplydemand($_REQUEST)?return_json(0):return_json(-1);
    }
/*
 * my_supplydemand
 * @name
 * 我的供需列表
 *
 * @arg
 * cid int 公司id must 40038
 * uid int 创建人id must 40002
 *
 * @optional
 * state int 完成状态 optional
 * type int 供需状态 optional
 *
 * @update
 * zhanghao 2014/12/24 16:09
 * */
    public function my_supplydemand(){
        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['uid'] = check_null(40002,true,$_REQUEST['uid']);

        if(!$rs = D('Supplydemand')->my_supplydemand($_REQUEST))
            return_json('40001');
        else
            return_json(0,$rs);
    }
//-----------------------------other----------------------------------------------
    public function upload_sd_attachment(){
        $path = 'Public/Uploads'.'/';
        if(!is_dir($path))mkdir($path,0777,true);
        $path.= 'supplydemand'.'/';
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