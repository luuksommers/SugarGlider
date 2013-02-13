<?php

/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/

require_once( "databaseobject.php" );
require_once( "answer.php" );
require_once( "questiontype.php" );
require_once( "questiongroup.php" );
require_once( "formcontrol.php" );
require_once( "lang/lang-en.php" );

/*******************************************************************************
*
*                               M A I N  C O D E
*
*******************************************************************************/

/*! 
 *  \brief Class handling all question database functions
 */
class Question extends DatabaseObject
{
	// Database column vars
	public $surveyid        = null;
	public $question        = null;
	public $questiontypeid  = null;
	public $questiongroupid = null;
	public $commentflag     = null;
	
	// List of all answers in this survey
	public $answers         = null;
	
	// Holder for the selected answer if applicable
	public $answer			= null;
	public $comment			= null;
	
	
	/*! 
	 *  \brief Creates a new question object
	 *  \param $questionid 	if not null, the given id will be retrieved from the database
	 *  \param $surveyid 	holds surveyid for new question record
	 *  \param $question	holds the question for new question record
	 */
	function __construct( $questionid = null, $surveyid = null, $question = null )
	{
		parent::__construct( DB_PREFIX . "question", "id" );
		
		// Load Excisting
		if( $questionid != null )
		{
			$this->id      = $questionid;
			$this->SetQuestionInfo();
			$this->answers = $this->GetAnswers();
		}
		
		// Create new one
		/*
		else if( $surveyid != null && $question != null )
		{
			$this->surveyid        = $surveyid;
			$this->question        = $question;
			
			$this->Insert( "surveyid, question", "$this->surveyid, '$this->question'" );
		}
		*/
	}
	
	
	/*! 
	 *  \brief Saves all available information for this survey
	 */
	public function SaveAll()
	{
		if( $this->id != null )
		{
			$this->Update( "question='$this->question', questiontypeid=$this->questiontypeid, questiongroupid=$this->questiongroupid, commentflag=$this->commentflag" );
		}
		else
		{
			$this->Insert( "surveyid,question,questiontypeid,questiongroupid,commentflag","$this->surveyid,'$this->question', $this->questiontypeid, $this->questiongroupid, $this->commentflag" );
		}
	}
	
	
	/*! 
	 *  \brief Retrieves all information about this question
	 *
	 * Retrieves all information about this question and stores it in it's local variables
	 */
	private function SetQuestionInfo()
	{
		if( $this->id != null )
		{
			$this->sql->Query( "SELECT surveyid, question, questiontypeid, questiongroupid, commentflag FROM $this->table_name WHERE $this->id_col = $this->id" );
			if( $this->sql->NumRows() )
			{
				$row = $this->sql->FetchRow();
				
				$this->surveyid        = $row[ 0 ];
				$this->question        = $row[ 1 ];
				$this->questiontypeid  = $row[ 2 ];
				$this->questiongroupid = $row[ 3 ];
				$this->commentflag     = $row[ 4 ];
			}
			$this->sql->FreeResult();
		}
	}
	
	/*! 
	 *  \brief Retrieves all answers of this question
	 *
	 * Retrieves all answers of this question and stores it in it's local variables
	 */
	public function GetAnswers()
	{
		// Retrieve Answers from database
		$this->sql->Query( "SELECT id FROM " . DB_PREFIX . "answer WHERE questionid = $this->id ORDER BY Id" );

		if( $this->sql->NumRows() )
		{
			$answers = array();
			while( $row = $this->sql->FetchRow() )
			{
				$answers[] = new Answer( $row[ 0 ] );
			}
		}
		
		$this->sql->FreeResult();
		return( $answers );
	}
	
	/*! 
	 *  \brief Deletes the question and linked answers
	 *
	 * Removes all answers and the question
	 */
	public function Remove()
	{
		Logger::Write( "~~Question::Remove()" );
		if( $this->answers != null )
		{
			foreach( $this->answers as $answer )
			{
				$answer->Del();
			}
		}
		$this->Del();
	}
	
	/*! 
	 *  \brief Builds a list of edit controls of this object
	 */
	public function GetEditControls()
	{
		$qt = new QuestionType();
		$qg = new QuestionGroup();
	
		$controls = array();
		$controls[] = new EditControl( "question$this->id"			, LOC_QUESTION	, TYPE_TEXTBOX		, $this->question			, null		, 250);
		$controls[] = new EditControl( "questiontype$this->id"		, LOC_TYPE		, TYPE_DROPDOWNBOX	, $this->questiontypeid		, $qt->list	, 0);
		$controls[] = new EditControl( "questiongroup$this->id"		, LOC_GROUP		, TYPE_DROPDOWNBOX	, $this->questiongroupid	, $qg->list	, 0);
		$controls[] = new EditControl( "questioncomment$this->id"	, LOC_COMMENT	, TYPE_CHECKBOX		, $this->commentflag		, null		, 0);
		
		return( $controls );
	}
	
	public function SaveEditControls( $formData )
	{
		$this->question        = htmlentities( $formData["question$this->id"] );
		$this->questiontypeid  = $formData["questiontype$this->id"];
		$this->questiongroupid = $formData["questiongroup$this->id"];
		if( $formData["questioncomment$this->id"] == true )
			$this->commentflag = 1;
		else
			$this->commentflag = 0;
		
		$this->SaveAll();
	}

	public function GetDetailObjects()
	{
		$retval = null;
		if( $this->answers != null )
		{
			$retval = array();
			foreach( $this->answers as $answer )
			{
				$retval[] = new DetailObject( $answer->id, $answer->answer, 'answer' );
			}
		}

		return( $retval );
	}
	
	public function GetObjectTitle()
	{
		return( $this->question );
	}
	
	public function GetParentObject()
	{
		return( new DetailObject( $this->surveyid, "", 'survey' ) );
	}
	
	public function GetObjectType()
	{
		return( 'question' );
	}
	
	public function GetDetailObjectType()
	{
		return( 'answer' );
	}

}
?>
