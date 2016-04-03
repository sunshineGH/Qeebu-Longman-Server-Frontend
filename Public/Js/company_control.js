//config
var herf=document.location.href;
var __APP__='/teamin/admin.php';
var __SELF__=herf;

//nav
switch(true){
	case herf.indexOf("Audience")>0:
		$("#nav1").addClass("current");
		break;
	case herf.indexOf("Project")>0:
		$("#nav2").addClass("current");
		break;
	case herf.indexOf("Event")>0:
		$("#nav3").addClass("current");
		break;
}
//Database
function create_sqlite(){
	$.get(__APP__+'/Admin/Database/create_sqlite',function(data){if(data.state==0)alert('生成成功')},'json');	
}
//company
function open_company_admin(cid){
	$.jBox.open("iframe:"+__APP__+'/Admin/Company/admin?cid='+cid, "修改", 900, 500, { buttons: { '关闭': true},closed: function () { window.location=__SELF__ }});	
}
//Audience
function open_audience(type,name){
	var iWidth=320; //窗口宽度
	var iHeight=480;//窗口高度
	var iTop=(window.screen.height-iHeight)/2;
	var iLeft=(window.screen.width-iWidth)/2;
		window.open(__APP__+"/Audience/choose_audience?type="+type+"&action="+name,"course_name","scrollbars=yes,Toolbar=no,Location=no,Direction=no,Width="+iWidth+" ,Height="+iHeight+",top="+iTop+",left="+iLeft);
}