<?php
/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

class Response {

    private $responseID;
    private $responseSubpoints;
    private $responseScore;
    private $responseYesVotes;
    private $responseNoVotes;
    private $responseVote;
    
    function __construct($responseID, $responseSubpoints, $responseScore, $responseYesVotes, $responseNoVotes, $responseVote) {     
        $this->responseID = $responseID;
        $this->responseSubpoints = $responseSubpoints;
        $this->responseScore = $responseScore;
        $this->responseYesVotes = $responseYesVotes;
        $this->responseNoVotes = $responseNoVotes;
        $this->responseVote = $responseVote;
    }
    
    public function setResponseID($responseID) {
        $this->responseID = $responseID;
    }       

    public function getResponseID() {           
        return $this->responseID;            
    }
    
    public function setResponseSubpoints($responseSubpoints) {
        $this->responseSubpoints = $responseSubpoints;
    }       

    public function getResponseSubpoints() {           
        return $this->responseSubpoints;            
    }

    public function setResponseScore($responseScore) {
        $this->responseScore = $responseScore;
    }       

    public function getResponseScore() {           
        return $this->responseScore;            
    }
    
    public function setResponseYesVotes($responseYesVotes) {
        $this->responseYesVotes = $responseYesVotes;
    }       

    public function getResponseYesVotes() {           
        return $this->responseYesVotes;            
    }
    
    public function setResponseNoVotes($responseNoVotes) {
        $this->responseNoVotes = $responseNoVotes;
    }       

    public function getResponseNoVotes() {           
        return $this->responseNoVotes;            
    }
    
    public function setResponseVote($responseVote) {
        $this->responseVote = $responseVote;
    }       

    public function getResponseVote() {           
        return $this->responseVote;            
    }
}
?>