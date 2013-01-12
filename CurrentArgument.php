<?php
/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

class CurrentArgument {

    private $rId; 
    private $lastAId;
    private $argumentSubpoints;
    private $argumentIsAgree;
    
    function __construct($rId, $lastAId) { 
        require('db/config.php');
        
        $mysqli = new mysqli($host, $username, $password, $db);                    
        if ($stmt = $mysqli->prepare("SELECT r.responseText, c.isAgree FROM Responses r, (SELECT responseId, isAgree FROM Context WHERE responseId = ? AND parentId = ?) c WHERE c.responseId = r.responseId;")) {
            $stmt->bind_param('ii', $rId, $lastAId);
            $stmt->execute();
            $stmt->bind_result($subpoint, $this->argumentIsAgree);
            
            if($stmt->fetch()) {
                if(is_null($subpoint)) {
                    $stmt->close();
                    
                    if ($stmt = $mysqli->prepare("SELECT r.responseText FROM Responses r, ResponseSubpoints rs WHERE rs.responseId = ? AND rs.subpointId = r.responseId;")) {
                        $stmt->bind_param('i', $rId);
                        $stmt->execute();
                        $stmt->bind_result($subpoint);
                        
                        while($stmt->fetch()) {
                            $this->argumentSubpoints[] = str_replace('\\', "", $subpoint);
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