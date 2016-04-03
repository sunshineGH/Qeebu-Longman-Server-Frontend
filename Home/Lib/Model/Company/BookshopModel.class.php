<?php

class BookshopModel extends Model{

//----------------------------------- init ---------------------------------------------


//----------------------------------insert--------------------------------------------
//----------------------------------delete--------------------------------------------
//----------------------------------update--------------------------------------------
//----------------------------------select--------------------------------------------
    public function get_category(){
        $rs = M('bookshop_category')->select();
        return $rs;
    }

    public function get_data($data){
        $data['count']   = $data['count'] == 0 ? 10 : $data['count'];
        $data['page']    = $data['page']  == 0 ? 1  : $data['page'];
        $map = [
            'bookshop_category_id'=>$data['bookshop_category_id']
        ];
        $rs = M('bookshop')
            ->field('id,title,desc,author,price,image_path')
            ->where($map)
            ->limit((($data['page']-1)*$data['count']).",".$data['count'])
            ->select();
        foreach($rs as &$val){
            $val['image_path'] != '' && $val['image_path'] = C('PUBLIC_URL').$val['image_path'];
            $val['i_pay'] = '0';
        }
        return $rs;
    }
}