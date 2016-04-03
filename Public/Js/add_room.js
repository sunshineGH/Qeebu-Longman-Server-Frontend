$("#submit").click(function(){
var num = /[1-9]\d*/;
var seat = $("#seats").val();
if($(".must:eq(0)").val()==""){
	$(".must:eq(0)").css({border:"2px solid red"});	
	return false;	
};
if(seat=="" || !num.test(seat)){	
	$("#seats").css({border:"2px solid #F55112"});
	return false;
}	
});
$(".must").focus(function(){
	$(this).css({border:""});
});
