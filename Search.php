<?php
    require('PorterStemmer.php');

    $stopwords = array("", " ", "a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount", "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as", "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");
    
    
    if(isset($_GET["query"])) {    
        $query = strip_tags($_GET["query"]);
        $query = trim($query);
        $query = filter_var($query, FILTER_SANITIZE_STRING);
        $query = '%'.$query.'%';
        
        require('db/config.php');

        try {
            $dbh = new PDO('mysql:host=' . $host . ';dbname=' . $db, $username, $password);
            
            $str1 = 'health';
            $str2 = 'care';
            
            $responseWords = explode(" ", trim($query));
            
            $postings = array();
                
            foreach ($responseWords as $responseWord) {
                $tempResponseWord = strtolower(preg_replace("/[^A-Za-z]/", '', $responseWord));
                
                if (!in_array($tempResponseWord, $stopwords)) {  
                    $stem = PorterStemmer::Stem($tempResponseWord);
                    //print($stem . ", ");
                    
                    $postings[] = $stem;
                }
            }
            
            $paramPlaceholders = "";
            $temp = 0;
            
            foreach ($postings as $postingsValue) {
                if($temp == 0) {
                    $temp = 1;
                    $paramPlaceholders = $paramPlaceholders . " i1.stemText = ? ";
                } else {
                    $paramPlaceholders = $paramPlaceholders . " OR i1.stemText = ? ";
                }
            }
            
            //print $paramPlaceholders;
            
            //$paramPlaceholders = "i1.stemText = ? OR i1.stemText = ?";
            
            
            $stmt = $dbh->prepare("SELECT r.responseId, r.responseText FROM Responses r, (SELECT i1.responseId FROM `InvertedIndex` i1, (SELECT stemText, COUNT(stemText) AS df FROM `InvertedIndex` GROUP BY stemText) i2 WHERE i1.stemText = i2.stemText AND ( " . $paramPlaceholders . " ) GROUP BY i1.`responseId` ORDER BY SUM(i1.tf * (1/i2.df)) DESC LIMIT 10) s WHERE r.responseId = s.responseId;");
            //$stmt->execute(array($str1, $str2));
            
            //print_r ($postings);
            $stmt->execute($postings);
            while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT))
            {
                print("
                <p id=\"" . $row[0] . "\" class=\"searchResponse\">" . $row[1] . "</p>");
            
              //$data = $row[0] . "\t" . $row[1] . "\n";
              //print $data;
            }
            
            $dbh = null;
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
        


        
        //$mysqli = new mysqli($host, $username, $password, $db);
        /*
        $prepareText = "SELECT r.responseText FROM Responses r, (SELECT i1.responseId FROM `InvertedIndex` i1, (SELECT stemText, COUNT(stemText) AS df FROM `InvertedIndex` GROUP BY stemText) i2 WHERE i1.stemText = i2.stemText AND ( ";

        $prepareText = $prepareText . "i1.stemText = ? OR i1.stemText = ?";

        $prepareText = $prepareText . " ) GROUP BY i1.`responseId` ORDER BY SUM(i1.tf * (1/i2.df)) DESC LIMIT 10) s WHERE r.responseId = s.responseId;";
        */
        
        $prepareText = "SELECT r.responseText FROM Responses r, (SELECT i1.responseId FROM `InvertedIndex` i1, (SELECT stemText, COUNT(stemText) AS df FROM `InvertedIndex` GROUP BY stemText) i2 WHERE i1.stemText = i2.stemText AND ( i1.stemText = 'health' OR i1.stemText = 'care' ) GROUP BY i1.`responseId` ORDER BY SUM(i1.tf * (1/i2.df)) DESC LIMIT 10) s WHERE r.responseId = s.responseId;";
        
        if ($stmt = $mysqli->prepare($prepareText)) {
            $stmt->bind_param('ss', "health", "care");
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

    /*
SELECT r.responseText FROM Responses r, (SELECT i1.responseId FROM `InvertedIndex` i1, (SELECT stemText, COUNT(stemText) AS df FROM `InvertedIndex` GROUP BY stemText) i2 WHERE i1.stemText = i2.stemText AND ( i1.stemText = 'health' OR i1.stemText = 'care' ) GROUP BY i1.`responseId` ORDER BY SUM(i1.tf * (1/i2.df)) DESC LIMIT 10) s WHERE r.responseId = s.responseId;
*/

?>
