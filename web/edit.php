<?php

require_once('../src/utils.inc.php');

require_once(MORIARTY_DIR . 'credentials.class.php');
require_once(MORIARTY_DIR . 'store.class.php');
require_once(MORIARTY_DIR . 'sparqlservice.class.php');
require_once(MORIARTY_DIR . 'httprequestfactory.class.php');

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
	$resourceUri = $_GET['resourceUri'];
	$predicate = $_GET['predicate'];
	$value = $_GET['value'];
	$type = $_GET['type'];
			
 	render($storeName, $resourceUri, $predicate, $value, $type);
}

function doPost()
{
	$storeName = $_POST['storeName'];
	$resourceUri = $_POST['subject'];

	if (!empty($_POST['btnOK']))
	{
		$newPredicate = trim($_POST['newPredicate']);
		$newObject = trim($_POST['newObject']);
		$newType = trim($_POST['newType']);
	
		$oldPredicate = trim($_POST['oldPredicate']);
		$oldObject = trim($_POST['oldObject']);
		$oldType = trim($_POST['oldType']);
	
		$subject = $resourceUri;
	
		$before = describe_to_simple_graph($storeName, $resourceUri);
		$after = describe_to_simple_graph($storeName, $resourceUri);
			
		$before->remove_property_values($resourceUri, 'http://schemas.talis.com/2005/dir/schema#etag');
		$after->remove_property_values($resourceUri, 'http://schemas.talis.com/2005/dir/schema#etag');
	
		// remove old triple if specified	
		if (!empty($oldPredicate) && !empty($oldObject) && !empty($oldType))
		{
			if ($oldType == 'uri')
			{
				$after->remove_resource_triple($resourceUri, $oldPredicate, $oldObject);
			}
			else
			{
				$after->remove_literal_triple($resourceUri, $oldPredicate, $oldObject);
			}
		}
			
		// add new triple if specified	
		if (!empty($newPredicate) && !empty($newObject) && !empty($newType))
		{
			if ($newType == 'uri')
			{
				$after->add_resource_triple($resourceUri, $newPredicate, $newObject);
			}
			else
			{
				$after->add_literal_triple($resourceUri, $newPredicate, $newObject);
			}
		}

		if (!apply_changes($storeName, $resourceUri, $before, $after))
		{
			die();
		}
	}
	
	header('Location: show.php?storeName='.$storeName.'&resourceUri='.urlencode($resourceUri));
	
}

function render($storeName, $resourceUri, $predicate, $value, $type)
{
	$uriSelected = ($type == 'uri' ? 'selected="selected"' : '');
	$literalSelected = ($type == 'literal' ? 'selected="selected"' : '');
	
	$content = '';
	
	$content .= '<form method="post">';
	$content .= '<p><label>subject</label><input type="text" id="subject" name="subject" value="'.$resourceUri.'" size="70%" maxlength="500"/>';
	$content .= '<p><label>predicate</label><input type="text" id="newPredicate" name="newPredicate" value="'.$predicate.'" size="70%" maxlength="500"/>';
	$content .= '<p><label>value</label><input type="text" id="newObject" name="newObject" value="'.$value.'" size="70%" maxlength="500"/>';
	$content .= '<p><label>type</label><select name="newType">';
	$content .= '<option value="literal" ' . $literalSelected . ' >literal</option>';
	$content .= '<option value="uri" ' . $uriSelected . ' >uri</option>';
	$content .= '</select>';
	$content .= '<input type="hidden" name="storeName" value="' . $storeName . '"/>';
	$content .= '<input type="hidden" name="oldPredicate" value="' . $predicate . '"/>';
	$content .= '<input type="hidden" name="oldObject" value="' . $value . '"/>';
	$content .= '<input type="hidden" name="oldType" value="' . $type . '"/>';
	$content .= '<div style="text-align: center;"><br/>';
	$content .= '<input type="submit" name="btnCancel" value="Cancel"/>';
	$content .= '<input type="submit" name="btnOK" value="OK"/>';
	$content .= '</div>';
	$content .= '</form>';
	
	renderPage($storeName, $resourceUri, 'Edit triple', $content);
}

?>