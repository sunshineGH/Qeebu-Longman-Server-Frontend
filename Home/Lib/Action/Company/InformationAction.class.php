<?php
/**
 * Created by PhpStorm.
 * User: zhanghao
 * Date: 2014/12/22
 * Time: 10:45
 */
class InformationAction extends Action{

    private $informations	= "`information_id`, `information_title`, `information_subtitle`, `information_desc`, `information_type`, `information_size`, `information_section`, `information_category`, `information_url_type`, `information_url_upload`, `information_url_editor`, `information_image`, `information_channel`,`time_insert` as `information_time`";
    private $information	= "`information_id`";
    private $information_channels ="`channel_id`, `channel_name`, `channel_sort`";
    private	$information_channel  ='`channel_id`';
    private $arr = [];
    //information
    public function get_database_synchronization(){

        if(!Database($_REQUEST['cid']))return;
        $_REQUEST['uid']      = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['last_time']= isset($_REQUEST['last_time'])?$_REQUEST['last_time']:1;

        $this->arr['information']         = D("Sqlite")->get_database_synchronization('information',$this->informations,$this->information,$_REQUEST['last_time']);
        $this->arr['information_channel'] = D("Sqlite")->get_database_synchronization('information_channel',$this->information_channels,$this->information_channel,$_REQUEST['last_time']);

        if($this->arr['information']['insert']!=null)
        {
            foreach($this->arr['information']['insert'] as $val){
                if($val['information_url_type']==1)
                    $val['information_url']=$val['information_url_editor'];
                else
                    $val['information_url']=C('DOMAIN_NAME').__ROOT__.'/'.$val['information_url_upload'];
                if($val['information_image']!='')
                    $val['information_image']=C('DOMAIN_NAME').__ROOT__.'/'.$val['information_image'];
                $val['downLoadNum']=M('information_log','','DB_MEETING')->where('information_id='.$val['id'])->count();
                unset($val['information_url_type']);
                unset($val['information_url_upload']);
                unset($val['information_url_editor']);
                $information_insert[]= $val;
            }
            $val = null;unset($val);
            $this->arr['information']['insert']=$information_insert;
        }

        if($this->arr['information']['update']!=null){
            foreach($this->arr['information']['update'] as $val){
                if($val['information_url_type']==1)
                    $val['information_url']=$val['information_url_editor'];
                else
                    $val['information_url']  =C('DOMAIN_NAME').__ROOT__.'/'.$val['information_url_upload'];
                if($val['information_image']!='')
                    $val['information_image']=C('DOMAIN_NAME').__ROOT__.'/'.$val['information_image'];
                $val['downLoadNum']=M('information_log','','DB_MEETING')->where('information_id='.$val['id'])->count();
                unset($val['information_url_type']);
                unset($val['information_url_upload']);
                unset($val['information_url_editor']);
                $information_update[]= $val;
            }
            $val = null;unset($val);
            $this->arr['information']['update']=$information_update;
        }
        $this->arr['last_time']=time();
        return_json(0,$this->arr);
    }
//学习百宝箱数据库sqlite下载
    public function get_database(){


        $rs =array(
//            'sqlite_path'       => C('DOMAIN_NAME').__ROOT__.'/'.$information_sqlite_path,
            'sqlite_path'       =>'http://huiyi.qeebu.cn/teamin/Public/Uploads/sqlite/studybox/VAhQVg==/studybox.db',
            'sqlite_version'    =>7,
            'sqlite_must_update'=>0
        );
        return_json(0,$rs);
        /*try {
            $information_sqlite_path = 'Public/Uploads/sqlite/';
            if (!is_dir($information_sqlite_path)) mkdir($information_sqlite_path, 0777, true);
            $information_sqlite_path .= 'studybox/';
            if (!is_dir($information_sqlite_path)) mkdir($information_sqlite_path, 0777, true);
            $information_sqlite_path .= $_REQUEST['cid'] . '/';
            if (!is_dir($information_sqlite_path)) mkdir($information_sqlite_path, 0777, true);

            $information_sqlite_path .= 'studybox.db';
            unlink($information_sqlite_path);
            $dbh = new PDO("sqlite:{$information_sqlite_path}");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = 'CREATE TABLE information(';
            $sql.=      'information_id integer PRIMARY KEY,';
            $sql.=      'information_title Varchar DEFAULT NULL,';
            $sql.=      'information_subtitle Varchar DEFAULT NULL,';
            $sql.=      'information_desc TEXT DEFAULT NULL,';
            $sql.=      'information_type Varchar DEFAULT NULL,';
            $sql.=      'information_size Varchar DEFAULT NULL,';
            $sql.=      'information_section Varchar DEFAULT NULL,';
            $sql.=      'information_category integer,';
            $sql.=      'information_channel integer,';
            $sql.=      'information_url Varchar DEFAULT NULL,';
            $sql.=      'information_image Varchar DEFAULT NULL,';
            $sql.=      'information_isDownload integer,';
            $sql.=      'information_time,';
            $sql.=      'downLoadNum integer';
            $sql.= ');';

            $sql.= 'CREATE TABLE information_channel(';
            $sql.=      'channel_id integer PRIMARY KEY,';
            $sql.=      'channel_name Varchar DEFAULT NULL,';
            $sql.=      'channel_sort integer';
            $sql.= ');';

            $dbh->exec($sql);
            $rs =array(
                'sqlite_path'       => C('DOMAIN_NAME').__ROOT__.'/'.$information_sqlite_path,
                'sqlite_version'    =>7,
                'sqlite_must_update'=>0
            );
            return_json(0,$rs);
        } catch (Exception $e) {
            echo "error!!:$e";
            exit;
        }*/
    }
}