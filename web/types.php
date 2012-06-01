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
	$data = array();		
	
	$index = select_to_array($storeName, 'SELECT DISTINCT ?o WHERE {?s a ?o}');	

	$content = '';
	
   	$content .= '<form method="post" action="find.php"';
	$content .= '<input type="hidden" name="storeName" size="15%" value="' . $storeName . '"/>&nbsp;';
	$content .= '<div align="center">';
	$content .= '<input type="text" name="resourceUri" size="80%" value=""/>&nbsp;';
	$content .= '<input type="submit" name="submit" value="Find"/>';
	$content .= '</div>';
	$content .= '</form>';

	if (count($index) > 0 )
	{
		
		$content .= 'Found ' . count($index) . ' data types<p>';
		$content .= '<div class="findResults">';
		$content .= '<ul>';
		foreach ($index as $item)
		{
			$itemUri = $item['o']['value'];
			$label = $itemUri;
			$showUri = 'find.php?storeName=' . $storeName . '&resourceUri=' . urlencode($itemUri);
			$content .= '<li><span class="lnkShowItem" ><a href="' . $showUri . '">' . $label . '</a></span></li>';
		}
		$content .= '</ul>';
		$content .= '</div>';
	}
	else
	{
		$content .= 'No data types found<p>';
	}
	    
	renderPage($storeName, null, 'Data types', $content);
}

?>