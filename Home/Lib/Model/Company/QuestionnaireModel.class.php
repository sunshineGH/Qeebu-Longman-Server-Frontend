<?php

class QuestionnaireModel extends Model{

    public $questionnaire_list_cows = 'a.`questionnaire_id`, `questionnaire_title`,`questionnaire_publish`, `questionnaire_detail`,  `questionnaire_state`, `questionnaire_end_time`';

    public function get_question_num($questionnaire_id)
    {
        $rs = M('questionnaire_question','','DB_MEETING')->field('questionnaire_id')->where('questionnaire_id='.$questionnaire_id)->select();
        return count($rs);
    }

    public function get_audience_num($questionnaire_id)
    {
        $rs = M('questionnaire_audience_answer','','DB_MEETING')
            ->field('distinct audience_id')
            ->where('questionnaire_id='.$questionnaire_id)
            ->select();
        return count(i_array_column($rs,'audience_id'));
    }


    public function get_questionnaire_list($data)
    {
        $data['page']               = $data['page']  == null? 1:$data['page'];
        $data['count']              = $data['count'] == null?10:$data['count'];
        $data['questionnaire_state']= $data['questionnaire_state'] == null?1:$data['questionnaire_state'];

        $sql = 'select '.$this->questionnaire_list_cows.' FROM  `questionnaire` a';

        if($data['have_answer'] == 1)
            $sql.= ' where questionnaire_end_time>='.time().' and questionnaire_state in ('.$data['questionnaire_state'].') and questionnaire_id not in (select distinct questionnaire_id from questionnaire_audience_answer where audience_id = '.$data['uid'].')';
        else
            $sql.= ' where questionnaire_state in ('.$data['questionnaire_state'].') and questionnaire_id in (select distinct questionnaire_id from questionnaire_audience_answer where audience_id = '.$data['uid'].')';

        $sql.= ' order by a.questionnaire_id desc';
        $sql.= ' limit '.($data['page']-1)*$data['count'].','.$data['count'];
        $rs  = M('questionnaire','','DB_MEETING')->query($sql);

        foreach($rs as $val){
            $val['questionnaire_num'] = $this->get_question_num($val['questionnaire_id']);
            $val['participant_num']   = $this->get_audience_num($val['questionnaire_id']);
            $val['audience_name']     = D('Audience')->get_audience_name_by_uid($val['questionnaire_publish']);
            $val['audience_name']     = $val['audience_name']==false?'管理员':$val['audience_name'];
	        unset($val['questionnaire_publish']);
            $arr[]=$val;
        }
        return $arr;
    }

    public function get_questionnaire_by_id($data){

        $arr = [];
        $rs  = M('questionnaire','','DB_MEETING')->where(['questionnaire_id'=>$data['questionnaire_id']])->select();
        foreach($rs as $val){
            $val['questionnaire_num'] = $this->get_question_num($val['questionnaire_id']);
            $val['participant_num']   = $this->get_audience_num($val['questionnaire_id']);
            $val['audience_name']     = D('Audience')->get_audience_name_by_uid($val['questionnaire_publish']);
            $val['audience_name']     = $val['audience_name']==false?'管理员':$val['audience_name'];
            unset($val['questionnaire_publish']);
            $arr[]=$val;
        }
        return $arr;
    }

    public function submit_answers($data)
    {
        M('questionnaire_audience_answer','','DB_MEETING')->where('audience_id='.$data['audience_id'].' and question_id='.$data['question_id'])->delete();
        $type = $this->get_question_type($data['question_id']);
        if($type == 2){
            $answers = explode(',',$data['answer']);

            foreach($answers as $val)
            {
                $data1           = $data;
                $data1['answer'] = $val;
                M('questionnaire_audience_answer','','DB_MEETING')->add($data1);
            }
            return true;
        }else
            return M('questionnaire_audience_answer','','DB_MEETING')->add($data);
    }

    public function get_questionnaire_question($data){
        $rs = M('questionnaire_question','','DB_MEETING')->where('questionnaire_id='.$data['questionnaire_id'])->select();
        foreach($rs as &$val) {
            if ($val['question_type'] == 3)
                $val['option'] = null;
            else
                $val['option'] = $this->get_question_option($val['question_id']);
            $val['option'] == false && $val['option'] = null;
        }
        return $rs;
    }

    public function get_question_option($question_id){
        return M('questionnaire_question_opt','','DB_MEETING')->where('question_id='.$question_id)->order('opt')->select();
    }

    public function get_questionnaire_answer($data){
        $arr   = [];
        $newrs = [];

        $rs = M('questionnaire_audience_answer','','DB_MEETING')->field('question_id,answer,other')->where('questionnaire_id='.$data['questionnaire_id'].' and audience_id='.$data['audience_id'])->select();

        foreach($rs as $val)
        {
            if($arr[$val['question_id']] != null)
                $arr[$val['question_id']]['answer'] .= ','.$val['answer'];
            else
                $arr[$val['question_id']]['answer'] = $val['answer'];
            $arr[$val['question_id']]['other']      = $val['other'];
            $arr[$val['question_id']]['question_id']= $val['question_id'];
        }
        $val=null; unset($val);
        foreach($arr as &$val){
            $type = $this->get_question_type($val['question_id']);
            if($type ==3 )
                $val['result'] = null;
            else
                $val['result'] = $this ->get_question_result($val['question_id'],$type);
            $val['result'] == array() && $val['result'] = null;
            $newrs[]=$val;
        }

        $newrs == array() && $newrs = null;

        return $newrs;
    }

    public function get_questionnaire_answer_result($data){
        $arr   = [];
        $newrs = [];

        $rs = M('questionnaire_question','','DB_MEETING')
            ->field('question_id')
            ->where('questionnaire_id='.$data['questionnaire_id'])
            ->select();

        foreach($rs as $val){
            if($arr[$val['question_id']] != null)
                $arr[$val['question_id']]['answer'] .= ','.$val['answer'];
            else
                $arr[$val['question_id']]['answer'] = $val['answer'];
            $arr[$val['question_id']]['other']      = $val['other'];
            $arr[$val['question_id']]['question_id']= $val['question_id'];
        }
        $val=null; unset($val);
        foreach($arr as &$val){
            $type = $this->get_question_type($val['question_id']);
            if($type ==3 )
                $val['result'] = null;
            else
                $val['result'] = $this ->get_question_result($val['question_id'],$type);
            $val['result'] == array() && $val['result'] = null;
            $newrs[$val['question_id']]=$val['result'];
        }

        $newrs == array() && $newrs = null;

        return $newrs;
    }

    /**
     * @param $question_id
     * @return mixed
     */
    public function get_question_result($question_id,$type){
        $arr = [];

        $sql    = 'select count(answer) as num,answer as opt from questionnaire_audience_answer where question_id='.$question_id.' GROUP by answer';
        $rs     = M('questionnaire_audience_answer','','DB_MEETING')->query($sql);
        //总票数
        $count  = M('questionnaire_audience_answer','','DB_MEETING')->where('question_id='.$question_id)->count();
        //选项数
        $opts   = M('questionnaire_question_opt','','DB_MEETING')->field('question_id,opt')->where('question_id='.$question_id)->select();

        foreach($rs as $val){
            $val['opt'] == null && $val['opt'] = 'other';
            $val['percent'] = round(($val['num'] / $count),2);
            $arr[$val['opt']] = array('num'=>$val['num'],'percent'=>$val['percent']);
        }
        $val=null;unset($val);

        foreach($opts as $val){
            $arr[$val['opt']] == null && $arr[$val['opt']] = array('num'=>0,'percent'=>0);
        }

        if($type == 4)
            $arr['other'] == null && $arr['other'] = array('num'=>0,'percent'=>0);

        ksort($arr);
        return $arr;
    }

    public function get_question_type($question_id){
        return M('questionnaire_question','','DB_MEETING')->where('question_id='.$question_id)->getField('question_type');
    }

    public function get_audience_answer_by_question_id($question_id){
        $rs = M('questionnaire_audience_answer','','DB_MEETING')
            ->field('other')
            ->where('question_id='.$question_id)
            ->select();
        return i_array_column($rs,'other');

    }

    public function get_question_title_by_id($question_id){
        $rs = M('questionnaire_question','','DB_MEETING')
            ->where('question_id='.$question_id)
            ->getField('question_title');
        return $rs;
    }

    public function get_answer_detail_by_id_answer($question_id,$answer){
        $map = [
            'question_id'=>$question_id,
            'opt'     =>$answer
        ];

        return M('questionnaire_question_opt','','DB_MEETING')
            ->where($map)
            ->getField('answer');
    }

    public function get_audience_answer($questionnaire_id){
        $rs = M('questionnaire_audience_answer','','DB_MEETING')
            ->where('questionnaire_id='.$questionnaire_id)
            ->order('question_id')
            ->select();

        $arr = [];

        foreach($rs as $val){

            $arr[$val['audience_id']][$val['question_id']] = [
                'question_title'=>$this->get_question_title_by_id($val['question_id']),
            ];
            if($val['answer']!=''){
                $arr[$val['audience_id']][$val['question_id']]['answer_detail'] = $val['answer'];//$this->get_answer_detail_by_id_answer($val['question_id'],$val['answer']);
            }else{
                $arr[$val['audience_id']][$val['question_id']]['answer_detail'] = $val['other'];
            }
        }

        $new_arr = [];
        foreach($arr as $key=>$val){

            $new_arr[$key] = [
                'question'      =>$val,
                'nums'          =>count($val),
                'audience_name' =>D('Audience')->new_get_audience_name_by_uid($key)
            ];
        }

        usort($new_arr,function($a,$b){
            if($a['nums'] == $b['nums']){
                return 0;
            }
            return ($a['nums'] > $b['nums']) ? -1 : 1;
        });

        return $new_arr;
    }
}
