<?php
/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

class CurrentArgument {

    private $argumentSubpoints;
    private $argumentIsAgree;
    
    function __construct($rId, $lastAId) { 
        
        $lastAIdOuter = intval($lastAId);
        $rIdInner = intval($rId);
        $rIdOuter = -1;
        
        if(strstr($lastAId, 's') != false) {
            $lastAIdArr = explode( 's', $lastAId );
            $lastAIdOuter = intval($lastAIdArr[1]);
        }
        
        if(strstr($rId, 's') != false) {
            $rIdArr = explode( 's', $rId );
            $rIdInner = intval($rIdArr[0]);
            $rIdOuter = intval($rIdArr[1]); 
        }
        
        require('db/config.php');
        
        $mysqli = new mysqli($host, $username, $password, $db);                    
        if ($stmt = $mysqli->prepare("SELECT r.responseText, c.isAgree FROM Responses r, (SELECT responseId, isAgree FROM Context WHERE responseId = ? AND parentId = ?) c WHERE c.responseId = r.responseId;")) {
            $stmt->bind_param('ii', $rIdInner, $lastAIdOuter);
            $stmt->execute();
            $stmt->bind_result($subpoint, $this->argumentIsAgree);
            
            if($stmt->fetch()) {
                if(is_null($subpoint)) {
                    $stmt->close();
                    
                    if ($stmt = $mysqli->prepare("SELECT r.responseId, r.responseText FROM Responses r, ResponseSubpoints rs WHERE rs.responseId = ? AND rs.subpointId = r.responseId;")) {
                        $stmt->bind_param('i', $rId);
                        $stmt->execute();
                        $stmt->bind_result($subpointId, $subpoint);
                        
                        while($stmt->fetch()) {
                            if($rIdOuter == -1 || $rIdOuter==$subpointId) {
                                $this->argumentSubpoints[] = str_replace('\\', "", $subpoint);
                            }
                            else {
                                $this->argumentSubpoints[] = "<span style=\"color:#ccc;\">".str_replace('\\', "", $subpoint)."</span>";
                            }
                        }
                    }
                } else {
                    $this->argumentSubpoints[] = str_replace('\\', "", $subpoint);
                }
            }
            
            $stmt->close();
        }
        
        $mysqli->close();
    }
    
    public function getArgumentSubpoints() {           
        return $this->argumentSubpoints;            
    }
    
    public function getArgumentIsAgree() {           
        return intval($this->argumentIsAgree);            
    }
}
?>