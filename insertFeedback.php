<?php session_start();

function insertFeedback() {
    
    if( isset($_POST["feedbackInteresting"]) || isset($_POST["feedbackBest"]) || isset($_POST["feedbackLeast"]) || isset($_POST["feedbackChange"]) || isset($_POST["feedbackContinue"]) || isset($_POST["feedbackRecommend"]) || isset($_POST["feedbackLikertHealth"]) || isset($_POST["feedbackOpinionHealth"]) ) {

        $feedbackInteresting = filter_var( retrievePostVar("feedbackInteresting") , FILTER_SANITIZE_STRING);
        $feedbackBest = filter_var( retrievePostVar("feedbackBest") , FILTER_SANITIZE_STRING);
        $feedbackLeast = filter_var( retrievePostVar("feedbackLeast") , FILTER_SANITIZE_STRING);
        $feedbackChange = filter_var( retrievePostVar("feedbackChange") , FILTER_SANITIZE_STRING);
        $feedbackContinue = filter_var( retrievePostVar("feedbackContinue") , FILTER_SANITIZE_STRING);
        $feedbackRecommend = filter_var( retrievePostVar("feedbackRecommend") , FILTER_SANITIZE_STRING);
        $feedbackLikertHealth = intval( retrievePostVar("feedbackLikertHealth") );
        $feedbackOpinionHealth = filter_var( retrievePostVar("feedbackOpinionHealth") , FILTER_SANITIZE_STRING);    
    
        $user = "";
    
        if( isset($_SESSION['user']) ) {
            $user = $_SESSION['user'];
        }
    
        require('db/config.php');
            
        $mysqli = new mysqli($host, $username, $password, $db);

        if ($stmt = $mysqli->prepare("INSERT INTO Feedback (user, feedbackInteresting, feedbackBest, feedbackLeast, feedbackChange, feedbackContinue, feedbackRecommend, feedbackLikertHealth, feedbackOpinionHealth) VALUES (?,?,?,?,?,?,?,?,?);")) {
            $stmt->bind_param('sssssssis', $user, $feedbackInteresting, $feedbackBest, $feedbackLeast, $feedbackChange, $feedbackContinue, $feedbackRecommend, $feedbackLikertHealth, $feedbackOpinionHealth);
            $stmt->execute();
            
            $stmt->close();
        }
        
        $mysqli->close();
    }
}
    
?>