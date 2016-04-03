<?php
/*邮件发送*/
function Tomail($email,$title,$content,$id){
	$_POST['timestamp']=time();
	include_once("Public/PHPMailer_v5.1/class.phpmailer.php"); //下载的文件必须放在该文件所在目录
	$mail = new PHPMailer(); //建立邮件发送类	
	$mail->IsSMTP(); // 使用SMTP方式发送
	$mail->Host = "smtp.exmail.qq.com"; // 您的企业邮局域名
	$mail->SMTPAuth = true; // 启用SMTP验证功能
	$mail->Username = "support@qeebu.cn"; // 邮局用户名(请填写完整的email地址)]
	$mail->Password = "zhanghao123"; // 邮局密码
	$mail->Port=25;
	$mail->From = "support@qeebu.cn"; //邮件发送者email地址
	$mail->FromName = "";
	$mail->AddAddress("$email", "");//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")//
	$mail->AddReplyTo("", "");//
	//$mail->AddAttachment("/var/tmp/file.tar.gz"); // 添加附件//
	$mail->IsHTML(true); // set email format to HTML //是否使用HTML格式
	$mail->CharSet = "UTF-8"; //指定编码方式
	$mail->Subject = $title; //邮件标题
	$mail->Body = $content; //邮件内容
	//$mail->AltBody = "This is the body in plain text for non-HTML mail clients"; //附加信息，可以省略
	if(!$mail->Send()){
		return false;
		//return array('code'=>0,'msg'=>"发送失败");	
	}else{
		//return true;
		return array('code'=>1,'msg'=>$id);	
	}	
}

function to_create_team_database(){

}


?>