<?php

class ExercisesModel extends Model{

    public function get_topic($data){

        $data['page'] = $data['page'] == 0 ? 1 : $data['page'];
        $data['count']= $data['count']== 0 ? 10 : $data['count'];

        $level = D('User')->get_user('',$data['uid'],'speaking_level,grammar_level');

        if($data['type'] == 1){
            if($level['grammar_level'] == 0)return [];
            $level = $level['grammar_level'];
        }else{
            if($level['speaking_level'] == 0)return [];
            $level = $level['speaking_level'];
        }

        $map=[
            'level'=>$level,
            'type' =>$data['type']
        ];
        $rs = M('exercises_topic')->where($map)
            ->field('id,name,type as topic_type')
            ->order('id desc')
            ->limit((($data['page']-1)*$data['count']).",".$data['count'])
            ->select();
        if(!$rs)return false;

        foreach($rs as &$val){
            $val['id'] = StrCode($val['id']);
        }
        return $rs;
    }

    public function get_questions($topic_id){

        $map=[
            'topic_id'=>$topic_id
        ];
        $rs = M('exercises_question')->where($map)
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
            $answer = $this->get_question_answer($val['id']);
            $val['answer'] = $answer[0];
            $val['true_answer'] = $answer[1];
        }
        return $rs;

    }

    public function get_question_item($question_id){

        $map['question_id'] = $question_id;
        $rs = M('exercises_question_images')->where($map)->select();
        if(!$rs)return [];

        $img_arr = [];
        foreach($rs as $val){
            $img_arr[] = D('Files')->get_file($val['file_id']);
        }
        return $img_arr;
    }

    public function get_question_answer($question_id){

        $map['question_id'] = $question_id;
        $rs = M('exercises_question_opt')
            ->field('id as answer_id,question_id,opt,answer,file_id,true_answer')
            ->where($map)->select();
        if(!$rs)return [[],''];

        $true_answer = '';
        foreach($rs as &$val){
            if($val['file_id']!=0){
                $val['answer'] = D('Files')->get_file($val['file_id']);
                $val['type']   = '1';
            }else{
                $val['type']   = '0';
            }
            $val['true_answer'] == 1 && $true_answer = $val['opt'];
            unset($val['file_id']);
            unset($val['true_answer']);
        }
        return [$rs,$true_answer];
    }
}