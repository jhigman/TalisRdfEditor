<?php

$appRoot = dirname(dirname(__FILE__));
set_include_path(get_include_path() . PATH_SEPARATOR . $appRoot);
set_include_path(get_include_path() . PATH_SEPARATOR . $appRoot . '/lib');

define('MORIARTY_ARC_DIR', $appRoot . '/lib/arc/');
define('MORIARTY_DIR', $appRoot . '/lib/moriarty/');

//require_once '../lib/httpclient/http.php';
//require_once '../lib/sasl/sasl.php';

require_once MORIARTY_DIR .'moriarty.inc.php';

require_once MORIARTY_ARC_DIR . 'ARC2.php';

require_once(MORIARTY_DIR . 'httprequestfactory.class.php');
require_once(MORIARTY_DIR . 'credentials.class.php');
require_once(MORIARTY_DIR . 'store.class.php');
require_once(MORIARTY_DIR . 'storecollection.class.php');
require_once(MORIARTY_DIR . 'sparqlservice.class.php');
require_once(MORIARTY_DIR . 'changeset.class.php');
require_once(MORIARTY_DIR . 'changesetbatch.class.php');

function getStore($storeName)
{
	$credentials = getStoreCredentials($storeName);
	return new Store("http://api.talis.com/stores/$storeName", $credentials);
}

function getStoreCredentials($storeName)
{
	$details = getLastLogin();
	
	// TODO: prompt for new user/pass if storename isn't same as last login
	
	$username = $details['username'];
	$password = $details['password'];

	if (empty($username) && empty($password)) 
	{
		return null;
	}
	
	return new Credentials($username, $password);
}

function get_store_status($storeName)
{
	$store = getStore($storeName);
    $config = $store->get_config();
    return $config->get_access_status();
}

function saveLastLogin($storeName, $username, $password)
{
	$expire = time()+60*60*24*30;
	
	setcookie("rdfeditorStoreName", $storeName, $expire);
	setcookie("rdfeditorUsername", $username, $expire);
	setcookie("rdfeditorPassword", base64_encode($password), $expire);
}

function getLastLogin()
{
	$lastStoreName = @$_COOKIE['rdfeditorStoreName'];
	$lastUsername = @$_COOKIE['rdfeditorUsername'];
	$lastPassword = @$_COOKIE['rdfeditorPassword'];
	
	if(!empty($lastPassword))
	{
		$lastPassword = base64_decode($lastPassword);
	}
	
	return array('storeName' => $lastStoreName, 'username' => $lastUsername, 'password' => $lastPassword);
}

function parse_to_simple_index($rdfxml)
{
	$triples = array();

	if (strlen($rdfxml) > 0)
	{
		$parser = ARC2::getRDFXMLParser();
		$parser->parse(null, $rdfxml);
		$triples = $parser->getSimpleIndex(0);
	}

	return $triples;
}

function describe_to_simple_index($storeName, $resourceUri)
{
	$store = getStore($storeName);
	$sparql = $store->get_sparql_service();
	$response = $sparql->describe($resourceUri);
	if ( $response->body ) {
		return parse_to_simple_index($response->body);
	}
	return null;
}

function select_to_array($storeName, $select)
{
	$store = getStore($storeName);
	$sparql = $store->get_sparql_service();
	$results = $sparql->select_to_array($select);
	if ( !empty($results)) {
		return $results;
	}
	return null;
}

function describe_to_simple_graph($storeName, $resourceUri)
{
	$store = getStore($storeName);
	$sparql = $store->get_sparql_service();
	return $sparql->describe_to_simple_graph($resourceUri);
}

function search_to_resource_list($storeName, $searchTerm)
{
	$store = getStore($storeName);
	$contentBox = $store->get_contentbox();
	return $contentBox->search_to_resource_list($searchTerm, 100);	
}

function apply_changes($storeName, $resourceUri, $before, $after)
{
	$args = array('subjectOfChange' => $resourceUri, 'before' => $before->get_index(), 'after' => $after->get_index(), 'creatorName' => 'RDF Editor', 'changeReason' => 'user edit' );
    $cs = new ChangeSet($args);
    
	if ($cs->has_changes())
	{
		$store = getStore($storeName);
		
		$cs_response = $store->get_metabox()->apply_changeset_rdfxml($cs->to_rdfxml()); 

		if (!$cs_response->is_success())
		{			
		    echo 'Failed to apply changeset : <br/><pre>' . $cs_response->to_string() . '</pre>';
			return false;
		}
	}
	else
	{
	    echo 'No changes to apply';
		return false;
	}
	return true;	
}

function getOAIArray($storeName, $token, $from, $to)
{
	$from = $from . 'T00:00:00Z';
	$to = $to . 'T00:00:00Z';
	
	$store = getStore($storeName);

	$oai = $store->get_oai_service();
	
	$xml = $oai->list_records($token, $from, $to);

	if(!empty($xml->body))
	{
		return $oai->parse_oai_xml($xml->body);
	}
	return array();
}

function getHourPart($datestamp)
{
	return substr($datestamp, 11, 2);
}

function getDatePart($datestamp)
{
	return substr($datestamp, 0, 10);
}

function renderPage($storeName, $resourceUri, $title, $content)
{
    $header = file_get_contents('../templates/header.tpl');
    $footer = file_get_contents('../templates/footer.tpl');
    
	echo '<head>';
	echo '<title>';
	echo 'RDF Editor';
	if (!empty($storeName))
	{
		echo ' | ' . $storeName;
	}
	echo '</title>';
    
    echo $header;
    
    echo '</head>';
	echo '<body>';
    
	echo '<a href="index.php">Home</a>'; 
	if (!empty($storeName))
	{
		echo ' | <a href="status.php?storeName=' . $storeName . '">Status</a>'; 
		echo ' | <a href="http://api.talis.com/stores/' . $storeName . '">Services</a>'; 
		echo ' | <a href="types.php?storeName=' . $storeName . '">Data types</a>';
		echo ' | <a href="upload.php?storeName=' . $storeName . '">Upload</a>';
		echo ' | <a href="reset.php?storeName=' . $storeName . '">Reset</a>';
		echo ' | <a href="add.php?storeName=' . $storeName . '&resourceUri=' . urlencode($resourceUri) . '">Add triple</a>';
		echo ' | <a href="activity.php?storeName=' . $storeName . '">Recent changes</a>';
	}	

	if (!empty($storeName))
	{
		echo '<h2 align="center">' . $storeName . '</h2>';
	}
	else
	{
		echo '<h2 align="center">RDF Editor</h2>';
	}
	
	echo '<h3 align="center">' . $title . '</h3>';
	echo '<p><p>';

	echo $content;
	
	echo $footer;
	
	echo '</body>';
	echo '</html>';
}

?>