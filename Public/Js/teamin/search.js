//url---路径
//name---参数
//div---盒子class
function search_name(url,name,div){
alert(url);	
alert(name);
alert(div);
	/*$.ajax({
		url:url,
		data:{"search":name},
		type:"post",
		success:function(data){
			$("."+div).html(data);
		}
	});*/
});