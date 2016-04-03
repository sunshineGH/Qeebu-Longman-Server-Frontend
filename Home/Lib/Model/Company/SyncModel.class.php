<?php

class SyncModel extends Model{

    public function get_database_synchronization($table,$fields,$field,$last,$where=[]){

        $re = [];

        $map = [
            'insert' => [
                'time_insert'=>['gt',$last],
                '_string'    =>'time_delete<time_insert'
            ],
            'update' => [
                'time_insert'=>['lt',$last],
                'time_update'=>['egt',$last],
                '_string'    =>'time_delete < time_insert'
            ],
            'delete' => [
                'time_insert'=>['elt',$last],
                'time_delete'=>['gt',$last]
            ]
        ];
        if($where!=[] && count($where)>0){
            $map['insert'] = array_merge($map['insert'],$where);
            $map['update'] = array_merge($map['update'],$where);
            $map['delete'] = array_merge($map['delete'],$where);
        }
        $add_list   = M($table)->field($fields)->where($map['insert'])->select();
        $update_list= M($table)->field($fields)->where($map['update'])->select();
        $delete_list= M($table)->field($field) ->where($map['delete'])->select();

        if($add_list==null || count($add_list)==0)$re['insert']=null;
        else $re['insert'] = $add_list;
        if($update_list==null || count($update_list)==0)$re['update']=null;
        else $re['update'] = $update_list;
        if($delete_list==null || count($delete_list)==0)$re['delete']=null;
        else $re['delete'] = $delete_list;

        return $re;
    }
}