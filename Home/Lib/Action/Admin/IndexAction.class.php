<?php

class IndexAction extends CommonAction{


    public function index(){

        //查询当前软件用户量
        $push_uri = M('push_uri');
        $res = $push_uri->count();
        $this->assign('software_num',$res);
        //查询IOS
        $res = $push_uri->where('phone_system=1')->count();
        $this->assign('ios_num',$res);

        //查询android
        $res = $push_uri->where('phone_system=2')->count();
        $this->assign('android_num',$res);

        //查询教师人数
        $teacher = M('user');
        $res = $teacher->where('role=2')->count();
        $this->assign('teacher',$res);

        $this->display();
    }
}