<?php

require_once('../src/utils.inc.php');

if ($_SERVER['REQUEST_METHOD']=='GET')
{
    doGet();
}
else
{
    doPost();
}

function doGet()
{
	$storeName = $_GET['storeName'];	
	render($storeName);
}

function doPost()
{	
	$storeName = $_POST['storeName'];	
	render($storeName);
}


function render($storeName)
{
	$content = '';
	
	$status = get_store_status($storeName);
	
	$content .= '<div style="text-align: center;"><br/>';
	$content .= $status;
	$content .= '</div>';

	renderPage($storeName, null, 'Store status', $content);
}

?>