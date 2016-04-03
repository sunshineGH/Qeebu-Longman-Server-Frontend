 /***********************************************
*奇步互动Teamin项目                                                          *
*本文件开发者，曹伟。                                                         *
************************************************/
/*复选框全选*/
    function checkall(){        
        var parameter  = checkall.arguments;
        var check = parameter[0];   //复选框对象
        var all_check = parameter[1];//选框状态。        
        $.each(check,function(i,obj){                   
            obj.checked=all_check;
        });     
    }
/*复选框反选*/
    function checkfan(){    
        var parameter = checkfan.arguments;
        var check = parameter[0];                   
        $.each(check,function(i,obj){                   
            if(obj.checked==true){
                obj.checked = false;
            }else{
                obj.checked = true;
            };
        });     
    }
/*全选*/
  $("input[name='checkAll'],a[name='checkall']").click(function(){
    document.getElementById("fan_name").checked=false;
    var check = $("input[name='check[]'],input[name='checkAll']");
    var all_check = document.getElementById("all_name").checked;
    if($(this).attr("name")=="checkall"){        
        switch(all_check){
            case true:
                all_check=false;
            break;
            case false:
                all_check=true;
            break;
        }
    }
    checkall(check,all_check);    
  });
/*反选*/
  $("input[name='checkFan'],a[name='checkother']").click(function(){
    var check = $("input[name='check[]']");
    document.getElementById("all_name").checked=false;
    var checkFan = document.getElementById("fan_name").checked;
    if($(this).attr("name")=="checkother"){
        switch(checkFan){
            case true:
                document.getElementById("fan_name").checked=false;
            break;
            case false:
                document.getElementById("fan_name").checked=true;
            break;
        }
    }
    checkfan(check);
  });

/*添加提交验证*/

/*验证信息*/
function inputneed(){
    var parameter = inputneed.arguments;
    var ele1 = parameter[0];
    var ele2 = parameter[1];
    var ele3 = parameter[2];
    var ele4 = parameter[3];
    var ele5 = parameter[4];    
    $.each(ele1,function(index,obj){
        if(obj.value == ''){
            obj.style.border="2px solid red";
            obj.style.background="#F7C6BC";
            return false;            
        }else if(index==ele4 && ele2.test(obj.value)==false){              
            obj.style.border="2px solid red";
            obj.style.background="#F7C6BC";
            return false;
        }else if((ele5 && index==ele5) && ele3.test(obj.value)==false){            
            obj.style.border="2px solid red";
            obj.style.background="#F7C6BC";
            return false;
       }else{          
           obj.style.border="";
           obj.style.background="";           
       }
    })
}

/*删除信息*/
/*删除信息*/
function deleteData(){    
    var parameter  = deleteData.arguments;
    var id = parameter[0];
    var url = parameter[1];
    var self = parameter[2];
    if(confirm('确定删除该信息吗?')){    
            $.ajax({
            type:'post',
            url:url,     
            data:{"id":id},
            dataType:"json",
            success: function(data){
                    alert(data.msg);
                    if(data.code==1){
                        window.location=self;   
                    }
            }
        }); 
    }
}

function toUpt(){
    var parameter  = toUpt.arguments;
    var id = parameter[0];  
    var url = parameter[1]; 
    var self = parameter[2];
    var width = parameter[3];
    var height = parameter[4];
    $.jBox.open("iframe:"+url+"?id="+id, "请假修改",width, height, { buttons: {},closed: function(){ window.location=self}});
}

//多选删除
function checkdelete(){
    var parameter = checkdelete.arguments;
    var ob = parameter[0];
    var url = parameter[1];
    var self = parameter[2];
    var arr = new Array(); 
    if(confirm("删除有风险，请慎用！")){
        var len = ob.length;               
        if(len==0){
            alert("请选择要删除的信息！");
        }else{            
            $.each(ob,function(index,obj){
                arr.push(obj.value);               
            });
            $.ajax({
                url:url,
                data:{"id":arr},
                type:"post",
                dataType:"json",
                success:function(data){
                    alert(data.msg);
                    if(data.code==1){
                        window.location=self;   
                    }
                }
            });
        }
    }  
}
//关闭窗口
function closediv(){  
  $.each(closediv.arguments,function(index,obj){
     $("."+obj).css("display","none");  
  })
}
//弹出窗口
function updiv(){  
  var parameter = updiv.arguments;
  $.each(parameter,function(index,obj){    
     $("."+obj).css("display",""); 
  }); 
}

 