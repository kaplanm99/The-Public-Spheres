<?php
/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

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

function arrayPHPToJS($arr, $curRID) {
    $jsArr = "";
    
    foreach ($arr as $val) {
        $jsArr = $jsArr . "&aIds[]=" . $val;
    }
    
    $jsArr = $jsArr . "&aIds[]=" . $curRID;
    
    return $jsArr;
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

function agreeDisagreeRatio($responseID) {
    require('db/config.php');
    $mysqli2 = new mysqli($host, $username, $password, $db);
        
    if ($stmt2 = $mysqli2->prepare("SELECT COUNT(c.responseId) FROM Context c WHERE c.parentId = ? AND c.isAgree = 1;")) {
        $stmt2->bind_param('i', $responseID);
        $stmt2->execute();
        $stmt2->bind_result($agrCount);
        
        $stmt2->fetch();
    }

    $stmt2->close();
    
    if ($stmt2 = $mysqli2->prepare("SELECT COUNT(c.responseId) FROM Context c WHERE c.parentId = ? AND c.isAgree = 0;")) {
        $stmt2->bind_param('i', $responseID);
        $stmt2->execute();
        $stmt2->bind_result($disagrCount);
        
        $stmt2->fetch();
    }

    $stmt2->close();
    
    if($disagrCount == 0) {
        if($agrCount == 0) {
            $ratio = 1;
        } else {
            $ratio = 100;
        }
    }
    else {
        $ratio = $agrCount/$disagrCount;
    }
    
    $mysqli2->close();
    
    return $ratio;
}

function outputResponses($type, $typeIsAgree, $respID, $aIds) {
    print("    
    <div class=\"".$type."CircleColumn circleColumnSize\">
        <h3 class=\"".$type."Title titleSize\">".$type."</h3>
        <div class=\"responses responsesSize\">");
                
    require('db/config.php');

    $mysqli = new mysqli($host, $username, $password, $db);
    
    if ($stmt = $mysqli->prepare("SELECT r.responseId, r.responseText FROM Responses r, (SELECT responseId FROM Context WHERE parentId = ? AND isAgree = ?) c WHERE c.responseId = r.responseId;")) {
        $stmt->bind_param('ii', $respID, $typeIsAgree);
        $stmt->execute();
        $stmt->bind_result($responseID, $responseText);
        
        // fetch values
        while ($stmt->fetch()) {
            if($respID == 0) {
                print("<p id=\"$responseID\" onclick=\"goToRID(this, event, $responseID ,'&aIds[]=0');\">$responseText id_$responseID</p>");
            } else {
                print("<p id=\"$responseID\" onclick=\"goToRID(this, event, $responseID ,'".arrayPHPToJS($aIds,$respID)."');\">$responseText id_$responseID</p>");
            }
        
            if($typeIsAgree == 0 || $typeIsAgree == 1 || $typeIsAgree == 2) {
                $ratio = agreeDisagreeRatio($responseID);
                
                print("<script type=\"text/javascript\">$(document).ready(function(){changeBGC(document.getElementById(\"$responseID\"), $ratio, $typeIsAgree);});</script>");
            }
        }
        
        $stmt->close();
    }
    
    $mysqli->close();
    
    print("        
        </div>
    </div>");
}

function outputDiscussionContents($respID, $aIds) {

    print("<div class=\"circleResponses circleResponsesSize\">");

    outputResponses("Agree", 1, $respID, $aIds);
                                
    print("        
    <div class=\"dividingLine dividingLineSize\"></div>
    ");
    
    outputResponses("Disagree", 0, $respID, $aIds);
    
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

    outputResponses("Categories", 3, $respID, $aIds);
                                
    print("
    <div class=\"dividingLine dividingLineSize\"></div>");
        
    outputResponses("Discussions", 2, $respID, $aIds);
    
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
    <link rel="stylesheet" type="text/css" href="style.css" />     
    <title></title>    
    <script src="http://code.jquery.com/jquery-1.8.0.min.js"></script>
    <script src="colorConverter.js"></script>
    <script src="thePublicSpheres.js"></script>
</head>
<body>

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
        <h2 class="statement statementSize" onclick="goToRID(this, event, 0, '');">Public Spheres: Ideas Taking Shape</h2>

    <?php
        $parentsOutputText = ""; 
        $hasParents = true;
        
        require('db/config.php');
        
        $mysqli = new mysqli($host, $username, $password, $db);

        if($rId != 0) 
        {        
            $temp_aIds = array();
            foreach ($aIds as $aId) {
                if ($stmt = $mysqli->prepare("SELECT r.responseText, c.isAgree FROM Responses r, (SELECT responseId, isAgree FROM Context WHERE responseId = ?) c WHERE c.responseId = r.responseId;")) {
                    $stmt->bind_param('i', $aId);
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
            }
            
            //Close the database connection
            $mysqli->close();
            
            print("$parentsOutputText");
        }
        
        require('db/config.php');
        
        $mysqli = new mysqli($host, $username, $password, $db);                    
        if ($stmt = $mysqli->prepare("SELECT r.responseText, c.isAgree FROM Responses r, (SELECT responseId, isAgree FROM Context WHERE responseId = ?) c WHERE c.responseId = r.responseId;")) {
            $stmt->bind_param('i', $rId);
            $stmt->execute();
            $stmt->bind_result($statementText, $statementIsAgree);
            
            // fetch values
            if($stmt->fetch()) {
                print("<div id=\"innerCircle\" class=\"circle circleSize\"><h2 class=\"statement statementSize\">$statementText</h2>");
                
                if($statementIsAgree == 1) {
                    print("<p class=\"agreeLabel\">Agree</p>");
                } elseif($statementIsAgree == 0){
                    print("<p class=\"disagreeLabel\">Disagree</p>");
                }
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
    <h2 class="statement statementSize">Public Spheres: Ideas Taking Shape</h2>

    <?php
        outputCategoryContents($rId, array());
    ?>
    
    </div>
</div>

<?php        
    }

?>

</body>
</html>