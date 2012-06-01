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
	if (isset($_GET['storeName']))
	{
		$storeName = $_GET['storeName'];
	}
	if (isset($_GET['resourceUri']))
	{
		$resourceUri = $_GET['resourceUri'];
	}
	
 	render($storeName, $resourceUri);
}

function doPost()
{
	$storeName = $_POST['storeName'];
	$resourceUri = $_POST['newSubject'];

	if (!empty($_POST['btnOK']))
	{
		$newPredicate = trim($_POST['newPredicate']);
		$newObject = trim($_POST['newObject']);
		$newType = trim($_POST['newType']);
	
		$subject = $resourceUri;
	
		$before = describe_to_simple_graph($storeName, $resourceUri);
		$after = describe_to_simple_graph($storeName, $resourceUri);
		
		$before->remove_property_values($resourceUri, 'http://schemas.talis.com/2005/dir/schema#etag');
		$after->remove_property_values($resourceUri, 'http://schemas.talis.com/2005/dir/schema#etag');
	
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

function render($storeName, $resourceUri)
{
	$content = '';
	
	$content .= '<form method="post">';
	$content .= '<p><label>subject</label><input type="text" id="newSubject" name="newSubject" value="' . $resourceUri . '" size="70%" maxlength="500"/>';
	$content .= '<p><label>predicate</label><input type="text" id="newPredicate" name="newPredicate" value="" size="70%" maxlength="500"/>';
	$content .= '<p><label>value</label><input type="text" id="newObject" name="newObject" value="" size="70%" maxlength="500"/>';
	$content .= '<p><label>type</label><select name="newType">';
	$content .= '<option value="literal">literal</option>';
	$content .= '<option value="uri">uri</option>';
	$content .= '</select>';
	$content .= '<input type="hidden" name="storeName" value="' . $storeName . '"/>';
	$content .= '<div style="text-align: center;"><br/>';
	$content .= '<input type="submit" name="btnCancel" value="Cancel"/>';
	$content .= '<input type="submit" name="btnOK" value="OK"/>';
	$content .= '</div>';
	$content .= '</form>';
	
	renderPage($storeName, $resourceUri, 'Add triple', $content);
}

?>