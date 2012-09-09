<?php
class Response {

    private $responseID;
    private $responseText;
    
    function __construct($responseID, $responseText) {           
        $this->responseID = $responseID;
        $this->responseText = $responseText;        
    }
    
    public function setResponseID($responseID) {
        $this->responseID = $responseID;
    }       

    public function getResponseID() {           
        return $this->responseID;            
    }
    
    public function setResponseText($responseText) {
        $this->responseText = $responseText;
    }       

    public function getResponseText() {           
        return $this->responseText;            
    }

}
?>