<?php
/* Copyright (c) 2012 Michael Andrew Kaplan
 * See the file license.txt for copying permission. */

require('PorterStemmer.php'); 

function invertedIndexInsert ($responseId, $responseText) {
    
    $stopwords = array("", " ", "a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount", "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as", "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");
    
    $responseWords = explode(" ", trim($responseText));
            
    $postings = array();
        
    foreach ($responseWords as $responseWord) {
        $tempResponseWord = strtolower(preg_replace("/[^A-Za-z]/", '', $responseWord));
        
        if (!in_array($tempResponseWord, $stopwords)) {  
            $stem = PorterStemmer::Stem($tempResponseWord);
            //print($stem . ", ");
            
            if (isset($postings[$stem])) {
                $postings[$stem]++;
            } else {
                $postings[$stem] = 1;
            }
        }
    }
    
    $wordCount = count($postings);
    
    require('db/config.php');

    $mysqliInv = new mysqli($host, $username, $password, $db);
        
    foreach ($postings as $postingsKey => $postingsValue) {
        //print ($postingsKey . " = " . $postingsValue . ", ");
        
        $tf = $postingsValue/$wordCount;
        
        if ($stmtInv = $mysqliInv->prepare("INSERT INTO InvertedIndex (stemText,responseId,tf) VALUES (?,?,?);")) {
            $stmtInv->bind_param('sid', $postingsKey, $responseId, $tf);
            $stmtInv->execute();
            $stmtInv->close();
        }          
    }
    
    $mysqliInv->close();
        
}
        
?>