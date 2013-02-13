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
class QuestionGroup extends DatabaseObject
{
	public $list            = null;
	public $name            = null;
	public $lowestrate      = null;
	public $highestrate     = null;
	public $alternativerate = null;
	public $numberofoptions = null;
	
	// -------------------------------------------------------------------------
	// Constructor
	// -------------------------------------------------------------------------
	function __construct( $questiongroupid = null )
	{
		parent::__construct( DB_PREFIX . "questiongroup", "Id" );
		
		if( $questiongroupid == null )
		{
			$this->sql->query( "SELECT Id, Name FROM $this->table_name ORDER BY Name" );
			if( $this->sql->NumRows() )
			{
				$this->list = array();
				while( $row = $this->sql->FetchRow() )
				{
					$this->list[] = array( $row[ 0 ], $row[ 1 ] );
				}
			}
			
			$this->sql->FreeResult();
		}
		else
		{
			$this->sql->query( "SELECT Name, LowestRate, HighestRate, AlternativeRate, NumberOfOptions FROM $this->table_name WHERE Id = $questiongroupid" );
			if( $this->sql->NumRows() )
			{
				$row = $this->sql->FetchRow();
				$this->name            = $row[ 0 ];
				$this->lowestrate      = $row[ 1 ];
				$this->highestrate     = $row[ 2 ];
				$this->alternativerate = $row[ 3 ];
				$this->numberofoptions = $row[ 4 ];
			}
		}
	}
}
?>