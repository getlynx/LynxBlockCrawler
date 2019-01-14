<?php

/* * * * * * * * * * * 
  WalletRPC()
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

    $this->debug("==================================================");
    $this->debug("Class WalletRPC(): RPC User = ".$this->rpc_user);
    $this->debug("Class WalletRPC(): RPC Pass = ".$this->rpc_pass);
    $this->debug("Class WalletRPC(): RPC Addy = ".$this->rpc_addy);
    $this->debug("Class WalletRPC(): RPC Port = ".$this->rpc_port);
    $this->debug("Class WalletRPC(): Phrase = ".$this->phrase);
    $this->debug("==================================================");
  }

  function debug($output="")
  {
    if ( $output == "" ) { return FALSE; }
    if ( is_array( $output ) ) { $output = implode( ',', $output); }
    echo "<script>console.log( 'DEBUG --> " . $output . "' );</script>";
  }

  function run($command="")
  {
    // Command must be supplied
    if ($command == ""){return FALSE;}

    //  Encode the request as JSON for the wallet
    $jdata = json_encode($command);

    $this->debug("==================================================");
    $this->debug("WalletRPC.Run(): ".$jdata);
    $this->debug("==================================================");

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

    print_r($return);

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
  # getblockchaininfo

  function getblockchaininfo()
  {
    $command["method"] = "getblockchaininfo";
    //$command["params"][0] = NULL;
    $results = $this->run($command);
    $this->debug($results);
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
    $this->debug($results);
  }





} // end of class WalletRPC

?>