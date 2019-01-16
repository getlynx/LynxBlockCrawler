<?php

// Show debug console output?
define("DEBUG", FALSE);

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
$$\   $$\     $$\ $$\   $$\ $$\   $$\                               
$$ |  \$$\   $$  |$$$\  $$ |$$ |  $$ |                              
$$ |   \$$\ $$  / $$$$\ $$ |\$$\ $$  |                              
$$ |    \$$$$  /  $$ $$\$$ | \$$$$  /                               
$$ |     \$$  /   $$ \$$$$ | $$  $$<                                
$$ |      $$ |    $$ |\$$$ |$$  /\$$\                               
$$$$$$$$\ $$ |    $$ | \$$ |$$ /  $$ |                              
\________|\__|    \__|  \__|\__|  \__|                              
$$$$$$$\  $$\                     $$\                               
$$  __$$\ $$ |                    $$ |                              
$$ |  $$ |$$ | $$$$$$\   $$$$$$$\ $$ |  $$\                         
$$$$$$$\ |$$ |$$  __$$\ $$  _____|$$ | $$  |                        
$$  __$$\ $$ |$$ /  $$ |$$ /      $$$$$$  /                         
$$ |  $$ |$$ |$$ |  $$ |$$ |      $$  _$$<                          
$$$$$$$  |$$ |\$$$$$$  |\$$$$$$$\ $$ | \$$\                         
\_______/ \__| \______/  \_______|\__|  \__|                        
 $$$$$$\                                   $$\                      
$$  __$$\                                  $$ |                     
$$ /  \__| $$$$$$\  $$$$$$\  $$\  $$\  $$\ $$ | $$$$$$\   $$$$$$\   
$$ |      $$  __$$\ \____$$\ $$ | $$ | $$ |$$ |$$  __$$\ $$  __$$\  
$$ |      $$ |  \__|$$$$$$$ |$$ | $$ | $$ |$$ |$$$$$$$$ |$$ |  \__| 
$$ |  $$\ $$ |     $$  __$$ |$$ | $$ | $$ |$$ |$$   ____|$$ |       
\$$$$$$  |$$ |     \$$$$$$$ |\$$$$$\$$$$  |$$ |\$$$$$$$\ $$ |   by  
 \______/ \__|      \_______| \_____\____/ \__| \_______|\__| auscoi
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

// Include and instantiate the BlockCrawler class 
require_once ("./classes/class_BlockCrawler.php");
$BlockCrawler = new BlockCrawler("/var/www/crawler.conf");

// Check for a $_REQUEST and set page content accordingly...
    if (isset($_REQUEST["hash"]))    { $BlockCrawler->site_content = $BlockCrawler->lookup_block($_REQUEST["hash"], TRUE); }
elseif (isset($_REQUEST["txid"]))    { $BlockCrawler->site_content = $BlockCrawler->lookup_txid($_REQUEST["txid"]); }
elseif (isset($_REQUEST["address"])) { $BlockCrawler->site_content = $BlockCrawler->lookup_address($_REQUEST["address"]); }
elseif (isset($_REQUEST["height"]))  { $BlockCrawler->site_content = $BlockCrawler->lookup_block($_REQUEST["height"]); }
elseif (isset($_REQUEST["search"]))  {
	
	// Make sure there's a request...
	if (empty($_REQUEST["search"])) 
	{ 
		$BlockCrawler->site_content = $BlockCrawler->error("no_request");
	}
	// Make sure it's alphanumeric only...
	elseif (! ctype_alnum($_REQUEST["search"])) 
	{
		$BlockCrawler->site_content = $BlockCrawler->error("alphanumeric");
	}
	// Check query character length...
	elseif (strlen($_REQUEST["search"]) == 64) 
	{
		// The query is either a txid or blockhash
		$BlockCrawler->debug("QUERY: ".$_REQUEST["search"]." (hash)");
		$BlockCrawler->site_content = $BlockCrawler->check_hash($_REQUEST["search"]);
	}
	/* 
	elseif (strlen($_REQUEST["search"]) > 30) 
	{
		// The query is an address
		$BlockCrawler->site_content = $BlockCrawler->lookup_address($_REQUEST["search"]);
		$BlockCrawler->debug("QUERY: ".$_REQUEST["search"]." (address)");
	}
	*/ 
	else {
		// The query is a block height
		$BlockCrawler->debug("QUERY: ".$_REQUEST["search"]." (block height)");
		$BlockCrawler->site_content = $BlockCrawler->lookup_block($_REQUEST["search"]);
	}
} 
else 
{

	// No query? Display dashboard instead!
	$BlockCrawler->debug("LOAD PAGE: Dashboard");
	$BlockCrawler->site_content = $BlockCrawler->show_dashboard();
}

// Build page
echo $BlockCrawler->site_header();
echo $BlockCrawler->site_content();
echo $BlockCrawler->site_footer(); 

?>
