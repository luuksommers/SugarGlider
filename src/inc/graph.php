<?php
	// The following function is from: 
	// www.netlobo.com/php_percentage_bar.html
	// Thank you!

	// Usage:
	// <img src="inc/graph.php?per=76.82" alt="76.82% graph" />

    // returns a PNG graph from the $_GET['per'] variable
    $image = imagecreate(302,15);
    $background = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
    $foreground = imagecolorallocate($image, 0x00, 0x8A, 0x01);
    $border     = imagecolorallocate($image, 0x99, 0x99, 0x99);
    $textcolor  = imagecolorallocate($image, 0xFF, 0x00, 0x00);
    
    $per = $_GET['per'];
    $perdisp = $per;
    //if ($per == 0)
    //	$per = 1;

	if( strlen( $perdisp ) > 5 )
		$perdisp = substr( $perdisp, 0, 5 );
	$perdisp .= "%";

    $grad = imagecreatefrompng("../img/grad.png");
    imagecopy($image, $grad, 1, 1, 0, 0, ($per * 3), 13);
    imagerectangle($image, 0, 0, 301, 14, $border);
    
    // Insert percent
	ImageString ($image, 5, 5, 0, $perdisp, $textcolor);
	
    header("Content-type: image/png");
    imagepng($image, NULL, 7);
?> 