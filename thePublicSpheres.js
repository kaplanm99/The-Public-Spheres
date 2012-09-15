/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

function goToRID(el, event, rID, aIDs) {
	var target = event.srcElement || event.target;

	if( el === target ) {
		if(rID == 0) {
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

function showTop(responseID)
{
	document.getElementById('centerBoxID').innerHTML = "id_" + responseID;
	
	var boxWidth = 500;
	var boxHeight = 240;
	
	var screenWidth=document.all?document.body.clientWidth:window.innerWidth;
	var screenHeight=document.all?document.body.clientHeight:window.innerHeight;

	var xPos = (screenWidth - boxWidth) * 0.5;
	var yPos = (screenHeight - boxHeight) * 0.5;

	document.getElementById('centerBox').style.left=xPos+'px';
	document.getElementById('centerBox').style.top=yPos+'px';

	document.getElementById('greyOverlay').style.display='block';
	document.getElementById('centerBox').style.display='block';
}


function closeTop()
{
	document.getElementById('greyOverlay').style.display='none';
	document.getElementById('centerBox').style.display='none';
	document.getElementById('loginRegisterBox').style.display='none';
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

$(document).ready(function(){
  $("#AgreeButton").click(function(event){
	 $("#rIsAgree").attr("value",1);
	 $("#responseForm").submit();
   });
   
   $("#logoutLink").click(function(event){
	 $("#logoutForm").submit();
   });
   
   $("#DisagreeButton").click(function(event){
	 $("#rIsAgree").attr("value",0);
	 $("#responseForm").submit();
   });

	$("#DiscussionButton").click(function(event){
	 $("#rIsAgree").attr("value",2);
	 $("#responseForm").submit();
   });
   
   $("#CategoryButton").click(function(event){
	 $("#rIsAgree").attr("value",3);
	 $("#responseForm").submit();
   });
   
   $("#greyOverlay").click(closeTop);
   $(".closeButton").click(closeTop);
   
   $(".forkIcon").mouseover(function(){
	 $(this).children('img').attr("src", "forkHighlighted.png");
   });
   
   $(".forkIcon").mouseout(function(){
	 $(this).children('img').attr("src", "fork.png");
   });
   
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

	$(".responseP").each(function (index, domEle) {
        $(domEle).width($(domEle).parent().width()-52);
	});
	
	$(".dividingLineSize").each(function (index, domEle) {
        $(domEle).height($(domEle).parent().height() - $("#responseForm").height());
	});
	
	$(".circleColumnSize").each(function (index, domEle) {
        $(domEle).height($(domEle).parent().height() - $("#responseForm").height());
	});
	
	
	$("#mainCircleSize").css("visibility", "visible");
 });