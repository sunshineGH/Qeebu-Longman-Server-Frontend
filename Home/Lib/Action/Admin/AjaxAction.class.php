<?php


class AjaxAction extends Action {

	/**
	 * 测试函数
	 *
	 * @return Response
	 */
	public function index()
	{
		$arr = array(
		    "user" =>asdfhasadfasdfasdfaskldfjas,
		    "pass" => bbbbbbbb,
		    "name" => 'response'
		);
		echo $_GET['jsoncallback'] . "(".json_encode($arr).")";
	}

	/**
	 * 根据套题ID查询该套题下的小题.
	 *
	 * @return Response
	 */
	public function show_question()
	{
		$topic_id = $_GET['id'];

		$question = M('exercises_question');
		$arr = $question->field('id,title')->where('topic_id='.$topic_id)->select();

		echo $_GET['jsoncallback'] . "(".json_encode($arr).")";
	}



	/**
	 * 根据用户ID查询水平
	 *
	 * @return Response
	 */
	public function show_user_level()
	{
		$id = $_GET['id'];

		$user = M('user');
		$arr = $user->field('uid,level_test,speaking_level,grammar_level')->where('uid='.$id)->select();

		echo $_GET['jsoncallback'] . "(".json_encode($arr).")";
	}

	/**
	 * 根据调查问卷套题ID查询该套题下的小题.
	 *
	 * @return Response
	 */
	public function satisfaciton_question()
	{
		$topic_id = $_GET['id'];

		$question = M('satisfaction_investigate_question');
		$arr = $question->field('id,title')->where('topic_id='.$topic_id)->select();

		echo $_GET['jsoncallback'] . "(".json_encode($arr).")";

	}
	/**
	 * 根据课程的ID查询该课程的上课时间.
	 *
	 * @return Response
	 */
	public function class_time()
	{
		$class_id = $_GET['id'];

		$class_time = M('class_time');
		$arr = $class_time->field('id,time_start,time_end')->where('class_id='.$class_id)->select();

		echo $_GET['jsoncallback'] . "(".json_encode($arr).")";

	}
	/**
	 * 根据线下课程的ID查询该课程的班级.
	 *
	 * @return Response
	 */
	public function show_class()
	{
		$id = $_GET['id'];

		$class = M('class');
		$arr = $class->where('course_id='.$id.' and time_delete=0')->select();

		echo $_GET['jsoncallback'] . "(".json_encode($arr).")";

	}
	/**
	 * 根据线下课程的ID查询该课程的详情.
	 *
	 * @return Response
	 */
	public function show_course_desc()
	{
		$id = $_GET['id'];

		$course = M('course');
		$arr = $course->field('desc')->where('id='.$id)->find();

		echo $_GET['jsoncallback'] . "(".json_encode($arr).")";

	}
	/**
	 * 根据class_time的ID更改上课时间
	 *
	 * @return Response
	 */
	public function change_class_time()
	{
		$class_time_id = $_GET['class_time_id'];
		$time_start = $_GET['time_start'];
		$time_end = $_GET['time_end'];

		//改课次时间
		$class_time = M('class_time');
		$arr = array(
			'time_start' => $time_start,
			'time_end' => $time_end,
			);
		$res = $class_time->where('id='.$class_time_id)->save($arr);

		//改所有人的上课时间
		$class_time_student = M('class_time_student');
		$arr = array(
			'time_start' => $time_start,
			);
		$res = $class_time_student->where('class_time_id='.$class_time_id)->save($arr);



		$arr = array('res'=>1);
		echo $_GET['jsoncallback'] . "(".json_encode($arr).")";

	}
}