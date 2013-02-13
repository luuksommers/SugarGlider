<?php
/*
    Main Survey System
    Copyright (C) 2008  Luuk Sommers

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/

require_once( "inc/config.php" );
require_once( "classes/survey.php" );
require_once( "classes/session.php" );
require_once( "classes/logger.php" );
require_once( "classes/questiongroup.php" );
require_once( "classes/surveyuser.php" );

/*******************************************************************************
*
*                               M A I N  C O D E
*
*******************************************************************************/


if( TEST && isset( $_GET["d"] ) )
{
	session_start();
	session_destroy();
	session_start();
}

function validate_form( $aFormValues, $action )
{
	$objResponse = new xajaxResponse();
	Logger::Write( "aFormValues -> " . print_r($aFormValues, true) );
	
	$session   = $_SESSION[ "session" ];
	$survey      = $_SESSION[ "survey" ];
	$questions   = $survey->GetPageQuestions();
	
	foreach( $questions as $question )
	{
		// CHECK NORMAL QUESTION
    	if( $question->questiontypeid == 1 )
    	{
    		$answer_id = $aFormValues["questionnormal$question->id"];
			if( $answer_id == null || $answer_id == "" )
			{
				Logger::Write( "## NORMAL invalid: $question->id - $answer_id" );
				$objResponse->assign("result$question->id","innerHTML", "Selecteer een optie" );
				$errors++;
			}
			else
			{
				$question->answer  = $answer_id;
				$objResponse->assign("result$question->id","innerHTML", "" );
			}
    	}
    	
    	// CHECK RATE QUESTION
    	else if( $question->questiontypeid == 2 )
    	{
    		foreach( $question->answers as $answer )
    		{
    			$rate_id = $aFormValues["questionrate$question->id-$answer->id"];
				if( $rate_id == null || $rate_id == "" )
				{
					Logger::Write( "## RATE invalid: $question->id - $answer->id" );
					$objResponse->assign("result$question->id-$answer->id","innerHTML", "Selecteer een optie" );
					$errors++;
				}
				else
				{
					$answer->rate = $rate_id;
					$objResponse->assign("result$question->id-$answer->id","innerHTML", "" );
				}
    		}
    	}
    	
    	// CHECK MULTI QUESTION
    	else if( $question->questiontypeid == 3 )	
    	{
    		$selected = 0;
    		$question->answer = array();
    		foreach( $question->answers as $answer )
    		{
    			$answer_id = $aFormValues["questionmulti$question->id-$answer->id"];
				if( $answer_id != null && $answer_id != "" )
				{
					$selected++;
					$question->answer[] = $answer->id;
				}
    		}
			
    		
			if( $selected == 0 )
			{
				$objResponse->assign("result$question->id","innerHTML", "Selecteer minimaal 1 optie" );
				$errors++;
			}
			else
			{
				$objResponse->assign("result$question->id","innerHTML", "" );	
			}
    	}
    	
    	// CHECK OPEN QUESTION
    	else if( $question->questiontypeid == 4 )	
    	{
    		$answer = $aFormValues["questionopen$question->id"];
			//if( $answer == null || $answer == "" )
			//{
			//	Logger::Write( "## OPEN invalid: $question->id - $answer_id" );
			//	$objResponse->assign("result$question->id","innerHTML", "Selecteer een optie" );
			//	$errors++;
			//}
			//else
			{
				$question->answer  = htmlentities( $answer );
				$objResponse->assign("result$question->id","innerHTML", "" );
			}
    	}
    	
    	// CHECK OPEN MULTI QUESTION
    	else if( $question->questiontypeid == 2 )
    	{
    		foreach( $question->answers as $answer )
    		{
    			$rate = $aFormValues["questionopenmulti$question->id-$answer->id"];
				if( $rate_id == null || $rate_id == "" )
				{
					Logger::Write( "## OPEN MULTI invalid: $question->id - $answer->id" );
					$objResponse->assign("result$question->id-$answer->id","innerHTML", "Selecteer een optie" );
					$errors++;
				}
				else
				{
					$answer->rate      = $rate_id;
					$objResponse->assign("result$question->id-$answer->id","innerHTML", "" );
				}
    		}
    	}
    	
    	$question->comment = htmlentities( $aFormValues[ "questiontext$question->id" ] );
	}
	
	if( $errors == 0 )
	{
		if( $action == "next" )
		{
			$objResponse->assign("surveyForm","innerHTML", getSurveyForm( true, $survey ) );
		}
		else if( $action == "finish" )
		{
			$survey->Finish( $session->id );
			$objResponse->assign( "surveyForm","innerHTML", "<h2>Bedankt!</h2>Bedankt voor het invullen van de enquete. Met jouw bijdrage worden de knelpunten verbetert.<BR>" );
		}
	}
	else
	{
		$objResponse->assign("generalError","innerHTML", "Er zijn 1 of meer vragen niet ingevuld" );
	}
	
	$objResponse->script( "window.scrollTo(0, 0);" );

	return $objResponse;
}

function login( $form_values )
{
	$objResponse = new xajaxResponse();
	
	$survey  = $_SESSION[ "survey" ];
	$session = $_SESSION[ "session" ];

	if( $survey->asklogin && $form_values[ "password" ] != "1234" )
	{
		$objResponse->assign( "generalError","innerHTML", "Ongeldig wachtwoord" );
	}
	else if( $form_values[ "firstname" ] == null || 
			 $form_values[ "lastname" ] == null ||
			 $form_values[ "birthdate" ] == null )
	{
		$objResponse->assign( "generalError","innerHTML", "Niet alle gegevens zijn ingevuld" );
	}
	else if( !checkData( $form_values[ "birthdate" ] ) )
	{
		$objResponse->assign( "generalError","innerHTML", "Ongeldige geboortedatum" );
	}
	else
	{
		$su = new SurveyUser( htmlentities( $form_values[ "password" ] ) );
		$su->surveyid   = $survey->id;
		$su->sessionid  = $session->id;
		$su->firstname  = htmlentities( $form_values[ "firstname" ] );
		$su->lastname   = htmlentities( $form_values[ "lastname" ] );
		$su->birthdate  = formatDate( $form_values[ "birthdate" ] );
		$su->externalid = htmlentities( $form_values[ "externalid" ] );
		$su->used		= 0;
		$su->Save();
		
		$_SESSION[ "user" ]  = $su;
		$_SESSION[ "login" ] = true;
		$objResponse->assign( "surveyForm","innerHTML", getSurveyForm( false, $survey ) );
		$objResponse->assign( "generalError","innerHTML", "" );
	}
	
	return $objResponse;
}

function checkData( $mydate ) 
{
    if( !isset( $mydate ) || $mydate == "" )
    {
        return false;
    }
    
    list($dd,$mm,$yy)=explode("-",$mydate);
    if (is_numeric($yy) && is_numeric($mm) && is_numeric($dd))
    {
        return checkdate($mm,$dd,$yy);
    }
    return false;
}

function formatDate( $mydate ) 
{
    list($dd,$mm,$yy) = explode("-",$mydate);
    
	return( "$yy-$mm-$dd" );
}

function buildLoginForm( $survey )
{
	$form_string .= "<form id='login' method='get' action='javascript:void(null);'>\n";
	$form_string .= "<table>\n";
	
	if( $survey->askinfo )
	{
		$form_string .= "<tr><td class='name'>Voornaam *:</td><td><input type='text' id='firstname' name='firstname' size='50' maxlength='50'></td></tr>\n";
		$form_string .= "<tr><td class='name'>Achternaam *:</td><td><input type='text' id='lastname' name='lastname' size='50' maxlength='50'></td></tr>\n";
		$form_string .= "<tr><td class='name'>Geboortedatum (dd-mm-jjjj) *:</td><td><input type='text' id='birthdate' name='birthdate' size='10' maxlength='10'></td></tr>\n";
		$form_string .= "<tr><td class='name'>Afdeling :</td><td><input type='text' id='externalid' name='externalid' size='50' maxlength='50'></td></tr>\n";
	}
	
	if( $survey->asklogin )
	{
		$form_string .= "<tr><td class='name'>Password:</td><td><input type='text' id='password' name='password' size='15' maxlength='15'></td></tr>\n";
	}
	
	$form_string .= "</table>\n";
	$form_string .= "<div class='errorbig' id=\"generalError\"></div><BR>\n";
	$form_string .= "<p align='right'><a href='#' onClick=\"xajax_login(xajax.getFormValues('login'))\">Start &raquo;</a></p>\n";
	$form_string .= "</form>\n";

	return( $form_string );
}

function getSurveyForm( $next, $survey )
{
	$form_string  = "";
	$form_string .= "<H2>$survey->title</H2>\n";
	$form_string .= "<HR>\n";
	
	// Only display login information the first time
	if( !$next && !$_SESSION[ "login" ] && ( $survey->asklogin || $survey->askinfo ) )
	{
		$form_string .= "<P>$survey->description</P>\n";
		$form_string .= "<HR>\n";
		if( !$_SESSION[ "login" ] && ( $survey->asklogin || $survey->askinfo ) )
		{
			$form_string .= buildLoginForm( $survey );
			return $form_string;
		}
	}

	// Goto the next page is next is true~!
	if( $next )
		$survey->GotoNextPage();
		
	$questions = $survey->GetPageQuestions();
	
	$form_string .= "<form id='survey' method='get' action='javascript:void(null);'>\n";
	$form_string .= "<div class='errorbig' id=\"generalError\"></div><BR>\n";
	
	if( $questions != null )
	{
		foreach( $questions as $question )
		{
	    	$form_string .= "<h3>$question->question</h3>\n";

			// DISPLAY NORMAL QUESTION
	    	if( $question->questiontypeid == 1 )
	    	{
	    		if( $question->answers != null )
	    		{
				$first=true;
					foreach( $question->answers as $answer ) 
					{
if( $first == true )
{
$first = false;
}
else
{
$form_string .= "<BR>";
}
						$form_string .= "<input id=\"questionnormal$question->id\" name=\"questionnormal$question->id\" type=\"radio\" value=\"$answer->id\" $checked>$answer->answer\n";
					}
					$form_string .= "<div class='error' id=\"result$question->id\" name=\"result$question->id\"></div><BR>\n";
				}
	    	}
	    	
	    	// DISPLAY RATE QUESTION
	    	else if( $question->questiontypeid == 2 )
	    	{
	    		if( $question->answers != null )
	    		{
		    		// DISPLAY RATE TITLE
		    		$qg = new QuestionGroup( $question->questiongroupid );
		    		$form_string .= "<table><tr><td>&nbsp;</td>\n";
		    		$form_string .= "<td><B>$qg->lowestrate</B></td>\n";
		    		for( $i = 2; $i < $qg->numberofoptions; $i++ )
		    		{
		    			$form_string .= "<td>&nbsp;</td>";
		    		}
		    		$form_string .= "\n<td><B>$qg->highestrate</B></td>\n";
		    		if( $qg->alternativerate != null && $qg->alternativerate != "" )
		    		{
		    			$form_string .= "<td style='width:30px;'>&nbsp;</td>\n";
		    			$form_string .= "<td><B> $qg->alternativerate</B></td>\n";
		    		}
		    		$form_string .= "</tr>\n";
		    		
		    		// DISPLAY RATE ANSWERS
					foreach( $question->answers as $answer ) 
					{ 
						$form_string .= "<tr>\n";
						$form_string .= "<td>$answer->answer</td>\n";
			    		for( $i = 0; $i < $qg->numberofoptions; $i++ )
			    		{
			    			if( $i == 0 )
			    				$form_string .= "<td align='right'>";
			    			else
			    				$form_string .= "<td>";
			    			$form_string .= "<input id=\"questionrate$question->id-$answer->id\" name=\"questionrate$question->id-$answer->id\" type=\"radio\" value=\"$i\"></td>\n";
			    		}
			    		if( $qg->alternativerate != null && $qg->alternativerate != "" )
			    		{
			    			$form_string .= "<td>&nbsp;</td>\n";
			    			$form_string .= "<td align=left'><input name=\"questionrate$question->id-$answer->id\" type=\"radio\" value=\"$qg->numberofoptions\"></td>\n";
			    		}
			    		$form_string .= "<td><div class='error' id=\"result$question->id-$answer->id\" name=\"result$question->id-$answer->id\"></div></td>\n\n";
						$form_string .= "</tr>\n";
					}
					$form_string .= "</table>\n";
				}
	    	}
	    	
	    	// DISPLAY MULTI QUESTION
	    	else if( $question->questiontypeid == 3 )
	    	{
	    		if( $question->answers != null )
	    		{
					foreach( $question->answers as $answer ) 
					{
						$form_string .= "<input name=\"questionmulti$question->id-$answer->id\" type=\"checkbox\" value=\"$answer->id\" $checked>$answer->answer\n";
					}
					$form_string .= "<div class='error' id=\"result$question->id\" name=\"result$question->id\"></div><BR>\n";
				}
	    	}
	    	
	    	// DISPLAY OPEN QUESTION
	    	else if( $question->questiontypeid == 4 )
	    	{
				$form_string .= "<textarea id=\"questionopen$question->id\" name=\"questionopen$question->id\" cols='92' rows='4' maxlength='500'></textarea>\n";
				$form_string .= "<div class='error' id=\"result$question->id\" name=\"result$question->id\"></div><BR>\n";
	    	}
	    	
	    	// DISPLAY OPEN MULTI QUESTION
	    	else if( $question->questiontypeid == 5 )
	    	{
	    		if( $question->answers != null )
	    		{
	    			$form_string .= "<table>\n";

					foreach( $question->answers as $answer ) 
					{
						$form_string .= "<tr>\n";
						$form_string .= "<td>$answer->answer</td>\n";
						$form_string .= "<td><textarea id='description' name='description' cols='92' rows='4' maxlength='500'></textarea></td>\n";
						$form_string .= "</tr>\n";
					}
					$form_string .= "</table>\n";
					$form_string .= "<div class='error' id=\"result$question->id\" name=\"result$question->id\"></div><BR>\n";
				}
	    	}
	    	
	    	// Comment
	    	if( $question->commentflag == true )
	    	{
				$form_string .= "<BR>Commentaar:<BR><textarea id='questiontext$question->id' name='questiontext$question->id' cols='92' rows='4' maxlength='500'></textarea><BR>";
	    	}
		}
		
		$form_string .= "<P align='right'>";
		if( $survey->GetCurrentPageNumber() == $survey->GetTotalPages() )
		{
			$form_string .= "<a href='#' onClick=\"xajax_validate_form(xajax.getFormValues('survey'), 'finish' )\">Ik ben klaar! &raquo;</a>\n";
		}
		else
		{
			$form_string .= "<a href='#' onClick=\"xajax_validate_form(xajax.getFormValues('survey'), 'next' )\">Volgende pagina &raquo;</a>\n";
		}
		
		// Only display pagenumbers if there is something to show
		if( $survey->GetTotalPages() > 1 )
		{
			$form_string .= "<BR><div id='pageNumber'>Page: " . $survey->GetCurrentPageNumber() . " / " . $survey->GetTotalPages() . "</div>";
		}
		$form_string .= "</P>";
	}
	$form_string .= "</form>";
	
	return( $form_string );
}


function register_functions( $xajax )
{
	$xajax->registerFunction( "validate_form" );
	$xajax->registerFunction( "login" );
}

function on_page_load()
{
	// Store this session in the database
	session_start();
	if( !isset( $_SESSION[ "id" ] ) )
	{
		$_SESSION[ "id" ] = session_id();
		
		// Generate new session
		$session = new Session( $_SESSION[ "id" ] );
		$_SESSION[ "session" ] = $session;
		$_SESSION[ "login" ]   = false;
	}
}

function create_menu()
{

}

function create_body()
{
	if(!isset( $_SESSION[ "survey" ] ) )
	{
		if( isset( $_GET[ "survey" ] ) )
		{
			$survey = new Survey( $_GET[ "s" ] );		// Retrieve the first survey
			$_SESSION[ "survey" ] = $survey;
		}
		else
		{
			$survey = new Survey( 4 );		// Retrieve the first survey
			$_SESSION[ "survey" ] = $survey;
		}
	}
	
	if( !isset( $survey ) && isset( $_SESSION[ "survey" ] ) )
	{
		$survey    = $_SESSION[ "survey" ];
	}
	
	if( isset( $survey ) )
	{
		if( $survey->AlreadyDone() )
		{
			$form_data = "Enquete reeds ingevuld!<BR>\n";
		}
		else
		{
			$form_data .= "<div id='surveyForm'>\n";
			$form_data .= getSurveyForm( false, $survey );
			$form_data .= "</div>\n";
		}
	}

	echo( $form_data );
}

include( THEME_PATH . "survey.php" );

?>

