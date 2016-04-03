/**
 * Created by Administrator on 2015/2/28.
 */
var herf=document.location.href;
switch(true){
    case herf.indexOf("Audience")>0:
        $("#nav1").addClass("current");
        $("#nav1_1").addClass("current");
        break;
    case herf.indexOf('Meeting/meeting_category_list')>0:
        $('#nav1').addClass('current');
        $('#nav1_2').addClass('current');
        break;
    case herf.indexOf("Department")>0:
        $("#nav1").addClass("current");
        $("#nav1_3").addClass("current");
        break;
    case herf.indexOf("Notice")>0:
        $("#nav2").addClass("current");
        break;
    case herf.indexOf("Project")>0:
        $("#nav3").addClass("current");
        break;
    case herf.indexOf("Leave")>0:
        // $("#nav5").addClass("current");
        // if(herf.indexOf("index"))
        //     $("#nav5_1").addClass("current");
        // else 
            if(herf.indexOf("leave_type")>0)
            $("#nav5_2").addClass("current");
        break;
    case herf.indexOf("Meeting/meeting_list")>0:
        $("#nav4").addClass("current");
        $("#nav4_1").addClass("current");
        break;
    
    case herf.indexOf("Meeting/group_list")>0:
        $("#nav4").addClass("current");
        $("#nav4_2").addClass("current");
        break;
    case herf.indexOf("Meeting/index")>0:
        $("#nav4").addClass("current");
        $("#nav4_3").addClass("current");
        break;
 
    case herf.indexOf('News')>0:
        $('#nav6').addClass('current');
        if(herf.indexOf('News/index')>0)
            $('#nav6_1').addClass('current');
        else if(herf.indexOf('News/add_news_channel')>0)
            $('#nav6_2').addClass('current');
        break;
    case herf.indexOf("Questionnaire")>0:
        $("#nav7").addClass("current");
        break;
    case herf.indexOf('Information')>0:
        $("#nav8").addClass('current');
        if(herf.indexOf("Information/index")>0)
            $("#nav8_1").addClass("current");
        else if(herf.indexOf("Information/channel_index")>0)
            $("#nav8_2").addClass("current");
        break;
    case herf.indexOf("Company/Admin")>0:
        $("#nav9").addClass("current");
        break;
    case herf.indexOf('Supplydemand')>0:
        $("#nav10").addClass('current');
        if(herf.indexOf("Supplydemand/index")>0)
            $("#nav10_1").addClass("current");
        else if(herf.indexOf("Supplydemand/channel")>0)
            $("#nav10_2").addClass("current");
        break;
    case herf.indexOf("Address")>0:
        $("#nav11").addClass("current");
        break;
    case herf.indexOf("Admin/App")>0:
        $("#nav12").addClass("current");
        break;

}
