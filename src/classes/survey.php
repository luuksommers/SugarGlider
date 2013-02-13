<?php
/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/

require_once( "databaseobject.php" );
require_once( "logger.php" );
require_once( "question.php" );
require_once( "vote.php" );
require_once( "formcontrol.php" );
require_once( "lang/lang-en.php" );

/*******************************************************************************
*
*                               M A I N  C O D E
*
*******************************************************************************/

/*! 
 *  \brief Class handling all survey database functions
 */
class Survey extends DatabaseObject
{
	// Database column vars
	public $title            = null;
	public $description      = null;
	public $asklogin         = null;
	public $askinfo          = null;
	public $questionsperpage = null;

	// When a list is retrieved it will be stored in $list
	public $list        	 = null;
	
	// List of all questions in this survey
	public $questions   	 = null;
	
	// Page information
	public $current_page     = 1;

	/*! 
	 *  \brief Creates a new survey object
	 *  \param $surveyid 	if not null, the given id will be retrieved from the database
	 *  \param $title 		if not null, a new survey will be inserted in the database
	 */
	function __construct( $surveyid = null, $title = null )
	{
		parent::__construct( DB_PREFIX . "survey", "id" );
		
		// Get excisting Poll
		if( $surveyid != null )
		{
			$this->id          = $surveyid;
			$this->SetSurveyInfo();
			$this->questions   = $this->GetQuestions();
		}

		// Fetch List of surveys
		else
		{
			$this->sql->query( "SELECT id FROM $this->table_name ORDER BY id DESC" );
			if( $this->sql->NumRows() )
			{
				$this->list = array();
				while( $row = $this->sql->FetchRow() )
				{
					$this->list[] = new Survey( $row[ 0 ] );
				}
			}
			
			//$this->sql->FreeResult();
		}
	}
	
	
	/*! 
	 *  \brief Saves all available information for this survey
	 */
	public function SaveAll()
	{
		if( $this->id != null )
		{
			$this->Update( "title='".$this->title."', description='".$this->description."', asklogin=".$this->asklogin.", askinfo=".$this->askinfo.", questionsperpage=".$this->questionsperpage."" );
		}
		else
		{
			$this->Insert( "title, description,asklogin,askinfo,questionsperpage", "'".$this->title."','".$this->description."',".$this->asklogin.",".$this->askinfo.",".$this->questionsperpage );
		}
	}
	
	
	/*! 
	 *  \brief Retrieves all information about this survey
	 *
	 * Retrieves all information about this survey and stores it in it's local variables
	 */
	private function SetSurveyInfo()
	{
		if( $this->id != null )
		{
			$this->sql->Query( "SELECT title, description, asklogin, askinfo, questionsperpage FROM $this->table_name WHERE $this->id_col = $this->id" );
			if( $this->sql->NumRows() )
			{
				$row = $this->sql->FetchRow();
				
				$this->title            = $row[ 0 ];
				$this->description      = $row[ 1 ];
				$this->asklogin         = $row[ 2 ];
				$this->askinfo          = $row[ 3 ];
				$this->questionsperpage = $row[ 4 ];
			}
			$this->sql->FreeResult();
		}
	}
	
	
	/*! 
	 *  \brief Retrieves all questions of this survey
	 *
	 * Retrieves all questions of this survey and stores it in it's local variables
	 */
	public function GetQuestions()
	{
		// Retrieve questions for this survey
		$this->sql->Query( "SELECT id FROM " . DB_PREFIX . "question WHERE surveyid = $this->id ORDER BY Id" );

		if( $this->sql->NumRows() )
		{
			$questions = array();
			while( $row = $this->sql->FetchRow() )
			{
				$questions[] = new Question( $row[ 0 ] );
			}
		}
		
		$this->sql->FreeResult();
		
		return( $questions );
	}
	
	
	/*! 
	 *  \brief Deletes the question and linked answers
	 *
	 * Removes all answers and the question
	 */
	public function Remove()
	{
		Logger::Write( "~~Survey::Remove()" );
		if( $this->questions != null )
		{
			foreach( $this->questions as $question )
			{
				$question->Remove();
			}
		}
		$this->Del();
	}
	
	
	/*! 
	 *  \brief Calculates the total number of pages to go through
	 */
	public function GetTotalPages()
	{
		if( $this->questionsperpage > 0 )
		{
			$tot_questions = count( $this->questions );
			$pages = $tot_questions / $this->questionsperpage;
			
			// Check if there is a rest number
			if( $tot_questions % $this->questionsperpage )
				$pages++;
		}
		else
		{
			$pages = 1;	
		}
		
		return( $pages );
	}
	
	/*! 
	 *  \brief Returns the current page number
	 */
	public function GetCurrentPageNumber()
	{
		return( $this->current_page );
	}
	
	
	/*! 
	 *  \brief Returns the current page questions
	 */
	public function GetPageQuestions()
	{
		Logger::Write( "~~Survey::GetCurrQuestions()" );
		if( $this->questions != null )
		{
			if( $this->questionsperpage > 0 )
			{
				$start_index = $_SESSION[ "startindex" ];
				return( array_slice( $this->questions, $start_index, $this->questionsperpage ) );
			}
			else
			{
				return( $this->questions );
			}
		}
		
		return( null );
	}
	
	
	/*! 
	 *  \brief Sets question index to next page
	 */
	public function GotoNextPage()
	{
		Logger::Write( "~~Survey::GotoNextPage()" );
		if( $this->questions != null )
		{
			// Set Start Index
			$start_index = $_SESSION[ "startindex" ];
			if( ( $start_index + $this->questionsperpage ) < count( $this->questions ) )
			{
				$start_index += $this->questionsperpage;
			}
			
			Logger::Write( "GotoNextPage: $start_index" );
			
			$_SESSION[ "startindex" ] = $start_index;
			
			$this->current_page++;
		}
	}
	
	/*! 
	 *  \brief Finishes the survey by storing all results in the database
	 */
	public function Finish( $sessionid )
	{
		Logger::Write( "~~Survey::Finish($sessionid)" );
		if( $this->questions != null )
		{
			foreach( $this->questions as $question )
			{
				if( $question->questiontypeid == 1 )
				{
					$vote = new Vote();
					$vote->sessionid  = $sessionid;
					$vote->surveyid   = $this->id;
					$vote->questionid = $question->id;
					$vote->answerid   = $question->answer;
					$vote->comment    = $question->comment;
					$vote->SaveAll();
				}
				else if( $question->questiontypeid == 2 )
				{
					if( $question->answers != null )
					{
						foreach( $question->answers as $answer )
						{
							$vote = new Vote();
							$vote->sessionid  = $sessionid;
							$vote->surveyid   = $this->id;
							$vote->questionid = $question->id;
							$vote->answerid   = $answer->id;
							$vote->rateid     = $answer->rate;
							$vote->comment    = $question->comment;
							$vote->SaveAll();
							
							$question->comment = "";
						}
					}
				}
				else if( $question->questiontypeid == 3 )
				{
					// answer holds the given answer objects
					if( $question->answer != null )
					{
						foreach( $question->answer as $answerid )
						{
							$vote = new Vote();
							$vote->sessionid  = $sessionid;
							$vote->surveyid   = $this->id;
							$vote->questionid = $question->id;
							$vote->answerid   = $answerid;
							$vote->comment    = $question->comment;
							$vote->SaveAll();
							
							$question->comment = "";
						}
					}
				}
				else if( $question->questiontypeid == 4 )
				{
					$vote = new Vote();
					$vote->sessionid  = $sessionid;
					$vote->surveyid   = $this->id;
					$vote->questionid = $question->id;
					$vote->answertext = $question->answer;
					$vote->comment    = $question->comment;
					$vote->SaveAll();
				}
				else if( $question->questiontypeid == 5 )
				{
					if( $question->answer != null )
					{
						foreach( $question->answers as $answer )
						{
							$vote = new Vote();
							$vote->sessionid  = $sessionid;
							$vote->surveyid   = $this->id;
							$vote->questionid = $question->id;
							$vote->answerid   = $answer->id;
							$vote->answertext = $answer->rate;
							$vote->comment    = $question->comment;
							$vote->SaveAll();
							
							$question->comment = "";
						}
					}
				}
			}
		}
		
		setcookie( "nightflowerorg:s$this->id", true, time()+60*60*24*30 );
	}
	
	/*! 
	 *  \brief Verifies cookie if this survey has already been done
	 */
	public function AlreadyDone()
	{
		if( TEST )
			return( false );

		return( $_COOKIE["nightflowerorg:s$this->id"] );
	}
	
	/*! 
	 *  \brief Builds a list of edit controls of this object
	 */
	public function GetEditControls()
	{
		$controls   = array();
		$controls[] = new EditControl( "surveytitle$this->id"		, LOC_TITLE				, TYPE_TEXTBOX		, $this->title				, null		, 250);
		$controls[] = new EditControl( "surveydescription$this->id"	, LOC_DESCRIPTION		, TYPE_MULTILINEBOX	, $this->description		, $qt->list	, 2048);
		$controls[] = new EditControl( "surveyaskinfo$this->id"		, LOC_ASKINFO			, TYPE_CHECKBOX		, $this->askinfo			, null		, 0);
		$controls[] = new EditControl( "surveyasklogin$this->id"	, LOC_ASKLOGIN			, TYPE_CHECKBOX		, $this->asklogin			, null		, 0);
		$controls[] = new EditControl( "surveyqperpage$this->id"	, LOC_QUESTIONSPERPAGE	, TYPE_TEXTBOX		, $this->questionsperpage	, null		, 1);
		
		return( $controls );
	}

	public function SaveEditControls( $formData )
	{
		Logger::Write( "~~Survey::SaveEditControls()" );
		$this->title        = htmlentities( $formData["surveytitle$this->id"] );
		$this->description  = nl2br( htmlentities( $formData["surveydescription$this->id"] ) );
		
		if( isset( $formData["surveyaskinfo$this->id"] ) && $formData["surveyaskinfo$this->id"] == true )
			$this->askinfo = 1;
		else
			$this->askinfo = 0;
		
		if( isset( $formData["surveyasklogin$this->id"] ) && $formData["surveyasklogin$this->id"] == true )
			$this->asklogin = 1;
		else
			$this->asklogin = 0;
		
		$this->questionsperpage  = htmlentities( $formData["surveyqperpage$this->id"] );
			
		$this->SaveAll();
		Logger::Write( "~~Survey::SaveEditControls()-FINISHED" );
	}

	public function GetDetailObjects()
	{
		$retval = null;
		if( $this->questions != null )
		{
			$retval = array();
			foreach( $this->questions as $question )
			{
				$retval[] = new DetailObject( $question->id, $question->question, 'question' );
			}
		}

		return( $retval );
	}
	
	public function GetObjectTitle()
	{
		return( $this->title );
	}
	
	public function GetParentObject()
	{
		return( null );
	}
	
	public function GetObjectType()
	{
		return( 'survey' );
	}
	
	public function GetDetailObjectType()
	{
		return( 'question' );
	}
}
?>
