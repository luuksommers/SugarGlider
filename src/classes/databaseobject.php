<?php
/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/
require_once( "databaseconnection.php" );
require_once( "logger.php" );
require_once( "inc/config.php" );

/*******************************************************************************
* Class: DataBaseObject
* Function: Base class for accessing the database
*******************************************************************************/
class DatabaseObject
{
	protected $sql	      = null;
	public    $id_col     = 0;
	public    $table_name = "";
	public    $auto_id    = true;
	public    $id    	  = "";
	
	function __construct( $table_name, $identity_col, $auto_id = true )
	{
		$this->table_name = $table_name;
		$this->id_col     = $identity_col;
		$this->auto_id    = $auto_id;
		if( $this->auto_id )
		{
			$this->id     = 0;
		}
		$this->sql = new DatabaseConnection( DB_HOST, DB_USER, DB_PASS, DB_NAME, true );
		
		if( !$this->sql->Open() )
		{
			die ('Error opening database: ' . DB_NAME );	
		}
	}
	
	function __destruct()
	{
		$this->sql->Close();
	}
	
	public function Exists()
	{
		$this->sql->Query( "SELECT * FROM $this->table_name WHERE $this->id_col = $this->id" );
		if( $this->sql->NumRows() )
			$retval = true;
		else
			$retval = false;
		
		return( $retval );
	}
	
	// -------------------------------------------------------------------------
	// Updates excisting record
	// -------------------------------------------------------------------------
	public function Update( $update_flds )
	{
		$this->sql->Query( "UPDATE $this->table_name SET $update_flds WHERE $this->id_col = $this->id" );
	}
	
	// -------------------------------------------------------------------------
	// Inserts a new record
	// -------------------------------------------------------------------------
	public function Insert( $col_names, $col_values )
	{
		$this->sql->Query( "INSERT INTO $this->table_name ($col_names) VALUES ($col_values)" );
		if( $this->auto_id )
		{
			$this->id = $this->sql->GetInsertedId();
		}
	}
	
	// -------------------------------------------------------------------------
	// Inserts a new record
	// -------------------------------------------------------------------------
	public function Del()
	{
		$this->sql->Query( "DELETE FROM $this->table_name WHERE $this->id_col = $this->id" );
	}
}

?>