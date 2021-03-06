<?php session_start();
/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

function retrievePostVar($varName) {
    $postVar = "";
    
    if( isset($_POST[$varName]) ) {
        $postVar = strip_tags($_POST[$varName]);
        $postVar = trim($postVar);
    }
    
    return $postVar;
} 

function ancestorString($arr) {
    if(count($arr) == 0) {
        return "&aIds[]=0";
    }
    
    return ancestorStringNonZero($arr);
}

function ancestorStringNonZero($arr) {
    $jsArr = "";

    foreach ($arr as $val) {
        $jsArr = $jsArr . "&aIds[]=" . $val;
    }
    
    return $jsArr;
}
 
if(isset($_GET["aIds"])) {
    $aIds = $_GET["aIds"];
}            
 
require('InvertedIndex.php');                 
 
if(isset($_POST["op"]) && $_POST["op"] == "logout" && isset($_SESSION['user'])) {
    unset($_SESSION['user']);
}
 
require('user-man.php'); 
$manage_user_result = manage_user();

require('insertFeedback.php');
insertFeedback();

require('insertDemographicSurvey.php');
insertDemographicSurvey();

function insertResponse($text, $userName) {
    // Insert into Responses only if it doesn't exist when searched for. Write a subfunction that first searches for it and if it doesn't exist in Responses then inserts it into responses. It should either return the existing id or the insert_id.
    
    require('db/config.php');
        
    $mysqli = new mysqli($host, $username, $password, $db);

    $responseId = -1;
    
    if ($stmt = $mysqli->prepare("SELECT r.responseId FROM Responses r WHERE r.responseText = ?;")) {
        $stmt->bind_param('s', $text);
        $stmt->execute();
        $stmt->bind_result($subpointId);
        
        if($stmt->fetch()) {
            $responseId = $subpointId;
        }
        $stmt->close();
    }
    
    if($responseId == -1) {
        if ($stmt = $mysqli->prepare("INSERT INTO Responses (responseText, user) VALUES (?,?);")) {
            $stmt->bind_param('ss', $text, $userName);
            
            if($stmt->execute()) {
                $responseId = $stmt->insert_id;
                
                // Fix this
                invertedIndexInsert($responseId, $text);
                
            }
            $stmt->close();
        }
    }
    
    $mysqli->close();
    
    return $responseId;
}

$rID = 0;

if(isset($_POST["rID"])&&isset($_POST["vote"])&&isset($_POST["rPID"]) && isset($_SESSION['user'])) {    
    $rID = strip_tags($_POST["rID"]);
    $rID = trim($rID);
    $rID = intval($rID);
    
    $vote = strip_tags($_POST["vote"]);
    $vote = trim($vote);
    $vote = intval($vote);
    
    $rPID = strip_tags($_POST["rPID"]);
    $rPID = trim($rPID);
    //$rPID = intval($rPID);
    $rIdOuter = intval($rPID);
        
    if(strstr($rPID, 's') != false) {
        $rIdArr = explode( 's', $rPID );
        $rIdOuter = intval($rIdArr[1]); 
    }
    
    if($vote == 1 || $vote == 0) { 

        require('db/config.php');
        
        $mysqli = new mysqli($host, $username, $password, $db);

        $hasVote = false; 
        $sameVote = false;        
        
        if ($stmt = $mysqli->prepare("SELECT vote FROM Votes WHERE responseId = ? AND parentId = ? AND user = ?")) {
            $stmt->bind_param('iis', $rID, $rIdOuter, $_SESSION['user']);
            $stmt->execute();
            $stmt->bind_result($voteResult);
                    
            if($stmt->fetch()) {
                $hasVote = true;
                
                if($voteResult == $vote) {
                    $sameVote = true;
                }
            }
            
            $stmt->close();
        }                
                        
        if(!$sameVote) {
            
            if(!$hasVote) {
                if ($stmt = $mysqli->prepare("INSERT INTO Votes (responseId, parentId, user, vote, aIds) VALUES (?,?,?,?,?);")) {
                    $stmt->bind_param('iisis', $rID, $rIdOuter, $_SESSION['user'],  $vote, ancestorStringNonZero($aIds));
                
                    if($stmt->execute()) {
                        $stmt->close();
                        
                        if (($vote == 1 || $vote == 0) && $stmt = $mysqli->prepare("UPDATE Context SET yesVotes=(SELECT COUNT(*) FROM Votes WHERE Votes.responseId = ? AND Votes.parentId = ? AND vote = 1 AND Context.responseId = Votes.responseId AND  Context.parentId = Votes.parentId), noVotes=(SELECT COUNT(*) FROM Votes WHERE Votes.responseId = ? AND Votes.parentId = ? AND vote = 0 AND Context.responseId = Votes.responseId AND  Context.parentId = Votes.parentId) WHERE Context.responseId = ? AND Context.parentId = ?")) {
                            $stmt->bind_param('iiiiii', $rID, $rIdOuter, $rID, $rIdOuter, $rID, $rIdOuter);
                            $stmt->execute();
                        }
                    }
                    
                    $stmt->close();
                }
            }
            else {
                if ($stmt = $mysqli->prepare("UPDATE Votes SET vote = ? WHERE responseId = ? AND parentId = ? AND user = ?;")) {
                    $stmt->bind_param('iiis', $vote, $rID, $rIdOuter, $_SESSION['user']);
                
                    if($stmt->execute()) {
                        $stmt->close();
                        
                        if (($vote == 1 || $vote == 0) && $stmt = $mysqli->prepare("UPDATE Context SET yesVotes=(SELECT COUNT(*) FROM Votes WHERE Votes.responseId = ? AND Votes.parentId = ? AND vote = 1 AND Context.responseId = Votes.responseId AND  Context.parentId = Votes.parentId), noVotes=(SELECT COUNT(*) FROM Votes WHERE Votes.responseId = ? AND Votes.parentId = ? AND vote = 0 AND Context.responseId = Votes.responseId AND  Context.parentId = Votes.parentId) WHERE Context.responseId = ? AND Context.parentId = ?")) {
                            $stmt->bind_param('iiiiii', $rID, $rIdOuter, $rID, $rIdOuter, $rID, $rIdOuter);
                            $stmt->execute();
                        }
                    }
                    
                    $stmt->close();
                }
            }
        } 
        else {
            if ($stmt = $mysqli->prepare("DELETE FROM Votes WHERE responseId = ? AND parentId = ? AND user = ?;")) {
                $stmt->bind_param('iis', $rID, $rIdOuter, $_SESSION['user']);
                
                if($stmt->execute()) {
                    $stmt->close();
                    
                    if (($vote == 1 || $vote == 0) && $stmt = $mysqli->prepare("UPDATE Context SET yesVotes=(SELECT COUNT(*) FROM Votes WHERE Votes.responseId = ? AND Votes.parentId = ? AND vote = 1 AND Context.responseId = Votes.responseId AND  Context.parentId = Votes.parentId), noVotes=(SELECT COUNT(*) FROM Votes WHERE Votes.responseId = ? AND Votes.parentId = ? AND vote = 0 AND Context.responseId = Votes.responseId AND  Context.parentId = Votes.parentId) WHERE Context.responseId = ? AND Context.parentId = ?")) {
                        $stmt->bind_param('iiiiii', $rID, $rIdOuter, $rID, $rIdOuter, $rID, $rIdOuter);
                        $stmt->execute();
                    }                    
                }
                
                $stmt->close();
            }
        }
            
        $mysqli->close();
    }
}
 
if(isset($_POST["rText"])&&isset($_POST["rIsAgree"])&&isset($_POST["rPID"]) && isset($_SESSION['user'])) {    
    
    $rText = $_POST["rText"];
            
    foreach ($rText as $key => &$temp_subpointText) {
        $temp_subpointText = strip_tags($temp_subpointText);
        $temp_subpointText = trim($temp_subpointText);
        $temp_subpointText = filter_var($temp_subpointText, FILTER_SANITIZE_STRING);
        
        if(strlen($temp_subpointText) == 0) {
            unset($rText[$key]);
        }
    }
    unset($temp_subpointText);
    
    
    $rIsAgree = strip_tags($_POST["rIsAgree"]);
    $rIsAgree = trim($rIsAgree);
    
    if($rIsAgree == "0" || $rIsAgree == "1" || $rIsAgree == "2" || $rIsAgree == "3" || $rIsAgree == "4") {
        $rIsAgree = intval($rIsAgree);
    } else {
        $rIsAgree = -1;
    }
    
    $rPID = strip_tags($_POST["rPID"]);
    $rPID = trim($rPID);
    //$rPID = intval($rPID);
    $rIdOuter = intval($rPID);
        
    if(strstr($rPID, 's') != false) {
        $rIdArr = explode( 's', $rPID );
        $rIdOuter = intval($rIdArr[1]); 
    }
    
    if(($rIdOuter == 0 || responseExists($rIdOuter))&& count($rText) != 0 && $rIsAgree != -1) {
    
        require('db/config.php');
            
        $mysqli = new mysqli($host, $username, $password, $db);                    
        if(count($rText) == 1) {
            
            foreach ($rText as $temp_subpointText) {
                
                $newRID = insertResponse($temp_subpointText, $_SESSION['user']);
                
                if ($newRID != -1){
                    if ($stmt = $mysqli->prepare("INSERT INTO Context (responseId, isAgree, parentId, user, aIds) VALUES (?,?,?,?,?);")) {
                        $stmt->bind_param('iiiss', $newRID, $rIsAgree, $rIdOuter, $_SESSION['user'], ancestorStringNonZero($aIds));
                        
                        $stmt->execute();
                    }
                }
            }
        } elseif(count($rText) > 1) {
        
            if ($stmt = $mysqli->prepare("INSERT INTO Responses (user) VALUES (?);")) {
                $stmt->bind_param('s', $_SESSION['user']);
                
                if($stmt->execute()) {
                    $newRID = $stmt->insert_id;
                    $stmt->close();
                    
                    if ($stmt = $mysqli->prepare("INSERT INTO Context (responseId, isAgree, parentId, user) VALUES (?,?,?,?);")) {
                        $stmt->bind_param('iiis', $newRID, $rIsAgree, $rIdOuter, $_SESSION['user']);
                
                        $stmt->execute();
                        $stmt->close();
                    }
                   
                    require('db/config.php');
                    
                    $mysqli2 = new mysqli($host, $username, $password, $db);
                    
                    foreach ($rText as &$temp_subpointText) {
                        $newSID = insertResponse($temp_subpointText, $_SESSION['user']);
                        
                        if ($newSID != -1){
                            if ($stmt2 = $mysqli2->prepare("INSERT INTO ResponseSubpoints (responseId,subpointId) VALUES (?,?);")) {
                                $stmt2->bind_param('ii', $newRID,$newSID);
                                $stmt2->execute();
                                $stmt2->close();
                            }
                        }
                    }
                    
                    $mysqli2->close();
                }
                
                $stmt->close();
            }
        }
        
        $mysqli->close();        
    }
}

function responseExists($responseId) {
    $exists = false;
    
    require('db/config.php');
    $mysqli = new mysqli($host, $username, $password, $db);                    
    
    if ($stmt = $mysqli->prepare("SELECT timestamp FROM Responses WHERE responseId = ?;")) {
        $stmt->bind_param('i', $responseId);
        $stmt->execute();
        $stmt->bind_result($timestamp);
        
        if($stmt->fetch()) {
            $exists = true;
        }
        
        $stmt->close();
    }
        
    $mysqli->close();
    
    return $exists;
}

function outputForm($respID, $aIds, $button1Text, $button2Text) {
    if(isset($_SESSION['user'])) {
        print("        
        <form id=\"responseForm\" action=\"index.php?rId=$respID".ancestorStringNonZero($aIds)."\" method=\"post\">
            <div id=\"SearchPreviousResponsesBox\"> 
                <h1>Reuse Previous Responses</h1>
                
                <!--<input type=\"text\" name=\"searchPreviousResponsesQuery\" id=\"searchPreviousResponsesQuery\" size=\"60\">-->
                
                <div id=\"searchResponses\"></div>
                
                <p>
                <img src=\"closeButton2.png\" class=\"closeButton\" />
                </p>
            </div>

            <div id=\"textAreas\">
            <textarea name=\"rText[]\" class=\"textbox textboxSize\"></textarea>
            </div>
            <input type=\"hidden\" id=\"rIsAgree\" name=\"rIsAgree\" value=\"0\" />
            
            <input type=\"hidden\" id=\"rPID\" name=\"rPID\" value=\"$respID\" />
            
            <div class=\"formButtons\">
                <p id=\"SubmitResponse".$button1Text."Button\" class=\"".$button1Text."Button argumentSubmitButton\">".$button1Text."</p>");
                
                if($button1Text == "Support") {
                    print("<p id=\"SubmitResponseNeutralButton\" class=\"NeutralButton argumentSubmitButton\">Neutral</p>");
                }
                
                print("<p id=\"SubmitResponse".$button2Text."Button\" class=\"".$button2Text."Button argumentSubmitButton\">".$button2Text."</p>");
                
                if($button1Text == "Support") {
                    print("<p id=\"AddAnotherSubpointButton\" class=\"argumentSubmitButton\">Add another subpoint input box</p>");
                }
                
                print("</div>
        </form>");
    }
    else {
         print("<p class=\"responseFormPlaceholder\"><span class=\"loginRegisterLink\">Create Account/Sign In</span> to add a response</p>");
    }
}

function outputDiscussionContents($respID, $aIds) {

    print("<div class=\"circleResponses circleResponsesSize\">");

    require('Responses.php');
    $agreeResponses = new Responses("Support", 1, $respID, $aIds);
    $agreeResponses->generateResponses();
    $agreeResponses->outputResponses();
                                
    print("        
    <div class=\"dividingLine dividingLineSize\"></div>
    ");
    
    $neutralResponses = new Responses("Neutral", 4, $respID, $aIds);
    $neutralResponses->generateResponses();
    $neutralResponses->outputResponses();
                                
    print("        
    <div class=\"dividingLine dividingLineSize\"></div>
    ");
    
    $disagreeResponses = new Responses("Oppose", 0, $respID, $aIds);
    $disagreeResponses->generateResponses();
    $disagreeResponses->outputResponses();
    
    outputForm($respID, $aIds, "Support", "Oppose");
    
    print("</div>");
}

function outputCategoryContents($respID, $aIds) {
    print("<div class=\"circleResponses circleResponsesSize\">");

    require('Responses.php');
    $categoriesResponses = new Responses("Categories", 3, $respID, $aIds);
    $categoriesResponses->generateResponses();
    $categoriesResponses->outputResponses();
                                
    print("
    <div class=\"dividingLine dividingLineSize\"></div>");
        
    $discussionsResponses = new Responses("Discussions", 2, $respID, $aIds);
    $discussionsResponses->generateResponses();
    $discussionsResponses->outputResponses();
    
    outputForm($respID, $aIds, "Category", "Discussion");
    
    print("</div>");
}

function isSubpoint($responseId, $subpointId) {
    
    $result = false;
     
    require('db/config.php');
    
    $mysqli = new mysqli($host, $username, $password, $db);

    if ($stmt = $mysqli->prepare("SELECT responseId FROM ResponseSubpoints WHERE responseId = ? AND subpointId = ?;")) {
        $stmt->bind_param('ii', $responseId, $subpointId);
        $stmt->execute();
        
        if($stmt->fetch()) {
            $result = true;
        }
        
        $stmt->close();
    }
    
    $mysqli->close();
    
    return $result;
}

function isParent($parentId, $responseId) {
    $result = false;
     
    require('db/config.php');
    
    $mysqli = new mysqli($host, $username, $password, $db);

    if ($stmt = $mysqli->prepare("SELECT parentId FROM Context WHERE parentId = ? AND responseId = ?;")) {
        $stmt->bind_param('ii', $parentId, $responseId);
        $stmt->execute();
        
        if($stmt->fetch()) {
            $result = true;
        }
        
        $stmt->close();
    }
    
    $mysqli->close();
    
    return $result;
}

function validParent($first, $second) {
    
    $firstOuter = intval($first);
    $secondInner = intval($second);
    
    if(strstr($first, 's') != false) {
        $firstArr = explode( 's', $first );
        
        if(count($firstArr) == 2) {
            if(!isSubpoint(intval($firstArr[0]), intval($firstArr[1]))) {
                //print("subpoint" . $firstArr[0] . " " . $firstArr[1]);
                return false;
            }
            
            $firstOuter = intval($firstArr[1]);
        } else {
            return false;
        }
    }
    
    if(strstr($second, 's') != false) {
        $secondArr = explode( 's', $second );
        
        if(count($secondArr) == 2) {
            if(!isSubpoint(intval($secondArr[0]), intval($secondArr[1]))){
                //print("subpoint" . $secondArr[0] . " " . $secondArr[1]);
                return false;
            }
            
            $secondInner = intval($secondArr[0]);
        } else {
            return false;
        }
    }
    /*
    if(!isParent($firstOuter, $secondInner)) {
        print("parent" . $firstOuter . " " . $secondInner);
    }
    */
    return ( isParent($firstOuter, $secondInner) );
}

function validAncestors($aIds, $rId) {
    
    foreach ($aIds as $aId) {
        $first = $second; 
        $second = $aId;
        
        if(isset($first)) {
            if(!validParent($first, $second)) {
                return false;
            }
        }        
    
    }
    $first = $second; 
    $second = $rId;
    
    if(!validParent($first, $second)) {
        return false;
    }
    
    return true;
}

$rId = 0;

if(isset($_GET["rId"])) {
    $rId = strip_tags($_GET["rId"]);
    $rId = trim($rId);
    //$rId = intval($rId);
    
    if(intval($rId) != 0) {
        if(isset($_GET["aIds"])) {
            $aIds = $_GET["aIds"];
            
            foreach ($aIds as &$temp_aId) {
                $temp_aId = strip_tags($temp_aId);
                $temp_aId = trim($temp_aId);
                //$temp_aId = intval($temp_aId);
            }
        
            unset($temp_aId);
            
            if(!validAncestors($aIds, $rId)) {
                $rId = 0;
            } /*else {
                $rId = intval($rId);
            }*/
        } else{
            $rId = 0;
        }
    }
}

require('CurrentArgument.php');
$currentArgument = new CurrentArgument($rId, $aIds[count($aIds)-1]);
?>
<!DOCTYPE html>
<html>
<head> 
    <link href="styles/stylesheets/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
    
    <title>The Public Spheres</title>    
    <script src="http://code.jquery.com/jquery-1.8.0.min.js"></script>
    <script src="colorConverter.js"></script>
    <script src="thePublicSpheres.js"></script>
</head>
<body>

<div id="greyOverlay" onClick="closeTop();"> 
</div>

<div id="howto">
    <p>
	<img src="closeButton2.png" class="closeButton" />
	</p>
    <div>
        <h1>How-to Guide</h1>
        <p>
        Please map out all constructive arguments, not only the arguments that you personally agree with.<br/><br/>
        <iframe width="420" height="315" src="http://www.youtube.com/embed/kyUJjrRcxDY" frameborder="0" allowfullscreen></iframe><br/><br/>       
        This web application is intended to be a resource for fully mapped out arguments so that people can learn about, contribute to, and participate in arguments in areas that interest them in a deep and detailed manner. Users can contribute, organize, and vote on arguments for their own benefit and for the benefit of others.
        <br/><br/>
        The interface can be navigated by clicking on the large or small rectangles to open up that level of argument. 
        <br/><br/>
        Categories are for organizing discussions, making it faster and easier to find a personally interesting discussion. There are 4 types of arguments: Discussion, Support, Neutral, and Oppose. Discussions are debate statements that others will respond to. Each Discussion, Supporting, Neutral, or Opposing arguments can have arguments made to it. This allows arguments to be threaded and reach a deep level of analysis.
        <br/><br/>
        You can browse the interface by clicking on responses or outer circles to open up to that level.
        <br/><br/>
        Add an argument by entering text in the text box at the bottom of the interface and clicking the relevant button. If you see a previous argument that is equivalent to the argument that you are typing, click on that argument to use it instead. Using the existing response in the new context to prevent having a redundant critique of that response when the critique has already been made on the argument elsewhere.
        <br/><br/>
        You can provide feedback on whether an argument is constructive or not by pressing a button below each argument. When you vote, the page will refresh and the button that you clicked will be colored to indicate your vote. You can undo a vote by clicking again on the triangle you previously clicked.
        <br/><br/>
        Please break your arguments down into subpoints by clicking on the Add another subpoint button for each subpoint and typing a subpoint in each textbox.
        </p>
    </div>
</div>

<div id="loginRegisterBox"> 
	<p>
	<img src="closeButton2.png" class="closeButton" />
	</p>
    
    <div style="float:left">
        <h1>Create Account</h1><br>
        <?php
            print("<form action=\"index.php?rId=$rId".ancestorStringNonZero($aIds)."\" method=\"post\">");
        ?>
                <input type="hidden" name="op" value="new">
                Username:
                <input type="text" name="user" size="40"><br>
                Password:
                <input type="password" name="pass" size="40"><br>
                <br>
<textarea style="
    width: 370px;
    height: 280px;
">We are asking you to take part in an evaluation of the Public Spheres. This project is carried out by Michael Kaplan at Cornell University with the purpose of understanding how people discuss topics online to better build tools for online deliberation. 

If you agree to participate, you will be asked to fill a short demographics survey, create an account, and then interact with the Public Spheres website. You can leave the website and get back to it later and log in again with the account you created. 
There are no particular risks or benefits associated with this interview.
There is no compensation for your participation.

Your answers will be confidential. We will record your activities on the website, but we collect no identifying information, and the only way in which we identify you is through the account name you create. Your account name will not be displayed to others, and we will use a code to connect your recorded activities to your account name. 

Taking part is completely voluntary. If you decide not to take part or to skip some of the questions, it will not affect your current or future relationship with Cornell University. You are free to withdraw at any time.
Please ask any questions you have now. If you have questions later, you may contact the researcher: Michael Kaplan, at mak364@cornell.edu, or the faculty supervisor, Gilly Leshed at gl87@cornell.edu.

If you have any questions or concerns regarding your rights as a participant in this class project, you may contact the Institutional Review Board (IRB) at 607-255-5138 or access their website at http://www.irb.cornell.edu. You may also report your concerns or complaints anonymously through http://www.ethicspoint.com or by calling toll free at 1- 866-293-3077. Ethicspoint is an independent organization that serves as a liaison between the University and the person bringing the complaint so that anonymity can be ensured.

Statement of Age of Subject and Consent
By clicking the button below I agree that I am over 18, have read the consent form, and agree to participate in this study. </textarea>
<br/><br>
                <input type="submit" value="I agree, create account">
            </form>
        <p></p>
    </div>               
    <div style="
        float: left;
        height: 400px;
        border-right: 1px solid black;
        margin-left: 20px;
        margin-right: 20px;">
    </div>
    <div style="float: left;">
        <h1>Sign in</h1><br>
        <?php
            print("<form action=\"index.php?rId=$rId".ancestorStringNonZero($aIds)."\" method=\"post\">");
        ?>
            <input type="hidden" name="op" value="login">
            Username:
            <input type="text" name="user" size="40"><br>
            Password:
            <input type="password" name="pass" size="40">
            
            <br><br>
            
            <input type="submit" value="Sign in">
        </form>
    </div>
    
</div>

<?php
    require('demographicSurvey.php');
?>

<div id="feedback">
    <p>
	<img src="closeButton2.png" class="closeButton" />
	</p>
    <div style="text-align:left">
        <h2>Feedback</h2>
        <p><br/><br/>Feel free to answer one or more of the following questions<br/><br/></p>

        <?php
            print("<form action=\"index.php?rId=$rId".ancestorStringNonZero($aIds)."\" method=\"post\">");
        ?>

        Is this an interesting product? Why or why not?<br/>
        <textarea name="feedbackInteresting" rows="3" cols="80"></textarea><br/>

        What do you like the best about this interface?<br/>
        <textarea name="feedbackBest" rows="3" cols="80"></textarea><br/>

        What do you like the least about this interface?<br/>
        <textarea name="feedbackLeast" rows="3" cols="80"></textarea><br/>

        What would you change about this interface?<br/>
        <textarea name="feedbackChange" rows="3" cols="80"></textarea><br/>

        Would you continue to use it ? Why or why not?<br/>
        <textarea name="feedbackContinue" rows="3" cols="80"></textarea><br/>

        Would you recommend it to others? Why or why not?<br/>
        <textarea name="feedbackRecommend" rows="3" cols="80"></textarea><br/>

        Please choose the answer that mostly closely matches your opinion on the statement:<br/> 
        "Health Care is a Human Right"<br/>
        <input name="feedbackLikertHealth" type="radio" value="1" /> Strongly Agree<br/>
        <input name="feedbackLikertHealth" type="radio" value="2" /> Mildly Agree<br/>
        <input name="feedbackLikertHealth" type="radio" value="3" /> Undecided<br/>
        <input name="feedbackLikertHealth" type="radio" value="4" /> Mildly Disagree<br/>
        <input name="feedbackLikertHealth" type="radio" value="5" /> Strongly Disagree<br/>

        Please explain your opinion on whether Health Care is a Human Right<br/>
        <textarea name="feedbackOpinionHealth" rows="3" cols="80"></textarea><br/>
        
        <input type="submit" value="Submit">
        </form>
    </div>
</div>

<?php
if($rId != 0) {
?>

<div id="mainCircleSize">
    <div class="circle circleSize" onclick="goToRID(this, event, 0 ,'');">
        <!--<p id="search" style="
        position: absolute;  top: 5px;  
        right: 275px;  cursor: pointer;  color: blue;">
            Search
        </p>-->
        <p id="howtoLink" style="
        position: absolute;  top: 8px;  
        left: 275px;  cursor: pointer;  color: blue;">
            How-to Guide
        </p>
        <h2 class="statement statementSize" onclick="goToRID(this, event, 0, '');">The Public Spheres: Ideas Taking Shape</h2>
        

    <?php
        if(isset($_SESSION['user'])) {
            print ("<form id=\"logoutForm\" action=\"index.php?rId=$rId".ancestorStringNonZero($aIds)."\" method=\"post\"><p id=\"user\">Welcome, ".htmlspecialchars($_SESSION['user'])."
                    <span id=\"logoutLink\">Logout <input type=\"hidden\" name=\"op\" value=\"logout\"></span>
                </p></form>");
        }
        else {
            print ("<p class=\"loginRegisterLink loginRegisterTop\">Create Account/Sign In</p>");
        }
    
        $parentsOutputText = ""; 
        $hasParents = true;
        
        require('db/config.php');
        
        $mysqli = new mysqli($host, $username, $password, $db);

        if($rId != 0) 
        {        
            $temp_aIds = array();
            $lastAIdOuter = 0;
            
            foreach ($aIds as $aId) {
                $aIdInner = intval($aId);
                $aIdOuter = -1;
                
                if(strstr($aId, 's') != false) {
                    $aIdArr = explode( 's', $aId );
                    $aIdInner = intval($aIdArr[0]);
                    $aIdOuter = intval($aIdArr[1]); 
                }
                
                
            
                if ($stmt = $mysqli->prepare("SELECT r.responseText, c.isAgree FROM Responses r, (SELECT responseId, isAgree FROM Context WHERE responseId = ? AND parentId = ?) c WHERE c.responseId = r.responseId;")) {
                    $stmt->bind_param('ii', $aIdInner, $lastAIdOuter);
                    $stmt->execute();
                    $stmt->bind_result($parentText, $parentIsAgree);
                    
                    if($stmt->fetch()) {
                        $anotherCircle = "<div class=\"circle circleSize";
                        
                        $anotherCircle = $anotherCircle . "\" onclick=\"goToRID(this, event, '$aId' ,'".ancestorString($temp_aIds)."');\">";
                        
                        if(is_null($parentText)) {
                            if($aIdOuter == -1) {
                                $anotherCircle = $anotherCircle . "<div class=\"selectEntireArgument\" style=\"background-color:#555;\" onclick=\"goToRID(this, event, '$aIdInner','".ancestorString($temp_aIds)."');\"></div><h2 style=\"float:left;width:96%;\" class=\"statement statementSize";
                            } else {
                                $anotherCircle = $anotherCircle . "<div class=\"selectEntireArgument\" onclick=\"goToRID(this, event, '$aIdInner','".ancestorString($temp_aIds)."');\"></div><h2 style=\"float:left;width:96%;\" class=\"statement statementSize";
                            }
                        } else {
                            $anotherCircle = $anotherCircle . "<h2 class=\"statement statementSize";
                        }
                        
                        if($parentIsAgree == 1) {
                            $anotherCircle = $anotherCircle . " supportCircleTitle";
                        } elseif($parentIsAgree == 0){
                            $anotherCircle = $anotherCircle . " opposeCircleTitle";
                        } elseif($parentIsAgree == 4){
                            $anotherCircle = $anotherCircle . " neutralCircleTitle";
                        }
                        
                        
                        $anotherCircle = $anotherCircle ."\" onclick=\"goToRID(this, event, '$aId','".ancestorString($temp_aIds)."');\">";

                        if(is_null($parentText)) {
                
                            require('db/config.php');

                            $mysqli3 = new mysqli($host, $username, $password, $db);
                            
                            if ($stmt3 = $mysqli3->prepare("SELECT r.responseId, r.responseText FROM Responses r, ResponseSubpoints rs WHERE rs.responseId = ? AND rs.subpointId = r.responseId;")) {
                                $stmt3->bind_param('i', $aIdInner);
                                $stmt3->execute();
                                $stmt3->bind_result($subpointId, $subpointText);
                            
                                $temp = 0;
                                
                                while($stmt3->fetch()) {
           
                                    if($temp == 0) {
                                        if($aIdOuter == -1 || $aIdOuter==$subpointId) {
                                            $anotherCircle = $anotherCircle ."<span onclick=\"goToRID(this, event, '".$aIdInner."s".$subpointId."' ,'".ancestorString($temp_aIds)."');\" >".str_replace('\\', "", $subpointText)."</span>";
                                        }
                                        else {
                                            $anotherCircle = $anotherCircle ."<span style=\"color:#ccc;\" onclick=\"goToRID(this, event, '".$aIdInner."s".$subpointId."' ,'".ancestorString($temp_aIds)."');\" >".str_replace('\\', "", $subpointText)."</span>";
                                        }
                                        
                                        $temp = 1;
                                    }
                                    else {
                                        if($aIdOuter == -1 || $aIdOuter==$subpointId) {
                                            $anotherCircle = $anotherCircle . " <br/><span class=\"subpointArgumentLine\" > </span><br/> " . "<span onclick=\"goToRID(this, event, '".$aIdInner."s".$subpointId."' ,'".ancestorString($temp_aIds)."');\" >".str_replace('\\', "", $subpointText)."</span>";
                                        }
                                        else {
                                            $anotherCircle = $anotherCircle . " <br/><span class=\"subpointArgumentLine\" > </span><br/> " . "<span style=\"color:#ccc;\" onclick=\"goToRID(this, event, '".$aIdInner."s".$subpointId."' ,'".ancestorString($temp_aIds)."');\" >".str_replace('\\', "", $subpointText)."</span>";
                                        }
                                    }
                                    
                                }
                                    
                                $stmt3->close();
                            }
                            
                            $mysqli3->close();
                            
                        } else {
                            $anotherCircle = $anotherCircle .str_replace('\\', "", $parentText);
                        }
                        
                        $anotherCircle = $anotherCircle ."</h2>\n";
                        
                        $tempAID = $aId."";
                        array_push($temp_aIds, $tempAID);
                        
                        $parentLabel = "";
                        
                        if($parentIsAgree == 1) {
                            $parentLabel = "<p class=\"titleArrowImg\" style=\"color: #b6d7a8;\"><span style=\"background-color: #F7F7F7;\">is supported by<img src=\"titleArrow.png\" style=\" height: 11px; position: relative;  top: 1px;\"></span></p>";
                        
                        } elseif($parentIsAgree == 0){
                            $parentLabel = "<p class=\"titleArrowImg\" style=\"color: #ea9999;\"><span style=\"background-color: #F7F7F7;\">is opposed by<img src=\"titleArrow.png\" style=\" height: 11px; position: relative;  top: 1px;\"></span></p>";
                        } elseif($parentIsAgree == 4) {
                            $parentLabel = "<p class=\"titleArrowImg\" style=\"color: #EAC799;\"><span style=\"background-color: #F7F7F7;\">is responded to neutrally by<img src=\"titleArrow.png\" style=\" height: 11px; position: relative;  top: 1px;\"></span></p>";
                        } elseif($parentIsAgree == 3) {
                            $parentLabel = "<p class=\"titleArrowImg\" style=\"color: #99B6EA;\"><span style=\"background-color: #F7F7F7;\">contains the category<img src=\"titleArrow.png\" style=\" height: 11px; position: relative;  top: 1px;\"></span></p>";
                        } elseif($parentIsAgree == 2) {
                            $parentLabel = "<p class=\"titleArrowImg\" style=\"color: #EAC799;\"><span style=\"background-color: #F7F7F7;\">contains the discussion<img src=\"titleArrow.png\" style=\" height: 11px; position: relative;  top: 1px;\"></span></p>";
                        }
                        
                        $parentsOutputText = $parentsOutputText. $anotherCircle . $parentLabel;
                        
                    } else {
                        $hasParents = false;
                    }
                    
                    $stmt->close();
                } else {
                    $hasParents = false;
                }
                
                $lastAIdOuter = intval($aId);
                
                if(strstr($aId, 's') != false) {
                    $lastAIdArr = explode( 's', $aId );
                    $lastAIdOuter = intval($lastAIdArr[1]);
                }
            }
            
            //Close the database connection
            $mysqli->close();
            
            print("$parentsOutputText");
        }
        
        print("<div id=\"innerCircle\" class=\"circle circleSize");
        
        print("\">");
        
        
        if(count($currentArgument->getArgumentSubpoints()) > 1) {
            if($currentArgument->getRIdOuter() == -1) {
                print("<div class=\"selectEntireArgument\" style=\"background-color:#555;\"></div><h2 style=\"float:left;width:96%;\" class=\"statement statementSize");
            } else {                
                print("<div class=\"selectEntireArgument\" onclick=\"goToRID(this, event, '" . $currentArgument->getRIdInner() . "','".ancestorStringNonZero($aIds)."');\" style=\"cursor:pointer;\"></div><h2 style=\"float:left;width:96%;\" class=\"statement statementSize");
            }
        } else {
            print("<h2 class=\"statement statementSize");
        }
        
        if($currentArgument->getArgumentIsAgree() == 1) {
            print(" supportCircleTitle");
        } elseif($currentArgument->getArgumentIsAgree() == 0){
            print(" opposeCircleTitle");
        } elseif($currentArgument->getArgumentIsAgree() == 4){
            print(" neutralCircleTitle");
        }
        
        print("\">");
        
        $curArgOutput = "";
        $temp = 0;
        
        foreach ($currentArgument->getArgumentSubpoints() as $subpoint) {
            if($temp != 0) {
                $curArgOutput = $curArgOutput . " <br/><span class=\"subpointArgumentLine\" > </span><br/> ";
            }
            
            $curArgOutput = $curArgOutput . "<span onclick=\"goToRID(this, event, '".$currentArgument->getRIdInner()."s".$currentArgument->getArgumentSubpointId($temp)."' ,'".ancestorString($aIds)."');\" ";
            
            if(!($currentArgument->getRIdOuter() == -1 || $currentArgument->getRIdOuter()==$currentArgument->getArgumentSubpointId($temp)) ) {
                $curArgOutput = $curArgOutput . " style=\"color:#ccc;\" ";
            }
            
            $curArgOutput = $curArgOutput . ">".$subpoint."</span>";
            
            
            $temp++;
        }
        
        print($curArgOutput);
    
        print("</h2>");
        
        if($currentArgument->getArgumentIsAgree() == 1) {
            print("<p class=\"titleArrowImg\" style=\"color: #b6d7a8;\"><span style=\"background-color: #F7F7F7;\">is supported by<img src=\"titleArrow.png\" style=\" height: 11px; position: relative;  top: 1px;\"></span></p>");
        
        } elseif($currentArgument->getArgumentIsAgree() == 0){
            print("<p class=\"titleArrowImg\" style=\"color: #ea9999;\"><span style=\"background-color: #F7F7F7;\">is opposed by<img src=\"titleArrow.png\" style=\" height: 11px; position: relative;  top: 1px;\"></span></p>");
        } elseif($currentArgument->getArgumentIsAgree() == 4) {
            print("<p class=\"titleArrowImg\" style=\"color: #EAC799;\"><span style=\"background-color: #F7F7F7;\">is responded to neutrally by<img src=\"titleArrow.png\" style=\" height: 11px; position: relative;  top: 1px;\"></span></p>");
        } elseif($currentArgument->getArgumentIsAgree() == 3) {
            print("<p class=\"titleArrowImg\" style=\"color: #99B6EA;\"><span style=\"background-color: #F7F7F7;\">contains the category<img src=\"titleArrow.png\" style=\" height: 11px; position: relative;  top: 1px;\"></span></p>");
        } elseif($currentArgument->getArgumentIsAgree() == 2) {
            print("<p class=\"titleArrowImg\" style=\"color: #EAC799;\"><span style=\"background-color: #F7F7F7;\">contains the discussion<img src=\"titleArrow.png\" style=\" height: 11px; position: relative;  top: 1px;\"></span></p>");
        }
        
        if($currentArgument->getArgumentIsAgree() == 3) {
            outputCategoryContents($rId, $aIds);
        } else {
            outputDiscussionContents($rId, $aIds);
        }
        
        print("</div>");
        
        foreach ($aIds as $aId) {
            print("</div>");
        }
    ?>
    
    </div>    
</div>

<?php
    } else {
?>

<div id="mainCircleSize">
<div id="innerCircle" class="circle circleSize">
    <!--
    <p id="search" style="
    position: absolute;  top: 5px;  
    right: 275px;  cursor: pointer;  color: blue;">
        Search
    </p>
    -->
    <p id="howtoLink" style="
    position: absolute;  top: 8px;  
    left: 275px;  cursor: pointer;  color: blue;">
        How-to Guide
    </p>
    <h2 class="statement statementSize">The Public Spheres: Ideas Taking Shape</h2>
    <?php
        if(isset($_SESSION['user'])) {
            print ("<form id=\"logoutForm\" action=\"index.php?rId=$rId".ancestorStringNonZero($aIds)."\" method=\"post\"><p id=\"user\">Welcome, ".htmlspecialchars($_SESSION['user'])."
                    <span id=\"logoutLink\">Logout <input type=\"hidden\" name=\"op\" value=\"logout\"></span>
                </p></form>");
        }
        else {
            print ("<p class=\"loginRegisterLink loginRegisterTop\">Create Account/Sign In</p>");
        }
        outputCategoryContents($rId, array());
    ?>
    
    </div>
</div>

<?php        
    }

?>

<div style="
    width: 1270px;
    margin-left: auto;
    margin-right: auto;
    margin-top: 10px;
">
    <p style="
    float: left;
    margin-left: 100px;
    margin-right: 300px;">
        Text is available under a <a rel="license" href="http://creativecommons.org/licenses/by/3.0/deed.en_US">Creative Commons Attribution 3.0 Unported License</a>.
    </p>
     <p id="feedbackLink" style="cursor: pointer;  color: blue;">
        Provide Feedback
    </p>
</div>

</body>
</html>