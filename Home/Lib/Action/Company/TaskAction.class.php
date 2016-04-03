<?php
/**
 * Created by zhanghao.
 * User: Zhanghao
 * tel: 18609059892
 * qq: 261726511
 */
class TaskAction extends Action{

    public $req_data = [];

//-------------------------------------init--------------------------------------

    protected function _initialize(){
        $this->req_data = json_decode($_REQUEST['req'],true);
        if (!$this->req_data){
            return_json(-1);
            exit();
        }
    }

    public function index(){
        $this->_initialize();
        switch($this->req_data['type']){
            case 'sync':
                $this->sync();
                return;
            case 'get':
                $this->client_get();
                break;
            case 'get_project':
                $this->client_get_project();
                break;
            case 'get_task_by_project':
                $this->client_get_task_by_project();
                break;
            case 'update_project'       : $this->update_project();       break;
            case 'update_task'          : $this->update_task();          break;

            case 'create_new_task'      : $this->create_new_task();      break;
            case 'create_new_project'   : $this->create_new_project();   break;

            case 'update_task_read_time': $this->update_task_read_time();break;

            case 'done_myself_task'     : $this->done_myself_task();     break;
            case 'get_task_execution'   :
                $this->get_task_execution();
                break;

            default:
                return_json(-1);
                return;
        }
    }

//-------------------------------------insert------------------------------------
    public function create_new_project(){

        if(!Database($this->req_data['data']["cid"]))exit;

        $data['uid']			    = check_null(40002,true,$this->req_data['data']['uid']);
        $data['project_name']       = check_null(40081,true,$this->req_data['data']['project_name']);
        $data['project_detail']     = $this->req_data['data']['project_detail'];
        $data['project_time_begin'] = $this->req_data['data']['project_time_begin'];
        $data['project_time_end']   = $this->req_data['data']['project_time_end'];
        $data['project_time_done']  = $this->req_data['data']['project_time_done'];
        $data['project_members_id'] = $this->req_data['data']['project_members_id'];
        $data['project_color']      = $this->req_data['data']['project_color'];

        $rs = D('Task')->create_new_project($data);
        if($rs){
            $project = D('Task')->get_project_by_id_all($rs);
            return_json('0',$project);
        }else
            return_json('-1');
    }

    public function create_new_task(){

        if(!Database($this->req_data['data']["cid"]))exit;

        $data = [
            'uid'                       => check_null(40002,true,$this->req_data['data']['uid']),
            'task_content'              => check_null(40121,true,$this->req_data['data']['task_content']),
            'task_project_id'           => $this->req_data['data']['task_project_id'],
            'task_time_begin'           => $this->req_data['data']['task_time_begin'],
            'task_time_end'             => $this->req_data['data']['task_time_end'],
            'task_time_done'            => $this->req_data['data']['task_time_done'],
            'task_members_id'           => $this->req_data['data']['task_members_id'],
            'task_desc'                 => $this->req_data['data']['task_desc'] ? $this->req_data['data']['task_desc'] : '',

            //second add for local notice
            'task_notice_time'          => $this->req_data['data']['task_notice_time'] ? $this->req_data['data']['task_notice_time'] : 0,
            'task_notice_repeat'        => $this->req_data['data']['task_notice_repeat'] ? $this->req_data['data']['task_notice_repeat'] : 0,

            //the urgency and importance
            'task_urgency'              => $this->req_data['data']['task_urgency'] ? $this->req_data['data']['task_urgency'] : 0,
            'task_importance'           => $this->req_data['data']['task_importance'] ? $this->req_data['data']['task_importance'] : 0,

            //new add share task log
            'task_is_share_on'          => $this->req_data['data']['task_is_share_on'],
            'task_at_members_id'        => $this->req_data['data']['task_at_members_id'],
            'task_is_department'        => $this->req_data['data']['task_is_department'],
            'task_share_members_id'     => $this->req_data['data']['task_share_members_id'],
            'task_members_department_id'=> $this->req_data['data']['task_members_department_id'],
            'task_share_name'           => $this->req_data['data']['task_share_name']
        ];
        $data['task_creater_id'] = $data['uid'];

        $rs = D('Task')->create_new_task($data);
        if($rs){
            $return_data = [
                'task_id'=>$rs,
                'task_content'=>$data['task_content']
            ];
            return_json('0',$return_data);
        }else
            return_json('-1');
    }
//-------------------------------------update------------------------------------
    public function update_task(){
        if(!Database($this->req_data['data']["cid"]))exit;
        check_null(40002,true,$this->req_data['data']['uid']);
        check_null(40122,true,$this->req_data['data']['task_id']);

        $rs = D('Task')->update_task($this->req_data['data']);
        if($rs){
            return_json('0');
        }else
            return_json('-1');
    }

    public function done_myself_task(){
        if(!Database($this->req_data['data']["cid"]))exit;
        $data = [
            'uid'    => check_null(40002,true,$this->req_data['data']['uid']),
            'task_id'=> check_null(40122,true,$this->req_data['data']['task_id']),
            'time_insert'=>time()
        ];
        $rs = D('Task')->done_myself_task($data);
        if($rs)
            return_json(0);
        else
            return_json(40001);
    }

    public function update_project(){
        if(!Database($this->req_data['data']["cid"]))exit;
        $data['uid']        = check_null(40002,true,$this->req_data['data']['uid']);
        $data['project_id'] = check_null(40049,true,$this->req_data['data']['project_id']);

        if(!D('Task')->get_task_project_admin($data)){
            return_json(40120);return;
        }

        $rs = D('Task')->update_project($this->req_data['data']);
        if($rs)
            return_json('0');
        else
            return_json('-1');
    }

    public function update_task_read_time(){
        if(!Database($this->req_data['data']["cid"]))exit;

        $data['uid']       = check_null(40002,true,$this->req_data['data']['uid']);
        $data['task_id']   = check_null(40122,true,$this->req_data['data']['task_id']);

        $rs = D('Task')->update_task_read_time($data);

        if($rs)
            return_json(0);
        else
            return_json(-1);
    }
//-------------------------------------delete--------------------------------------
//-------------------------------------select--------------------------------------
    public function get_task_execution(){
        if(!Database($this->req_data['data']["cid"]))exit;
        $data = [
            'task_id'=> check_null(40122,true,$this->req_data['data']['task_id']),
        ];
        $rs = D('Task')->get_task_execution_detail($data);
        if($rs)
            return_json(0,$rs);
        else
            return_json(40001);
    }

    public function sync(){

        if(!Database($this->req_data['data']["cid"]))exit;
        $data['uid']       = check_null(40002,true,$this->req_data['data']['uid']);
        $data['timestamp'] = check_null(40042,true,$this->req_data['data']['timestamp']);

        $rs['project'] = D('Task')->sync_project($data);
        $rs['task']    = D('Task')->sync_task($data);
        $rs['key']     = StrCode($this->req_data['data']["cid"],'DECODE');

        return_json(0,$rs,time());
    }

    public function client_get(){
        if(!Database($this->req_data['data']["cid"]))exit;
        $data['uid']         = check_null(40002,true,$this->req_data['data']['uid']);
        $data['project_id']  = $this->req_data['data']['project_id'] ? $this->req_data['data']['project_id'] : 0;
        $data['no_resolved'] = $this->req_data['data']['no_resolved'] ? $this->req_data['data']['no_resolved'] : 0;

        $rs = D('Task')->get_my_task($data);
        if($rs)
            return_json(0,$rs,time());
        else
            return_json(40001);
    }

    public function client_get_project(){
        if(!Database($this->req_data['data']["cid"]))exit;
        $data['uid']       = check_null(40002,true,$this->req_data['data']['uid']);

        $rs = D('Task')->get_my_project($data);
        if($rs)
            return_json(0,$rs,time());
        else
            return_json(40001);
    }

    public function client_get_task_by_project(){
        if(!Database($this->req_data['data']["cid"]))exit;
        $data['uid']       = check_null(40002,true,$this->req_data['data']['uid']);
        $data['project_id']= check_null(40049,true,$this->req_data['data']['project_id']);

        $rs = D('Task')->get_task_by_project($data);
        if($rs)
            return_json(0,$rs,time());
        else
            return_json(40001);
    }

}