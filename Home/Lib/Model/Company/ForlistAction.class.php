<?php
class ForlistAction extends Action {
	
	private $AppId		="wxdfc8b8c3eee4696b";
	private $AppSecret	="80c75e46c755b5417d631634816a357e";
	
	public function index(){
		$token_json=$this->http_request("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->AppId."&secret=".$this->AppSecret);
		$token_array=json_decode($token_json);
		$token=$token_array->access_token;
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$token}";
		$data = "
		{
			\"button\": 
			[
				{				
					\"name\": \"在线学习\",
					\"sub_button\": 
					[
						{
							\"type\": \"view\",
							\"name\": \"学生导学\",
							\"url\": \"http://scy.bgy.org.cn/photographic/html/Course_Introduction/guidance.html\"
						},
						{
							\"type\": \"view\",
							\"name\": \"在线学习\",
							\"url\": \"http://scy.bgy.org.cn/photographic/processEbCourseOnline.do?actionType=learning\"
						},
						{
							\"type\": \"view\",
							\"name\": \"企业课堂\",
							\"url\": \"http://scy.bgy.org.cn/photographic/html/sbsjx/luxiang_list.html\"
						},
						{
							\"type\": \"view\",
							\"name\": \"互动社区\",
							\"url\": \"http://m.wsq.qq.com/262954577\"
						}
					]
				},
				{
					\"name\": \"学习检测\",
					\"sub_button\": 
					[
						{
							\"type\": \"view\",
							\"name\": \"模拟考试\",
							\"url\": \"http://scy.bgy.org.cn/photographic/processExamManag.do?actionType=querySimuExam\"
						},
						{
							\"type\": \"view\",
							\"name\": \"综合测试\",
							\"url\": \"http://scy.bgy.org.cn/photographic/processExamManag.do?actionType=queryMockExam\"
						},
						{
							\"type\": \"view\",
							\"name\": \"作品展示\",
							\"url\": \"http://scy.bgy.org.cn/photographic/html/self_test/zuop.html\"
						}
					]
				},
				{
					\"name\": \"教学资源\",
					\"sub_button\": 
					[
						{
							\"type\": \"view\",
							\"name\": \"课程内容\",
							\"url\": \"http://scy.bgy.org.cn/photographic/welcome.do?actionType=welcome\"
						},
						{
							\"type\": \"view\",
							\"name\": \"专家答疑\",
							\"url\" : \"http://scy.bgy.org.cn/photographic/processQuestion.do?actionType=queryExpert\"
						},
						{
							\"type\": \"view\",
							\"name\": \"老师在线\",
							\"url\": \"http://scy.bgy.org.cn/photographic/processQuestion.do?actionType=queryTeacher\"
						},
						{
							\"type\": \"view\",
							\"name\": \"电子课件\",
							\"url\": \"http://scy.bgy.org.cn/photographic/html/teaching_resources/ppt_list.html\"
						}
					]
				}
			]
		}";
		$rs=$this->post($url,$data);
		echo $rs; 	
	}
	public function http_request($url,$timeout=30,$header=array()){  
        if (!function_exists('curl_init')) {  
            throw new Exception('server not install curl');  
        }  
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
        curl_setopt($ch, CURLOPT_HEADER, true);  
        curl_setopt($ch, CURLOPT_URL, $url);  
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);  
        if (!empty($header)) {  
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);  
        }  
        $data = curl_exec($ch);  
        list($header, $data) = explode("\r\n\r\n", $data);  
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
        if ($http_code == 301 || $http_code == 302) {  
            $matches = array();  
            preg_match('/Location:(.*?)\n/', $header, $matches);  
            $url = trim(array_pop($matches));  
            curl_setopt($ch, CURLOPT_URL, $url);  
            curl_setopt($ch, CURLOPT_HEADER, false);  
            $data = curl_exec($ch);  
        }  
  
        if ($data == false) {  
            curl_close($ch);  
        }  
        @curl_close($ch);  
        return $data;  
    }
	public function post($url, $jsonData){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$jsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		$result = curl_exec($ch) ;
		curl_close($ch) ;
		return $result;
   }
}
