<?php session_start();
/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

if(isset($_POST["op"]) && $_POST["op"] == "logout" && isset($_SESSION['user'])) {
    unset($_SESSION['user']);
}
 
require('user-man.php'); 

$manage_user_result = manage_user();


if(isset($_POST["searchPreviousResponsePID"])&&isset($_POST["searchPreviousResponseRID"])&&isset($_POST["searchPreviousResponseIsAgree"]) && isset($_SESSION['user'])) {    
    $searchPreviousResponseRID = strip_tags($_POST["searchPreviousResponseRID"]);
    $searchPreviousResponseRID = trim($searchPreviousResponseRID);
    
    if(strlen($searchPreviousResponseRID) != 0) {
        $searchPreviousResponseRID = intval($searchPreviousResponseRID);
    }
    else {
        $searchPreviousResponseRID = -1;
    }
    
    $searchPreviousResponseIsAgree = strip_tags($_POST["searchPreviousResponseIsAgree"]);
    $searchPreviousResponseIsAgree = trim($searchPreviousResponseIsAgree);
    
    if($searchPreviousResponseIsAgree == "0" || $searchPreviousResponseIsAgree == "1" || $searchPreviousResponseIsAgree == "2" || $searchPreviousResponseIsAgree == "3" || $searchPreviousResponseIsAgree == "4") {
        $searchPreviousResponseIsAgree = intval($searchPreviousResponseIsAgree);
    } else {
        $searchPreviousResponseIsAgree = -1;
    }
    
    $searchPreviousResponsePID = strip_tags($_POST["searchPreviousResponsePID"]);
    $searchPreviousResponsePID = trim($searchPreviousResponsePID);
    $searchPreviousResponsePID = intval($searchPreviousResponsePID);
    
    if(($searchPreviousResponsePID == 0 || responseExists($searchPreviousResponsePID))&& $searchPreviousResponseRID != -1 && $searchPreviousResponseIsAgree != -1) {    
		require('db/config.php');
		
		$mysqli = new mysqli($host, $username, $password, $db);                    
		
		if ($stmt = $mysqli->prepare("INSERT INTO Context (responseId, isAgree, parentId, user) VALUES (?,?,?,?);")) {
			$stmt->bind_param('iiis', $searchPreviousResponseRID, $searchPreviousResponseIsAgree, $searchPreviousResponsePID, $_SESSION['user']);
			
			$stmt->execute();
		}
			
		$stmt->close();
			
		$mysqli->close();   
    }
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
    $rPID = intval($rPID);
    
    if($vote == 1 || $vote == 0) { 

        require('db/config.php');
        
        $mysqli = new mysqli($host, $username, $password, $db);

        $hasVote = false; 
        $sameVote = false;        
        
        if ($stmt = $mysqli->prepare("SELECT vote FROM Votes WHERE responseId = ? AND parentId = ? AND user = ?")) {
            $stmt->bind_param('iis', $rID, $rPID, $_SESSION['user']);
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
                if ($stmt = $mysqli->prepare("INSERT INTO Votes (responseId, parentId, user, vote) VALUES (?,?,?,?);")) {
                    $stmt->bind_param('iisi', $rID, $rPID, $_SESSION['user'],  $vote);
                
                    if($stmt->execute()) {
                        $stmt->close();
                        
                        if (($vote == 1 || $vote == 0) && $stmt = $mysqli->prepare("UPDATE Context SET yesVotes=(SELECT COUNT(*) FROM Votes WHERE Votes.responseId = ? AND Votes.parentId = ? AND vote = 1 AND Context.responseId = Votes.responseId AND  Context.parentId = Votes.parentId), noVotes=(SELECT COUNT(*) FROM Votes WHERE Votes.responseId = ? AND Votes.parentId = ? AND vote = 0 AND Context.responseId = Votes.responseId AND  Context.parentId = Votes.parentId) WHERE Context.responseId = ? AND Context.parentId = ?")) {
                            $stmt->bind_param('iiiiii', $rID, $rPID, $rID, $rPID, $rID, $rPID);
                            $stmt->execute();
                        }
                    }
                    
                    $stmt->close();
                }
            }
            else {
                if ($stmt = $mysqli->prepare("UPDATE Votes SET vote = ? WHERE responseId = ? AND parentId = ? AND user = ?;")) {
                    $stmt->bind_param('iiis', $vote, $rID, $rPID, $_SESSION['user']);
                
                    if($stmt->execute()) {
                        $stmt->close();
                        
                        if (($vote == 1 || $vote == 0) && $stmt = $mysqli->prepare("UPDATE Context SET yesVotes=(SELECT COUNT(*) FROM Votes WHERE Votes.responseId = ? AND Votes.parentId = ? AND vote = 1 AND Context.responseId = Votes.responseId AND  Context.parentId = Votes.parentId), noVotes=(SELECT COUNT(*) FROM Votes WHERE Votes.responseId = ? AND Votes.parentId = ? AND vote = 0 AND Context.responseId = Votes.responseId AND  Context.parentId = Votes.parentId) WHERE Context.responseId = ? AND Context.parentId = ?")) {
                            $stmt->bind_param('iiiiii', $rID, $rPID, $rID, $rPID, $rID, $rPID);
                            $stmt->execute();
                        }
                    }
                    
                    $stmt->close();
                }
            }
        } 
        else {
            if ($stmt = $mysqli->prepare("DELETE FROM Votes WHERE responseId = ? AND parentId = ? AND user = ?;")) {
                $stmt->bind_param('iis', $rID, $rPID, $_SESSION['user']);
                
                if($stmt->execute()) {
                    $stmt->close();
                    
                    if (($vote == 1 || $vote == 0) && $stmt = $mysqli->prepare("UPDATE Context SET yesVotes=(SELECT COUNT(*) FROM Votes WHERE Votes.responseId = ? AND Votes.parentId = ? AND vote = 1 AND Context.responseId = Votes.responseId AND  Context.parentId = Votes.parentId), noVotes=(SELECT COUNT(*) FROM Votes WHERE Votes.responseId = ? AND Votes.parentId = ? AND vote = 0 AND Context.responseId = Votes.responseId AND  Context.parentId = Votes.parentId) WHERE Context.responseId = ? AND Context.parentId = ?")) {
                        $stmt->bind_param('iiiiii', $rID, $rPID, $rID, $rPID, $rID, $rPID);
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
    $rPID = intval($rPID);
    
    if(($rPID == 0 || responseExists($rPID))&& count($rText) != 0 && $rIsAgree != -1) {
    
        require('db/config.php');
            
        $mysqli = new mysqli($host, $username, $password, $db);                    
        if(count($rText) == 1) {
            
            foreach ($rText as $temp_subpointText) {
                
                if ($stmt = $mysqli->prepare("INSERT INTO Responses (responseText, user) VALUES (?,?);")) {
                    $stmt->bind_param('ss', $temp_subpointText, $_SESSION['user']);
                    
                    if($stmt->execute()) {
                        $newRID = $stmt->insert_id;
                        $stmt->close();
                        
                        if ($stmt = $mysqli->prepare("INSERT INTO Context (responseId, isAgree, parentId, user) VALUES (?,?,?,?);")) {
                            $stmt->bind_param('iiis', $newRID, $rIsAgree, $rPID, $_SESSION['user']);
                    
                            $stmt->execute();
                        }
                    }
                    
                    $stmt->close();
                }
            }
        } elseif(count($rText) > 1) {
        
            if ($stmt = $mysqli->prepare("INSERT INTO Responses (user) VALUES (?);")) {
                $stmt->bind_param('s', $_SESSION['user']);
                
                if($stmt->execute()) {
                    $newRID = $stmt->insert_id;
                    $stmt->close();
                    
                    if ($stmt = $mysqli->prepare("INSERT INTO Context (responseId, isAgree, parentId, user) VALUES (?,?,?,?);")) {
                        $stmt->bind_param('iiis', $newRID, $rIsAgree, $rPID, $_SESSION['user']);
                
                        $stmt->execute();
                        $stmt->close();
                    }
                    
                    if ($stmt = $mysqli->prepare("INSERT INTO Responses (responseText, user) VALUES (?,?);")) {
                
                        foreach ($rText as &$temp_subpointText) {
                            $stmt->bind_param('ss', $temp_subpointText, $_SESSION['user']);
                            
                            if($stmt->execute()) {
                                $newSID = $stmt->insert_id;
                                
                                require('db/config.php');
                
                                $mysqli2 = new mysqli($host, $username, $password, $db);
                                
                                if ($stmt2 = $mysqli2->prepare("INSERT INTO ResponseSubpoints (responseId,subpointId) VALUES (?,?);")) {
                                    $stmt2->bind_param('ii', $newRID,$newSID);
                                    $stmt2->execute();
                                    $stmt2->close();
                                }
                                
                            }
                        }
                        
                    }
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

function outputForm($respID, $aIds, $button1Text, $button2Text) {
    if(isset($_SESSION['user'])) {
        print("        
        <form id=\"responseForm\" action=\"index.php?rId=$respID".ancestorStringNonZero($aIds)."\" method=\"post\">
        
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
                
                print("<p id=\"SubmitResponse".$button2Text."Button\" class=\"".$button2Text."Button argumentSubmitButton\">".$button2Text."</p>
                <p id=\"SearchPreviousResponsesButton\" class=\"argumentSubmitButton\">Search Previous Responses</p>");
                
                if($button1Text == "Support") {
                    print("<p id=\"AddAnotherSubpointButton\" class=\"argumentSubmitButton\">Add another subpoint input box</p>");
                }
                
                print("</div>
        </form>");
    }
    else {
         print("<p class=\"responseFormPlaceholder\">Login to add a response</p>");
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
    
}

function validParent($first, $second) {
    $tempValid = false;
    
    require('db/config.php');
    
    $mysqli = new mysqli($host, $username, $password, $db);                    
    if ($stmt = $mysqli->prepare("SELECT parentId FROM Context WHERE responseId = ?;")) {
        $stmt->bind_param('i', $second);
        $stmt->execute();
        $stmt->bind_result($parentId);
        
        while($stmt->fetch()) {
            if($parentId == $first) {
                $tempValid = true;
            }
        }
        
        $stmt->close();
    }
    
    //Close the database connection
    $mysqli->close();
    
    return $tempValid;
}

function validAncestors($aIds, $rId) {
    
    $valid = true;
    
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
    $rId = intval($rId);
    
    if($rId != 0) {
        if(isset($_GET["aIds"])) {
            $aIds = $_GET["aIds"];
            
            foreach ($aIds as &$temp_aId) {
                $temp_aId = strip_tags($temp_aId);
                $temp_aId = trim($temp_aId);
                $temp_aId = intval($temp_aId);
            }
        
            unset($temp_aId);
            
            if(!validAncestors($aIds, $rId)) {
                $rId = 0;
            }
        } else{
            $rId = 0;
        }
    }
}

require('CurrentArgument.php');
$currentArgument = new CurrentArgument($rId, $aIds[count($aIds)-1]);
?>

<html>
<head> 
    <link href="styles/stylesheets/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
    <link href="styles/stylesheets/print.css" media="print" rel="stylesheet" type="text/css" />
    <!--[if IE]>
        <link href="styles/stylesheets/ie.css" media="screen, projection" rel="stylesheet" type="text/css" />
    <![endif]-->
    
    <title>The Public Spheres</title>    
    <script src="http://code.jquery.com/jquery-1.8.0.min.js"></script>
    <script src="colorConverter.js"></script>
    <script src="thePublicSpheres.js"></script>
</head>
<body>

<div id="greyOverlay" onClick="closeTop();"> 
</div>

<div id="SearchPreviousResponsesBox"> 
	<h1>Search Previous Responses</h1>
    
    <!--<form action="index.php" method="POST">-->
        <p>
            <input type="text" name="searchPreviousResponsesQuery" id="searchPreviousResponsesQuery" size="60">
            <div id="searchResponses">
            </div>
            
            <?php
            
            print("<form id=\"searchPreviousResponseForm\" action=\"index.php?rId=".$rId."".ancestorStringNonZero($aIds)."\" method=\"post\">
        
            <input type=\"hidden\" id=\"searchPreviousResponsePID\" name=\"searchPreviousResponsePID\" value=\"".$rId."\" />");
            
            ?>

            <input type="hidden" id="searchPreviousResponseRID" name="searchPreviousResponseRID" value="-1">
            <input type="hidden" id="searchPreviousResponseIsAgree" name="searchPreviousResponseIsAgree" value="-1">
            
                <div class="formButtonsSPR">
                <?php
                    if($currentArgument->getArgumentIsAgree() == 3 || $rId == 0) {
                        print("<p id=\"SearchPreviousResponsesCategoryButton\" class=\"CategoryButton argumentSubmitButton\">Category</p>
                        <p id=\"SearchPreviousResponsesDiscussionButton\" class=\"DiscussionButton argumentSubmitButton\">Discussion</p>");
                    } else {
                        print("<p id=\"SearchPreviousResponsesSupportButton\" class=\"SupportButton argumentSubmitButton\">Support</p>
                        <p id=\"SearchPreviousResponsesNeutralButton\" class=\"NeutralButton argumentSubmitButton\">Neutral</p>
                        <p id=\"SearchPreviousResponsesOpposeButton\" class=\"OpposeButton argumentSubmitButton\">Oppose</p>");
                    }      
                ?>
                </div>
            
            </form>
        </p>
    <!--</form>-->
    
	<p>
	<img src="closeButton2.png" class="closeButton" />
	</p>
</div>

<div id="loginRegisterBox"> 
	<p>
	<img src="closeButton2.png" class="closeButton" />
	</p>
    
    <div>
        <h1>Register</h1>
        <p>
            <?php
            print("<form action=\"index.php?rId=$rId".ancestorStringNonZero($aIds)."\" method=\"post\">");
            ?>
                <input type="hidden" name="op" value="new">
                Username:<br>
                <input type="text" name="user" size="60"><br>
                Password:<br>
                <input type="password" name="pass" size="60"><br>
                <input type="submit" value="Create user">
            </form>
        </p>
    </div>
    
    <div>
        <h1>Login</h1>
        <p>
            <?php
            print("<form action=\"index.php?rId=$rId".ancestorStringNonZero($aIds)."\" method=\"post\">");
            ?>
                <input type="hidden" name="op" value="login">
                Username:<br>
                <input type="text" name="user" size="60"><br>
                Password:<br>
                <input type="password" name="pass" size="60"><br>
                <input type="submit" value="Log in">
            </form>
        </p>
    </div>

    <div>
        <h1>Change password</h1>
        <p>
            <?php
            print("<form action=\"index.php?rId=$rId".ancestorStringNonZero($aIds)."\" method=\"post\">");
            ?>
                <input type="hidden" name="op" value="change">
                Username:<br>
                <input type="text" name="user" size="60"><br>
                Current password:<br>
                <input type="password" name="pass" size="60"><br>
                New password:<br>
                <input type="password" name="newpass" size="60"><br>
                <input type="submit" value="Change password">
            </form>
        </p>
    </div>
</div>

<?php
if($rId != 0) {
?>

<div id="mainCircleSize">
    <div class="circle circleSize" onclick="goToRID(this, event, 0 ,'');">
        <h2 class="statement statementSize" onclick="goToRID(this, event, 0, '');">The Public Spheres: Ideas Taking Shape</h2>
        

    <?php
        if(isset($_SESSION['user'])) {
            print ("<p id=\"user\">Welcome, ".htmlspecialchars($_SESSION['user'])."</p>
                <form id=\"logoutForm\" action=\"index.php?rId=$rId".ancestorStringNonZero($aIds)."\" method=\"post\">
                    <p id=\"logoutLink\">Logout <input type=\"hidden\" name=\"op\" value=\"logout\"></p>
                </form>");
        }
        else {
            print ("<p id=\"loginRegisterLink\">Login/Register</p>");
        }
    
        $parentsOutputText = ""; 
        $hasParents = true;
        
        require('db/config.php');
        
        $mysqli = new mysqli($host, $username, $password, $db);

        if($rId != 0) 
        {        
            $temp_aIds = array();
            $lastAId = 0;
            
            foreach ($aIds as $aId) {
                if ($stmt = $mysqli->prepare("SELECT r.responseText, c.isAgree FROM Responses r, (SELECT responseId, isAgree FROM Context WHERE responseId = ? AND parentId = ?) c WHERE c.responseId = r.responseId;")) {
                    $stmt->bind_param('ii', $aId, $lastAId);
                    $stmt->execute();
                    $stmt->bind_result($parentText, $parentIsAgree);
                    
                    if($stmt->fetch()) {
                        $anotherCircle = "<div class=\"circle circleSize";
                        
                        if($parentIsAgree == 1) {
                            $anotherCircle = $anotherCircle . " supportCircle";
                        } elseif($parentIsAgree == 0){
                            $anotherCircle = $anotherCircle . " opposeCircle";
                        }
                        
                        $anotherCircle = $anotherCircle . "\" onclick=\"goToRID(this, event, $aId ,'".ancestorString($temp_aIds)."');\"><h2 class=\"statement statementSize";
                        
                        if($parentIsAgree == 1) {
                            $anotherCircle = $anotherCircle . " supportCircleTitle";
                        } elseif($parentIsAgree == 0){
                            $anotherCircle = $anotherCircle . " opposeCircleTitle";
                        }
                        
                        $anotherCircle = $anotherCircle ."\" onclick=\"goToRID(this, event, $aId,'".ancestorString($temp_aIds)."');\">";

                        if(is_null($parentText)) {
                
                            require('db/config.php');

                            $mysqli3 = new mysqli($host, $username, $password, $db);
                            
                            if ($stmt3 = $mysqli3->prepare("SELECT r.responseText, FROM Responses r, ResponseSubpoints rs WHERE rs.responseId = ? AND rs.subpointId = r.responseId;")) {
                                $stmt3->bind_param('i', $responseID);
                                $stmt3->execute();
                                $stmt3->bind_result($subpointText);
                            
                                $temp = 0;
                                
                                while($stmt3->fetch()) {
           
                                    if($temp == 0) {
                                        $anotherCircle = $anotherCircle .str_replace('\\', "", $subpointText);
                                        $temp = 1;
                                    }
                                    else {
                                        $anotherCircle = $anotherCircle . " <br/><span class=\"subpointArgumentLine\" > </span><br/> "  . $subpointText;
                                    }
                                    
                                }
                                    
                                $stmt3->close();
                            }
                            
                            $mysqli3->close();
                            
                        } else {
                            $anotherCircle = $anotherCircle .str_replace('\\', "", $parentText);
                        }
                        
                        
                        $anotherCircle = $anotherCircle ."</h2>";
                        
                        $tempAID = $aId."";
                        array_push($temp_aIds, $tempAID);
                        
                        $parentLabel = "";
                        
                        if($parentIsAgree == 1) {
                            $parentLabel = "<p class=\"supportLabel\">Support</p>";
                        } elseif($parentIsAgree == 0){
                            $parentLabel = "<p class=\"opposeLabel\">Oppose</p>";
                        } elseif($parentIsAgree == 2) {
                            $hasParents = false;
                        }
                        $parentsOutputText = $parentsOutputText. $anotherCircle . $parentLabel;
                    } else {
                        $hasParents = false;
                    }
                    
                    $stmt->close();
                } else {
                    $hasParents = false;
                }
                
                $lastAId = $aId;
            }
            
            //Close the database connection
            $mysqli->close();
            
            print("$parentsOutputText");
        }
        
        print("<div id=\"innerCircle\" class=\"circle circleSize");
        
        if($currentArgument->getArgumentIsAgree() == 1) {
            print(" supportCircle");
        } elseif($currentArgument->getArgumentIsAgree() == 0){
            print(" opposeCircle");
        }
        
        print("\"><h2 class=\"statement statementSize");
        
        if($currentArgument->getArgumentIsAgree() == 1) {
            print(" supportCircleTitle");
        } elseif($currentArgument->getArgumentIsAgree() == 0){
            print(" opposeCircleTitle");
        }
        
        print("\">");
        
        $curArgOutput = "";
        $temp = 0;
        
        foreach ($currentArgument->getArgumentSubpoints() as $subpoint) {
            if($temp == 0) {
                $curArgOutput = $curArgOutput . $subpoint;
                $temp = 1;
            }
            else {
                $curArgOutput = $curArgOutput . " <br/><span class=\"subpointArgumentLine\" > </span><br/> "  . $subpoint;
            }
        }
        
        print($curArgOutput);
    
        print("</h2>");
                
        if($currentArgument->getArgumentIsAgree() == 1) {
            print("<p class=\"supportLabel\">Support</p>");
        } elseif($currentArgument->getArgumentIsAgree() == 0){
            print("<p class=\"opposeLabel\">Oppose</p>");
        }
        
        if($currentArgument->getArgumentIsAgree() == 3) {
            outputCategoryContents($rId, $aIds);
        } else {
            outputDiscussionContents($rId, $aIds);
        }
        
    ?>
    
    </div>    
</div>

<?php
    } else {
?>

<div id="mainCircleSize">
<div id="innerCircle" class="circle circleSize">
    <h2 class="statement statementSize">The Public Spheres: Ideas Taking Shape</h2>
    <?php
        if(isset($_SESSION['user'])) {
            print ("<p id=\"user\">Welcome, ".htmlspecialchars($_SESSION['user'])."</p>
                <form id=\"logoutForm\" action=\"index.php?rId=$rId".ancestorStringNonZero($aIds)."\" method=\"post\">
                    <p id=\"logoutLink\">Logout <input type=\"hidden\" name=\"op\" value=\"logout\"></p>
                </form>");
        }
        else {
            print ("<p id=\"loginRegisterLink\">Login/Register</p>");
        }
        outputCategoryContents($rId, array());
    ?>
    
    </div>
</div>

<?php        
    }

?>

</body>
</html>