<?php
/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/
require_once( "databaseobject.php" );

/*******************************************************************************
* Class: Answer
* Function: Interface to the answer table in the database
*******************************************************************************/
class SurveyUser extends DatabaseObject
{
	// Public fields
	public $surveyid;
	public $sessionid;
	public $firstname;
	public $lastname;
	public $birthdate;
	public $externalid;
	public $password;
	public $used;

	
	/*! 
	 *  \brief Creates a new SurveyUser
	 *  \param $password Set to load the settings belonging to the password
	 */
	function __construct( $password = null )
	{
		parent::__construct( DB_PREFIX . "surveyuser", "id" );
		
		if( $password != null )
		{
			$query = "SELECT surveyid,sessionid,firstname,lastname,birthdate,externalid,used FROM $this->table_name WHERE password = '$password'";
			$this->sql->Query( $query );
			if( $this->sql->NumRows() )
			{
				$row = $this->sql->FetchRow();
				$this->surveyid   = $row[ 0 ];
				$this->sessionid  = $row[ 1 ];
				$this->firstname  = $row[ 2 ];
				$this->lastname   = $row[ 3 ];
				$this->birthdate  = $row[ 4 ];
				$this->externalid = $row[ 5 ];
				$this->used       = $row[ 6 ];
			}
		}
	}
	
	/*! 
	 *  \brief Saves the fields in this class
	 *  
	 *  Saves all fields to the database
	 */
	public function Save()
	{
		if( $this->Exists() )
		{
			$this->Update( "surveyid=$this->surveyid,sessionid=$this->sessionid,firstname='$this->firstname',lastname='$this->lastname',birthdate='$this->birthdate',externalid='$this->externalid',used=$this->used" );
		}
		else
		{
			$this->Insert( "surveyid,sessionid,firstname,lastname,birthdate,externalid,used", "$this->surveyid,$this->sessionid,'$this->firstname','$this->lastname','$this->birthdate','$this->externalid',$this->used" );
		}
	}
	
	public function GetList( $surveyid, $filter )
	{
		$list = array();
		$query = "SELECT sessionid,firstname,lastname,UNIX_TIMESTAMP(birthdate) as birthdate,externalid FROM $this->table_name WHERE surveyid = $surveyid AND sessionid in (SELECT DISTINCT sessionid FROM ".DB_PREFIX."vote WHERE surveyid=$surveyid) $filter";
		$this->sql->Query( $query );
		
		if( $this->sql->NumRows() )
		{
			while( $row = $this->sql->FetchRow() )
			{
				$list[] = $row;
			}
		}
		return( $list );
	}
}
?>
