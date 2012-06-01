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
 	render();
}

function doPost()
{
	$storeName = @$_POST['storeName'];
	$username = @$_POST['username'];
	$password = @$_POST['password'];

	saveLastLogin($storeName, $username, $password);
	
	header('Location: find.php?storeName='.$storeName.'&resourceUri=');
}

function render()
{
	$loginDetails = getLastLogin();
	
	$lastStoreName = $loginDetails['storeName'];
	$lastUsername = $loginDetails['username'];
	$lastPassword = $loginDetails['password'];
	
	$storeCollection = new StoreCollection('http://api.talis.com/stores');
	$storeCollection->retrieve();
	$storeUris = $storeCollection->get_store_uris();
	
	asort($storeUris);

	$content = '';
	
   	$content .= '<form method="post"';
	$content .= '<div align="center">';
	$content .= '<select name="storeName">';
	
	foreach($storeUris as $storeUri)
	{
		$storeName = str_replace('http://api.talis.com/stores/', '', $storeUri);
		$content .= '<option ';
		if ($storeName == $lastStoreName)
		{
			$content .= ' selected="selected" ';
		}
		$content .= ' value="' . $storeName . '">' . $storeName . '</option>';
	}
	$content .= '</select>';
	$content .= '</div>';
	
	$content .= '<br/><br/><br/>';

	$content .= '<div align="center">';
	$content .= '<label>Username</label><input type="text" name="username" value="'.$lastUsername.'"/>';
	$content .= '<p>';
	$content .= '<label>Password</label><input type="password" name="password" value="'.$lastPassword.'"/>';
	$content .= '</div>';
	
	$content .= '<br/><br/><br/>';
	
	$content .= '<div align="center">';
	$content .= '<input type="submit" name="submit" value="Select"/>';
	$content .= '</div>';
	
	$content .= '</form>';
	
	renderPage (null, null, 'Choose a store', $content);
}

?>