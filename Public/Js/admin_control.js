//config
var herf=document.location.href;
var __APP__='/teamin/teamin_admin.php';
var __SELF__=herf;

//nav
switch(true){
	case herf.indexOf("Admin/Company")>0:
		$("#nav1").addClass("current");
		break;
	case herf.indexOf("Admin/Controller")>0:
		$("#nav2").addClass("current");
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