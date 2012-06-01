<?php

require_once('../src/utils.inc.php');

require_once(MORIARTY_DIR . 'store.class.php');
require_once(MORIARTY_DIR . 'credentials.class.php');
require_once(MORIARTY_DIR . 'sparqlservice.class.php');
require_once(MORIARTY_DIR . 'httprequestfactory.class.php');
require_once(MORIARTY_DIR . 'changesetbatch.class.php');

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

function render($storeName, $message = null, $error = null)
{
	$content = '';

	if(!empty($message))
	{
		$content .= '<div align="center">';
		$content .= $message;
		$content .= '</div>';
	}
	
	if(!empty($error))
	{
		$content .= '<pre>';
		$content .= $error;
		$content .= '</pre>';
		$content .= '<br/>';
	}
	
	$content .= '<form enctype="multipart/form-data" action="upload.php" method="post">';
	$content .= '<input type="hidden" name="storeName" value="'.$storeName.'" />';
	$content .= '<div align="center">';
	$content .= '<br/>';
	$content .= '<p><input type="file" name="uploadedfile" size="80" />';
	$content .= '<br/>';
	$content .= '<br/>';
	$content .= '<p><input type="submit" name="submit" value="Upload"/>';
	$content .= '</div>';
	$content .= '</form>';
	
	renderPage($storeName, null, 'Upload file', $content);

}

function doPost()
{
	set_time_limit(120);
	
	$storeName = $_POST['storeName'];

	$fileName = basename($_FILES['uploadedfile']['name']);
	$tmpFile = $_FILES['uploadedfile']['tmp_name'];

	$rdfxml = file_get_contents($tmpFile);

	$store = getStore($storeName);

	$info = pathinfo($fileName);
	if ($info['extension'] == 'ttl')
	{
		$response = $store->get_metabox()->submit_turtle($rdfxml);
	}
	else
	{ 
		$response = $store->get_metabox()->submit_rdfxml($rdfxml);
	} 

	if ($response->is_success())
	{
		$message = 'Upload succeeded';
	    $error = null;
	}
	else
	{			
		$message = 'Upload failed';
	    $error = $response->to_string();
	}

	render($storeName, $message, $error);
}
?>