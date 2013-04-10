<?php session_start();

function insertDemographicSurvey() {
    
    if( isset($_SESSION['user']) && ( isset($_POST["demographicSurveyAge"]) || isset($_POST["demographicSurveyGender"]) || isset($_POST["demographicSurveyEducation"]) || isset($_POST["demographicSurveyPoliticalParty"]) || isset($_POST["demographicSurveyInterestInPolitics"]) || isset($_POST["demographicSurveyLikertHealth"]) || isset($_POST["demographicSurveyOpinionHealth"]) ) ) {

        $user = $_SESSION['user'];
    
        $demographicSurveyAge = intval( retrievePostVar("demographicSurveyAge") );
        $demographicSurveyGender = filter_var( retrievePostVar("demographicSurveyGender") , FILTER_SANITIZE_STRING);
        $demographicSurveyEducation = intval( retrievePostVar("demographicSurveyEducation") );
        $demographicSurveyPoliticalParty = filter_var( retrievePostVar("demographicSurveyPoliticalParty") , FILTER_SANITIZE_STRING);
        $demographicSurveyInterestInPolitics = intval( retrievePostVar("demographicSurveyInterestInPolitics") );
        $demographicSurveyLikertHealth = intval( retrievePostVar("demographicSurveyLikertHealth") );
        $demographicSurveyOpinionHealth = filter_var( retrievePostVar("demographicSurveyOpinionHealth") , FILTER_SANITIZE_STRING);
        
        require('db/config.php');
            
        $mysqli = new mysqli($host, $username, $password, $db);

        if ($stmt = $mysqli->prepare("INSERT INTO DemographicSurvey (user, demographicSurveyAge, demographicSurveyGender, demographicSurveyEducation, demographicSurveyPoliticalParty, demographicSurveyInterestInPolitics, demographicSurveyLikertHealth, demographicSurveyOpinionHealth) VALUES (?,?,?,?,?,?,?,?);")) {
            $stmt->bind_param('sisisiis', $user, $demographicSurveyAge, $demographicSurveyGender, $demographicSurveyEducation, $demographicSurveyPoliticalParty, $demographicSurveyInterestInPolitics, $demographicSurveyLikertHealth, $demographicSurveyOpinionHealth);
            $stmt->execute();
            
            $stmt->close();
        }
        
        $mysqli->close();
    }
}

    
?>