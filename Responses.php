<?php

require('Response.php');

class Responses {

    private $type;
    private $typeIsAgree;
    private $respID;
    private $aIds;
    private $responseArray;
    
    function __construct($type, $typeIsAgree, $respID, $aIds) { 
        $this->type = $type;
        $this->typeIsAgree = $typeIsAgree;
        $this->respID = $respID;
        $this->aIds = $aIds;
        $this->responseArray = array();
    }
    
    function arrayPHPToJS($arr, $curRID) {
        $jsArr = "";
        
        foreach ($arr as $val) {
            $jsArr = $jsArr . "&aIds[]=" . $val;
        }
        
        $jsArr = $jsArr . "&aIds[]=" . $curRID;
        
        return $jsArr;
    }
    
    function agreeDisagreeRatio($responseID) {
        require('db/config.php');
        $mysqli2 = new mysqli($host, $username, $password, $db);
            
        if ($stmt2 = $mysqli2->prepare("SELECT COUNT(c.responseId) FROM Context c WHERE c.parentId = ? AND c.isAgree = 1;")) {
            $stmt2->bind_param('i', $responseID);
            $stmt2->execute();
            $stmt2->bind_result($agrCount);
            
            $stmt2->fetch();
        }

        $stmt2->close();
        
        if ($stmt2 = $mysqli2->prepare("SELECT COUNT(c.responseId) FROM Context c WHERE c.parentId = ? AND c.isAgree = 0;")) {
            $stmt2->bind_param('i', $responseID);
            $stmt2->execute();
            $stmt2->bind_result($disagrCount);
            
            $stmt2->fetch();
        }

        $stmt2->close();
        
        if($disagrCount == 0) {
            if($agrCount == 0) {
                $ratio = 1;
            } else {
                $ratio = 100;
            }
        }
        else {
            $ratio = $agrCount/$disagrCount;
        }
        
        $mysqli2->close();
        
        return $ratio;
    }
    
    function generateResponses() {
        require('db/config.php');

        $mysqli = new mysqli($host, $username, $password, $db);
        
        if ($stmt = $mysqli->prepare("SELECT r.responseId, r.responseText FROM Responses r, (SELECT responseId FROM Context WHERE parentId = ? AND isAgree = ?) c WHERE c.responseId = r.responseId;")) {
            $stmt->bind_param('ii', $this->respID, $this->typeIsAgree);
            $stmt->execute();
            $stmt->bind_result($responseID, $responseText);
            
            while ($stmt->fetch()) {
                $this->responseArray[] = new Response($responseID, $responseText);
            }
            
            $stmt->close();
        }
        
        $mysqli->close();
    }
    
    public function outputResponses() {
        
        print("    
        <div class=\"".$this->type."CircleColumn circleColumnSize\">
            <h3 class=\"".$this->type."Title titleSize\">".$this->type."</h3>
            <div class=\"responses responsesSize\">");
        
        foreach ($this->responseArray as $response) {
            
            if($this->respID == 0) {
                print("<div id=\"".$response->getResponseID()."\" onclick=\"goToRID(this, event, ".$response->getResponseID()." ,'&aIds[]=0');\"><p class=\"responseP\" onclick=\"goToRID(this, event, ".$response->getResponseID()." ,'&aIds[]=0');\">".$response->getResponseText()."</p><p onclick=\"showTop(".$response->getResponseID().");return false;\" class=\"forkIcon\"><img src=\"fork.png\"><br/>Fork</p><p style=\"clear: both;\"></p></div>");
            } else {
                $arrJS = arrayPHPToJS($this->aIds,$this->respID);
                
                print("<div id=\"".$response->getResponseID()."\" onclick=\"goToRID(this, event, ".$response->getResponseID()." ,'$arrJS');\"><p class=\"responseP\" onclick=\"goToRID(this, event, ".$response->getResponseID()." ,'$arrJS');\">".$response->getResponseText()."</p><p onclick=\"showTop(".$response->getResponseID().");return false;\" class=\"forkIcon\"><img src=\"fork.png\"><br/>Fork</p><p style=\"clear: both;\"></p></div>");
            }
            
            if($this->typeIsAgree == 0 || $this->typeIsAgree == 1 || $this->typeIsAgree == 2) {
                $ratio = agreeDisagreeRatio($response->getResponseID());
                
                print("<script type=\"text/javascript\">$(document).ready(function(){changeBGC(document.getElementById(\"".$response->getResponseID()."\"), $ratio, " . $this->typeIsAgree . ");});</script>");
            }
        }
        
        print("        
            </div>
        </div>");
    }

}
?>