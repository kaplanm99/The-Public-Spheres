<?php
/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

class Response {

    private $responseID;
    private $responseText;
    private $responseScore;
    private $responseVote;
    
    function __construct($responseID, $responseText, $responseScore, $responseVote) {     
        $this->responseID = $responseID;
        $this->responseText = $responseText;
        $this->responseScore = $responseScore;
        $this->responseVote = $responseVote;
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

    public function setResponseScore($responseScore) {
        $this->responseScore = $responseScore;
    }       

    public function getResponseScore() {           
        return $this->responseScore;            
    }
    
    public function setResponseVote($responseVote) {
        $this->responseVote = $responseVote;
    }       

    public function getResponseVote() {           
        return $this->responseVote;            
    }
}
?>