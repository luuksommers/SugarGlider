<?php
/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/
require_once( "databaseobject.php" );

/*******************************************************************************
* Class: Session
* Function: Interface to the session table in the database
*******************************************************************************/
class Session extends DatabaseObject
{
	// Database column vars
	public $sessionid;
	
	/*! 
	 *  \brief Creates a new survey object
	 *  \param $surveyid 	if not null, the given id will be retrieved from the database
	 *  \param $title 		if not null, a new survey will be inserted in the database
	 */
	function __construct( $sessionid = null )
	{
		parent::__construct( DB_PREFIX . "session", "id" );

		$this->sessionid = $sessionid;

		// Create new one
		if( $sessionid != null )
		{
			$this->Insert( "sessionid, startdatetime", "'$this->sessionid',NOW()" );
		}
	}
}
?>
