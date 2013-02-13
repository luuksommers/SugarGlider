<?php
/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/
require_once( "logger.php" );

/*******************************************************************************
* Class: DatabaseConnection
* Function: Connects to database and executes queries
*******************************************************************************/
class DatabaseConnection
{
    // Connection parameters 
    private $host       = '';
    private $user       = '';
    private $password   = '';
    private $database   = '';
    private $persistent = false;

    // Database connection handle 
    private $conn       = NULL;

    // Query result 
    private $result     = false;

	/***************************************************************************
	* Function: Constructor
	***************************************************************************/
    function __construct( $host, $user, $password, $database, $persistent = false )
    {
        $this->host       = $host;
        $this->user       = $user;
        $this->password   = $password;
        $this->database   = $database;
        $this->persistent = $persistent;
    }

    public function Open()
    {
        // Choose the appropriate connect function 
		
        if ($this->persistent) {
            $func = 'mysql_pconnect';
        } else {
            $func = 'mysql_connect';
        }

        // Connect to the MySQL server 
        $this->conn = $func($this->host, $this->user, $this->password);
        if (!$this->conn) 
		{
            return false;
        }

        // Select the requested database 
        if (!mysql_select_db($this->database, $this->conn)) 
		{
            return false;
        }

        return true;
    }

    public function Close()
    {
		return (@mysql_close());
    }

    public function Error()
    {
        return (mysql_error());
    }

    public function Query($sql = '')
    {
    	Logger::Write( $sql );
		$this->Open();
        $this->result = mysql_query($sql) OR die( $this->Error() );
        return ($this->result != false);
    }
    
    public function GetInsertedId()
    {
    	$insertedId = mysql_insert_id($this->conn);
    	Logger::Write( "Created new record with ID: $insertedId" );
        return ($insertedId);
    }


    public function AffectedRows()
    {
        return (mysql_affected_rows($this->conn));
    }

    public function NumRows()
    {
    	$numRows = mysql_num_rows($this->result);
        return( $numRows );
    }

    public function FetchObject()
    {
        return (mysql_fetch_object($this->result, MYSQL_ASSOC));
    }

    public function FetchArray()
    {
        return (mysql_fetch_array($this->result, MYSQL_NUM));
    }

    public function FetchAssoc()
    {
        return (mysql_fetch_assoc($this->result));
    }
    
    public function FetchRow()
    {
		return (mysql_fetch_row($this->result));
    }

    public function FreeResult()
    {
        return (@mysql_free_result($this->result));
    }
}

?>
