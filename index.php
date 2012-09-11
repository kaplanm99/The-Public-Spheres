<?php session_start();
/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

if(isset($_POST["op"]) && $_POST["op"] == "logout") {
    unset($_SESSION['user']);
}

 
require('user-man.php'); 

$manage_user_result = manage_user();

//print($manage_user_result);
 
if(isset($_POST["rText"])&&isset($_POST["rIsAgree"])&&isset($_POST["rPID"])) {    
    $rText = strip_tags($_POST["rText"]);
    $rText = trim($rText);
    $rText = filter_var($rText, FILTER_SANITIZE_STRING);
    
    $rIsAgree = strip_tags($_POST["rIsAgree"]);
    $rIsAgree = trim($rIsAgree);
    
    if($rIsAgree == "0" || $rIsAgree == "1" || $rIsAgree == "2" || $rIsAgree == "3") {
        $rIsAgree = intval($rIsAgree);
    } else {
        $rIsAgree = -1;
    }
    
    $rPID = strip_tags($_POST["rPID"]);
    $rPID = trim($rPID);
    $rPID = intval($rPID);
    
    if(($rPID == 0 || responseExists($rPID))&& strlen($rText) != 0 && $rIsAgree != -1) {
    
        $pattern = '/^id_[0-9]+$/';
        
        if(preg_match($pattern, $rText)) {
            $mergeRID = substr($rText, 3);
            $mergeRID = intval($mergeRID);
            
            require('db/config.php');
            
            $mysqli = new mysqli($host, $username, $password, $db);                    
            
            if ($stmt = $mysqli->prepare("INSERT INTO Context (responseId, isAgree, parentId) VALUES (?,?,?);")) {
                $stmt->bind_param('iii', $mergeRID, $rIsAgree, $rPID);
                
                $stmt->execute();
            }
                
            $stmt->close();
                
            $mysqli->close();
        } else {
            require('db/config.php');
            
            $mysqli = new mysqli($host, $username, $password, $db);                    
            
            if ($stmt = $mysqli->prepare("INSERT INTO Responses (responseText) VALUES (?);")) {
                $stmt->bind_param('s', $rText);
                
                if($stmt->execute()) {
                    $newRID = $stmt->insert_id;
                    $stmt->close();
                    if ($stmt = $mysqli->prepare("INSERT INTO Context (responseId, isAgree, parentId) VALUES (?,?,?);")) {
                        $stmt->bind_param('iii', $newRID, $rIsAgree, $rPID);
                
                        $stmt->execute();
                    }
                }
                
                $stmt->close();
            }
                
            $mysqli->close();
        }
    }
}

function responseExists($responseId) {
    $exists = false;
    
    require('db/config.php');
    $mysqli = new mysqli($host, $username, $password, $db);                    
    
    if ($stmt = $mysqli->prepare("SELECT responseText FROM Responses WHERE responseId = ?;")) {
        $stmt->bind_param('i', $responseId);
        $stmt->execute();
        $stmt->bind_result($responseText);
        
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

function outputDiscussionContents($respID, $aIds) {

    print("<div class=\"circleResponses circleResponsesSize\">");

    require('Responses.php');
    $agreeResponses = new Responses("Agree", 1, $respID, $aIds);
    $agreeResponses->generateResponses();
    $agreeResponses->outputResponses();
                                
    print("        
    <div class=\"dividingLine dividingLineSize\"></div>
    ");
    
    $disagreeResponses = new Responses("Disagree", 0, $respID, $aIds);
    $disagreeResponses->generateResponses();
    $disagreeResponses->outputResponses();
    
    print("        
    <form id=\"responseForm\" name=\"input\" action=\"index.php?rId=$respID".ancestorStringNonZero($aIds)."\" method=\"post\">
    
        <textarea id=\"rText\" name=\"rText\" class=\"textbox textboxSize\"></textarea>
        <input type=\"hidden\" id=\"rIsAgree\" name=\"rIsAgree\" value=\"0\" />
        
        <input type=\"hidden\" id=\"rPID\" name=\"rPID\" value=\"$respID\" />
        
        <div class=\"formButtons\">
            <p id=\"agreeButton\">Agree</p>
            <p id=\"disagreeButton\">Disagree</p>
        </div>
    </form>");
    

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
    
    print("        
    <form id=\"responseForm\" name=\"input\" action=\"index.php?rId=$respID".ancestorStringNonZero($aIds)."\" method=\"post\">
    
        <textarea id=\"rText\" name=\"rText\" class=\"textbox textboxSize\"></textarea>
        <input type=\"hidden\" id=\"rIsAgree\" name=\"rIsAgree\" value=\"0\" />
        
            <input type=\"hidden\" id=\"rPID\" name=\"rPID\" value=\"$respID\" />
            
            <div class=\"formButtons\">
                <p id=\"categoryButton\">Category</p>
                <p id=\"discussionButton\">Discussion</p>
            </div>
        </form>
    </div>");    
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

<div id="centerBox"> 
	<h1>Fork</h1>
    <p>To insert this response as a response to another statement, copy the text below and paste it into the textbox of the statement that you want to include this response as a response to.</p>
    <p id="centerBoxID">ID will be shown here</p>
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
            <form action="index.php" method="POST">
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
            <form action="index.php" method="POST">
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
            <form action="index.php" method="POST">
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
} else {
    $rId = 0;
}

if($rId != 0) {
?>

<div id="mainCircleSize">
    <div class="circle circleSize" onclick="goToRID(this, event, 0 ,'');">
        <h2 class="statement statementSize" onclick="goToRID(this, event, 0, '');">The Public Spheres: Ideas Taking Shape</h2>
        

    <?php
        if(isset($_SESSION['user'])) {
            print ("<p id=\"user\">Welcome, ".htmlspecialchars($_SESSION['user'])."</p>
                <form id=\"logoutForm\" action=\"index.php\" method=\"POST\">
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
                        $anotherCircle = "<div class=\"circle circleSize\" onclick=\"goToRID(this, event, $aId ,'".ancestorString($temp_aIds)."');\"><h2 class=\"statement statementSize\" onclick=\"goToRID(this, event, $aId,'".ancestorString($temp_aIds)."');\">$parentText</h2>";
                        
                        $tempAID = $aId."";
                        array_push($temp_aIds, $tempAID);
                        
                        $parentLabel = "";
                        
                        if($parentIsAgree == 1) {
                            $parentLabel = "<p class=\"agreeLabel\">Agree</p>";
                        } elseif($parentIsAgree == 0){
                            $parentLabel = "<p class=\"disagreeLabel\">Disagree</p>";
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
        
        require('db/config.php');
        
        $mysqli = new mysqli($host, $username, $password, $db);                    
        if ($stmt = $mysqli->prepare("SELECT r.responseText, c.isAgree FROM Responses r, (SELECT responseId, isAgree FROM Context WHERE responseId = ? AND parentId = ?) c WHERE c.responseId = r.responseId;")) {
            $stmt->bind_param('ii', $rId, $aIds[count($aIds)-1]);
            $stmt->execute();
            $stmt->bind_result($statementText, $statementIsAgree);
            
            // fetch values
            if($stmt->fetch()) {
                print("<div id=\"innerCircle\" class=\"circle circleSize\"><h2 class=\"statement statementSize\">$statementText</h2>");
                
                //if(count($aIds) > 1) {
                    if($statementIsAgree == 1) {
                        print("<p class=\"agreeLabel\">Agree</p>");
                    } elseif($statementIsAgree == 0){
                        print("<p class=\"disagreeLabel\">Disagree</p>");
                    }
                //}
            }
            
            $stmt->close();
        }
        
        //Close the database connection
        $mysqli->close();
        
        if($statementIsAgree == 3) {
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
                <form id=\"logoutForm\" action=\"index.php\" method=\"POST\">
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