var $k = $("body").attr('id');
var tel = '';
var check = false;

function check_user(){
	$username = $('#username').val();
	$pwd      = $('#pwd').val();
	if($username == ''){
		alert('用户名不能为空');
		return false;
	}else if($pwd == ''){
		alert('密码不能为空');
		return false;	
	}
	param = {
		username : $username,
		pwd		 : $pwd,
		k	     : $k
	};
	$(this).attr('disable',false);
	$.ajax({
		type:'post',
		url:"/teamin/Team/for_login_web",
		data:param,
		dataType:"json",
		success:function(data){
			if(data.state==0){
				$(".step1").hide();
				$(".step2").show();
			}else{
				alert('用户名或密码不正确');
			}
		}
	});
	$(this).removeAttr('disable');
	return false;
}

function get_verify(act,index){	

	tel = $('[name="tel"]').val()
	var teltest = /^1[0-9]{10}$/
	if(tel==''){
		alert('请输入手机号或邮箱')
		return false
	}
	
	var param = {
		conditions : tel,
		for_reg	   : 1,
		web		   : 1,
		check_in   : 1,
		k		   : $k
	};
	$(this).attr('disable',false);
	$.ajax({
		type:'post',
		url:"/teamin/Audience/get_verify",
		data:param,
		dataType:"json",
		success:function(data){
			$(this).removeAttr('disable');
			if(act == 'yes'){
				if(data.state == 1){
					$(".step"+ index).hide();
					$(".step2").show();
				}else if(data.state == 40131){
					alert('当前账号已经被注册,请直接登录');
					$(".step"+ index).hide();
					$(".step1").show();
				}else if(data.state == 0){
					$(".step" + index).hide();
					index = index+1;
					$(".step" + index).show();
					time_run($("#resend_verify"));
					if(data.data.conditions_style == 1){
						$("#verify_type").html('手机');
					}else{
						$("#verify_type").html('邮箱');
					}
				}else{
					alert('您的手机或者邮箱格式不正确')	
				}
			}
		}
	})
}

function check_verify(index){
	
	verify = $('[name="verify"]').val();
	var verifytest = /^[0-9]{4}$/;
	if(verify=='' || verify=='请输入验证码' || !verifytest.test(verify)){
		alert('验证码格式不正确');
		$('[name="verify"]').focus();
		return false;
	}
	
	var param = {
		conditions : $('[name="tel"]').val(),
		verify	   : $('[name="verify"]').val(),
		web		   : 1,
		key 	   : $k
	};
	$.ajax({
		type:'post',
		url:"/teamin/Team/check_verify_from_web",
		data:param,
		dataType:"json",
		beforeSend: function(){
			$(this).attr('disable',false)	
		},
		success:function(data){
			$(this).removeAttr('disable')
			if(data.state == 0){
				check = true;
				$(".step" + index).hide();
				index = index+1;
				$(".step" + index).show();
			}else if(data.state==1){
				alert('当前手机号已注册teamin,已经成功加入当前team')
				$(".step" + index).hide();
				$(".step2").show();
			}else{
				alert('验证码错误');
				return false;
			}
		}
	})	
}

function register_audience(){
	nickname = $('#reg_name').val()
	password = $('#reg_pwd').val()
	checkpassword = $('#check_pwd').val()
	
	if(nickname == '' ){
		alert('姓名不能为空')
		return false;
	}else if(password=='' || checkpassword != password){
		alert('两次输入的密码不一致');	
		return false;
	}
		
	var param = {
		conditions 	  : $('[name="tel"]').val(),
		audience_name : nickname,
		audience_pwd  : password,
		k			  : $k
	};
	$.ajax({
		type:'post',
		url:"/teamin/Team/join_team_come_from_web",
		data:param,
		dataType:"json",
		beforeSend: function(){
			$(this).attr('disable',false)	
		},
		success:function(data){
			if(data.state==0){
				$(".step5").hide();
       			$(".step2").show();
			}else{
				alert('您的账号已注册过了,请直接登录');
				$(".step5").hide();
        		$(".step1").show();
			}
		}
	})
}

var wait=60;
function time_run() {
	if (wait == 0) {
		$("#resend_verify").removeAttr('disabled');
		$("#resend_verify").html('<span onclick="resend()">重新发送验证码</>');
		wait = 60;
	} else {
		$("#resend_verify").attr('disabled',true);
		if(wait<10)
			wait = '0'+wait;	
		$("#resend_verify").html("重 新 发 送(" + wait + ")");
		wait--;
		setTimeout(function() {
			time_run()
		},
		1000);
	}
}

function resend(){
	get_verify('no',4);
	wait = 60;
	time_run();
}

