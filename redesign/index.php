<?php

// Show debug console output?
define("DEBUG", FALSE);

/* 
EXAMPLES
--------
 TXID: 17be4183fe08a03942a9aa6b32c48ddcd70ac86a7d7281acb68413eda1a82cb4
BLOCK: 55971fc9e31bfcbd3cb61bca352cb8f5345f691f2b502b78ffdd31cf448d7722
*/

// Include and instantiate the BlockCrawler class 
require_once ("./classes/class_BlockCrawler.php");
$BlockCrawler = new BlockCrawler('/var/www/crawler.conf');
//$BlockCrawler = new BlockCrawler('./_resources/test.conf');

// Check for a $_REQUEST and set page content accordingly...
    if (isset($_REQUEST["hash"]))    { $BlockCrawler->site_content = $BlockCrawler->lookup_block($_REQUEST["hash"], TRUE); }
elseif (isset($_REQUEST["txid"]))    { $BlockCrawler->site_content = $BlockCrawler->lookup_txid($_REQUEST["txid"]); }
elseif (isset($_REQUEST["address"])) { $BlockCrawler->site_content = $BlockCrawler->lookup_address($_REQUEST["address"]); }
elseif (isset($_REQUEST["height"]))  { $BlockCrawler->site_content = $BlockCrawler->lookup_block($_REQUEST["height"]); }
elseif (isset($_REQUEST["search"]))  {
	
	$search_request = $_REQUEST["search"];

	if (empty($search_request)) 
	{ 
		$BlockCrawler->site_content = $BlockCrawler->error("no_request");
	}
	// Make sure it's alphanumeric only
	elseif (! ctype_alnum($search_request)) 
	{
		$BlockCrawler->site_content = $BlockCrawler->error("alphanumeric");
	}
	// Check query character length...
	elseif (strlen($search_request) == 64) 
	{
		// The query is either a txid or blockhash...
		$BlockCrawler->site_content = $BlockCrawler->check_hash($search_request);
		$BlockCrawler->debug("QUERY: ".$search_request." (hash)");
	
	}
	/* 
	elseif (strlen($search_request) > 30) 
	{
		// The query is an address...
		$BlockCrawler->site_content = $BlockCrawler->lookup_address($search_request);
		$BlockCrawler->debug("QUERY: ".$search_request." (address)");
	
	}
	*/ 
	else {
		// The query is a block height...
		$BlockCrawler->site_content = $BlockCrawler->lookup_block($search_request);
		$BlockCrawler->debug("QUERY: ".$search_request." (block height)");
	}
} else {

	// No query? Display dashboard
	$BlockCrawler->debug("SHOW: Dashboard");
	$BlockCrawler->site_content = $BlockCrawler->show_dashboard();

}

// Build page
echo $BlockCrawler->site_header();
echo $BlockCrawler->site_content;
echo $BlockCrawler->site_footer(); 

?>
