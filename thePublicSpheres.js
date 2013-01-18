/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

var subpointCount = 1;
 
function goToRID(el, event, rID, aIDs) {
	var target = event.srcElement || event.target;

	if( el === target ) {
		if(rID == "0") {
			window.location.href=("index.php");
		} else {
			window.location.href=("index.php?rId="+rID+aIDs);
		}
	}        
}

function changeBGC(el, ratio, typeIsAgree) {
	var hue; 
	var maxSat; 
	var value; 
	var sat;
	
	switch(typeIsAgree) {
		case 1:
		hue = 0.2826086956521739;
		maxSat = 0.4;
		value = 0.8745098039215686;
		break;
		case 0:
		hue = 0;
		maxSat = 0.5;
		value = 0.9176470588235294;
		break;
		case 2:
		hue = 0.09465020576131689;
		maxSat = 0.5;
		value = 0.9176470588235294;
		break;
	}
	
	 if(ratio < 1) {
		sat = (ratio*0.15) +(maxSat-0.3);
	} else {
		sat = maxSat - ((1/ratio)*0.15);
	}
	
	var rgbArray = hsvToRgb(hue, sat, value);
	
	el.style.backgroundColor = "rgb(" + Math.round(rgbArray[0]) + "," + Math.round(rgbArray[1]) + "," + Math.round(rgbArray[2]) + ")";
}

function closeTop()
{
	document.getElementById('greyOverlay').style.display='none';
	document.getElementById('loginRegisterBox').style.display='none';	document.getElementById('SearchPreviousResponsesBox').style.display='none';
}

function showLoginRegister()
{
	var boxWidth = 500;
	var boxHeight = 465;
	
	var screenWidth=document.all?document.body.clientWidth:window.innerWidth;
	var screenHeight=document.all?document.body.clientHeight:window.innerHeight;

	var xPos = (screenWidth - boxWidth) * 0.5;
	var yPos = (screenHeight - boxHeight) * 0.5;

	document.getElementById('loginRegisterBox').style.left=xPos+'px';
	document.getElementById('loginRegisterBox').style.top=yPos+'px';

	document.getElementById('greyOverlay').style.display='block';
	document.getElementById('loginRegisterBox').style.display='block';
}

function showSearchPreviousResponsesBox()
{
	document.getElementById('SearchPreviousResponsesBox').style.display='block';
}

function attachArrowMouseEvents(selector) {
	$("."+selector).mouseover(function(){
	 $(this).attr("src", selector+"Shadow.png");
   });

	$("."+selector).mouseout(function(){
	 $(this).attr("src", selector+".png");
   });
   
   $("."+selector+"Depressed").mouseover(function(){
	 $(this).attr("src", selector+"ShadowDepressed.png");
   });

	$("."+selector+"Depressed").mouseout(function(){
	 $(this).attr("src", selector+"Depressed"+".png");
   });
}

function submitVote(selector, theVote) {
    if(theVote == 0 || theVote == 1) {
        $("#"+selector+">form>.vote").attr("value",theVote);
        $("#"+selector+">form").submit();
    }
}

function searchPreviousResponses () {
    $.get("test.php", { query: $(this).val() },
        function(data) {
            $("#searchResponses").html(data);
            /*
            $(".searchResponse").click(function(event){
 
             $(".searchResponse").each(function (index, domEle) {
                $(domEle).css("borderWidth","1px");
                $(domEle).css("borderColor","#000000");
             });
             
             
             $(this).css("borderWidth","5px");
             $(this).css("borderColor","#708090");
             var searchPreviousResponseRID = $(this).attr("id");
             
             $("#searchPreviousResponseRID").attr("value",searchPreviousResponseRID);
            });
            */
    });
    
    document.getElementById('SearchPreviousResponsesBox').style.display='block';
}

$(document).ready(function(){
   $("#SubmitResponseOpposeButton").click(function(event){
	 $("#rIsAgree").attr("value",0);
	 $("#responseForm").submit();
   });

   $("#SubmitResponseSupportButton").click(function(event){
	 $("#rIsAgree").attr("value",1);
	 $("#responseForm").submit();
   });
   
	$("#SubmitResponseDiscussionButton").click(function(event){
	 $("#rIsAgree").attr("value",2);
	 $("#responseForm").submit();
   });
   
   $("#SubmitResponseCategoryButton").click(function(event){
	 $("#rIsAgree").attr("value",3);
	 $("#responseForm").submit();
   });
   
   $("#SubmitResponseNeutralButton").click(function(event){
	 $("#rIsAgree").attr("value",4);
	 $("#responseForm").submit();
   });
   
   //
   /*
   $("#SearchPreviousResponsesOpposeButton").click(function(event){
	 $("#searchPreviousResponseIsAgree").attr("value",0);
	 $("#searchPreviousResponseForm").submit();
   });

   $("#SearchPreviousResponsesSupportButton").click(function(event){
	 $("#searchPreviousResponseIsAgree").attr("value",1);
	 $("#searchPreviousResponseForm").submit();
   });
   
	$("#SearchPreviousResponsesDiscussionButton").click(function(event){
	 $("#searchPreviousResponseIsAgree").attr("value",2);
	 $("#searchPreviousResponseForm").submit();
   });
   
   $("#SearchPreviousResponsesCategoryButton").click(function(event){
	 $("#searchPreviousResponseIsAgree").attr("value",3);
	 $("#searchPreviousResponseForm").submit();
   });
   
   $("#SearchPreviousResponsesNeutralButton").click(function(event){
	 $("#searchPreviousResponseIsAgree").attr("value",4);
	 $("#searchPreviousResponseForm").submit();
   });
   */
   $("#logoutLink").click(function(event){
	 $("#logoutForm").submit();
   });
   
   $("#AddAnotherSubpointButton").click(function(event){
     if(subpointCount < 6) {
         subpointCount++;
         $("#textAreas").append('<textarea name="rText[]" class="textbox textboxSize"></textarea>');
         var tempWidth = ($("#innerCircle>.circleresponsessize").innerWidth()-(subpointCount*25))/subpointCount;
         
         if(tempWidth > 480) {
            tempWidth = 480;
         }
         
         $("#textAreas").width( (tempWidth+25)*subpointCount );
         
         $(".textboxsize").width(tempWidth);
     }
   });
   
   $("#greyOverlay").click(closeTop);
   $(".closeButton").click(closeTop);
   
   attachArrowMouseEvents("yesButton");
   attachArrowMouseEvents("noButton");
  
  /*  
  $(".forkIcon").mouseover(function(){
	 $(this).children('img').attr("src", "forkHighlighted.png");
   });
   
   $(".forkIcon").mouseout(function(){
	 $(this).children('img').attr("src", "fork.png");
   });
  */ 
   
   $("#loginRegisterLink").click(showLoginRegister);
   
   var circleHeight = 760;
   var statementHeights = new Array();
   
   $(".statement").each(function (index, domEle) {
        statementHeights[index] = $(domEle).height() + 9;
	});
   
   $(".circle").each(function (index, domEle) {
        if(index != 0) {
			circleHeight -= statementHeights[index-1]; 
			$(domEle).height(circleHeight);
		} else {
			$(domEle).height(circleHeight);
		}
	});
	
	$(".circleResponses").each(function (index, domEle) {
        $(domEle).height($(domEle).parent().height() - ($(domEle).parent().children(".statement").height()+9));
	});

	/*
    $(".responseP").each(function (index, domEle) {
        $(domEle).width($(domEle).parent().width()-67);
	});
    */
	
	$(".dividingLineSize").each(function (index, domEle) {
        $(domEle).height($(domEle).parent().height() - $("#responseForm").height());
	});
	
	$(".circleColumnSize").each(function (index, domEle) {
        $(domEle).height($(domEle).parent().height() - $("#responseForm").height() - $(".titleSize").outerHeight(true) - 1);
	});
	
	//$("#SearchPreviousResponsesButton").click(showSearchPreviousResponsesBox);
	
	//$(".textbox").keyup(searchPreviousResponses);	
	
    $("#textAreas").on("keyup", ".textbox", searchPreviousResponses);
    
    $(".selectEntireArgument").height($(".selectEntireArgument").parent().children(".statement").height());
    
	$("#mainCircleSize").css("visibility", "visible");
 });