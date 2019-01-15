<?php

// Show debug console output?
define("DEBUG", TRUE);

// Include and instantiate the BlockCrawler class 
require_once ("./classes/class_BlockCrawler.php");
$BlockCrawler = new BlockCrawler('/var/www/lynx.conf');
//$BlockCrawler = new BlockCrawler('./_resources/test.conf');

// Check for a $_REQUEST and set page content accordingly...
    if (isset($_REQUEST["hash"]))    { $BlockCrawler->site_content = $BlockCrawler->lookup_block($_REQUEST["hash"], TRUE); }
elseif (isset($_REQUEST["txid"]))    { $BlockCrawler->site_content = $BlockCrawler->lookup_txid($_REQUEST["txid"]); }
elseif (isset($_REQUEST["address"])) { $BlockCrawler->site_content = $BlockCrawler->lookup_address($_REQUEST["address"]); }
elseif (isset($_REQUEST["height"]))  { $BlockCrawler->site_content = $BlockCrawler->lookup_block($_REQUEST["height"]); }
elseif (isset($_REQUEST["search"]))  {
	
	$search_request = $_REQUEST["search"];

	echo "Search: ".$search_request;

	if ($search_request == ""){ $BlockCrawler->error("no_request"); return FALSE; }

	// Make sure it's alphanumeric only
	if (! ctype_alnum($search_request)) {
		BlockCrawler->site_content = $BlockCrawler->get_error("alphanumeric");
		$BlockCrawler->debug("ERROR: query was not alphanumeric");
		return FALSE;
	}
	
	// Check query character length...
	if (strlen($search_request) == 64) {
		
		// The query is either a txid or blockhash...
		$BlockCrawler->site_content = $BlockCrawler->check_hash($search_request);
		$BlockCrawler->debug("QUERY: ".$search_request." (hash)");

	} else if (strlen($search_request) > 30) {

		// The query is an address...
		$BlockCrawler->site_content = $BlockCrawler->lookup_address($search_request);
		$BlockCrawler->debug("QUERY: ".$search_request." (address)");

	} else {

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
