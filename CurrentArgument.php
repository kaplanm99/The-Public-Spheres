<?php
/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

class CurrentArgument {

    private $argumentSubpoints;
    private $argumentSubpointIds;
    private $argumentIsAgree;
    private $rIdInner;
    private $rIdOuter;
    
    function __construct($rId, $lastAId) { 
        
        $lastAIdOuter = intval($lastAId);
        $this->rIdInner = intval($rId);
        $this->rIdOuter = -1;
        
        if(strstr($lastAId, 's') != false) {
            $lastAIdArr = explode( 's', $lastAId );
            $lastAIdOuter = intval($lastAIdArr[1]);
        }
        
        if(strstr($rId, 's') != false) {
            $rIdArr = explode( 's', $rId );
            $this->rIdInner = intval($rIdArr[0]);
            $this->rIdOuter = intval($rIdArr[1]); 
        }
        
        require('db/config.php');
        
        $mysqli = new mysqli($host, $username, $password, $db);                    
        if ($stmt = $mysqli->prepare("SELECT r.responseText, c.isAgree FROM Responses r, (SELECT responseId, isAgree FROM Context WHERE responseId = ? AND parentId = ?) c WHERE c.responseId = r.responseId;")) {
            $stmt->bind_param('ii', $this->rIdInner, $lastAIdOuter);
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
                            $this->argumentSubpoints[] = str_replace('\\', "", $subpoint);
                            
                            $this->argumentSubpointIds[] = $subpointId;
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
    
    public function getArgumentSubpointId($index) {           
        return $this->argumentSubpointIds[$index];            
    }
    
    public function getArgumentIsAgree() {           
        return intval($this->argumentIsAgree);            
    }
    
    public function getRIdInner() {           
        return intval($this->rIdInner);            
    }
    
    public function getRIdOuter() {           
        return intval($this->rIdOuter);            
    }
}
?>