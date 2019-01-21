<html>
<head>
  <title>Block2Redis (PHP)</title>
  <meta http-equiv="Refresh" content="42">
  <style>
    body 
    {
      word-break: break-all;
      font-family: Verdana;
      background: #000;
      color: #999;
      font-size: small;
    }
    blockquote
    {
      border: 1px solid #ff0;
      margin: 15px 50px;
      padding: 15px;
    }
    hr
    {
      height: 10px;
      background-color: #fff;
      color: #fff;
    }
  </style>
</head>
<body>
<?php
  
  // Show debug console output?
  define("DEBUG", TRUE);

  // Create temp RPC containers
  $rpc_user = "133265384939286350723690880894";
  $rpc_pass = "108911118820170940262485973121";
  $rpc_addy = "127.0.0.1";
  $rpc_port = "9332";

  $coin = "LYNX";

  require_once ("classes/class_Block2Redis.php");
  $Block2Redis = new Block2Redis($rpc_user, $rpc_pass, $rpc_addy, $rpc_port, $coin);


  $Block2Redis->scan(10);



?>
</body>
</html>