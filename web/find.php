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
	
	render($storeName, $resourceUri);
}

function doPost()
{
	$storeName = $_POST['storeName'];
	$resourceUri = $_POST['resourceUri'];
	
	render($storeName, $resourceUri);
}

function render($storeName, $resourceUri)
{
	$content = '';
	
   	$content .= '<form method="get" action="find.php"';
	$content .= '<input type="hidden" name="storeName" size="15%" value="' . $storeName . '"/>&nbsp;';
	$content .= '<div align="center">';
	$content .= '<input type="text" name="resourceUri" size="80%" value="' . $resourceUri . '"/>&nbsp;';
	$content .= '<input type="submit" name="submit" value="Find"/>';
	$content .= '</div>';
	$content .= '</form>';

	if (!empty($resourceUri))
	{
		if (substr($resourceUri, 0, 5) == 'http:')
		{
			$index = describe_to_simple_index($storeName, $resourceUri);
			if (isset($index[$resourceUri]))
			{
				$content .= 'Found as subject<p>';
				$content .= '<div class="findResults">';
				$content .= '<ul>';
				$itemUri = $resourceUri;
				$showUri = 'show.php?storeName=' . $storeName . '&resourceUri=' . urlencode($itemUri);
				$content .= '<li><span class="lnkShowItem" ><a href="' . $showUri . '">' . $itemUri . '</a></span></li>';
				$content .= '</ul>';
				$content .= '</div>';
			}
			else
			{
				$content .= 'Not found as subject<p>';
			}
			$select = 'SELECT ?s where {?s ?p <' . $resourceUri . '>}';
			$selectResults = select_to_array($storeName, $select);
			if (!empty($selectResults))
			{
				$content .= 'Found as object<p>';
				$content .= '<div class="findResults">';
				$content .= '<ul>';
				foreach ($selectResults as $selectResult)
				{
					$itemUri = $selectResult['s']['value'];
					$showUri = 'show.php?storeName=' . $storeName . '&resourceUri=' . urlencode($itemUri);
					$content .= '<li><span class="lnkShowItem" ><a href="' . $showUri . '">' . $itemUri . '</a></span></li>';
				}
				$content .= '</ul>';
				$content .= '</div>';
			}
			else
			{
				$content .= 'Not found as object<p>';
			}
		}
		else
		{
			$results = search_to_resource_list($storeName, $resourceUri);
			$content .= 'Found ' . $results->total_results . ' items in indexes<p>';
			$content .= '<div class="findResults">';
			$content .= '<ul>';
			foreach ($results->items as $item)
			{
				$itemUri = $item['http://purl.org/rss/1.0/link'][0];
				$showUri = 'show.php?storeName=' . $storeName . '&resourceUri=' . urlencode($itemUri);
				$content .= '<li><span class="lnkShowItem" ><a href="' . $showUri . '">' . $itemUri . '</a></span></li>';
			}
			$content .= '</ul>';
			$content .= '</div>';
		}
	}	
	renderPage($storeName, null, null, $content);
}

?>