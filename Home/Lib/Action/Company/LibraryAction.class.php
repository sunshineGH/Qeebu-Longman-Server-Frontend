<?php

class LibraryAction extends Action{

    public function ln_get_channel($data){

        D('User')->check_token($data['uid'],$data['token']);
        $last = check_null(40017,true,$data['timestamp']);
        $rs = D('Library')->get_channel($last);

        if($rs)
            return_json(0,$rs,time());
        else
            return_json(40001);
    }

    public function ln_get_data($data){

        D('User')->check_token($data['uid'],$data['token']);

        $last = check_null(40017,true,$data['timestamp']);

        $rs['channel'] = D('Library')->get_channel($last);
        $rs['library'] = D('Library')->get_data($last,$data['uid']);
        $rs['timestamp'] = time();
        if($rs)
            return_json(0,$rs,time());
        else
            return_json(40001);
    }

    public function ln_get_library_channel($data){

        D('User')->check_token($data['uid'],$data['token']);
        $last = check_null(40017,true,$data['timestamp']);
        $rs = D('Library')->get_library_channel($last);

        if($rs)
            return_json(0,$rs,time());
        else
            return_json(40001);
    }

    public function ln_get_library_sqlite($data){

        D('User')->check_token($data['uid'],$data['token']);

        if(!is_file('Public/Uploads/sqlite/library.db'))make_library_sqlite();

        $rs = M('sqlite_database')->where(['name'=>'library'])->field('version,path')->find();
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);

    }

}