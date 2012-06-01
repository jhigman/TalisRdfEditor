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
	$token = @$_GET['token'];
	$from = @$_GET['from'];
	$to = @$_GET['to'];
	$type = @$_GET['submit'];
	
	if (empty($token))
	{
		$token = null;
	}
	
	if (empty($from))
	{
		$from = date('Y-m-d');
	}
	
	if (empty($to))
	{
		$fromTime = strtotime($from);
		$to = date('Y-m-d', $fromTime + (24*60*60));
	}
	
	if (empty($type))
	{
		$type = null;
	}
	
	render($storeName, $token, $from, $to, $type);
}

function doPost()
{
	echo 'not implemented';
	die();
}

function render($storeName, $token, $from, $to, $type)
{
	$content = '';
	
   	$content .= '<form method="get"';
	$content .= '<input type="hidden" name="storeName" size="15%" value="' . $storeName . '"/>&nbsp;';
	$content .= '<div align="center">';

	$content .= '<label>Start Date</label>';
	$content .= '<input id="from" name="from" type="text" value="'.$from.'" maxlength="30" size="30" />';
//	$content .= '<img class="ui-datepicker-trigger" src="images/datepicker.gif" alt="Pick a start date" title="Pick a start date"/>';
//	$content .= '<script type="text/javascript">';
//	$content .= '$(document).ready(function(){$("#from").datepicker({showOn: "button", buttonImage: "images/datepicker.gif", buttonImageOnly: true,  dateFormat: "yy-mm-dd", changeMonth: true, changeYear: true});});';
//	$content .= '</script>';
	$content .= '<p>';
	
	$content .= '<label>End Date</label>';
	$content .= '<input id="to" name="to" type="text" value="'.$to.'" maxlength="30" size="30" />';
//	$content .= '<img class="ui-datepicker-trigger" src="images/datepicker.gif" alt="Pick an end date" title="Pick an end date"/>';
//	$content .= '<script type="text/javascript">';
//	$content .= '$(document).ready(function(){$("#to").datepicker({showOn: "button", buttonImage: "images/datepicker.gif", buttonImageOnly: true,  dateFormat: "yy-mm-dd", changeMonth: true, changeYear: true});});';
//	$content .= '</script>';
	$content .= '<p>';

	$content .= '<input type="submit" name="submit" value="List"/>';
	$content .= '<input type="submit" name="submit" value="Graph"/>';

	$content .= '</div>';
	$content .= '</form>';
	
	if($type=='Graph')
	{
		$content .= renderGraph($storeName, $from, $to);
	}
	else
	{
		$content .= renderList($storeName, $token, $from, $to);
	}

	renderPage($storeName, null, null, $content);
}

function renderList($storeName, $token, $from, $to)
{
	$content = '';
	
	$results = getOAIArray($storeName, $token, $from, $to);
	
	$content .= '<p>';
	
	if(!empty($results['items']))
	{
		$content .= 'Found changed records';
		if (!empty($results['token']))
		{
			$token = $results['token'];
			$uri = '?storeName='.$storeName.'&from='.$from.'&to='.$to.'&token='.$token;
			$content .= ' (<a href="'.$uri.'">see more...</a>)';
		}

		$content .= '<p>';
		$content .= '<div class="findResults">';
		$content .= '<ul>';
		foreach ($results['items'] as $item)
		{
			$itemUri = $item['uri'];
			$datestamp = $item['datestamp'];
			$showUri = 'show.php?storeName=' . $storeName . '&resourceUri=' . urlencode($itemUri);
			$content .= '<li><span class="lnkShowItem" ><a href="' . $showUri . '">' . $itemUri . '</a> (' . $datestamp . ')</span></li>';
		}
		$content .= '</ul>';
		$content .= '</div>';
	}	
	else
	{
		$content .= 'No changes found<p>';
	}	
//	$content .= '<script type="text/javascript">doChart("'.$storeName.'", "'.$from.'", "'.$to.'");</script>';
	
	return $content;
}

function renderGraph($storeName, $from, $to)
{
	// can take a long time
	set_time_limit(60*3);
	
	$content = '';
	$token = null;
	
	$results = getOAIArray($storeName, $token, $from, $to);

	if(!empty($results['token']))
	{
		$token = $results['token'];
		while(!empty($token))
		{
			$moreResults = getOAIArray($storeName, $token, $from, $to);
			$results = array_merge_recursive($results,$moreResults);
			if(!empty($moreResults['token']))
			{
				$token = $moreResults['token'];
			}
			else
			{
				$token = null;
			}
		}
	}

	$series = makeTimeSeries($from, $to, $results);

	$xLabels = array();
	$xValues = array();
	foreach($series as $key => $uris)
	{
		$xLabels[] = date('Y-m-d\TH:00',$key);
		$xValues[] = count($uris);
	}
	
	$dataSeries='[' . implode(',', $xValues) . ']';
	$dataLabels='["' . implode('","', $xLabels) . '"]';

	$content .= '<p>';

	$content .= '<div align="center">';
	$content .= '<div id="container" style="height:600px; width:800px;"></div>';		
	$content .= '</div>';
	
	$content .= '<script type="text/javascript">';
	$content .= 'var dataSeries='.$dataSeries.';';
	$content .= 'var dataLabels='.$dataLabels.';';
	$content .= 'doChart("'.$storeName.'", "'.$from.'", "'.$to.'", dataSeries, dataLabels);';
	$content .= '</script>';
	
	return $content;
}

function makeTimeSeries($from, $to, $results)
{
	$vals = array();

	$fromDate = strtotime($from);
	$toDate = strtotime($to);	
	
	$index = $fromDate;
	while($index <= $toDate)
	{
		$vals[$index] = array();
		$index += 60*60;
	}

	foreach($results['items'] as $item)
	{
		// 2010-02-05T10:52:02Z -> 2010-02-05T10:00:00Z
		$itemDatestamp = $item['datestamp'];
		$itemDate = strtotime(substr($itemDatestamp,0,14) . '00:00Z');
		$vals["$itemDate"][] = $item['uri'];
	}
		
	return $vals;
}
?>