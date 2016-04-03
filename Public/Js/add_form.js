$("#submit").click(function(){

var len = $(".must").length;
for(var i = 0;i<len;i++){
	if($(".must:eq("+i+")").val()==""){
		$(".must:eq("+i+")").css({border:"2px solid red"});	
		return false;	
	};
	if($("#pwd").val()!=$("#pwds").val()){
		alert("密码两次输入不一致！");
		$("#pwd").css({border:"2px solid #F55112"});
		$("#pwds").css({border:"2px solid #F55112"});
		return false;
	}
}
	
});

$(".must").focus(function(){
	$(this).css({border:""});
});
