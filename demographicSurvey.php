<div id="demographicSurvey">
    <p>
	<img src="closeButton2.png" class="closeButton" />
	</p>
    <div style="text-align:left">
        <h2>Demographic Survey</h2>
        <p><br/><br/>Please help our study by answering any of the following questions<br/><br/></p>

        <?php
            print("<form action=\"index.php?rId=$rId".ancestorStringNonZero($aIds)."\" method=\"post\">");
        ?>

        What is your age? <input type="text" name="demographicSurveyAge"><br>
        What is your gender? <input type="text" name="demographicSurveyGender"><br><br>
        
        Please choose your highest level of education you have completed:<br/>
        <input name="demographicSurveyEducation" type="radio" value="1" />Less than High School<br/>
        <input name="demographicSurveyEducation" type="radio" value="2" />High School/GED<br/>
        <input name="demographicSurveyEducation" type="radio" value="3" />Some College<br/>
        <input name="demographicSurveyEducation" type="radio" value="4" />2 Year College Degree<br/>
        <input name="demographicSurveyEducation" type="radio" value="5" />4 Year College Degree<br/>
        <input name="demographicSurveyEducation" type="radio" value="6" />Master's Degree<br/>
        <input name="demographicSurveyEducation" type="radio" value="7" />Doctoral Degree<br/><br>
        
        What is your political party? <input type="text" name="demographicSurveyPoliticalParty"><br><br>
        
        Please choose your level of interest in politics:<br/>
        <input name="demographicSurveyInterestInPolitics" type="radio" value="1" /> Strongly Interested<br/>
        <input name="demographicSurveyInterestInPolitics" type="radio" value="2" /> Mildly Interested<br/>
        <input name="demographicSurveyInterestInPolitics" type="radio" value="3" /> Neither Interested nor Uninterested<br/>
        <input name="demographicSurveyInterestInPolitics" type="radio" value="4" /> Mildly Uninterested<br/>
        <input name="demographicSurveyInterestInPolitics" type="radio" value="5" /> Strongly Uninterested<br/><br>
        
        Please choose the answer that mostly closely matches your opinion on the statement:<br/> 
        "Health Care is a Human Right"<br/>
        <input name="demographicSurveyLikertHealth" type="radio" value="1" /> Strongly Agree<br/>
        <input name="demographicSurveyLikertHealth" type="radio" value="2" /> Mildly Agree<br/>
        <input name="demographicSurveyLikertHealth" type="radio" value="3" /> Undecided<br/>
        <input name="demographicSurveyLikertHealth" type="radio" value="4" /> Mildly Disagree<br/>
        <input name="demographicSurveyLikertHealth" type="radio" value="5" /> Strongly Disagree<br/><br>

        Please explain your opinion on whether Health Care is a Human Right<br/>
        <textarea name="demographicSurveyOpinionHealth" rows="3" cols="80"></textarea><br/>
        
        <input type="submit" value="Submit">
        </form>
    </div>
</div>

<?php

// If the user is a new user, send them to a page to fill out pre-use Demographic Survey Questions

if($manage_user_result == 'User created') {

    print("<script>$(document).ready(function(){showDemographicSurvey();});</script>");

}

?>