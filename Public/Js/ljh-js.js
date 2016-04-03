 /***********************************************
*奇步互动Teamin项目                                                          *
*本文件开发者，李记辉                                                        *
************************************************/

// 删除
function del(id){
    $.jBox.confirm("确定删除吗？" , "提示" , function(v,h,f){
        if(v === 'ok'){
            $.ajax({
                url:"__URL__/delAct",
                data:{"project_id":id},
                type:"get",
                success:function(data){
                    parent.location.reload(); 
                }
            });
        }
        return true;
    });
}

// 修改
function upt(id){
    var url = "__URL__/to_update?project_id="+id;
    $.jBox.open("iframe:"+url, "修改", 900, 500, { buttons: {},closed: function(){ 
        
    }});
}