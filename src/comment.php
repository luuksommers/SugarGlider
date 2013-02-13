<?php
/*
    Main Survey System
    Copyright (C) 2008  Luuk Sommers

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/
require_once( "classes/Question.php" );

/*******************************************************************************
*
*                               M A I N  C O D E
*
*******************************************************************************/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" >
<link href="sgs/css/sgs.css" rel="stylesheet" type="text/css" >
<title>Sugar Glider Survey</title>
</head>
<body>
<table height="742" width="990" align="center" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/topleft.png" height="50" width="50"></td>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/top.png" height="50" width="890"></td>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/topright.png" height="50" width="50"></td>
	</tr>
	<tr>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/logoleft.png" height="150" width="50"></td>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/logo.jpg" height="150" width="890"></td>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/logoright.png" height="150" width="50"></td>
	</tr>
	<tr>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/contentleft.png" height="442" width="50"></td>
		<td bgcolor="#FFFFFF" height="442" width="890">
			<div id="formDiv">
			<?php
			$question = new Question( $_GET[ 's' ], $_GET[ 'q' ] );
			$comments = $question->GetComments();
			if( $comments != null )
			{
				echo( "<table border='1'>" );
				foreach( $comments as $comment )
				{
					echo( "<tr><td>$comment</td></tr>" );
				}
				echo( "</table>" );
			}
			else
			{
				echo( "No comment found" );	
			}
			?>
			</div></td>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/contentright.png" height="442" width="50"></td>
	</tr>
	<tr>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/footerleft.png" height="50" width="50"></td>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/footer.jpg" height="50" width="840"></td>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/footerright.png" height="50" width="50"></td>
	</tr>
	<tr>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/botleft.png" height="50" width="50"></td>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/bot.png" height="50" width="840"></td>
		<td background="<?php echo $mosConfig_live_site;?>/sgs/images/botright.png" height="50" width="50"></td>
	</tr>
</table>	
</body>
</html>