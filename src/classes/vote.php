<?php
/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/

require_once( "databaseobject.php" );

/*******************************************************************************
*
*                               M A I N  C O D E
*
*******************************************************************************/

/*! 
 *  \brief Class that stores all vote info
 */
class Vote extends DatabaseObject
{
	// Database column vars
	public $sessionid  = 0;
	public $surveyid   = 0;
	public $questionid = 0;
	public $answerid   = 0;
	public $rateid     = 0;
	public $answertext = "";
	public $comment    = "";
	public $filter	   = "";
	
	/*! 
	 *  \brief Creates a new vote object
	 */
	function __construct()
	{
		parent::__construct( DB_PREFIX . "vote", "id" );
	}
	
	/*! 
	 *  \brief Saves all available information for this survey
	 */
	public function SaveAll()
	{
		if( $this->id != null || $this->id != "" )
		{
			$this->Update( "sessionid=$this->sessionid,surveyid=$this->surveyid,questionid=$this->questionid,answerid=$this->answerid,rateid=$this->rateid,answertext='$this->answertext',comment='$this->comment'" );
		}
		else
		{
			$this->Insert( "sessionid,surveyid,questionid,answerid,rateid,answertext,comment", "$this->sessionid,$this->surveyid,$this->questionid,$this->answerid,$this->rateid,'$this->answertext','$this->comment'" );	
		}
	}
	
	
	/*! 
	 *  \brief Retrieves all votes of a given survey
	 */
	public function GetSurveyVotes( $surveyid )
	{
		$votes = 0;
		$this->sql->Query( "SELECT COUNT(*) FROM $this->table_name WHERE surveyid = $surveyid $this->filter" );
		if( $this->sql->NumRows() )
		{
			$row = $this->sql->FetchRow();
			
			$votes = $row[ 0 ];
		}
		
		return( $votes );
	}
	
	/*! 
	 *  \brief Retrieves all votes of a given question
	 */
	public function GetQuestionVotes( $questionid )
	{
		$votes = 0;
		$this->sql->Query( "SELECT COUNT(*) FROM $this->table_name WHERE questionid = $questionid $this->filter" );
		if( $this->sql->NumRows() )
		{
			$row   = $this->sql->FetchRow();
			$votes = $row[ 0 ];
		}
		
		return( $votes );
	}

	/*! 
	 *  \brief Retrieves all votes of a given answer
	 */
	public function GetAnswerVotes( $answerid )
	{
		$votes = 0;
		$this->sql->Query( "SELECT COUNT(*) FROM $this->table_name WHERE answerid = $answerid $this->filter" );
		if( $this->sql->NumRows() )
		{
			$row   = $this->sql->FetchRow();
			$votes = $row[ 0 ];
		}
		
		return( $votes );
	}
	
	/*! 
	 *  \brief Retrieves all votes of a given answer rate
	 */
	public function GetRateVotes( $answerid, $rateid )
	{
		$votes = 0;
		$this->sql->Query( "SELECT COUNT(*) FROM $this->table_name WHERE answerid = $answerid AND rateid = $rateid $this->filter" );
		if( $this->sql->NumRows() )
		{
			$row   = $this->sql->FetchRow();
			$votes = $row[ 0 ];
		}

		return( $votes );
	}
	
	/*! 
	 *  \brief Retrieves all answers of a given question
	 */
	public function GetAnswerText( $questionid )
	{
		$answertext = array();
		$this->sql->Query( "SELECT answertext FROM $this->table_name WHERE questionid = $questionid AND answertext != '' $this->filter" );
		if( $this->sql->NumRows() )
		{
			while( $row = $this->sql->FetchRow() )
			{
				$answertext[] = $row[ 0 ];
			}
		}

		return( $answertext );
	}
	
	
	/*! 
	 *  \brief Retrieves all comments of a given question
	 */
	public function GetQuestionComment( $questionid )
	{
		$comment = array();
		$this->sql->Query( "SELECT Comment FROM $this->table_name WHERE questionid = $questionid AND comment != '' $this->filter" );
		if( $this->sql->NumRows() )
		{
			while( $row = $this->sql->FetchRow() )
			{
				$comment[] = $row[ 0 ];
			}
		}
		$this->sql->FreeResult();
		
		return( $comment );
	}
	
	/*! 
	 *  \brief Number of comments of a given question
	 */
	public function GetCommentCount( $questionid )
	{
		$count = 0;

		$this->sql->Query( "SELECT COUNT(*) FROM $this->table_name WHERE questionid = $questionid AND comment != '' $this->filter" );

		if( $this->sql->NumRows() )
		{
			$row   = $this->sql->FetchRow();
			$count = $row[ 0 ];
		}
		
		$this->sql->FreeResult();
		
		return( $count );
	}
}

?>