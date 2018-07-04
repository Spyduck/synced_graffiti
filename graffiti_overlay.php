<?php
// note: I don't remember why this checks for both GET and POST? ... just use POST
if (isset($_POST['imgdata']) && isset($_GET['file']))
{
	$imgstr = $_GET['imgdata']; // example data:image/png;base64,....
	$file = $_GET['file'];
}
else if (isset($_POST['imgdata']) && isset($_POST['file']))
{
	$imgstr = $_POST['imgdata'];
	$file = $_POST['file'];
}
if (isset($file) && isset($imgstr))
{
	if (strpos($file, '/') === false && strpos($file, '\\') == false && strpos($file, ':') == false)
	{
		$imgstr = urldecode($imgstr);
		$imgstr = str_replace(' ', '+', $imgstr);
		
		// try to get mime type and data
		if (!preg_match('/data:([^;]*);base64,(.*)/', $imgstr, $matches)) {
			http_response_code(400);
			die(400);
		}

		$content = base64_decode($matches[2]);

		header('Content-Type: '.$matches[1]);
		header('Content-Length: '.strlen($content));

		file_put_contents($file.'_tmp.png', $content);
		overlay($file.'.png', $file.'_tmp.png');
		file_put_contents($file.'.json', uniqid());
	}
	else
	{
		http_response_code(400);
		die(400);
	}
}
else
{
	http_response_code(400);
	die(400);
}

function overlay($saveas, $new)
{
	if (file_exists($saveas))
	{
		$dest = imagecreatefrompng($saveas);
	}
	else
	{
		$dest = imagecreatetruecolor(1024,1024);
		$white = imagecolorallocate($dest, 255, 255, 255);
		imagefilledrectangle($dest, 0, 0, 1024-1,1024-1, $white);
	}
	$src = imagecreatefrompng($new);
	imagealphablending($dest, true);
	imagesavealpha($dest, true);
	
	imagecopymerge_alpha($dest, $src, 0, 0, 0, 0, 1024, 1024, 100);

	// free memory
	imagepng($dest, $saveas);
	imagedestroy($dest);
	imagedestroy($src);
}

/** 
* PNG ALPHA CHANNEL SUPPORT for imagecopymerge(); 
* by Sina Salek 
* 
* Bugfix by Ralph Voigt (bug which causes it 
* to work only for $src_x = $src_y = 0. 
* Also, inverting opacity is not necessary.) 
* 08-JAN-2011 
* 
**/ 
function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){ 
	// creating a cut resource 
	$cut = imagecreatetruecolor($src_w, $src_h); 

	// copying relevant section from background to the cut resource 
	imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h); 
	
	// copying relevant section from watermark to the cut resource 
	imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h); 
	
	// insert cut resource to destination image 
	imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct); 
} 
?>