<?php


class ClassinfoAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$id = $_GET['id'];
		$this->assign('class_id',$id);
		//根据关联表class_user查出这个班的学生,再查出学生信息

		$search   = isset($_GET['search'])   ? $_GET['search']   : '';

		$res = pageing('class_user',20,$search,'id desc','class_id='.$id.' and role=1 and time_delete=0','','id='.$id);

		$user = M('user');

		foreach($res['data'] as &$val)
		{
			$user_res = $user->where('uid='.$val['user_id'])->find();
			$val['user_info'] = $user_res;
		}

		$this->assign('data',$res['data']);
		$this->assign('show',$res['show']);
		$this->display('index');
	}

	/**
	 * 查询不在这个班的用户
	 *
	 * @return Response
	 */
	public function create()
	{
		$class_id = $_GET['id'];
		$this->assign('class_id',$class_id);

		//先查询在这个班的用户
		$class_user = M('class_user');
		$class_user_res = $class_user->field('user_id')->where('class_id='.$class_id.' and time_delete=0')->select();
		foreach($class_user_res as &$val) {
			$val = $val['user_id'];
		}

		//查不在这个班的用户
		$map['uid']  = array('not in',$class_user_res);
		$map = 'uid not in ('.implode(',',$class_user_res).')';

		$res = pageing('user',7,'','',$map,'role=1','id='.$class_id);
		$this->assign('data',$res['data']);
		$this->assign('show',$res['show']);
		$this->display();
	}

	/**
	 * 把用户添加到这个班级
	 *
	 * @return Response
	 */
	public function store()
	{
		$class_id = $_GET['id'];
		$uid = $_POST['uid'];

		//查一下该班级属于哪个课程的
		$class = M('class');
		$res = $class->where('time_delete=0')->field('course_id')->find();
		$course_id = $res['course_id'];


		$class_user = M('class_user');
		$class_time = M('class_time');
		$class_time_user = M('class_time_student');
		// dump($uid);
		foreach($uid as $val) {
			// echo $val;
			// echo '<br>';
			$data_user_class = array(
				'class_id' 		=> $class_id,
				'course_id' 	=> $course_id,
				'user_id' 		=> $val,
				'role' 			=> 1,
				'time_insert' 	=> time(),
				'time_update' 	=> time(),
				'time_delete' 	=> 0,
				);
			$class_user->add($data_user_class);
			// echo $class_user->getLastSql();
			// echo '<br>';

			//根据课加class_time_student表,查这个班级有多少次课,每次课增加一条class_time_user记录

			$res = $class_time->where('class_id='.$class_id)->select();
			// dump($res);

			foreach($res as $v) {
				$arr = array(
					'class_id' 		=> $class_id,
					'class_time_id' => $v['id'],
					'user_id' 		=> $val,
					'time_start' 	=> $v['time_start'],
					'state' 		=> 1,
					'coming_style' 	=> 0,
					);
				$class_time_user->add($arr);
				// echo $class_time_user->getLastSql();
			}
		}

		$this->success('成功',U('Admin/Classinfo/index',array('id'=>$class_id)));

	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		$id = $_GET['id'];
		$class_id = $_GET['class_id'];
		//删除class_user表
		$class_user = M('class_user');
		$arr = array(
			'time_delete' => time(),
			);
		$class_user->where('user_id='.$id)->save($arr);

		//class_time_student
		$class_time_student = M('class_time_student');
		$class_time_student->where('user_id='.$id)->delete();

		$this->success('成功',U('Admin/Classinfo/index',array('id'=>$class_id)));
	}
}