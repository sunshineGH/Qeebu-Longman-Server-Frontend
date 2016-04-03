<?php
class QuestionnaireAction extends Action{

    public $req_data = [];

//-------------------------------------init--------------------------------------

    protected function _initialize(){
        $this->req_data = json_decode($_REQUEST['req'],true);
    }

    public function index(){
        $this->_initialize();
        switch($this->req_data['type']){
            case 'export_excel':
                $this->export_excel();
                break;
            case 'export_excel_detail':
                $this->export_excel_detail();
                break;
        }
    }
//----------------------------------insert----------------------------------------
    public function add_question_answer(){
        $rs = '';
        if(!Database($_REQUEST["cid"]))exit;
        $_REQUEST['uid']             = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['questionnaire_id']= check_null(40094,true,$_REQUEST['questionnaire_id']);
        $_REQUEST['answers']         = check_null(40095,true,$_REQUEST['answers']);

        $arr = json_decode($_REQUEST['answers'],true);

        foreach($arr as $val) {
            $data['questionnaire_id'] = $_REQUEST['questionnaire_id'];
            $data['audience_id']      = $_REQUEST['uid'];
            $data['question_id']      = $val['question_id'];
            $data['answer']           = isset($val['answer'])?$val['answer']:'';
            $data['other']            = isset($val['other'])?$val['other']:'';
            $rs = D('Questionnaire')-> submit_answers($data);
            $data = null;unset($data);
        }
        if(!$rs)
            return_json(-1);
        else
            return_json(0);
    }
//----------------------------------delete----------------------------------------
//----------------------------------update----------------------------------------
//----------------------------------select----------------------------------------
    public function get_questionnaire_list(){
        if(!Database($_REQUEST["cid"]))exit;
        $_REQUEST['uid']        = check_null(40002,true,$_REQUEST['uid']);
        //1 没答 2 答过
        $_REQUEST['have_answer']= check_null(40093,true,$_REQUEST['have_answer']);

        $rs = D('Questionnaire')-> get_questionnaire_list($_REQUEST);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function get_questionnaire_question(){
        if(!Database($_REQUEST["cid"]))exit;
        $_REQUEST['audience_id']      = check_null(40002,true,$_REQUEST['uid']);
        $_REQUEST['questionnaire_id'] = check_null(40094,true,$_REQUEST['questionnaire_id']);

        $rs['questions'] = D('Questionnaire')-> get_questionnaire_question($_REQUEST);

        $rs['answer']    = D('Questionnaire')-> get_questionnaire_answer($_REQUEST);

        $rs['participate_num'] = D('Questionnaire')->get_audience_num($_REQUEST['questionnaire_id']);

        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }


//----------------------------------other------------------------------------------
    public function export_excel(){
        if(!Database($this->req_data['data']["cid"]))exit;
        check_null(40094,true,$this->req_data['data']["questionnaire_id"]);

        header('content-type:text/html;charset=utf-8');

        $rs['questionnaire']   = D('Questionnaire')->get_questionnaire_by_id($this->req_data['data']);
        $rs['questions']       = D('Questionnaire')->get_questionnaire_question($this->req_data['data']);
        $rs['answer']          = D('Questionnaire')->get_questionnaire_answer_result($this->req_data['data']);
        $rs['participate_num'] = D('Questionnaire')->get_audience_num($this->req_data['data']['questionnaire_id']);

        $table = '';
        //这里导出选择题
        $i = 1;

        $avg_arr = [];

        foreach($rs['questions'] as $val){

            if($val['question_type']!=3){

                $nums = [];

                $opt = [];
                $result = $rs['answer'][$val['question_id']];
                foreach($val['option'] as $vv){
                    $opt[$vv['opt']] = $vv['answer'];
                }
                $j = count($result);
                foreach($result as $k=>$v){

                    for($x=1;$x<=$v['num'];$x++){
                        $nums[] = $j;
                    }
                    $j--;
                }
                $avg = round(array_sum($nums)/count($nums),2);
                $avg_arr[]=$avg;

                $j = count($result);
                $table.='<tr>
                    <td rowspan="'.$j.'">'.($i++).'、'.$val['question_title'].'</td>
                    <td style="text-align:center" rowspan="'.$j.'">'.$avg.'</td>
                    <td style="text-align:center" rowspan="'.$j.'">'.$this->getVariance($avg,$nums).'</td>';

                foreach($result as $k=>$v){
                    $table.='<td>'.$opt[$k].'</td>
                        <td style="text-align:center">['.$j.']</td>
                        <td style="text-align:center">'.$v['num'].'</td>
                        <td style="text-align:center">'.$v['percent'].'</td>
                      </tr>
                      <tr>';
                    $j--;
                }
                $table = substr($table,0,strlen($table)-4);
            }
        }


        //这里导出填空题
        $i = 1;
        foreach($rs['questions'] as $val){
            if($val['question_type']==3){
                $result = D('Questionnaire')->get_audience_answer_by_question_id($val['question_id']);
                $table.='
                  <tr>
                    <td colspan="7">&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="7"><strong>'.$i++.'、'.$val['question_title'].'：</strong></td>
                  </tr>';
                $z = 1;
                foreach($result as $v){
                    $table.='<tr>
                            <td colspan="7">'.$z++.'）'.$v.'</td>
                          </tr>';
                }
            }
        }


        $table1 = '<table border="1" cellspacing="1" cellpadding="1">
                      <tr>
                        <th colspan="7" scope="col"><span class="STYLE3">'.$rs['questionnaire'][0]['questionnaire_title'].'评估问卷反馈汇总</span></th>
                      </tr>
                      <tr>
                        <td colspan="2">&nbsp;</td>
                        <td colspan="5">&nbsp;</td>
                      </tr>
                      <tr>
                        <td colspan="2">&nbsp;</td>
                        <td colspan="5">&nbsp;</td>
                      </tr>
                      <tr>
                        <td colspan="2">&nbsp;</td>
                        <td colspan="5">参与人数:'.$rs['participate_num'].'</td>
                      </tr>
                      <tr>
                        <td width="320" rowspan="2"><div align="center" class="STYLE4">评价项目</div></td>
                        <td width="67" rowspan="2"><div align="center" class="STYLE4">平均分</div></td>
                        <td width="63" rowspan="2"><div align="center" class="STYLE4">标准差</div></td>
                        <td colspan="4"><div align="center" class="STYLE4">样本</div></td>
                      </tr>
                      <tr>
                        <td colspan="2"><div align="center"><strong>选项</strong></div></td>
                        <td width="78"><div align="center"><strong>个数</strong></div></td>
                        <td width="93"><div align="center"><strong>百分比(%)</strong></div></td>
                      </tr>
                      <tr>
                        <td height="52">1.总体评分</td>
                        <td style="text-align:center">'.round(array_sum($avg_arr)/count($avg_arr),2).'</td>
                        <td>&nbsp;</td>
                        <td width="82">&nbsp;</td>
                        <td width="59">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>';

        $table = $table1.$table;
        $table.='</table>';
        A('Admin/Excel')->download_excel($table);
    }

    public function export_excel_detail(){
        if(!Database($this->req_data['data']["cid"]))exit;
        check_null(40094,true,$this->req_data['data']["questionnaire_id"]);

        header('content-type:text/html;charset=utf-8');

        $rs['answers']         = D('Questionnaire')->get_audience_answer($this->req_data['data']['questionnaire_id']);

        $table1= '<table border="1" cellspacing="1" cellpadding="1">';

        $count = $rs['answers'][0]['nums'];
        $table1.= '
          <tr>
            <th width="120" rowspan="2" scope="col">问卷编号</th>
            <th colspan="'.$count.'" scope="col">&nbsp;</th>
          </tr>';

        $i=1;
        $table1 .='<tr>';
        foreach($rs['answers'][0]['question'] as $key=>$val){
            $table1 .='
                <td width="100">'.($i++).'、'.$val['question_title'].'</td>
            ';
        }
        $table1 .='</tr>';

        foreach($rs['answers'] as $val){

            $table1.='<tr>';
            $table1.='<th scope="row">'.$val['audience_name'].'</th>';
            foreach($val['question'] as $key=>$val1){
                $table1.='
                    <td><div align="center">'.$val1['answer_detail'].'</div></td>
                ';
            }
            $table1.='</tr>';
        }

        $table1.= '</table>';
        A('Admin/Excel')->download_excel($table1);
    }

    public function getVariance($avg, $list){
        $total_var = 0;
        foreach ($list as $lv){
            $total_var += pow( ($lv - $avg), 2 );
        }

        $Variance = sqrt( $total_var / (count($list) - 1 ) );
        return round($Variance,2);
    }

}