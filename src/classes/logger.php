<?php
require_once( "inc/config.php" );
class Logger
{
    public static function Write( $data )
    {
    	if( DEBUG )
    	{
	    	$logfile = "debug.txt";
	    	$prefix  = date( "Y-m-d H:i:s" );
			
			$fh = fopen( $logfile, 'a' ) or die( "can't open file" );
			fwrite( $fh, $prefix . " - " . $data );
			fwrite( $fh, "\n" );
			fclose( $fh );
		}
    }
}

?>