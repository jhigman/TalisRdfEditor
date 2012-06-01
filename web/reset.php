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
	$message = 'Are you sure you want to reset the store?';
	render($storeName, $message);
}


function render($storeName, $message)
{
	$content = '';
	
	$content .= '<form method="post">';
	$content .= '<input type="hidden" name="storeName" value="' . $storeName . '"/>';

	$content .= '<div style="text-align: center;"><br/>';
	$content .= '<input type="submit" name="btnCancel" value="Cancel"/>';
	$content .= '<input type="submit" name="btnOK" value="OK"/>';
	$content .= '</div>';
	$content .= '</form>';

	renderPage($storeName, null, $message, $content);
}

function doPost()
{
	$storeName = $_POST['storeName'];
	
	if (!empty($_POST['btnOK']))
	{
		$store = getStore($storeName);
		
		$job_queue = $store->get_job_queue();
		$response = $job_queue->schedule_reset_data();
	
		$content = '';
		if ($response->is_success())
		{
			$jobUri = $response->headers['location'];
			$message = 'Scheduled reset job';
	
			$content .= 'Reset job details<p>';
			$content .= '<div class="findResults">';
			$content .= '<ul>';
			$content .= '<li><span class="lnkShowItem" ><a href="' . $jobUri . '">' . $jobUri . '</a></span></li>';
			$content .= '</ul>';
			$content .= '</div>';
		} 
		else 
		{
			$message = 'Failed to schedule reset job';
	
			$content .= 'Response details<p>';
			$content .= '<pre>';
			$content .= $response->to_string();
			$content .= '</pre>';
		}

		renderPage($storeName, null, $message, $content);
	}
	else
	{
		header('Location: find.php?storeName='.$storeName.'&resourceUri=');
	}
	
}

?>