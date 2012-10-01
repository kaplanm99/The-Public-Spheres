<?php
/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

class CurrentArgument {

    private $rId; 
    private $lastAId;
    private $argumentText;
    private $argumentIsAgree;
    
    function __construct($rId, $lastAId) { 
        require('db/config.php');
        
        $mysqli = new mysqli($host, $username, $password, $db);                    
        if ($stmt = $mysqli->prepare("SELECT r.responseText, c.isAgree FROM Responses r, (SELECT responseId, isAgree FROM Context WHERE responseId = ? AND parentId = ?) c WHERE c.responseId = r.responseId;")) {
            $stmt->bind_param('ii', $rId, $lastAId);
            $stmt->execute();
            $stmt->bind_result($this->argumentText, $this->argumentIsAgree);
            $stmt->fetch();
            $stmt->close();
        }
        
        $mysqli->close();
    }
    
    public function getArgumentText() {           
        return $this->argumentText;            
    }
    
    public function getArgumentIsAgree() {           
        return $this->argumentIsAgree;            
    }
    
}
?>