// JavaScript Document
function loadmsg(info)
{
	$.mobile.loading('show', {  
		text: info, //加载器中显示的文字  
		textVisible: true, //是否显示文字  
		theme: 'b',        //加载器主题样式a-e  
		textonly: true,   //是否只显示文字  
		html: ""           //要显示的html内容，如图片等  
	});
	hideloader();	
}

function hideloader()
{
	window.setTimeout(function(){$.mobile.loading('hide')},2000);	
}