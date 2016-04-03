<?php

class LibraryModel extends Model{

    public function get_channel($last){

        $table = 'library_channel';
        $fields= 'id,name,channel_for,channel_sort';
        $field = 'id';
        $where = ['channel_for'=>1];
        return D('Sync')->get_database_synchronization($table,$fields,$field,$last,$where);
    }

    public function get_library_channel($last){

        $table = 'library_channel';
        $fields= 'id,name,channel_for,channel_sort';
        $field = 'id';
        $where = ['channel_for'=>2];
        return D('Sync')->get_database_synchronization($table,$fields,$field,$last,$where);
    }

    public function get_data($last,$uid){

        $course_ids = D('Course')->get_course_id_by_student_id($uid);

        $table  = 'library';
        $field  = 'id';
        $fields = 'id,`title`, `desc`, `file_id`,`price`,1 as pay,channel_id,library_for,time_insert';
        $where  = ['course_id'=>['in',implode(',',$course_ids)]];
        $rs     = D('Sync')->get_database_synchronization($table,$fields,$field,$last,$where);

        foreach($rs['insert'] as &$val){
            $file = D('Files')->get_file_detail($val['file_id'],'path_type,file_type,file_size,file_path');
            if(!$file){
                unset($val);
                continue;
            }
            unset($val['file_id']);
            $val['id'] = StrCode($val['id']);
            $val = array_merge($val,$file);
        }

        foreach($rs['update'] as &$val1){
            $file = D('Files')->get_file_detail($val1['file_id'],'path_type,file_type,file_size,file_path');
            if(!$file){
                unset($val1);
                continue;
            }
            unset($val1['file_id']);
            $val1['id'] = StrCode($val1['id']);
            $val1 = array_merge($val1,$file);
        }

        return $rs;
    }
}