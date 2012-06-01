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
	$storeName = @$_GET['storeName'];
	$resourceUri = @$_GET['resourceUri'];
	
 	render($storeName, $resourceUri);
}

function doPost()
{
	$storeName = @$_POST['storeName'];
	$resourceUri = @$_POST['resourceUri'];
	
	header('Location: find.php?storeName='.$storeName.'&resourceUri='.urlencode($resourceUri));
}

function render($storeName, $resourceUri)
{
	$data = array();		
	
	$index = describe_to_simple_index($storeName, $resourceUri);
	if (isset($index[$resourceUri]))
	{
		$data = $index[$resourceUri];		
		ksort($data);
	}
	

	$content = '';
	
   	$content .= '<form method="post"';
	$content .= '<input type="hidden" name="storeName" size="15%" value="' . $storeName . '"/>&nbsp;';
	$content .= '<div align="center">';
	$content .= '<input type="text" name="resourceUri" size="80%" value="' . $resourceUri . '"/>&nbsp;';
	$content .= '<input type="submit" name="submit" value="Find"/>';
	$content .= '</div>';
	$content .= '</form>';

	if (count($data) > 0 )
	{
		$content .= '<table>';
	    $content .= '<thead>';
	    $content .= '<tr><th>actions</th><th>predicate</th><th>object</th></tr>';
	    $content .= '</thead>';
	    $content .= '<tbody>';
	
		$content .= '<tr>';
		$content .= '<td>';
		$content .= '<span class="lnkDeleteTriple"><a title="delete all triples for resource" href="delete.php?storeName=' . $storeName . '&resourceUri=' . urlencode($resourceUri) . '">&nbsp;</a></span>';
		$content .= '<span class="lnkDeleteTriple"><a title="delete all triples for resource" href="delete.php?storeName=' . $storeName . '&resourceUri=' . urlencode($resourceUri) . '">&nbsp;</a></span>';
		$content .= '</td>';
		$content .= '<td></td>';
		$content .= '<td></td>';
		$content .= '</tr>';
		
	    
		foreach ($data as $predicate => $objects)
		{
			$index = 0;
			foreach ($objects as $object)
			{	
				$value = $object['value'];
				$type = $object['type'];
				 
			    $content .= '<tr>';
	
				$content .= '<td>';
				$content .= '<span class="lnkDeleteTriple"><a title="delete triple" href="delete.php?storeName=' . $storeName . '&resourceUri=' . urlencode($resourceUri) . '&predicate=' . urlencode($predicate) . '&type=' . urlencode($type) . '&value=' . urlencode($value) . '">&nbsp;</a></span>';
				$content .= '<span class="lnkEditTriple"><a title="edit triple" href="edit.php?storeName=' . $storeName . '&resourceUri=' . urlencode($resourceUri) . '&predicate=' . urlencode($predicate) . '&type=' . urlencode($type) . '&value=' . urlencode($value) . '">&nbsp;</a></span>';
				$content .= '</td>';
				
				$content .= '<td>' . $predicate . '</td>';
			    $content .= '<td>';
			    
				if ($type == 'uri')
				{
					$content .= '<a href="?storeName=' . $storeName . '&resourceUri=' . urlencode($value) . '">' . $value . '</a>';
				}
				else
				{
					$content .= $value;
				}
			    $content .= '</td>';
		
			    $content .= '</tr>' . "\n";
	
				$index++;		    
			}
		}
	
	    $content .= '</tbody>';
	    $content .= '</table>';
	}
	    
	renderPage($storeName, $resourceUri, null, $content);
}

?>