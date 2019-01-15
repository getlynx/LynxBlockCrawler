<?php

/* * * * * * * * * * * 
  Class WalletRPC()
 * * * * * * * * * * */

class WalletRPC {

  var $rpc_user;
  var $rpc_pass;
  var $rpc_addy;
  var $rpc_port;
  var $phrase;

  function __construct($rpc_user="", $rpc_pass="", $rpc_addy="127.0.0.1", $rpc_port="8888", $phrase="")
  {
    $this->rpc_user = $rpc_user;
    $this->rpc_pass = $rpc_pass;
    $this->rpc_addy = $rpc_addy;
    $this->rpc_port = $rpc_port;
      $this->phrase = $phrase;

    $this->debug(" Class WalletRPC(): Initializing...");
  }

  // Debug output to console
  function debug($output="")
  {
    if (DEBUG) {
      if ( $output == "" ) { return FALSE; }
      if ( is_array( $output ) ) { $output = implode( ',', $output); }
      echo "<script>console.log( 'DEBUG --> " . $output . "' );</script>";
    }
  }

  // Returns Array on success
  function run($command="")
  {
    // Command must be supplied
    if ($command == ""){return FALSE;}

    //  Encode the request as JSON for the wallet
    $jdata = json_encode($command);

    $this->debug(" WalletRPC.Run(): ".$jdata);

    //  Create curl connection object
    $coind = curl_init();

    //  Set the IP address and port for the wallet server
    curl_setopt ($coind, CURLOPT_URL, $this->rpc_addy);
    curl_setopt ($coind, CURLOPT_PORT, $this->rpc_port);

    //  Tell curl to use basic HTTP authentication
    curl_setopt($coind, CURLOPT_HTTPAUTH, CURLAUTH_BASIC) ;

    //  Provide the username and password for the connection
    curl_setopt($coind, CURLOPT_USERPWD, $this->rpc_user.":".$this->rpc_pass);

    //  JSON-RPC Header for the wallet
    curl_setopt($coind, CURLOPT_HTTPHEADER, array ("Content-type: application/json"));

    //  Prepare curl for a POST request
    curl_setopt($coind, CURLOPT_POST, TRUE);

    //  Provide the JSON data for the request
    curl_setopt($coind, CURLOPT_POSTFIELDS, $jdata); 

    //  Indicate we want the response as a string
    curl_setopt($coind, CURLOPT_RETURNTRANSFER, TRUE);

    //  Required by RPCSSL self-signed cert
    curl_setopt($coind, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($coind, CURLOPT_SSL_VERIFYHOST, FALSE);

    //  execute the request 
    $results = curl_exec($coind);

    //  Close the connection
    curl_close($coind);

    //  The JSON response is read into an array
    $return = json_decode ($results, TRUE);

    //  If an error message was received the message is returned
    //  to the calling code as a string.  
    if (isset ($return["error"]) || $return["error"] != "")
    {
      return $return["error"]["message"]."(Error Code: ".$return["error"]["code"].")";
    }

    //  If there was no error the result is returned to the calling code
    else
    {
      return $return["result"];
    }
  }

  


  # # # # # # # # # # # # # #
  #                         #
  #     == Blockchain ==    #
  #                         #
  # # # # # # # # # # # # # #

  ################################################################################################
  # getblock

  function getblock($blockhash="", $verbosity=1)
  {
    $command["method"] = "getblock";
    $command["params"][0] = $blockhash;
    $command["params"][1] = $verbosity;
    $results = $this->run($command);
    return $results;
  }

  ################################################################################################
  # getblockchaininfo

  function getblockchaininfo()
  {
    $command["method"] = "getblockchaininfo";
    $results = $this->run($command);
    return $results;
  }

  ################################################################################################
  # getblockhash

  function getblockhash($height=0)
  {
    $command["method"] = "getblockhash";
    $command["params"][0] = $height;
    $results = $this->run($command);
    return $results;
  }




  # # # # # # # # # # # # #
  #                       #
  #     == Network ==     #
  #                       #
  # # # # # # # # # # # # #

  ################################################################################################
  # getnetworkinfo

  function getnetworkinfo()
  {
    $command["method"] = "getnetworkinfo";
    //$command["params"][0] = NULL;
    $results = $this->run($command);
    return $results;
  }



  # # # # # # # # # # # # # # # # #
  #                               #
  #     == Raw Transactions ==    #
  #                               #
  # # # # # # # # # # # # # # # # #

  ################################################################################################
  # getrawtransaction

  function getrawtransaction($txid="", $verbose=FALSE, $blockhash=FALSE)
  {
    $command["method"] = "getrawtransaction";
    $command["params"][0] = $txid;
    $command["params"][1] = $verbose;
    if ($blockhash) { $command["params"][2] = $blockhash; }
    $results = $this->run($command);
    return $results;
  }

function getrawtransaction ($tx_id, $verbose=1)
  {
  //  The JSON-RPC request starts with a method name
    $request_array["method"] = "getrawtransaction";

  //  For getrawtransaction a txid is required  
    $request_array["params"][0] = $tx_id;
    $request_array["params"][1] = $verbose;

  //  Send the request to the wallet
    $info = wallet_fetch ($request_array);

  //  This function returns a string containing the block 
  //  hash value for the specified block in the chain
    return ($info);
  }

  # # # # # # # # # # # #
  #                     #
  #     == Wallet ==    #
  #                     #
  # # # # # # # # # # # #

  ################################################################################################
  # getwalletinfo

  function getwalletinfo()
  {
    $command["method"] = "getwalletinfo";
    //$command["params"][0] = NULL;
    $results = $this->run($command);
    return $results;
  }





} // end of class WalletRPC

?>