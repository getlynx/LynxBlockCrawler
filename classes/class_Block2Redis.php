<?php
/*

Rescan last 200 blocks from latest height each time

Block Conf = calculate based on # of blocks ahead of this one

Seperate address rescanner that loops through all existing in db and updates their data

COIN::Blockchain 
{



	"block::2565763":"{
		'date':'1234567890',
		'hash':'9ba939488a68ba53...772275073917',
		'ver':'536870912',
		'size':'243',
		'bits':'1c39660b',
		'nonce':'527982446',
		'diff':'4.45997062',
		'root':'7b5f3e5dc24...e203bb2ebbbf3',
		'txs':{
			'7b5f3e5dc24...e203bb2ebbbf3'
		}
	}",



	"tx::7b5f3e5dc24...e203bb2ebbbf3":"{
		'date':'1234567890',
		'ver':'1',
		'lock':'0',
		'block':'9ba939488...5314772275073917',
		'hex':'0100000001000...88ac00000000',
		'inputs':{
			{
				'038326270472f4...1000000000000000', # <-- either coinbase OR hex depending
			},
		},
		'outputs':{
			{
				'hex':'76a914d9fc995d9...0687474334988ac',
			},
		}
	}",



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



	"output::76a914d9fc995d9...0687474334988ac":"{
		'value':'1',
		'type':'pubkeyhash',
		'sigs':'1',
		'asm':'OP_DUP OP_HASH160 d9f...43349 OP_EQUALVERIFY OP_CHECKSIG',
		'hex':'76a914d9fc995d9...0687474334988ac',
		'addresses':{
			'KT5kYQXjv...dfe9LPPyJX1dKp',
		}
	}",



	"address::KT5kYQXjvubU2F7cHWtNdfe9LPPyJX1dKp":"{
		'txs':{
			'7b5f3e5dc24...e203bb2ebbbf3'
		}
	}",

*/

/*
 * Duplicates an entire blockchain within a single Redis hash key
 */

class Block2Redis {

  var $Redis;
  var $WalletRPC;
  var $blockchaininfo;
  var $height;
  var $RKEY;

	function __construct($rpc_user, $rpc_pass, $rpc_addy, $rpc_port, $coin="LYNX")
	{
		// Connect to Redis server on localhost 
		$this->Redis = new Redis(); 
		$this->Redis->connect('127.0.0.1', 6379);

		// get the latest db height
		$this->RKEY = $coin."::Blockchain";

		$this->height = $this->getheight();

		echo "Latest DB Block is ".$this->height."<br><br>";

		// Include and instantiate the WalletRPC class
		require_once ("class_WalletRPC.php");
		$this->WalletRPC = new WalletRPC($rpc_user, $rpc_pass, $rpc_addy, $rpc_port);

		// Get blockchain info for height
		$this->blockchaininfo = $this->WalletRPC->getblockchaininfo();

	}

 	// return latest database height
	function getheight() 
	{
	   	if ($this->Redis->exists($this->RKEY)) {
	   		if ($this->Redis->hexists($this->RKEY, "height")) {
	   			return $this->Redis->hget($this->RKEY, "height");
			}
		}
		return 0;
	}

  	// rescan the chain for any new blocks, starting backwards a bit
	function scan($rewind_by)
	{	
		$start_at = $this->height - $rewind_by;
		$start_at = ($start_at < 0) ? 0 : $start_at;

		echo "Scanning...<br/><br/>";

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



	// insert a key into Redis
	function add_key($rdata) {
		$this->Redis->hSet($this->RKEY, $rdata["key"], $rdata["data"]);
	}

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
				$new_tx = $this->process_tx($raw_tx);
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

		$block_data = $this->Redis-hGet($this->RKEY, $rdata["key"]);
		echo "<hr>".$block_data;
	}

	// assemble a new transaction to insert
	function process_tx($raw_tx) {
		/*
			"tx::7b5f3e5dc24...e203bb2ebbbf3":"{
				'date':'1234567890',
				'ver':'1',
				'lock':'0',
				'block':'9ba939488...5314772275073917',
				'hex':'0100000001000...88ac00000000',
				'inputs':{
					{
						'038326270472f4...1000000000000000', # <-- either coinbase OR hex depending
					},
				},
				'outputs':{
					{
						'hex':'76a914d9fc995d9...0687474334988ac',
					},
				}
			}",
		*/
	}

	// assemble a new transaction INPUTS to insert
	function build_input($raw_input) {
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

	// assemble a new transaction OUTPUTS to insert
	function build_output($raw_output) {
		
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
		$rdata['key'] = "output::".$raw_output["scriptPubKey"]["hex"];
		$rdata['data'] = preg_replace('/\s/', '', $jdata);
		
		return $rdata;
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