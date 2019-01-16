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

    $this->debug("WalletRPC.Run(): ".$jdata);

    //  Create curl connection object
    $coind = curl_init();

    //  Set the IP address and port for the wallet server
    curl_setopt ($coind, CURLOPT_URL, $this->rpc_addy);
    curl_setopt ($coind, CURLOPT_PORT, $this->rpc_port);

    //  Tell curl to use basic HTTP authentication
    curl_setopt($coind, CURLOPT_HTTPAUTH, CURLAUTH_BASIC) ;

    //  Provide the username and password for the connection
    curl_setopt($coind, CURLOPT_USERPWD, $this->rpc_user.":".$this->rpc_pass);

    //  JSON-RPC header for the wallet
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
    if (isset ($return["error"]) || !empty($return["error"]))
    {
      $this->debug('WalletRPC.Run(): --ERROR-- '.$return["error"]["message"].' (Error Code: '.$return["error"]["code"].')');
      return FALSE;

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

  function getblockhash($height=FALSE)
  {
    $command["method"] = "getblockhash";
    $command["params"][0] = $height;
    echo $height;
    if ( is_numeric($height) )
    {
      $results = $this->run($command);
      return $results;
    }
    else
    {
      return FALSE;
    }
    
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

  function getrawtransaction($txid="", $verbose=1, $blockhash=FALSE)
  {
    $command["method"] = "getrawtransaction";
    $command["params"][0] = $txid;
    $command["params"][1] = $verbose;
    if ($blockhash) { $command["params"][2] = $blockhash; }
    $results = $this->run($command);
    return $results;
  }

  # # # # # # # # # # #
  #                   #
  #     == Util ==    #
  #                   #
  # # # # # # # # # # #

  ################################################################################################
  # getnetworkhashps
  function getnetworkhashps($nblocks=FALSE, $height=FALSE)
  {
    $command["method"] = "getnetworkhashps";
    if ($nblocks) { $command["params"][0] = $nblocks; }
    if ($height) { $command["params"][1] = $height; }
    $results = $this->run($command);
    return $results;
  }
  function humanHashSpeed($networkhashps) {
      $hashspeed = 'H';
      $hashrate = $networkhashps;
      if ($networkhashps < 1) {
        $hashspeed = 'µH';
        $hashrate = $networkhashps / 1000;
      }
      if ($networkhashps >= 1000) {
        $hashspeed = 'KH';
        $hashrate = $networkhashps / 1000;
      }
      if ($networkhashps >= 1000000) {
        $hashspeed = 'MH';
        $hashrate = $networkhashps / 1000 / 1000;
      }
      if ($networkhashps >= 1000000000) {
        $hashspeed = 'GH';
        $hashrate = $networkhashps / 1000 / 1000 / 1000;
      }
      if ($networkhashps >= 1000000000000) {
        $hashspeed = 'TH';
        $hashrate = $networkhashps / 1000 / 1000 / 1000 / 1000;
      }
      return array('hashrate'=>$hashrate, 'hashspeed'=>$hashspeed);
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