<?php

/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/

require_once( "xajax_core/xajax.inc.php" );
require_once( "inc/config.php" );

/*******************************************************************************
*
*                               M A I N  C O D E
*
*******************************************************************************/

on_page_load();

$xajax = new xajax();
if( DEBUG )
{
	$xajax->setFlag( "debug", true );
}
register_functions( $xajax );
$xajax->processRequest();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="description" content="Night Flower Freelance ICT">
<meta name="keywords" content="Night, Flower, Freelance, ICT, NightFlower, NightFlower.nl, Bacolan, Bacolan.com">
<title>Night Flower</title>
<link href="<?php echo( THEME_PATH . "style.css " ); ?>" rel="stylesheet" type="text/css">
<?php 
	$xajax->printJavascript(); // Output the xajax javascript. This must be called between the head tags 
?>
<script type="text/javascript">
	xajax.callback.global.onRequest = function() {xajax.$('loading').style.display = 'block';}
	xajax.callback.global.beforeResponseProcessing = function() {xajax.$('loading').style.display='none';}
</script>
<script type="text/javascript" src="inc/maxlength.js"></script>
</head>
<!--
########################################
##                                    ##
##            Powered  by             ##
##            Night Flower            ##
##                                    ##
##                                    ##
##        www.nightflower.nl          ##
##                                    ##
########################################
-->
<body>
	<div id="center">
		<div id="container">
<!--
########################################
			<div id="header">
				<div id="header_inner">
				</div>
			</div>
########################################
-->
			<div id="menu">
				<div id="menu_inner"><a href="http://www.nightflower.nl/">Home</a> | <a href="http://www.nightflower.nl/?page=projecten">Projecten</a> | <a href="http://www.nightflower.nl/?page=contact">Contact</a></div>
			</div>
			<div id="content">
			
				<div id="content_inner">
				<div id='mainBody'>
				<?php create_body(); ?>
				</div>
				</div>

			</div>
		</div>
	</div>
<div id="loading" style="display: none;"><img src="img/loading.gif" alt=""/> Loading...</div>
</body>
</html>
