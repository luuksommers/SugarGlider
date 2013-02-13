<?php
/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/

require_once( "inc/config.php" );
require_once( "classes/vote.php" );
require_once( "classes/formcontrol.php" );
require_once( "classes/question.php" );
require_once( "classes/answer.php" );
require_once( "classes/survey.php" );
require_once( "classes/session.php" );
require_once( "classes/surveyuser.php" );

/*******************************************************************************
*
*                               F U N C T I O N S
*
*******************************************************************************/

function register_functions( $xajax )
{
	$xajax->registerFunction( "viewControl" );
	$xajax->registerFunction( "editControl" );
	$xajax->registerFunction( "deleteControl" );
	$xajax->registerFunction( "cancelEdit" );
	$xajax->registerFunction( "saveEdit" );
	$xajax->registerFunction( "addControl" );
}

function viewControl( $class, $id )
{
	if( $class == "survey" )
	{
		$control = new Survey( $id );
	}
	else if( $class == "question" )
	{
		$control = new Question( $id );
	}
	else if( $class == "answer" )
	{
		$control = new Answer( $id );
	}
	
	$formControl = new FormControl( $control );
	$objResponse = new xajaxResponse();
	$objResponse->assign("mainBody","innerHTML", $formControl->BuildViewForm() );
	return $objResponse;
}

function editControl()
{
	$formControl = FormControl::GetCurrentControl();
	
	$objResponse = new xajaxResponse();
	$objResponse->assign("mainBody","innerHTML", $formControl->BuildEditForm() );
	return $objResponse;
}

function saveEdit( $formValues = null )
{
	$formControl = FormControl::GetCurrentControl();
	$formControl->SaveControl( $formValues );
	
	$objResponse = new xajaxResponse();
	$objResponse->assign("mainBody","innerHTML", $formControl->BuildViewForm() );
	return $objResponse;
}

function deleteControl()
{
	$formControl = FormControl::GetCurrentControl();
	$formControl->DeleteControl();
	if( $formControl->parentObject != null )
	{
		return( viewControl( $formControl->parentObject->class, $formControl->parentObject->id ) );
	}
	else
	{
		return( surveyList() );
	}
}

function cancelEdit()
{
	$formControl = FormControl::GetCurrentControl();
	
	$objResponse = new xajaxResponse();
	$objResponse->assign("mainBody","innerHTML", $formControl->BuildViewForm() );
	return $objResponse;
}

function addControl( $controlClass, $parentId = null )
{
	if( $controlClass == "survey" )
	{
		$control = new Survey( $id );
	}
	else if( $controlClass == "question" )
	{
		$control = new Question( $id );
		$control->surveyid = $parentId;
	}
	else if( $controlClass == "answer" )
	{
		$control = new Answer( $id );
		$control->questionid = $parentId;
	}
	
	$formControl = new FormControl( $control );
	$objResponse = new xajaxResponse();
	$objResponse->assign("mainBody","innerHTML", $formControl->BuildEditForm() );
	return $objResponse;
}

/*! 
 *  \brief Generates a list of surveys
 *  \returns The complete survey request form
 */
function surveyList()
{
	$surveys = new Survey();
	
	$form_data .= "<h3>".LOC_SURVEYS."</h3>\n";
	$form_data .= "<HR>\n";
	$form_data .= "<P>\n";
	if( $surveys->list != null )
	{
		$form_data .= "<table cellpadding='5' cellspacing='5'>\n";
		$form_data .= "<tr>\n";
		$form_data .= "<td><B>".LOC_TITLE."</B></td>\n";
		$form_data .= "<td>&nbsp;</td>\n";
		$form_data .= "</tr>\n";
		foreach( $surveys->list as $survey ) 
		{
			$form_data .= "<tr>\n";
			$form_data .= "<td>$survey->title</td>\n";
			$form_data .= "<td><a href='#' onClick=\"xajax_viewControl('survey',$survey->id);\">".LOC_DETAILS."</a>\n";
			$form_data .= "<td><a href='$PHP_SELF?action=statistics&id=$survey->id'>".LOC_RESULTS."</a>\n";
			$form_data .= "</tr>\n";
		}
		$form_data .= "</table>\n";
	}
	else
	{
		$form_data .= "No survey found.<BR>";
	}
	
	$form_data .= "</P>\n";
	$form_data .= "<HR>\n";
	$form_data .= "<P>\n";
	$form_data .= "<a href='#' onClick=\"xajax_addControl('survey');\">".LOC_ADDSURVEY."</a>";
	$form_data .= "</P>\n";
	return( $form_data );
}

function on_page_load()
{
	// Store this session in the database
	session_start();
	if( $_SESSION[ "id" ] == "" )
	{
		session_regenerate_id();
		$_SESSION[ "id" ] = session_id();
		
		// Generate new session
		$session = new Session( $_SESSION[ "id" ] );
		$_SESSION[ "session" ] = $session;
		$_SESSION[ "login" ]   = false;
	}
}

function surveyStats()
{
	if( !isset( $_GET['id'] ) )
	{
		return( "Survey (". htmlentities( $_GET['id'] ) ." not found" );
	}
	
	$survey = new Survey( $_GET['id'] );
	$vote   = new Vote();
	
	$form_data .= "<h1>$survey->title</h1>\n";
	
	// Check if we have something to filter
	if( isset( $_GET[ 'session' ] ) )
	{
		$session = $_GET[ 'session' ];
		$vote->filter .= " AND sessionid = $session";
	}
	if( isset( $_GET[ 'answer' ] ) AND isset( $_GET[ 'question' ] ) )
	{
		$a = $_GET[ 'answer' ];
		$q = $_GET[ 'question' ];
		
		$vote->filter .= " AND sessionid IN (SELECT DISTINCT sessionid FROM $vote->table_name where questionid=$q AND answerid=$a)";
	}
	
	// Display remove filter link
	if( ( isset( $_GET[ 'answer' ] ) AND isset( $_GET[ 'question' ] ) ) OR isset( $_GET[ 'session' ] ) )
	{
		$form_data .= "<h2><a href='$PHP_SELF?action=statistics&id=$survey->id'>FILTERED RESULTS CLICK HERE TO REMOVE THE FILTER</A></h2>\n";
	}

	if( $survey->askinfo )
	{
		$su    = new SurveyUser();
		$users = $su->GetList( $survey->id, $vote->filter );
		//sessionid,firstname,lastname,birthdate,externalid
		if( $users != null )
		{
			$form_data .= "<h2>".LOC_USERINFO."</h2>\n";
			$form_data .= "<table width='75%' border='1'>\n";
			foreach( $users as $user )
			{
				$form_data .= "\t<tr>\n";
				$form_data .= "\t\t<td><a href='$PHP_SELF?action=statistics&id=$survey->id&session=".$user[0]."'>".$user[1]." ". $user[2]. "</a></td>\n";
				$form_data .= "\t\t<td>" . date('d-m-Y', $user[3]) . "</td>\n";
				$form_data .= "\t\t<td>".$user[4]."</td>\n";
				$form_data .= "\t</tr>\n";
			}
			$form_data .= "</table>\n";
		}
	}
	
	if( $survey->questions != null )
	{
		foreach( $survey->questions as $question )
		{
		    $form_data .= "<h2>$question->question</h2>\n";
		    
		    if( $question->commentflag == true )
		    {
		    	$comment_count = $vote->GetCommentCount( $question->id );
		    	$form_data .= "<a href='comment.php?s=1&q=$question->id' target='_blank'>View Comments ($comment_count)</a>";
		    }
		    
		    $form_data .= "<blockquote>\n";
	    	$form_data .= "<table width='75%' border='1'>";
	    	
	    	// NORMAL OR MULTI DISPLAY
		    if( $question->questiontypeid == 1 || $question->questiontypeid == 3 )	
		    {
		    	if( $question->answers != null )
	    		{
		    		$total_votes  = $vote->GetQuestionVotes( $question->id );
					foreach( $question->answers as $answer ) 
					{
						$answer_votes = $vote->GetAnswerVotes( $answer->id );
						
						$per = 0;
						if( $total_votes > 0 )
							$per = ( $answer_votes / $total_votes ) * 100;
						
						if( strlen( $per ) > 4 )
							$per = substr( $per, 0, 5 );
						
						$form_data .= "<TR>\n";
						$form_data .= "<TD WIDTH='30%'><a href='$PHP_SELF?action=statistics&id=$survey->id&question=$question->id&answer=$answer->id'>$answer->answer</a></TD>\n";
						$form_data .= "<TD WIDTH='50%'><img src='/inc/graph.php?per=$per' alt='$per% graph'></TD>\n";
						$form_data .= "<TD WIDTH='20%'>$answer_votes</TD>\n";
						$form_data .= "</TR>\n";
					}
				}
			}
			
			// RATE DISPLAY
			else if( $question->questiontypeid == 2 )
			{
		    	if( $question->answers != null )
	    		{
			    	$qg = new QuestionGroup( $question->questiongroupid );
					foreach( $question->answers as $answer ) 
					{
						$form_data .= "<TR>\n";
						$form_data .= "<TD WIDTH='30%'>$answer->answer</TD>\n";
						
						$answer_votes = $vote->GetAnswerVotes( $answer->id );
						
						$form_data .= "<TD WIDTH='50%'>";
						$form_data .= "$qg->lowestrate<BR>";
			    		for( $i = 0; $i < $qg->numberofoptions; $i++ )
			    		{
							$per = 0;
							if( $answer_votes > 0 )
							{
								$per = ( $vote->GetRateVotes( $answer->id, $i ) / $answer_votes ) * 100;
							}
			    			$form_data .= "<img src='/inc/graph.php?per=$per' alt='$per% graph'><BR>";
			    		}
			    		$form_data .= "$qg->highestrate";
			    		
			    		if( $qg->alternativerate != null && $qg->alternativerate != "" )
			    		{
			    			$form_data .= "<BR><BR>$qg->alternativerate:<BR>";
							if( $answer_votes > 0 )
							{
								$per = ( $vote->GetRateVotes( $answer->id, $qg->numberofoptions ) / $answer_votes ) * 100;
							}
			    			$form_data .= "<img src='/inc/graph.php?per=$per' alt='$per% graph'><BR>";
			    		}
	
						$form_data .= "</TD>\n";
						$form_data .= "<TD WIDTH='20%'>$answer_votes Votes</TD>\n";
						$form_data .= "</TR>\n";
					}
				}
			}
			
	    	// NORMAL OR MULTI DISPLAY
		    else if( $question->questiontypeid == 4 || $question->questiontypeid == 5 )
		    {
				$answertexts = $vote->GetAnswerText( $question->id );
				
				foreach( $answertexts as $answertext )
				{
					$form_data .= "<TR>\n";
					$form_data .= "<TD>$answertext</TD>\n";
					$form_data .= "</TR>\n";
				}
			}
			$form_data .= "</table>\n";
			$form_data .= "</blockquote>\n";
		}
	}
	return( $form_data );
}

function create_menu()
{
	$form_data .= "<table class='main' width='100%'><tr>\n";
	$form_data .= "<td><a href='$PHP_SELF?action=surveys'>Home</a></td>";
	$form_data .= "</tr></table>\n";
	echo( $form_data );
}

function create_body()
{
	if( $_GET['action'] == 'statistics' )
	{
		$form_data = surveyStats();
	}
	else
	{
		$form_data = surveyList();
	}
	
	echo( $form_data );
}

include( THEME_PATH . "admin.php" );

?>