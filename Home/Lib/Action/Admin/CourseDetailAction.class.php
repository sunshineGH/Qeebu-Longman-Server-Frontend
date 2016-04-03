<?php


class CourseDetailAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//课程ID
		$courseid = $_GET['courseid'];
		//获取此课程的所有课
		$detail = M('course_detail');
		$result = $detail->where('course_id='.$courseid)->select();


		if(isset($_GET['times'])) {
			$num = $_GET['times'];
		} else {
			$num = 1;
		}

		$this->assign('times',$num);
		$courseinfo = $result[$num-1];

		$this->assign('detail',$result);
		$this->assign('courseid',$courseid);
		$this->assign('courseinfo',$courseinfo);

		// echo '<pre>';
		// var_dump($num);
		// var_dump($result);
		// var_dump($courseinfo);
		// exit();

		$this->display('index');
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$this->display('create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store($courseid)
	{
		$detail = M('course_detail');
		$arr = array(
				'course_id'      	=> $courseid,
				'course_homework' 	=> '',
				'course_schedule' 	=> '',
				'course_detail' 	=> '',
				'time_insert' 		=> time(),
				);
		$detail_id = $detail->add($arr);

		$course = M('course');
		$course = $course->field('supporting_course_id')->where('id='.$courseid)->find();
		$supporting_course_id = $course['supporting_course_id'];

		$unit = M('supporting_course_unit');
		$arr = array(
				'supporting_course_id' 	=> $supporting_course_id,
				'course_detail_id' 		=> $detail_id,
				'name'					=> 'unit',
			);
		$unit->add($arr);

		//查class表,如果有该课程的班级,则加class_time记录
		$class = M('class');
		$class_time = M('class_time');
		$class_user = M('class_user');
		$class_time_user = M('class_time_student');
		$class_res = $class->field('id')->where('course_id='.$courseid.' and time_delete=0')->select();
		foreach($class_res as $val) {
			$arr = array(
				'class_id' 			=> $val['id'],
				'course_id' 		=> $courseid,
				'course_detail_id' 	=> $detail_id,
				'time_start' 		=> time(),
				'time_end' 			=> time(),
				'max_num' 			=> '',
				);
			$class_time_id = $class_time->add($arr);

			//增加class_time记录同时也要加,class_time_student记录
			//查这个班有多少人,每天人都加class_time_student记录
			$class_user_res = $class_user->where('class_id='.$val['id'].' and time_delete=0')->select();
			foreach($class_user_res as $v) {
				$arr = array(
					'class_id' 		=> $val['id'],
					'class_time_id' => $class_time_id,
					'user_id' 		=> $v['user_id'],
					'time_start' 	=> time(),
					'state' 		=> 1,
					'coming_style' 	=> 0,
					);
				$class_time_user->add($arr);
				echo $class_time_user->getLastSql();
			}
		}


		redirect(U('Admin/CourseDetail/index',array('courseid'=>$courseid,'times'=>1)));

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show()
	{
		$this->display('show');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit()
	{
		$this->display('edit');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update()
	{
		$courseid = $_GET['courseid'];
		$detailid = $_GET['detailid'];
		$arr = array(
				'course_id'      	=> $courseid,
				'course_homework' 	=> $_POST['course_homework'],
				'course_schedule' 	=> $_POST['course_schedule'],
				'course_detail' 	=> $_POST['course_detail'],
				);
		$detail = M('course_detail');
		$res = $detail->where('id='.$detailid)->save($arr);


		redirect(U('Admin/CourseDetail/index',array('courseid'=>$courseid,'times'=>1)));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		$detailid = $_GET['detailid'];
		$courseid = $_GET['courseid'];

		$user = M('course_detail');
		$user->where('id='.$detailid)->delete();

		$unit = M('supporting_course_unit');
		$unit->where('course_detail_id='.$detailid)->delete();

		//删除对应的class_time_student记录
		$class_time = M('class_time');
		$class_time_student = M('class_time_student');
		$res = $class_time->where('course_detail_id='.$detailid)->select();
		foreach($res as $val) {
			$class_time_student->where('class_time_id='.$val['id'])->delete();
			echo $class_time_student->getLastSql();

		}

		//删除对应的class_time记录
		$class_time->where('course_detail_id='.$detailid)->delete();


		redirect(U('Admin/CourseDetail/index',array('courseid'=>$courseid,'times'=>1)));
	}

}