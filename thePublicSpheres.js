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

$(document).ready(function(){
  $("#agreeButton").click(function(event){
	 $("#rIsAgree").attr("value",1);
	 $("#responseForm").submit();
   });
   
   $("#disagreeButton").click(function(event){
	 $("#rIsAgree").attr("value",0);
	 $("#responseForm").submit();
   });

	$("#discussionButton").click(function(event){
	 $("#rIsAgree").attr("value",2);
	 $("#responseForm").submit();
   });
   
   $("#categoryButton").click(function(event){
	 $("#rIsAgree").attr("value",3);
	 $("#responseForm").submit();
   });
 });