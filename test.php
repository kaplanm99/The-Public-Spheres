<?php
    
    if(isset($_GET["query"])) {    
        $query = strip_tags($_GET["query"]);
        $query = trim($query);
        $query = filter_var($query, FILTER_SANITIZE_STRING);
        $query = '%'.$query.'%';
        
        require('db/config.php');
                
        $mysqli = new mysqli($host, $username, $password, $db);

        if ($stmt = $mysqli->prepare("SELECT responseId, responseText FROM Responses WHERE responseText LIKE ? LIMIT 0 , 20;")) {
            $stmt->bind_param('s', $query);
            $stmt->execute();
            $stmt->bind_result($responseId, $responseText);
        
            while($stmt->fetch()) {
                print("
                <p id=\"$responseId\" class=\"searchResponse\">
                $responseText
                </p>");
            }
            
            $stmt->close();
        }
        
        $mysqli->close();
        
    }
?>