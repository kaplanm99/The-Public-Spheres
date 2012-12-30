<?php
/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

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
    
    private function arrayPHPToJS($arr, $curRID) {
        $jsArr = "";
        
        foreach ($arr as $val) {
            $jsArr = $jsArr . "&aIds[]=" . $val;
        }
        
        $jsArr = $jsArr . "&aIds[]=" . $curRID;
        
        return $jsArr;
    }
    
    private function ancestorStringNonZero($arr) {
        $jsArr = "";

        foreach ($arr as $val) {
            $jsArr = $jsArr . "&aIds[]=" . $val;
        }
        
        return $jsArr;
    }
    
    private function agreeDisagreeRatio($responseID) {
        require('db/config.php');
        $mysqli2 = new mysqli($host, $username, $password, $db);
        
        $tempIsAgree = 1;
        
        if ($stmt2 = $mysqli2->prepare("SELECT COUNT(c.responseId) FROM Context c WHERE c.parentId = ? AND c.isAgree = ?;")) {
            $stmt2->bind_param('ii', $responseID, $tempIsAgree);
            $stmt2->execute();
            $stmt2->bind_result($agrCount);
            
            $stmt2->fetch();
        
            $tempIsAgree = 0;
        
            $stmt2->bind_param('ii', $responseID, $tempIsAgree);
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
    
    public function generateResponses() {
        require('db/config.php');

        $mysqli = new mysqli($host, $username, $password, $db);
        
        if ($stmt = $mysqli->prepare("SELECT r.responseId, r.responseText, (c.yesVotes - c.noVotes) AS voteDifference, c.yesVotes, c.noVotes FROM Responses r, (SELECT responseId, score, yesVotes, noVotes FROM Context WHERE parentId = ? AND isAgree = ?) c WHERE c.responseId = r.responseId ORDER BY voteDifference DESC;")) {
            $stmt->bind_param('ii', $this->respID, $this->typeIsAgree);
            $stmt->execute();
            $stmt->bind_result($responseID, $responseText, $responseScore, $responseYesVotes, $responseNoVotes);
            
            while ($stmt->fetch()) {
            
                $responseVote = -1;
                
                if(isset($_SESSION['user'])) {
                    
                    require('db/config.php');

                    $mysqli2 = new mysqli($host, $username, $password, $db);
                    
                    if ($stmt2 = $mysqli2->prepare("SELECT vote FROM Votes WHERE responseId = ? AND parentId = ? AND user = ?;")) {
                        $stmt2->bind_param('iis', $responseID, $this->respID, $_SESSION['user']);
                        $stmt2->execute();
                        $stmt2->bind_result($responseVote);
                    
                        $stmt2->fetch();
                        
                        $stmt2->close();
                    }
                    
                    $mysqli2->close();
                }
                $this->responseArray[] = new Response($responseID, $responseText, $responseScore, $responseYesVotes, $responseNoVotes, $responseVote);
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
                $arrJS = "&aIds[]=0";
            } else {
                $arrJS = $this->arrayPHPToJS($this->aIds,$this->respID);
            }    
             
            print("<div id=\"".$response->getResponseID()."\" onclick=\"goToRID(this, event, ".$response->getResponseID()." ,'$arrJS');\" class=\"response\">");
            /*
            <div class=\"arrowIcons\">
            
            
                <form style=\"float:left;\" name=\"input\" action=\"index.php?rId=".$this->respID."".$this->ancestorStringNonZero($this->aIds)."\" method=\"post\">
                
                    <input type=\"hidden\" id=\"rPID\" name=\"rPID\" value=\"".$this->respID."\" />
                    <input type=\"hidden\" id=\"rID\" name=\"rID\" value=\"".$response->getResponseID()."\" />
                    <input type=\"hidden\" name=\"vote\" value=\"1\" />");
            
            if($response->getResponseVote() == 1){
                print("<input type=\"image\" src=\"upArrowDepressed.png\" class=\"upArrowDepressed arrow\">");
            }
            else {
                print("<input type=\"image\" src=\"upArrow.png\" class=\"upArrow arrow\">");
            }
            
            print("</form>
                " . $response->getResponseScore() . "
                <form style=\"float:left;\" name=\"input\" action=\"index.php?rId=".$this->respID."".$this->ancestorStringNonZero($this->aIds)."\" method=\"post\">
                    <input type=\"hidden\" id=\"rPID\" name=\"rPID\" value=\"".$this->respID."\" />
                    <input type=\"hidden\" id=\"rID\" name=\"rID\" value=\"".$response->getResponseID()."\" />
                    <input type=\"hidden\" name=\"vote\" value=\"-1\" />");
            
            if($response->getResponseVote() == 0){
                print("<input type=\"image\" src=\"downArrowDepressed.png\" class=\"downArrowDepressed arrow\">");
            }
            else {
                print("<input type=\"image\" src=\"downArrow.png\" class=\"downArrow arrow\">");
            }
            
            print("</form>
                
            </div>
            */
            print ("
            <p class=\"responseP\" onclick=\"goToRID(this, event, ".$response->getResponseID()." ,'$arrJS');\">".str_replace('\\', "", $response->getResponseText())."
            </p>
            <form name=\"input\" action=\"index.php?rId=".$this->respID."".$this->ancestorStringNonZero($this->aIds)."\" method=\"post\">
                <input type=\"hidden\" id=\"rPID\" name=\"rPID\" value=\"".$this->respID."\" />
                <input type=\"hidden\" id=\"rID\" name=\"rID\" value=\"".$response->getResponseID()."\" />
                <input type=\"hidden\" class=\"vote\" name=\"vote\" value=\"-1\" />
                <p class=\"constructive\">
                    Is this argument constructive?
                    <img src=\"yes.png\" class=\"constructiveButton yesButton\" onclick=\"submitVote(".$response->getResponseID().", 1);\" />(".$response->getResponseYesVotes().")
                    <img src=\"no.png\" class=\"constructiveButton noButton\" onclick=\"submitVote(".$response->getResponseID().", 0);\" />(".$response->getResponseNoVotes().")
                </p>
            </form>
            </div>");           
            
            if($this->typeIsAgree == 0 || $this->typeIsAgree == 1 || $this->typeIsAgree == 2) {
                $ratio = $this->agreeDisagreeRatio($response->getResponseID());
                
                print("<script type=\"text/javascript\">$(document).ready(function(){changeBGC(document.getElementById(\"".$response->getResponseID()."\"), $ratio, " . $this->typeIsAgree . ");});</script>");
            }
        }
        
        print("        
            </div>
        </div>");
    }

}
?>