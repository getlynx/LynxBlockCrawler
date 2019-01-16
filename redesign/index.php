<?php

// Show debug console output?
define("DEBUG", TRUE);

/* 
EXAMPLES
--------
 TXID: c2adb964220f170f6c4fe9002f0db19a6f9c9608f6f765ba0629ac3897028de5
BLOCK: 984b30fc9bb5e5ff424ad7f4ec1930538a7b14a2d93e58ad7976c23154ea4a76
*/

// Include and instantiate the BlockCrawler class 
require_once ("./classes/class_BlockCrawler.php");
//$BlockCrawler = new BlockCrawler('/var/www/lynx.conf');
$BlockCrawler = new BlockCrawler('./_resources/test.conf');

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
