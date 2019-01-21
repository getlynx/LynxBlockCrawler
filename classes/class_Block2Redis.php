<?php

/*
 * Crawls a blockchain and indexes it all to a single Redis hash
 */

class Block2Redis {

	// Redis containers
	var $Redis;
	var $RKEY;
	var $dbheight;
	
	// Wallet containers
	var $WalletRPC;
	var $blockchaininfo;

	// Raw data containers
	var $raw_block;
	var $raw_tx;
	
	function __construct($rpc_user, $rpc_pass, $rpc_addy, $rpc_port, $coin="LYNX")
	{
		// Connect to Redis server on localhost 
		$this->Redis = new Redis(); 
		$this->Redis->connect('127.0.0.1', 6379);

		// get the latest db height
		$this->RKEY = $coin."::Blockchain";

		$this->dbheight = $this->getdbheight();

		echo "<h1>Latest DB Block is ".$this->dbheight."<br><br>";

		// Include and instantiate the WalletRPC class
		require_once ("class_WalletRPC.php");
		$this->WalletRPC = new WalletRPC($rpc_user, $rpc_pass, $rpc_addy, $rpc_port);

		// Get blockchain info for height
		$this->blockchaininfo = $this->WalletRPC->getblockchaininfo();

		// Set raw data containers as arrays
		$this->clearcontainers();

	}

  	// scan the chain and record any new blocks
  	// overwriting backwards a bit each time

	function scan($rewind_by)
	{	
		$start_at = $this->dbheight - $rewind_by;
		$start_at = ($start_at < 0) ? 0 : $start_at;

		echo "Scanning from block ".$start_at."...</h1>";

		// check latest scanned height versus actual height    
	    while ($start_at < $this->blockchaininfo["blocks"]) 
	    {
	    	$this->clearcontainers();
	    	$block_hash = $this->WalletRPC->getblockhash(intval($start_at));
			$this->raw_block = $this->WalletRPC->getblock($block_hash);
			$this->process_block();
			$this->raw_block = [];
			$start_at++;

			// debug stop at 10
			if ($start_at == 250) { break; }
			// Clean up!
			
	    }	






		// build output
		//$output = $this->Block2Redis->build_output($txout);
		
		
	}

 	// return latest database height
	function getdbheight() 
	{
	   	if ($this->Redis->exists($this->RKEY))
	   		if ($this->Redis->hexists($this->RKEY, "height"))
	   			return $this->Redis->hget($this->RKEY, "height");
		return 0;
	}

	// insert a key into Redis
	function add_key($rdata) {
		$this->Redis->hSet($this->RKEY, $rdata["key"], $rdata["data"]);
	}

	function clearcontainers() {
		$this->raw_block = [];
		$this->raw_tx = [];
	}




/*

####   #       ###    ####  #   # 
#   #  #      #   #  #      #  #   
####   #      #   #  #      ###   
#   #  #      #   #  #      #  #   
####   #####   ###    ####  #   # 

*/

	// assemble a new block to insert
	function process_block() {

		if ( $this->raw_block ) 
		{

			$height = $this->raw_block["height"];

			// pre-render tx list if any are found
			$txs = "";
			if ( array_key_exists("tx", $this->raw_block) )
	    	{
				$txs = '"txs":[';
				foreach ($this->raw_block["tx"] as $key => $txid)
				{
					$comma = ($key == 0) ? "" : ",";
					$txs = $txs.$comma.'"'.$txid.'"';
					
					// collect each tx into its own key
					$this->raw_tx = $this->WalletRPC->getrawtransaction($txid);
					$this->process_tx();
					$this->raw_tx = [];
				}
				$txs = $txs."]";
			}

			// redis hash data
			$jdata = 
				'{
					"time":"'.$this->raw_block["time"].'",
					"hash":"'.$this->raw_block["hash"].'",
					"ver":"'.$this->raw_block["version"].'",
					"size":"'.$this->raw_block["size"].'",
					"bits":"'.$this->raw_block["bits"].'",
					"nonce":"'.$this->raw_block["nonce"].'",
					"diff":"'.$this->raw_block["difficulty"].'",
					"root":"'.$this->raw_block["merkleroot"].'",
					'.$txs.'
				}';

			// minify
			$rdata["key"] = "block::".$height;
			$rdata["data"] = preg_replace("/\s/", "", $jdata);
			
			// send data to Redis
			$this->add_key($rdata);

			// update db height value
			$this->Redis->hSet($this->RKEY, "height", $height);

			// debug: call it back and spit it out
			$block_data = $this->Redis->hGet($this->RKEY, $rdata["key"]);
			echo "<h2>&#8593; ".$rdata["key"]." &#8593;</h2>".$block_data."<hr>";

		}
	}

/*

#####  #   #  
  #     # #  
  #      #    
  #     # #    
  #    #   #  

*/

	// assemble a new transaction to insert
	function process_tx() {

		if ( $this->raw_tx )
		{

			$txid = $this->raw_tx["txid"];

			// pre-render inputs and outputs
			$this->process_txins($this->raw_tx["vin"]);
			$this->process_txouts($this->raw_tx["vout"]);

			$tx_comment = ( array_key_exists("tx-comment", $this->raw_tx) ) ? htmlspecialchars($this->raw_tx["tx-comment"]) : "";

			// redis hash data
			$jdata = 
				'{
					"time":"'.$this->raw_tx["time"].'",
					"ver":"'.$this->raw_tx["version"].'",
					"lock":"'.$this->raw_tx["locktime"].'",
					"block":"'.$this->raw_tx["blockhash"].'",
					"hex":"'.$this->raw_tx["hex"].'",
					"msg":"'.$tx_comment.'"
				}';

			// minify
			$rdata["key"] = "tx::".$txid;
			$rdata["data"] = preg_replace("/\s/", "", $jdata);
			
			// send data to Redis
			$this->add_key($rdata);




			// debug: call it back and spit it out
			$tx_data = $this->Redis->hGet($this->RKEY, $rdata["key"]);
			echo "<blockquote><h3>".$rdata["key"]."</h3>".$tx_data."</blockquote>";

		}
	} 

/*

#####  #   #  ####   #   #  #####
  #    ##  #  #   #  #   #    # 
  #    # # #  ####   #   #    # 
  #    #  ##  #      #   #    #  
#####  #   #  #       ###     # 

*/

	// assemble a new transaction INPUT to insert
	function process_txins($txins) {

		if ( $txins ) 
		{
			$jdata = "[";
			foreach ($txins as $key => $raw_input)
			{
				$comma = ($key == 0) ? "" : ",";
				
				// check if coinbase or not
				if (array_key_exists("coinbase", $raw_input))
				{
					$jdata = 
					$jdata.$comma.'{
						"cb":"'.$raw_input["coinbase"].'",
						"seq":"'.$raw_input["sequence"].'"
						
					}';
				} 
				else 
				{
					$jdata = 
					$jdata.$comma.'{
						"seq":"'.$raw_input["sequence"].'",
						"txid":"'.$raw_input["txid"].'",
						"out":"'.$raw_input["vout"].'",
						"asm":"'.$raw_input["scriptSig"]["asm"].'",
						"hex":"'.$raw_input["scriptSig"]["hex"].'"
					}';
				}
				
			}
			$jdata = $jdata."]";

			// minify
			$rdata["key"] = "txins::".$this->raw_tx["txid"];
			$rdata["data"] = preg_replace('/\s/', '', $jdata);

			// send block data to Redis
			$this->add_key($rdata);




			// debug: call it back and spit it out
			$input_data = $this->Redis->hGet($this->RKEY, $rdata["key"]);
			echo "<blockquote><h4>".$rdata["key"]."</h4>".$input_data."</blockquote>";

		}
	
	}

/*

 ###   #   #  #####  ####   #   #  #####
#   #  #   #    #    #   #  #   #    #
#   #  #   #    #    ####   #   #    # 
#   #  #   #    #    #      #   #    #  
 ###    ###     #    #       ###     #

*/
	
	// assemble a new transaction INPUT to insert
	function process_txouts($txouts) {

		if ( $txouts ) 
		{
			$jdata = "[";
			foreach ($txouts as $key => $raw_output)
			{
				$comma = ($key == 0) ? "" : ",";
				
				// pre-render address list if any are found
				$addresses = "";
				if (isset ($raw_output["scriptPubKey"]["addresses"]))
				{
					$addresses = ', "addresses":[';
					foreach ($raw_output["scriptPubKey"]["addresses"] as $key => $address)
					{
						$comma = ($key == 0) ? "" : ",";
						$addresses = $addresses.$comma.'"'.$address.'"';
						$raw_address["address"] = $address;
						$raw_address["txid"] = $this->raw_tx["txid"];
						
						// collect each address into its own key
						$this->process_address($raw_address);

					}

					$addresses = $addresses."]";
				}

				$jdata = 
				$jdata.$comma.'{
					"value":"'.$raw_output["value"].'",
					"type":"'.$raw_output["scriptPubKey"]["type"].'",
					"sigs":"'.$raw_output["scriptPubKey"]["reqSigs"].'",
					"asm":"'.$raw_output["scriptPubKey"]["asm"].'",
					"hex":"'.$raw_output["scriptPubKey"]["hex"].'"
					'.$addresses.'
				}';
			}
			$jdata = $jdata."]";

			// minify
			$rdata["key"] = "txouts::".$this->raw_tx["txid"];
			$rdata["data"] = preg_replace('/\s/', '', $jdata);

			// send block data to Redis
			$this->add_key($rdata);




			// debug: call it back and spit it out
			$output_data = $this->Redis->hGet($this->RKEY, $rdata["key"]);
			echo "<blockquote><h4>".$rdata["key"]."</h4>".$output_data."</blockquote>";

		}
	
	}

/*
  
 ###   ####   ####   ####   #####   ####   ####
#   #  #   #  #   #  #   #  #      #      #    
#####  #   #  #   #  ####   ####    ###    ### 
#   #  #   #  #   #  #  #   #          #      #
#   #  ####   ####   #   #  #####  ####   ####

*/

	// assemble new address data to insert
	function process_address($raw_address) {
		/*
			"address::KT5kYQXjvubU2F7cHWtNdfe9LPPyJX1dKp":"{
				"txs":[
					"7b5f3e5dc24...e203bb2ebbbf3",
					"7b5f3e5dc24...e203bb2ebbbf3"
				]
			}",
		*/
		if ( $raw_address ) 
		{
			$address = $raw_address["address"];
			$akey = "address::".$address;
			$txid = $raw_address["txid"];

			// find matching address key and read existing tx list
			$txids = [];
			$rdata = $this->Redis->hGet($this->RKEY, $akey);
			if ($rdata)
			{
				// get list if exists
				$txids = json_decode($rdata, TRUE);
			}

			var_dump($txids);

			// add txid to the list if it is not already there
			if (! in_array($txid, $txids)) { array_push($txids, $txid); }

			$jdata = '{"txs":[';

			foreach ($txids as $key => $id)
			{
				$comma = ($key == 0) ? "" : ",";
				$jdata = $jdata.$comma.'"'.$id.'"';				
			}

			$jdata = $jdata.']}';

			// minify
			$rdata["key"] = $akey;
			$rdata["data"] = preg_replace('/\s/', '', $jdata);

			// send block data to Redis
			$this->add_key($rdata);

			// debug: call it back and spit it out
			$address_data = $this->Redis->hGet($this->RKEY, $rdata["key"]);
			echo "<blockquote><h4>".$rdata["key"]."</h4>".$address_data."</blockquote>";

		} else { echo "<blockquote>NULL ADDRESS</blockquote>"; }
	}


}

?>