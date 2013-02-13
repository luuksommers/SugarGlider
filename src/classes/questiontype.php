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
 *  \brief Class handling all question type functions
 */
class QuestionType extends DatabaseObject
{
	//Database column vars
	public $name            = null;
	
	// Holds a list of available QuestionTypes
	public $list            = null;
	
	function __construct( $questiontypeid = null )
	{
		parent::__construct( DB_PREFIX . "questiontype", "Id" );
		
		if( $questiontypeid == null )
		{
			$this->sql->query( "SELECT id, name FROM $this->table_name ORDER BY name" );
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
			$this->sql->query( "SELECT Name FROM $this->table_name WHERE id = $questiontypeid" );
			if( $this->sql->NumRows() )
			{
				$row = $this->sql->FetchRow();
				$this->name            = $row[ 0 ];
			}
		}
	}
}
?>