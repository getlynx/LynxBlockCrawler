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
	var $raw_input;
	var $raw_output;
	var $raw_address;

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
	    	$block_hash = $this->WalletRPC->getblockhash(intval($start_at));
			$this->raw_block = $this->WalletRPC->getblock($block_hash);
			$this->process_block();
			$start_at++;

			// debug stop at 10
			if ($start_at == 10) { break; }
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
		$this->raw_input = [];
		$this->raw_output = [];
		$this->raw_address = [];
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
				$txs = '"txs":{';
				foreach ($this->raw_block["tx"] as $key => $tx)
				{
					$comma = ($key == 0) ? "" : ",";
					$txs = $txs.$comma.'"'.$key.'":"'.$tx.'"';
					$this->raw_tx = $this->WalletRPC->getrawtransaction($tx);
					
					// collect each tx into its own key
					$this->process_tx();
				}
				$txs = $txs."}";
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

			// Clean up!
			$this->clearcontainers();

			// debug: call it back and spit it out
			$block_data = $this->Redis->hGet($this->RKEY, $rdata["key"]);
			echo "<hr><h2>Block # ".$height."</h2>".$block_data;

		} else { echo "<hr>NULL BLOCK"; }
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
			$inputs = '"inputs":{';
			foreach ($this->raw_tx["vin"] as $key => $this->raw_input)
			{
				$comma = ($key == 0) ? "" : ",";
				$input_id = ( array_key_exists("coinbase", $this->raw_input) ) ? $this->raw_input["coinbase"] : $this->raw_input["scriptSig"]["hex"];
				$input_type = ( array_key_exists("coinbase", $this->raw_input) ) ? "coinbase" : "hex";
				$inputs = $inputs.$comma.'"'.$input_type.'":"'.$input_id.'"';

				// collect each input into its own key
				$this->process_input();
			}
			$inputs = $inputs."}";
			
			$outputs = '"outputs":{';
			foreach ($this->raw_tx["vout"] as $key => $this->raw_output)
			{
				$comma = ($key == 0) ? "" : ",";
				$outputs = $outputs.$comma.'"hex":"'.$this->raw_output["scriptPubKey"]["hex"].'"';

				// collect each output into its own key
				$this->process_output();
			}
			$outputs = $outputs."}";

			$tx_comment = ( array_key_exists("tx-comment", $this->raw_tx) ) ? htmlspecialchars($this->raw_tx["tx-comment"]) : "";

			// redis hash data
			$jdata = 
				'{
					"time":"'.$this->raw_tx["time"].'",
					"ver":"'.$this->raw_tx["version"].'",
					"lock":"'.$this->raw_tx["locktime"].'",
					"block":"'.$this->raw_tx["blockhash"].'",
					"hex":"'.$this->raw_tx["hex"].'",
					"msg":"'.$tx_comment.'",
					'.$inputs.',
					'.$outputs.'
				}';

			// minify
			$rdata["key"] = "tx::".$txid;
			$rdata["data"] = preg_replace("/\s/", "", $jdata);
			
			// send data to Redis
			$this->add_key($rdata);




			// debug: call it back and spit it out
			$tx_data = $this->Redis->hGet($this->RKEY, $rdata["key"]);
			echo "<blockquote><h3>TxID ".$txid."</h3>".$tx_data."</blockquote>";

		} else { echo "<blockquote>NULL TX</blockquote>"; }
	} 

/*

#####  #   #  ####   #   #  #####
  #    ##  #  #   #  #   #    # 
  #    # # #  ####   #   #    # 
  #    #  ##  #      #   #    #  
#####  #   #  #       ###     # 

*/

	// assemble a new transaction INPUT to insert
	function process_input() {

		if ( $this->raw_input ) 
		{

			// redis hash data
			// check if coinbase or not
			if (array_key_exists("coinbase", $this->raw_input))
			{
				$jdata = 
				'{
					"cb":"'.$this->raw_input["coinbase"].'",
					"seq":"'.$this->raw_input["sequence"].'",
					
				}';
				$rdata["key"] = "input::".$this->raw_input["coinbase"];
			} 
			else 
			{
				$jdata = 
				'{
					"seq":"'.$this->raw_input["sequence"].'",
					"txid":"'.$this->raw_input["txid"].'",
					"out":"'.$this->raw_input["vout"].'",
					"asm":"'.$this->raw_input["scriptSig"]["asm"].'",
					"hex":"'.$this->raw_input["scriptSig"]["hex"].'",
				}';
				$rdata["key"] = "input::".$this->raw_input["scriptPubKey"]["hex"];
			}

			// minify
			
			$rdata["data"] = preg_replace('/\s/', '', $jdata);

			// send block data to Redis
			$this->add_key($rdata);




			// debug: call it back and spit it out
			$input_data = $this->Redis->hGet($this->RKEY, $rdata["key"]);
			echo "<blockquote><h4>Input (".$rdata["key"].")</h4>".$input_data."</blockquote>";
		
		} else { echo "<blockquote>NULL INPUT</blockquote>"; }
	}

/*

 ###   #   #  #####  ####   #   #  #####
#   #  #   #    #    #   #  #   #    #
#   #  #   #    #    ####   #   #    # 
#   #  #   #    #    #      #   #    #  
 ###    ###     #    #       ###     #

*/

	// assemble a new transaction OUTPUT to insert
	function process_output() {
		
		if ( $this->raw_output ) 
		{

			$hex = $this->raw_output["scriptPubKey"]["hex"];

			// pre-render address list if any are found
			$addresses = "";
			if (isset ($this->raw_output["scriptPubKey"]["addresses"]))
			{
				$addresses = '"addresses":{';
				foreach ($this->raw_output["scriptPubKey"]["addresses"] as $key => $address)
				{
					$comma = ($key == 0) ? "" : ",";
					$addresses = $addresses.$comma.'"'.$key.'":"'.$address.'"';
					$this->raw_address["address"] = $address;
					$this->raw_address["txid"] = $this->raw_tx["txid"];
					
					// collect each address into its own key
					$this->process_address();

					// clear out for the next
					$this->raw_address = [];
				}

				$addresses = $addresses."}";
			}

			// redis hash data
			$jdata = 
				'{
					"value":"'.$this->raw_output["value"].'",
					"type":"'.$this->raw_output["scriptPubKey"]["type"].'",
					"sigs":"'.$this->raw_output["scriptPubKey"]["reqSigs"].'",
					"asm":"'.$this->raw_output["scriptPubKey"]["asm"].'",
					"hex":"'.$hex.'",
					'.$addresses.'
				}';

			// minify
			$rdata["key"] = "output::".$hex;
			$rdata["data"] = preg_replace('/\s/', '', $jdata);


			// clear the raw data container
			$this->raw_output = [];



			// debug: call it back and spit it out
			$output_data = $this->Redis->hGet($this->RKEY, $rdata["key"]);
			echo "<blockquote><h4>Output (".$hex.")</h4>".$output_data."</blockquote>";
		
		} else { echo "<blockquote>NULL OUTPUT</blockquote>"; }
	}

/*
  
 ###   ####   ####   ####   #####   ####   ####
#   #  #   #  #   #  #   #  #      #      #    
#####  #   #  #   #  ####   ####    ###    ### 
#   #  #   #  #   #  #  #   #          #      #
#   #  ####   ####   #   #  #####  ####   ####

*/

	// assemble new address data to insert
	function process_address() {
		/*
			"address::KT5kYQXjvubU2F7cHWtNdfe9LPPyJX1dKp":"{
				'txs':{
					'7b5f3e5dc24...e203bb2ebbbf3'
				}
			}",
		*/
		if ( $this->raw_address ) 
		{
			$address = $this->raw_address["address"];
			$akey = "address::".$address;
			$txid = $this->raw_address["txid"];

			// find matching address key and read existing tx list
			$txs = $this->Redis->hGet($this->RKEY, $akey);
			if ($txs)
			{
				val_dump(json_decode($txs, TRUE));
			}			


			// toss out any duplicate transactions

			// update list with new txids

			// send back to Redis

			/*

			// pre-render address list if any are found
			$txs = "";
			if (isset ($this->raw_output["scriptPubKey"]["addresses"]))
			{
				$addresses = '"txs":{';
				foreach ($this->raw_output["scriptPubKey"]["addresses"] as $key => $address)
				{
					$comma = ($key == 0) ? "" : ",";
					$addresses = $addresses.$comma.'"'.$key.'":"'.$address.'"';
				}
				$addresses = $addresses."}";
			}

			// redis hash data
			$jdata = 
				'{
					'.$txs.'
				}';

			// minify
			$rdata["key"] = "output::".$hex;
			$rdata["data"] = preg_replace('/\s/', '', $jdata);

			// send block data to Redis
			$this->add_key($rdata);

			// clear the raw data container
			$this->raw_output = [];

			*/

			// debug: call it back and spit it out
			echo "<blockquote><h4>Address</h4>".$this->raw_address["address"]."</blockquote>";
		
		} else { echo "<blockquote>NULL ADDRESS</blockquote>"; }
	}


}

?>