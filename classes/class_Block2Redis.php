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

  var $blockchaininfo;
  var $height;

  function __construct($rpc_user, $rpc_pass, $rpc_addy, $rpc_port)
  {
    // check that Redis is isntalled and operating

    // check if blockchain key exists

    // if not, insert new --> COIN::Blockchain

  	// TODO: get latest db height
    $this->height = $this->height();

	// Include and instantiate the WalletRPC class
    require_once ("class_WalletRPC.php");
    $this->WalletRPC = new WalletRPC($rpc_user, $rpc_pass, $rpc_addy, $rpc_port);

    // Get blockchain info for height
    $this->blockchaininfo = $this->WalletRPC->getblockchaininfo();
	
	// check latest scanned height versus actual height    
    if ($this->height < $this->blockchaininfo["blocks"]) 
  	{ 
  		$this->crawl(200); 
  	}

  }

  	// rescan the chain for any new blocks, starting backwards a bit
	function crawl($rewind_by) {
		
		$start_at = $this->blockchaininfo["blocks"] - $rewind_by;

		if ($this->height < $start_at)
		{

		}
		
		
	}

	// return latest database height
	function height() {
		// get array of REDIS hkeys matching "block::*" (maybe using hScan)
		// copy to new array parsing out "block::"
		// sort by value, largest first
		// return first index
		return 0;
	}

	// assemble a new block to insert
	function build_block($raw_block) {
		/*
			"block::$height":"{
				'date':'$date',
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
		*/
	}

	// insert the block into Redis
	function add_block() {

	}

	// assemble a new transaction to insert
	function build_tx($raw_tx) {
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

	// insert the transaction into Redis
	function add_tx() {

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

	// insert the transaction INPUTS into Redis
	function add_input() {

	}

	// assemble a new transaction OUTPUTS to insert
	function build_output($raw_output) {
		
		// pre-render address list if any are found
		$addresses = "";
		if (isset ($raw_output["scriptPubKey"]["addresses"]))
		{
			$addresses = "'addresses':{";
			foreach ($raw_output["scriptPubKey"]["addresses"] as $address)
			{
				$addresses = $addresses."'".$address."',";
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
				".$addresses."
			}';

		// minify
		$rdata['key'] = "output::".$raw_output["scriptPubKey"]["hex"];
		$rdata['value'] = preg_replace('/\s/', '', $jdata);
		
		return $rdata;
	}

	// insert the transaction OUTPUTS into Redis
	function add_output() {

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