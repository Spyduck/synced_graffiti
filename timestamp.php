<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$content = '0';
if (isset($_GET['file'])) {
    $file = $_GET['file'];
	$file = preg_replace("([^\w\s\d\-_~,])", '', $file);
	$file = urlencode($file).'.json';
	set_error_handler("warning_handler", E_WARNING);
	$content = file_get_contents($file);
	if ($content == '')
	{
		$content = '0';
	}
	restore_error_handler();
	echo $content;
	exit;
}
echo '0';
function warning_handler($errno, $errstr) { 
	// do nothing
}
?>