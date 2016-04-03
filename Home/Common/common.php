<?php
/*
 * ==============================
 * @author zhangqiang
 * @date 2015-09-17
 * ==============================
 * @description    随机颜色
 * @key            随机数
**/
function color($key){
	$arr = array('bg-primary','bg-warning','bg-success','bg-success','bg-primary','bg-warning');
 	echo $arr[$key];
}


/*
 * ==============================
 * @author zhangqiang
 * @date 2015-09-17
 * ==============================
 * @description    输入时间戳,,判断如果时间戳为0,,输出空,,如果不为0,则转换为字符串输出
 * @key
**/
function print_time($key){
 	echo $key==0?"":date('Y-m-d',$key);
}


/*
 * ==============================
 * @author zhangqiang
 * @date 2015-09-17
 * ==============================
 * @description    输出theme类型中文
 * @key            theme类型
**/
function theme_type($key){
	$arr = array('习题','视频','hmtl');
 	echo $arr[$key];
}
/*
 * ==============================
 * @author zhangqiang
 * @date 2015-09-17
 * ==============================
 * @description    计算年龄
 * @key
**/
function age($key)
{
	if($key==0){
		return '';
	}
	return ceil((time()-$key)/31536000);
}

/**
 * ==============================
 * @author
 * @edit
 * @date  2015-09-17
 * ==============================
 * @description    分页函数
 * @tbName     表名
 * @pageSize   每页显示数据数
 * @seach      like条件
 * @order      排序
 * @where      条件
 * @col        查询条件的字段
 *
 *
 * @return $rs
 *      $rs['show']
 *	    $rs['data']
 */

function pageing($tbName='',$pageSize=10,$search='',$order='',$where='',$col='',$param=''){

	$get_param='';

	if($search){
		$get_param.="&seach={$search}";
		//$get_param.="&where={$where}";
		$search=" and {$col} like '%{$search}%'";
	}
	if($order!=''){
		$get_param.="&order={$order}";
	}
	if($param!=''){
		$get_param.="&{$param}";
	}

	$model=M($tbName);
	$totalRow = $model->where($where.$search)->count();
	$totalPage= ceil($totalRow/$pageSize);

	$page=isset($_GET['page'])?$_GET['page']:1;
	$page=$page>$totalPage?$totalPage:$page;
	$page=$page<1?1:$page;

	$rs['data']=$model
		->where($where.$search)
		->order("{$order}")
		->limit(($page-1)*$pageSize.','.$pageSize)
		->select();
		// echo $model->getLastSql();

	$pageFor=__ACTION__."/"."?page=";

	if($page>1){
		$rs['show']="<li><a href='{$pageFor}1{$get_param}' title='First Page'>首页</a></li><li><a href='{$pageFor}".($page-1)."{$get_param}' title='Previous Page'>上一页</a></li>";
	}
	$init = 1;
	$max = $totalPage;
	$pagelen=4;
	$pagelen = ($pagelen%2)?$pagelen:$pagelen+1;
	$pageoffset = ($pagelen-1)/2;
	if($totalPage>$pagelen){
		if($page<=$pageoffset){
			$init=1;
			$max = $pagelen;
		}else{
			if($page+$pageoffset>=$totalPage+1){
				$init = $totalPage-$pagelen+1;
			}else{
				$init = $page-$pageoffset;
				$max = $page+$pageoffset;
			}
		}
	}
	//生成html
	for($i=$init;$i<=$max;$i++){
		$current=$i==$page?'current':'';
		$rs['show'].="
				<li><a href='{$pageFor}{$i}{$get_param}' class='number {$current}' title='{$i}'>{$i}</a></li>
			";
	}
	if($page<$totalPage){
		$rs['show'].="<li><a href='{$pageFor}".($page+1)."{$get_param}' title='Next Page'>下一页</a></li>
				  <li><a href='{$pageFor}{$totalPage}{$get_param}' title='Last Page'>尾页</a></li>
			";
	}
	return $rs;
}
/*
 * This function use by check given param not null
 * $val  $error_code
 * $need must or no
 * $dom  the request param
 * */
function check_null($val='',$need,$dom=''){
	if(trim($dom)=='' || trim($dom)==NULL){
		if($need){
			return_json($val);
			exit;
		}else{
			return $val;
		}
	}else{
		return $dom;
	}
}

/*
 * This function to return json
 * $state is error code
 * $data return array
 * $timestamp  return time()
 * $update	just not use but one function
 * */
function return_json($state='',$data='',$timestamp='',$update=''){

	$zh_error_msg = [
		'0'=>'ok!',
		'-1'=>'Error!!',
		'40001'=>'No data!',
		'40002'=>'Miss username!',
		'40003'=>'Miss password!',
		'40004'=>'Username not exits!',
		'40005'=>'Password not correct!',
		'40006'=>'Username is exits!',
		'40007'=>'Miss nickname!',
		'40008'=>'SMS exception!',
		'40009'=>'Miss verify!',
		'40010'=>'Error verify!',
		'40011'=>'The tel format is not correct!',
		'40012'=>'Error token!',
		'40013'=>'Miss id!',
		'40014'=>'Miss course_id',
		'40015'=>'permission denied!',
		'40016'=>'Miss topic_id',
		'40017'=>'Miss timestamp',
		'40018'=>'Miss class_id',
		'40019'=>'Already brings flower!',
		'40020'=>'Miss class_space_id',
		'40021'=>'Miss homework_date',
		'40022'=>'Miss reply_content',
		'40023'=>'Miss class_homework_id',
		'40024'=>'Miss leave_date',
		'40025'=>'Miss class_time_id',
		'40026'=>'Miss student_id',
		'40027'=>'Miss leave_id',
		'40028'=>'No sets',
		'40029'=>'Miss class_date',
		'40030'=>'Miss supporting_course_id',
		'40031'=>'Miss unit_id',
		'40032'=>'Miss news_id',
		'40033'=>'Miss trade_no',
		'40034'=>'There is not enough positions of',
		'40035'=>'Registration has ended',
		'40036'=>'Miss special_case_date',
		'40037'=>'Miss bookshop_category_id',
		'40038'=>'Miss satisfaction_investigate_answer',
		'40039'=>'Miss phone_system',
		'40040'=>'Miss device_token',
		'40041'=>'Miss version',
		'40042'=>'Miss model_id',
	];

	$obj 		= null;
	$obj->state	= $state;
	$obj->msg 	= $zh_error_msg[$state];
	$timestamp != '' && $obj->timestamp = $timestamp;
	$update	   != '' && $obj->update	= $update;
	$data	   != '' && $obj->data		= $data;

	echo json_encode($obj);
	exit();
}

/*
 * push sms
 * */
function for_sms($phone_no, $content){

	$sn = C('Sms_sn');
	$password = C('Sms_pwd');
	$params = '';
	$line = '';
	$flag = 0;
	$argv = array(
		'sn'=>$sn,
		'pwd'=>strtoupper(md5($sn.$password)),
		'mobile'=>$phone_no,
		'content'=>$content.'[奇步互动]',//iconv( "UTF-8", "gb2312//IGNORE" ,'您好测试短信[奇步互动]'),
		'ext'=>'',
		'stime'=>'',
		'msgfmt'=>'',
		'rrid'=>''
	);
	foreach ($argv as $key=>$value) {
		if ($flag!=0) {
			$params .= "&";
			$flag = 1;
		}
		$params.= $key."="; $params.= urlencode($value);
		$flag = 1;
	}
	$length = strlen($params);
	$fp = fsockopen("sdk.entinfo.cn",8061,$errno,$errstr,10) or exit($errstr."--->".$errno);
	$header = "POST /webservice.asmx/mdsmssend HTTP/1.1\r\n";
	$header .= "Host:sdk.entinfo.cn\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: ".$length."\r\n";
	$header .= "Connection: Close\r\n\r\n";
	$header .= $params."\r\n";
	fputs($fp,$header);
	$inheader = 1;
	while (!feof($fp)) {
		$line = fgets($fp,1024);
		if ($inheader && ($line == "\n" || $line == "\r\n")) {
			$inheader = 0;
		}
		if ($inheader == 0) {
		}
	}
	$line=str_replace("<string xmlns=\"http://tempuri.org/\">","",$line);
	$line=str_replace("</string>","",$line);
	$result=explode("-",$line);
	if(count($result)>1)
		return false;
	else
		return true;

}

/*
 * array_column not use by php<5.4 and use this
 * */
function i_array_column($input, $columnKey, $indexKey=null){
	if(!function_exists('array_column')){
		$columnKeyIsNumber  = (is_numeric($columnKey))?true:false;
		$indexKeyIsNull     = (is_null($indexKey))?true :false;
		$indexKeyIsNumber   = (is_numeric($indexKey))?true:false;
		$result             = array();
		foreach((array)$input as $key=>$row){
			if($columnKeyIsNumber){
				$tmp= array_slice($row, $columnKey, 1);
				$tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null;
			}else{
				$tmp= isset($row[$columnKey])?$row[$columnKey]:null;
			}
			if(!$indexKeyIsNull){
				if($indexKeyIsNumber){
					$key = array_slice($row, $indexKey, 1);
					$key = (is_array($key) && !empty($key))?current($key):null;
					$key = is_null($key)?0:$key;
				}else{
					$key = isset($row[$indexKey])?$row[$indexKey]:0;
				}
			}
			$result[$key] = $tmp;
		}
		return $result;
	}else{
		return array_column($input, $columnKey, $indexKey);
	}
}




/*
 * push email
 * */
function for_mail($email,$title,$content){

	$sn   = C('Email_sn');
	$pwd  = C('Email_pwd');
	$host = C('Email_host');

	include_once("Public/PHPMailer_v5.1/class.phpmailer.php");

	$mail = new PHPMailer();
	$address =	$email;
	$mail->IsSMTP();
	$mail->Host = $host;
	$mail->SMTPAuth = true;
	$mail->Username = $sn;
	$mail->Password = $pwd;
	$mail->Port=25;
	$mail->From = $sn;
	$mail->FromName = "TeamIn";
	$mail->AddAddress("$address", "");
	$mail->AddReplyTo("", "");
	$mail->IsHTML(true);
	$mail->CharSet = "UTF-8";
	$mail->Subject = $title;
	$mail->Body = $content;
	if(!$mail->Send())
		return false;
	else
		return true;
}

function StrCode($string,$action='ENCODE'){
	$string = ''.$string;
	$action!= 'ENCODE' && $string = base64_decode($string);
	$code 	= '';
	$key 	= substr(md5(C('OWN_KEY')),8,18);
	$keyLen = strlen($key);
	$strLen = strlen($string);
	for($i=0;$i<$strLen;$i++){
		$k = $i % $keyLen;
		$code.=$string[$i] ^ $key[$k];
	}
	return ($action != 'DECODE' ? base64_encode($code) : $code);
}

function self_md5($string){
	return md5(md5(C('OWN_KEY')).md5($string));
}

function make_library_sqlite(){
	try{
		$path='Public/Uploads/sqlite/library.db';

		$dbh=new PDO("sqlite:{$path}");
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$sql = <<<EOF
			CREATE TABLE library(
				library_id Varchar PRIMARY KEY,
				library_title Varchar DEFAULT NULL,
				library_desc Text DEFAULT NULL,
				library_price Varchar DEFAULT NULL,
				library_pay  Varchar DEFAULT NULL,
				library_for integer DEFAULT NULL,
				channel_id integer DEFAULT NULL,
				time_insert integer DEFAULT NULL,
				is_download integer DEFAULT NULL,
				file_type Varchar DEFAULT NULL,
				file_size Varchar DEFAULT NULL,
				file_path Varchar DEFAULT NULL
			);
EOF;
		$sql .= <<<EOF
			CREATE TABLE library_channel(
				channel_id Varchar PRIMARY KEY,
				channel_name Varchar DEFAULT NULL,
				channel_for integer DEFAULT NULL,
				channel_sort integer DEFAULT NULL
			);
EOF;
		$dbh->exec($sql);

		$rs = M('sqlite_database')->where(['name'=>'library'])->find();
		if(!$rs){
			M('sqlite_database')->add([
				'name'=>'library',
				'version'=>1,
				'path'=>C('DOMAIN_NAME').__ROOT__.'/'.$path
			]);
		}else{
			M('sqlite_database')->where(['name'=>'library'])->save([
				'version'=>$rs['version']+1,
				'path'=>C('DOMAIN_NAME').__ROOT__.'/'.$path
			]);
		}
	}catch(Exception $e) {
		echo "error!!:$e";
		exit;
	}
}

function get_the_month($date){
	$firstDay 	= date('Y-m-01', strtotime($date));
	$lastDay 	= date('Y-m-d', strtotime("$firstDay +1 month -1 day"));
	return array(strtotime($firstDay),strtotime($lastDay));
}

function init_public_url($url){
	if($url == ''){
		return '';
	}else{
		preg_match_all('/^(http)|^(public\/upload)/i',$url,$arr);
		if($arr[0][0]=="http"){
			return $url;
		}elseif($arr[0][0]=="public/upload"){
			return C('PUBLIC_URL').$url;
		}
	}
	return '';
}

?>