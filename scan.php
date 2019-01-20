<?php
  
  // Show debug console output?
  define("DEBUG", TRUE);

  // Create temp RPC containers
  $rpc_user = "133265384939286350723690880894";
  $rpc_pass = "108911118820170940262485973121";
  $rpc_addy = "127.0.0.1";
  $rpc_port = "9332";

  require_once ("classes/class_Block2Redis.php");
  $Block2Redis = new Block2Redis($rpc_user, $rpc_pass, $rpc_addy, $rpc_port);


  $Block2Redis->scan(0);



?>
