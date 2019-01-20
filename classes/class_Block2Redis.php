<?php

/*
 * Duplicates an entire blockchain within a single Redis hash key
 */

class Block2Redis {

	// Redis containers
	var $Redis;
	var $RKEY;
	var $dbheight;
	
	// Wallet containers
	var $WalletRPC;
	var $blockchaininfo;

	function __construct($rpc_user, $rpc_pass, $rpc_addy, $rpc_port, $coin="LYNX")
	{
		// Connect to Redis server on localhost 
		$this->Redis = new Redis(); 
		$this->Redis->connect('127.0.0.1', 6379);

		// get the latest db height
		$this->RKEY = $coin."::Blockchain";

		$this->dbheight = $this->getdbheight();

		echo "Latest DB Block is ".$this->dbheight."<br><br>";

		// Include and instantiate the WalletRPC class
		require_once ("class_WalletRPC.php");
		$this->WalletRPC = new WalletRPC($rpc_user, $rpc_pass, $rpc_addy, $rpc_port);

		// Get blockchain info for height
		$this->blockchaininfo = $this->WalletRPC->getblockchaininfo();

	}

  	// scan the chain and record any new blocks
  	// overwriting backwards a bit each time

	function scan($rewind_by)
	{	
		$start_at = $this->dbheight - $rewind_by;
		$start_at = ($start_at < 0) ? 0 : $start_at;

		echo "Scanning from block ".$start_at."...<br/><br/>";

		// check latest scanned height versus actual height    
	    while ($start_at < $this->blockchaininfo["blocks"]) 
	    {
	    	$block_hash = $this->WalletRPC->getblockhash(intval($start_at));
			$raw_block = $this->WalletRPC->getblock($block_hash);
			$this->process_block($raw_block);
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




/*

####   #       ###    ####  #   #   ####
#   #  #      #   #  #      #  #   #    
####   #      #   #  #      ###     ### 
#   #  #      #   #  #      #  #       #
####   #####   ###    ####  #   #  #### 

*/

	// assemble a new block to insert
	function process_block($raw_block) {

		// pre-render tx list if any are found
		$txs = "";
		if ( array_key_exists("tx", $raw_block) )
    	{
			$txs = '"txs":{';
			foreach ($raw_block["tx"] as $key => $tx)
			{
				$comma = ($key == 0) ? "" : ",";
				$txs = $txs.$comma.'"'.$key.'":"'.$tx.'"';
				$raw_tx = $this->WalletRPC->getrawtransaction($tx);
				
				// collect each tx into its own key
				$this->process_tx($raw_tx);
			}
			$txs = $txs."}";
		}

		// redis hash data
		$jdata = 
			'{
				"time":"'.$raw_block["time"].'",
				"hash":"'.$raw_block["hash"].'",
				"ver":"'.$raw_block["version"].'",
				"size":"'.$raw_block["size"].'",
				"bits":"'.$raw_block["bits"].'",
				"nonce":"'.$raw_block["nonce"].'",
				"diff":"'.$raw_block["difficulty"].'",
				"root":"'.$raw_block["merkleroot"].'",
				'.$txs.'
			}';

		// minify
		$rdata["key"] = "block::".$raw_block["height"];
		$rdata["data"] = preg_replace("/\s/", "", $jdata);
		
		// send block data to Redis
		$this->add_key($rdata);

		// update db height value
		$this->Redis->hSet($this->RKEY, "height", $raw_block["height"]);

		// debug: call it back and spit it out
		$block_data = $this->Redis->hGet($this->RKEY, $rdata["key"]);
		echo "<hr>".$block_data;
	}

/*

#####  #   #   ####
  #     # #   #    
  #      #     ### 
  #     # #       #
  #    #   #  #### 

*/

	// assemble a new transaction to insert
	function process_tx($raw_tx) {

		// pre-render inputs and outputs
		$inputs = '"inputs":{';
		if (array_key_exists("vin",$raw_tx))
		{
			foreach ($raw_tx["vin"] as $key => $raw_input)
			{
				$comma = ($key == 0) ? "" : ",";
				$input_id = ( array_key_exists("coinbase", $raw_input) ) ? $raw_input["coinbase"] : $raw_input["scriptSig"]["hex"];
				$input_type = ( array_key_exists("coinbase", $raw_input) ) ? "coinbase" : "hex";
				$inputs = $inputs.$comma.'"'.$input_type.'":"'.$input_id.'"';

				// collect each input into its own key
				$this->process_input($raw_input);
			}
		}
		$inputs = $inputs."}";
		
		$outputs = '"outputs":{';
		if (array_key_exists("vin",$raw_tx))
		{
			foreach ($raw_tx["vout"] as $key => $raw_output)
			{
				$comma = ($key == 0) ? "" : ",";
				$outputs = $outputs.$comma.'"hex":"'.$raw_ouput["scriptSig"]["hex"].'"';

				// collect each output into its own key
				$this->process_input($raw_output);
			}
		}
		$outputs = $outputs."}";

		// redis hash data
		$jdata = 
			'{
				"time":"'.$raw_tx["time"].'",
				"ver":"'.$raw_tx["version"].'",
				"lock":"'.$raw_tx["locktime"].'",
				"block":"'.$raw_tx["blockhash"].'",
				"hex":"'.$raw_tx["hex"].'",
				"msg":"'.htmlspecialchars($raw_tx["tx-comment"]).'",
				"intype":"'.$input_type.'",
				'.$inputs.',
				'.$outputs.'
			}';

		// minify
		$rdata["key"] = "tx::".$raw_tx["txid"];
		$rdata["data"] = preg_replace("/\s/", "", $jdata);
		
		// send block data to Redis
		$this->add_key($rdata);

		// debug: call it back and spit it out
		$tx_data = $this->Redis->hGet($this->RKEY, $rdata["key"]);
		echo "<blockquote>".$tx_data."</blockquote>";
	}

/*

#####  #   #  ####   #   #  #####   ####
  #    ##  #  #   #  #   #    #    #
  #    # # #  ####   #   #    #     ###
  #    #  ##  #      #   #    #        #
#####  #   #  #       ###     #    ####

*/

	// assemble a new transaction INPUTS to insert
	function process_input($raw_input) {
		/*
			"input::038326270472f4...1000000000000000":"{
				value':'1',
				'type':'pubkeyhash',
				'sigs':'1',
				'asm':'OP_DUP OP_HASH160 d9f...43349 OP_EQUALVERIFY OP_CHECKSIG',
				'hex':'76a914d9fc995d9...0687474334988ac',
				'addresses':{
					'KT5kYQXjv...dfe9LPPyJX1dKp',
				}
			}",
		*/
	}

/*

 ###   #   #  #####  ####   #   #  #####   ####
#   #  #   #    #    #   #  #   #    #    #
#   #  #   #    #    ####   #   #    #     ###
#   #  #   #    #    #      #   #    #        #
 ###    ###     #    #       ###     #    ####

*/

	// assemble a new transaction OUTPUTS to insert
	function process_output($raw_output) {
		
		// pre-render address list if any are found
		$addresses = "";
		if (isset ($raw_output["scriptPubKey"]["addresses"]))
		{
			$addresses = '"addresses":{';
			foreach ($raw_output["scriptPubKey"]["addresses"] as $key => $address)
			{
				$comma = ($key == 0) ? "" : ",";
				$addresses = $addresses.$comma.'"'.$key.'":"'.$address.'"';
			}
			$addresses = $addresses."}";
		}

		// redis hash data
		$jdata = 
			'{
				"value":"'.$raw_output["value"].'",
				"type":"'.$raw_output["scriptPubKey"]["type"].'",
				"sigs":"'.$raw_output["scriptPubKey"]["reqSigs"].'",
				"asm":"'.$raw_output["scriptPubKey"]["asm"].'",
				"hex":"'.$raw_output["scriptPubKey"]["hex"].'",
				'.$addresses.'
			}';

		// minify
		$rdata["key"] = "output::".$raw_output["scriptPubKey"]["hex"];
		$rdata["data"] = preg_replace('/\s/', '', $jdata);

		// send block data to Redis
		$this->add_key($rdata);

		// debug: call it back and spit it out
		$output_data = $this->Redis->hGet($this->RKEY, $rdata["key"]);
		echo "<blockquote>".$output_data."</blockquote>";
	}

	// assemble new address data to insert
	function build_address($raw_address) {
		/*
			"address::KT5kYQXjvubU2F7cHWtNdfe9LPPyJX1dKp":"{
				'txs':{
					'7b5f3e5dc24...e203bb2ebbbf3'
				}
			}",
		*/
	}

	// insert or update the Redis address data
	function update_address() {
		
		// find matching address key and tx list

		// toss out any duplicate transactions

		// update list with new txids

		// send back to Redis
	}


}

?>