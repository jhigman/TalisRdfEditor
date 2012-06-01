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
	$resourceUri = $_GET['resourceUri'];
	$predicate = @$_GET['predicate'];
	$type = @$_GET['type'];
	$value = @$_GET['value'];
	
	if(empty($predicate) && empty($type) && empty($value)) 
	{
		$message = 'Delete ALL triples for resource';
	}
	else
	{
		$message = 'Delete triple';
	}
	render($storeName, $resourceUri, $predicate, $type, $value, $message);
}


function render($storeName, $resourceUri, $predicate, $type, $value, $message)
{
	$content = '';
	
	$content .= '<form method="post">';
	$content .= '<p><label>subject</label><input readonly="readonly" type="text" id="subject" name="subject" value="'.$resourceUri.'" size="70%" maxlength="500"/>';
	if(!empty($predicate))
	{
		$content .= '<p><label>predicate</label><input readonly="readonly" type="text" id="predicate" name="predicate" value="'.$predicate.'" size="70%" maxlength="500"/>';
	}
	if(!empty($value))
	{
		$content .= '<p><label>value</label><input readonly="readonly" type="text" id="value" name="value" value="'.$value.'" size="70%" maxlength="500"/>';
	}
	if(!empty($type))
	{
		$content .= '<p><label>type</label><input readonly="readonly" type="text" id="type" name="type" value="'.$type.'" size="70%" maxlength="500"/>';
	}
	$content .= '<input type="hidden" name="storeName" value="' . $storeName . '"/>';
	$content .= '<input type="hidden" name="resourceUri" value="' . $resourceUri . '"/>';
	$content .= '<div style="text-align: center;"><br/>';
	$content .= '<input type="submit" name="btnCancel" value="Cancel"/>';
	$content .= '<input type="submit" name="btnOK" value="OK"/>';
	$content .= '</div>';
	$content .= '</form>';

	renderPage($storeName, $resourceUri, $message, $content);
}

function doPost()
{
	$storeName = $_POST['storeName'];
	$resourceUri = $_POST['resourceUri'];
	$predicate = @$_POST['predicate'];
	$type = @$_POST['type'];
	$value = @$_POST['value'];
	
	if (!empty($_POST['btnOK']))
	{
		$before = describe_to_simple_graph($storeName, $resourceUri);
		$after = describe_to_simple_graph($storeName, $resourceUri);

		if(empty($predicate) && empty($type) && empty($value))
		{
			$after->remove_all_triples();
		}
		else
		{
			if ($type == 'uri')
			{
				$after->remove_resource_triple($resourceUri, $predicate, $value);
			}
			else
			{
				$after->remove_literal_triple($resourceUri, $predicate, $value);
			}
		}		
		if (!apply_changes($storeName, $resourceUri, $before, $after))
		{
			die();
		}
	}
	
	header("Location: show.php?storeName=$storeName&resourceUri=" . urlencode($resourceUri));
}


?>