// JavaScript Document
function doload(){
	if(!checkinput($('[name="tel"]'),'请填写手机号'))
		return false;
	if(!checkinput($('[name="pwd"]'),'请填写密码'))
		return false;	
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
function to_reg(){
	$.mobile.changePage($('#reg'),{transition:"flip"});	
}
function to_load(){
	$.mobile.changePage($('#load'),{transition:"flip",reverse:true});
}
function viewProfile() {
    typeof WeixinJSBridge != "undefined" && WeixinJSBridge.invoke && WeixinJSBridge.invoke("profile", {
        username: "gh_dcd1bf2f8f8f",
        scene: "57"
    });
}
/*function WeiXinAddContact(wxid) {    
 if (typeof WeixinJSBridge == 'undefined') return false;        
 WeixinJSBridge.invoke('addContact', {            

 	webtype: '1',            
 	username: wxid //微信原始ID       
 },
 function(d) {
	 alert(d.err_msg);
	 alert(d.err_desc);             
 	// 返回d.err_msg取值，d还有一个属性是err_desc             
	// add_contact:cancel 用户取消             
	// add_contact:fail　关注失败             
	// add_contact:ok 关注成功             
	// add_contact:added 已经关注            
	// WeixinJSBridge.log(d.err_msg);                    
 });

}*/