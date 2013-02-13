<?php
/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/

require_once( "databaseobject.php" );
require_once( "formcontrol.php" );
require_once( "lang/lang-en.php" );

/*******************************************************************************
*
*                               M A I N  C O D E
*
*******************************************************************************/

/*! 
 *  \brief Class handling all answer database functions
 */
class Answer extends DatabaseObject
{
	// Database column vars
	public $questionid  = null;
	public $answer      = null;
	
	// Holder for the selected answer if applicable
	public $rate		= null;

	/*! 
	 *  \brief Creates a new answer object
	 *  \param $answerid 	if not null, the given id will be retrieved from the database
	 *  \param $questionid 	holds questionid for new answer record
	 *  \param $answer		holds the answer for new answer record
	 */
	function __construct( $answerid = null, $questionid = null, $answer = null )
	{
		parent::__construct( DB_PREFIX . "answer", "id" );
		
		// Load Excisting
		if( $answerid != null )
		{
			$this->id = $answerid;
			$this->SetAnswerInfo();
		}
		
		// Create new one
		/*
		else if( $questionid != null && $answer != null )
		{
			$this->questionid = $questionid;
			$this->answer     = $answer;
			$this->Insert( "questionid, answer", "$this->questionid, '$this->answer'" );
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
			$this->Update( "answer='$this->answer'" );
		}
		else
		{
			$this->Insert( "questionid, answer", "$this->questionid, '$this->answer'" );
		}
	}
	
	
	/*! 
	 *  \brief Retrieves all information about this answer
	 *
	 * Retrieves all information about this answer and stores it in it's local variables
	 */
	private function SetAnswerInfo()
	{
		if( $this->id != null )
		{
			$this->sql->Query( "SELECT questionid, answer FROM $this->table_name WHERE $this->id_col = $this->id" );
			if( $this->sql->NumRows() )
			{
				$row = $this->sql->FetchRow();
				
				$this->questionid = $row[ 0 ];
				$this->answer     = $row[ 1 ];
			}
			$this->sql->FreeResult();
		}
	}
	
	/*! 
	 *  \brief Deletes the answers
	 *
	 * Removes this answer
	 */
	public function Remove()
	{
		$this->Del();
	}
	
	/*! 
	 *  \brief Builds a list of edit controls of this object
	 */
	public function GetEditControls()
	{
		$controls = array();
		$controls[] = new EditControl( "answer$this->id", LOC_ANSWER, TYPE_TEXTBOX, $this->answer, null, 250);
		return( $controls );
	}
	
	public function SaveEditControls( $formData )
	{
		$this->answer = htmlentities( $formData["answer$this->id"] );
		$this->SaveAll();
	}
	
	public function GetDetailObjects()
	{
		return( null );
	}
	
	public function GetObjectTitle()
	{
		return( $this->answer );
	}

	public function GetParentObject()
	{
		return( new DetailObject( $this->questionid, "", 'question' ) );
	}
	
	public function GetObjectType()
	{
		return( 'answer' );
	}
	
	public function GetDetailObjectType()
	{
		return( null );
	}
	
}
?>