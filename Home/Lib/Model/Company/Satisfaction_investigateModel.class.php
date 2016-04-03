<?php

class Satisfaction_investigateModel extends Model{


//-------------------------------------init------------------------------------------

//-----------------------------------------------------------------------------------
    public function get_topic($data){

        $data['page'] = $data['page'] == 0 ? 1 : $data['page'];
        $data['count']= $data['count']== 0 ? 10 : $data['count'];

        $map=[
            'type' =>$data['type']
        ];
        $rs = M('satisfaction_investigate_topic')->where($map)
            ->field('`id`, `name`, `desc`, `end_time`')
            ->order('id desc')
            ->limit((($data['page']-1)*$data['count']).",".$data['count'])
            ->select();

        if(!$rs)return false;

        foreach($rs as &$val){
            $val['questions_num'] = $this->get_questions_num($val['id']);
            $val['user_num']      = $this->get_user_num($val['id']);
            $val['if_i_do']       = $this->get_user_answer($val['id'],$data['uid']) ? '1' : '0';
            $val['topic_type']    = '4';
            $val['id'] = StrCode($val['id']);
        }
        return $rs;
    }

    public function get_questions_num($topic_id){
        $map = ['topic_id'=>$topic_id];
        return M('satisfaction_investigate_question')->where($map)->count();
    }

    public function get_user_num($topic_id){
        $map = ['topic_id'=>$topic_id];
        return M('satisfaction_investigate_user_answer')->where($map)->count();
    }

    public function get_user_answer($topic_id,$uid){
        $map = ['topic_id'=>$topic_id,'uid'=>$uid];
        return M('satisfaction_investigate_user_answer')->where($map)->find();
    }

    public function get_questions($topic_id){
        $map=[
            'topic_id'=>$topic_id
        ];
        $rs = M('satisfaction_investigate_question')->where($map)
            ->field('id,title,detail,question_type')
            ->select();
        if(!$rs)return false;

        foreach($rs as &$val){
            $val['images'] = [];
            $val['audio'] = '';

            $item = $this->get_question_item($val['id']);
            if($item){
                foreach($item as $val1){
                    $type = end(explode('.',$val1));
                    if($type == 'mp3' || $type == 'm4a')
                        $val['audio'] = $val1;
                    else
                        $val['images'][]= $val1;
                }
            }
            $val['answer']      = $this->get_question_answer($val['id']);
            $val['true_answer'] = '0';
        }
        return $rs;
    }

    public function get_question_item($question_id){

        $map['question_id'] = $question_id;
        $rs = M('satisfaction_investigate_question_images')->where($map)->select();
        if(!$rs)return [];

        $img_arr = [];
        foreach($rs as $val){
            $img_arr[] = D('Files')->get_file($val['file_id']);
        }
        return $img_arr;
    }

    public function get_question_answer($question_id){

        $map['question_id'] = $question_id;
        $rs = M('satisfaction_investigate_question_opt')
            ->field('id as answer_id,question_id,opt,answer,file_id')
            ->where($map)->select();
        if(!$rs)return [[],''];

        foreach($rs as &$val){
            if($val['file_id']!=0){
                $val['answer'] = D('Files')->get_file($val['file_id']);
                $val['type']   = '1';
            }else{
                $val['type']   = '0';
            }
            unset($val['file_id']);
        }
        return $rs;
    }

    public function submit_user_answer($data){
        return M('satisfaction_investigate_user_answer')->add($data);
    }
}