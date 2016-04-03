// config.js
var base_url = "http://www.subaoming.cn";
var base_root= "/zhilian";
var base_index_action = base_root+'/index.php/Index/';
var base_audience_action = base_root+'/index.php/Audience/';
var msgInfo = {
	msg_0	 :'成功',
	msg_40001:'',
	msg_40002:'',
	msg_40003:'',
	msg_40004:'',
	msg_40005:'请勿重复投票',
	msg_40006:'请勿重复报名',
	msg_40007:'发送失败',
}
/*function showmsg(page){
	var div = '<div id="WeiXinShareTips" onclick="javascript:this.remove()" style="position: absolute;z-index: 1000;left: 10%; top: 30%;width: 80%;height: 30%; background-color: rgba(100, 100, 100, .8);position: fixed; right:0"><div id="email_tcss"></div><div id="email_tc_fcs"><img id="email_fsi1" src="__PUBLIC__/weixin/images/BG_02.png" alt=""><img id="email_fsi2" src="__PUBLIC__/weixin/images/IC.png" alt=""><p>欢迎您，<span>慕容小二</span> ！</p><div>正在为您跳转至登陆前页面...</div></div></div>';
	$(page).append(div);
	setTimeout(function(){$('#WeiXinShareTips').hide();},1000);
}*/
// comment.js
function loadmsg(info){
	/*var div = '<div id="WeiXinShareTips" onclick="javascript:this.remove()" style="position: fixed;z-index: 1000;left: 10%; top: 40%;width: 80%;height: 36px; background-color: rgba(100, 100, 100, .8); color:#fff; font-size:24px">';
	div+=info;
	div+='</div>';
	setTimeout(function(){$('#WeiXinShareTips').hide();},1000);
	$('#lcading').append(div);*/
	alert(info);
	//$("#email_tcss").css("display","");
	//$("#email_tc_fcs").css("display","");
}
function hideloader(){
	window.setTimeout(function(){$.mobile.loading('hide')},2000);	
}
function checkinput(Dom,info){
	if(Dom.val()==''){
		loadmsg(info);
		window.setTimeout(function(){Dom.focus();},2000);
		return false;
	}else{
		return true;	
	}	
}
// login.js
function audience_login(){
	if(!checkinput($('[name="audience_tel"]'),'请填写手机号'))return false;
	if(!checkinput($('[name="audience_pwd"]'),'请填写密码'))return false;
	var param={
		audience_tel:$('[name="audience_tel"]').val(),
		audience_pwd:$('[name="audience_pwd"]').val()
	}
	$.ajax({
		type:'post',
		url :base_audience_action+'login',
		data:param,
		dataType:"json",
		success: function(data){
			switch(data.state){
				case 0:alert('登陆成功');
				if($('[name="return_back"]').val()!=''){
					if($('[name="return_for"]').val()!='')
						window.location=base_index_action+$('[name="return_back"]').val()+'?for='+$('[name="return_for"]').val();
					else
						window.location=base_index_action+$('[name="return_back"]').val();
				}
				break;
				case 40002:alert('手机号不存在');break;	
				case 40003:alert('密码不正确');break;	
			}
		}
	});		
}
// reg.js
function audience_reg(){
	if(!checkinput($('[name="audience_tel"]'),'请填写手机号'))return false;
	//查询验证码是否正确
	if($('[name="verify"]').val()==''){
		alert('验证码不正确');
		return false;
	}
	param={
		audience_verify:$('[name="verify"]').val(),
		audience_tel:$('[name="audience_tel"]').val()
	}
	$.ajax({
		type:'post',
		url :base_audience_action+'check_verify',
		data:param,
		dataType:"json",
		success: function(data){
			if(data.state != 0){
				alert('验证码不正确');
				return false;
			}else{
				if(!checkinput($('[name="audience_name"]'),'请填写姓名'))return false;
				if(!checkinput($('[name="audience_email"]'),'请填写邮箱'))return false;
				if(!checkinput($('[name="audience_pwd"]'),'请填写密码'))return false;
				if(!checkinput($('[name="audience_company"]'),'请填写公司'))return false;
				if(!checkinput($('[name="audience_position"]'),'请填写职位'))return false;
				if($('[name="audience_pwd"]').val()!=$('[name="audience_checkPwd"]').val()){
					loadmsg('两次输入的密码不一致');
					return false;
				}
				var param={
					audience_tel:$('[name="audience_tel"]').val(),
					audience_name:$('[name="audience_name"]').val(),
					audience_pwd:$('[name="audience_pwd"]').val(),
					audience_email:$('[name="audience_email"]').val(),
					audience_company:$('[name="audience_company"]').val(),
					audience_position:$('[name="audience_position"]').val(),
				}
				$.ajax({
					type:'post',
					url :base_audience_action+'reg',
					data:param,
					dataType:"json",
					success: function(data){
						if(data.state==0){
							alert('注册成功!');
							if($('[name="return_back"]').val()!=''){
								if($('[name="return_for"]').val()!='')
									window.location=base_index_action+$('[name="return_back"]').val()+'?for='+$('[name="return_for"]').val();
								else
									window.location=base_index_action+$('[name="return_back"]').val();	
							}
						}else
							alert('注册失败!');	
					}
				});			
			}
		}
	});
}
// check_tel.js
function check_tel(){
	$('[name="audience_tel"]').attr("action-data",0);
	if($('[name="audience_tel"]').val().length < 11){
		$("#check_tel").text("当前手机号格式,不正确!");
		return;
	}
	param={
		audience_tel:$('[name="audience_tel"]').val()
	}
	$.ajax({
		type:'post',
		url :base_audience_action+'check_tel',
		data:param,
		dataType:"json",
		success: function(data){
			if(data.state==0){
				$("#check_tel").text("当前号码已被注册!请直接登陆!");
			}else{
				$("#check_tel").text("恭喜！此号码尚未注册!");
				$('[name="audience_tel"]').attr("action-data",1);
			}
		}
	});
}
// send_verify.js
function send_reg_verify(){
	if($('[name="audience_tel"]').attr("action-data") != 1 || $('[name="audience_tel"]').val().length < 9){
		alert('请填写正确的手机号码');
		return;
	}
	param={
		audience_tel:$('[name="audience_tel"]').val()
	}
	$.ajax({
		type:'post',
		url :base_audience_action+'send_verify',
		data:param,
		dataType:"json",
		success: function(data){
			if(data.state==0){
				$("#send_to").html('<input type="text" value="60" id="time_re" disabled style="width:15px;border:0">秒后重新发送');
				re_send();
			}else{
				alert('发送失败,请检查手机号码是否正确');
			}
		}
	});
}
// send_verify.js
function send_verify(){
	/*if($('[name="audience_tel"]').attr("action-data") != 1 || $('[name="audience_tel"]').val().length < 9){
		alert('请填写正确的手机号码');
		return;
	}*/
	param={
		audience_tel:$('[name="audience_tel"]').val()
	}
	$.ajax({
		type:'post',
		url :base_audience_action+'send_verify',
		data:param,
		dataType:"json",
		success: function(data){
			if(data.state==0){
				$("#send_to").html('<input type="text" value="60" id="time_re" disabled style="width:15px;border:0">秒后重新发送');
				re_send();
			}else{
				alert('发送失败,请检查手机号码是否正确');
			}
		}
	});
}
function re_send(){
	setTimeout(function(){
		if($("#time_re").val() != 0){
			$("#send_to").attr('disabled','ture');
			$("#time_re").val($("#time_re").val()-1);
			re_send();
		}else{
			$("#send_to").removeAttr('disabled');
			$("#send_to").html('重新发送');
		}
	},1000);
}

function check_verify(){
	if($('[name="verify"]').val()==''){
		return false;
	}
	param={
		audience_verify:$('[name="verify"]').val(),
		audience_tel:$('[name="audience_tel"]').val()
	}
	$.ajax({
		type:'post',
		url :base_audience_action+'check_verify',
		data:param,
		dataType:"json",
		success: function(data){
			if(data.state == 0){
				return true;
			}else{
				return false;
			}
		}
	});
}
// find_password.js
function find_pwd(){
	if(!checkinput($('[name="audience_tel"]'),'请填写手机号'))return false;
	if(!checkinput($('[name="verify"]'),'验证码不正确'))return false;
	//查询验证码是否正确
	param={
		audience_verify:$('[name="verify"]').val(),
		audience_tel:$('[name="audience_tel"]').val()
	}
	$.ajax({
		type:'post',
		url :base_audience_action+'check_verify',
		data:param,
		dataType:"json",
		success: function(data){
			if(data.state != 0){
				alert('验证码不正确');
				return false;
			}else{
				window.location=base_index_action+'password2';
			}
		}
	});
}
// upt_pwd.js
function upt_pwd(where){
	if(!checkinput($('[name="audience_pwd"]'),'请填写密码'))return false;
	if($('[name="audience_pwd"]').val()!=$('[name="audience_checkPwd"]').val()){
		loadmsg('两次输入的密码不一致');
		return false;
	}
	param={
		audience_pwd:$('[name="audience_pwd"]').val()
	}
	$.ajax({
		type:'post',
		url :base_audience_action+'upt_pwd',
		data:param,
		dataType:"json",
		success: function(data){
			if(data.state != 0){
				alert('修改失败');
				return false;
			}else{
				alert('修改成功');
				if(where==2)
					window.location=base_index_action+'individual_center';
				else
					window.location=base_index_action+'sign_in';
			}
		}
	});
}
// upt_data.js
function upt_data(data_param){
	switch(data_param){
		case 'audience_name':
			var param={audience_name:$('[name="audience_name"]').val()}
			if(!checkinput($('[name="audience_name"]'),'请填写姓名'))
			return false;
			break;
		case 'audience_email':
			var param={audience_email:$('[name="audience_email"]').val()}
			if(!checkinput($('[name="audience_email"]'),'请填写邮箱'))
			return false;
			break;
		case 'audience_company':
			var param={audience_company:$('[name="audience_company"]').val()}
			if(!checkinput($('[name="audience_company"]'),'请填写公司'))
			return false;
			break;
		case 'audience_position':
			var param={audience_position:$('[name="audience_position"]').val()}
			if(!checkinput($('[name="audience_position"]'),'请填写职位')){
				return false;
			}
			break;
		case 'audience_birthday':
			var param={audience_birthday:$('[name="audience_birthday"]').val()}
			if(!checkinput($('[name="audience_birthday"]'),'请填写生日')){
				return false;
			}
			break;
		case 'audience_hobby':
			var param={audience_hobby:$('[name="audience_hobby"]').val()}
			if(!checkinput($('[name="audience_hobby"]'),'请填写爱好')){
				return false;
			}
			break;
		default:
			alert('错误!');
			return;
			break;
	}
	$.ajax({
		type:'post',
		url :base_audience_action+'upt_data',
		data:param,
		dataType:"json",
		success: function(data){
			if(data.state != 0){
				alert('修改失败,无修改');
				return false;
			}else{
				alert('修改成功');
				window.location=base_index_action+'individual_center';
			}
		}
	});	
}
// upt_tel.js
function upt_tel(){
	if(!checkinput($('[name="audience_tel"]'),'请填写手机号'))return false;
	if(!checkinput($('[name="verify"]'),'验证码不正确'))return false;
	//查询验证码是否正确
	param={
		audience_verify:$('[name="verify"]').val(),
		audience_tel:$('[name="audience_tel"]').val()
	}
	$.ajax({
		type:'post',
		url :base_audience_action+'check_verify',
		data:param,
		dataType:"json",
		success: function(data){
			if(data.state != 0){
				alert('验证码不正确');
				return false;
			}else{
				$.ajax({
					type:'post',
					url :base_audience_action+'upt_tel',
					data:param,
					dataType:"json",
					success: function(data){
						if(data.state != 0){
							alert('修改失败');
						}else{
							alert('修改成功');
						}
						return false;
					}
				});
			}
		}
	});
}


// 投票 vote.js
function votes_submit(){
	if(!checkinput($('[name="audience_name"]'),'姓名不能为空'))return false;
	if(!checkinput($('[name="audience_tel"]'),'电话不能为空'))return false;
	if($('[name="audience_tel"]').val().length != 11){
		alert('请填写11位手机号码');return false;
	}
	var g = /^[0-9]{11}$/;
	var n = $('[name="audience_tel"]').val();
	if(!g.test(n)){alert('请填写11位手机号码');return false;}
	
	if(!checkinput($('[name="votes1"]'),'第一项不能为空'))return false;
	if(!checkinput($('[name="votes2"]'),'第二项不能为空'))return false;
	if(!checkinput($('[name="votes3"]'),'第三项不能为空'))return false;
	if($('[name="votes1"]').val()==$('[name="votes3"]').val()){alert('请填写三家不同的公司!');return false;}
	if($('[name="votes2"]').val()==$('[name="votes3"]').val()){alert('请填写三家不同的公司!');return false;}
	if($('[name="votes1"]').val()==$('[name="votes2"]').val()){alert('请填写三家不同的公司!');return false;}

	//查询验证码是否正确
	param={
		audience_name:$('[name="audience_name"]').val(),
		audience_tel:$('[name="audience_tel"]').val(),
		votes1:$('[name="votes1"]').val(),
		votes2:$('[name="votes2"]').val(),
		votes3:$('[name="votes3"]').val()
	}
	$.ajax({
		type:'post',
		data:param,
		dataType:"json",
		url :base_audience_action+'votes',
		beforeSend: function(){
			$("#Save_Game3").text('提交中,请稍后....');
			$("#for_submit_vote").removeAttr('onClick');
			$("#for_submit_vote_iniv").text('提交中,请稍后....');
			$("#for_submit_vote_iniv").removeAttr('onClick');
		},
		success: function(data){
			$("#for_submit_vote").attr('onClick','votes_submit()');
			switch(data.state){
				case 40005:
					$("#for_submit_vote_iniv").removeAttr('onClick');
					$("#for_submit_vote_iniv").text('提交');
					alert(msgInfo.msg_40005);break;
				case 40004:
					alert(msgInfo.msg_40004);
					window.location = base_index_action+'login';
					break;
				case 0	  :
					$("#for_submit_vote_iniv").removeAttr('onClick');
					$("#for_submit_vote_iniv").text('正在跳转,请稍后....');
					window.location = base_index_action+'vote_success?id='+data.id;
					break;
			}
			return false;
		}
	});
}

// sign_in.js
function sign_in(num){
	switch(num){
		case 1:
			if(!checkinput($('[name="company_long_name"]'),'公司全称不能为空'))return false;
			if(!checkinput($('[name="company_short_name"]'),'公司简称不能为空'))return false;
			if(!checkinput($('[name="founding_time"]'),'成立时间不能为空'))return false;
			if($('[name="founding_time"]').val().length != 4){
					alert('成立时间不正确');return false;
			}
			var g = /^[0-9]{4}$/;
			var n = $('[name="founding_time"]').val();
			if(!g.test(n)){alert('成立时间不正确');return false;}
			
			
			$("#apply1").hide();
			$("#apply2").show();
			save_sign_in_data();
			break;
		case 2:
			if(!checkinput($('[name="CEO"]'),'CEO不能为空'))return false;
			if(!checkinput($('[name="HR_head"]'),'HR负责人不能为空'))return false;
			if(!checkinput($('[name="website"]'),'网址不能为空'))return false;
			if(!checkinput($('[name="brand"]'),'品牌不能为空'))return false;
			if($("[name=enterprise_nature]").val()=='7'){
				if(!checkinput($('[name="enterprise_nature_other"]'),'企业性质不能为空'))return false;	
			}
			if($("[name=industry]").val()=='14'){
				if(!checkinput($('[name="industry_other"]'),'所属行业不能为空'))return false;	
			}
			$("#apply2").hide();
			$("#apply3").show();
			save_sign_in_data();
			break;
		case 3:
			if(!checkinput($('[name="contacts"]'),'联系人姓名'))return false;
			if(!checkinput($('[name="position"]'),'职位不能为空'))return false;
			if(!checkinput($('[name="phone"]'),'办公室电话'))return false;
			if(!checkinput($('[name="mobile_no"]'),'手机不能为空'))return false;
			if($('[name="mobile_no"]').val().length != 11){
				alert('请填写11位手机号码');return false;
			}
			var g = /^[0-9]{11}$/;
			var n = $('[name="mobile_no"]').val();
			if(!g.test(n)){alert('请填写11位手机号码');return false;}
			if(!checkinput($('[name="email"]'),'邮箱不能为空'))return false;
			var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			var mail = $('[name="email"]').val();
			if (!filter.test(mail)){
				alert('您的电子邮件格式不正确');	
				return false;
			}
			
			$("#apply3").hide();
			$("#apply4").show();
			save_sign_in_data();
			break;
		case 4:
			var param={
				company_long_name:$('[name="company_long_name"]').val(),
				company_short_name:$('[name="company_short_name"]').val(),
				founding_time:$('[name="founding_time"]').val(),
				chinese_headquarters_cmbProvince:$('[name="cmbProvince"]').val(),
				chinese_headquarters:$('[name="cmbCity"]').val()+$('[name="cmbArea"]').val(),	
				sign_position_sibProvince:$('[name="sibProvince"]').val(),			
				sign_position:$('[name="sibCity"]').val()+$('[name="sibArea"]').val(),
				total_assets:$('[name="total_assets"]').val(),
				headcount:$('[name="headcount"]').val(),
				recruitment_scale:$('[name="recruitment_scale"]').val(),
				CEO:$('[name="CEO"]').val(),
				HR_head:$('[name="HR_head"]').val(),
				website:$('[name="website"]').val(),
				brand:$('[name="brand"]').val(),
				enterprise_nature:$('[name="enterprise_nature"]').val(),
				enterprise_nature_other:$('[name="enterprise_nature_other"]').val(),
				industry:$('[name="industry"]').val(),
				industry_other:$('[name="industry_other"]').val(),
				enterprise_scale:$('[name="enterprise_scale"]').val(),
				contacts:$('[name="contacts"]').val(),
				position:$('[name="position"]').val(),
				phone:$('[name="phone"]').val(),
				email:$('[name="email"]').val(),
				mobile_no:$('[name="mobile_no"]').val(),
				sign_city:$('[name="sign_city"]').val(),
				winner_100:$('[name="winner_100"]:checked').val(),
				winner_30:$('[name="winner_30"]:checked').val(),
				winner_small:$('[name="winner_small"]:checked').val(),
				winner_society:$('[name="winner_society"]:checked').val(),
				winner_potential:$('[name="winner_potential"]:checked').val(),
				winner_innovation:$('[name="winner_innovation"]:checked').val()
			}
			$.ajax({
				type:'post',
				data:param,
				dataType:"html",
				url :base_audience_action+'sigh_in',
				beforeSend: function(){
					$("#Save_Gamebs").attr('value','提交中,请稍后....');
					$("#Save_Gamebs").attr('disabled','disabled');
				},
				success: function(data){
					$("#Save_Gamebs").removeAttr('disabled');
					$("#Save_Gamebs").attr('value','提 交');
					data=data.replace('Invalid address: ','');
					data=$.trim(data);
					if(data==0){
						window.location = base_index_action+'sigh_success';
					}else{
						//alert('邮件发送失败,可能原因当前邮件错误,请联系管理员修改您的联系人邮件!');
						window.location = base_index_action+'sigh_success';	
					}
					return false;
				}
			});
			break;
	}
}
function save_sign_in_data(){
	var param={
		company_long_name:$('[name="company_long_name"]').val(),
		company_short_name:$('[name="company_short_name"]').val(),
		founding_time:$('[name="founding_time"]').val(),
		chinese_headquarters_cmbProvince:$('[name="cmbProvince"]').val(),
		chinese_headquarters:$('[name="cmbCity"]').val()+$('[name="cmbArea"]').val(),
		sign_position_sibProvince:$('[name="sibProvince"]').val(),			
		sign_position:$('[name="sibCity"]').val()+$('[name="sibArea"]').val(),
		total_assets:$('[name="total_assets"]').val(),
		headcount:$('[name="headcount"]').val(),
		recruitment_scale:$('[name="recruitment_scale"]').val(),
		CEO:$('[name="CEO"]').val(),
		HR_head:$('[name="HR_head"]').val(),
		website:$('[name="website"]').val(),
		brand:$('[name="brand"]').val(),
		enterprise_nature:$('[name="enterprise_nature"]').val(),
		enterprise_nature_other:$('[name="enterprise_nature_other"]').val(),
		industry:$('[name="industry"]').val(),
		industry_other:$('[name="industry_other"]').val(),
		enterprise_scale:$('[name="enterprise_scale"]').val(),
		contacts:$('[name="contacts"]').val(),
		position:$('[name="position"]').val(),
		phone:$('[name="phone"]').val(),
		email:$('[name="email"]').val(),
		mobile_no:$('[name="mobile_no"]').val(),
		sign_city:$('[name="sign_city"]').val(),
		winner_100:$('[name="winner_100"]:checked').val(),
		winner_30:$('[name="winner_30"]:checked').val(),
		winner_small:$('[name="winner_small"]:checked').val(),
		winner_society:$('[name="winner_society"]:checked').val(),
		winner_potential:$('[name="winner_potential"]:checked').val(),
		winner_innovation:$('[name="winner_innovation"]:checked').val()
	}
	$.ajax({
		type:'post',
		data:param,
		dataType:"json",
		url :base_index_action+'save_sign_in_data'
	});	
}
// send_report.js
function send_report(){
	if(!checkinput($('[name="information_id"]'),'您还没有登录!'))
		window.location = base_index_action+'login?back=report_download';
	if(!checkinput($('[name="audience_email"]'),'请填写正确的邮箱!'))
		return false;
	var param={
		information_id:$('[name="information_id"]').val(),
		audience_email:$('[name="audience_email"]').val()
	};
	$.ajax({
		type:'post',
		data:param,
		dataType:"html",
		url :base_audience_action+'send_report_url',
		success: function(data){
			data=data.replace('Invalid address: ','');
			data=$.trim(data);
			if(data==0){
				window.location = base_index_action+'send_success?id='+param.information_id;
			}else{
				window.location = base_index_action+'send_success?id='+param.information_id;	
			}
			return false;
		}
	});
}