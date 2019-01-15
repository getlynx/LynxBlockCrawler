<?php

/* * * * * * * * * * * * 
  Class BlockCrawler()
 * * * * * * * * * * * */

class BlockCrawler {

  var $html;
  var $blockchaininfo;
  var $networkinfo;
  var $walletinfo;

  var $site_header;
  var $site_content;
  var $site_footer;

  var $WalletRPC;


  function __construct($conf_file)
  {
    $this->debug(" Class BlockCrawler(): Initializing...");

    date_default_timezone_set('UTC');

    // Create temp RPC containers
    $rpc_user = "";
    $rpc_pass = "";
    $rpc_addy = "";
    $rpc_port = "";

    // Open lynx.conf (sitting well outside of WWW scope)
    $conf = fopen($conf_file, "r") or die("Unable to read conf file...");

    // Iterate through each line until end-of-file
    while(!feof($conf)) {
      
      // Get each line pointer...
      $line = fgets($conf);
      
      // Split the line at the = sign
      $array = explode("=", $line);

      // Capture config data
      if (trim($array[0]) == "rpcuser") {
        $rpc_user = str_replace('"', "", trim($array[1]));
      } else if (trim($array[0]) == "rpcpassword") {
        $rpc_pass = str_replace('"', "", trim($array[1]));
      } else if (trim($array[0]) == "rpcbind" && trim($array[1]) != "::1") {
        $rpc_addy = str_replace('"', "", trim($array[1]));
      } else if (trim($array[0]) == "rpcport") {
        $rpc_port = str_replace('"', "", trim($array[1]));
      }
    }

    // Close the file
    fclose($conf);

    // Include and instantiate the WalletRPC class using RPC creds pulled from conf
    require_once ("class_WalletRPC.php");
    $this->WalletRPC = new WalletRPC($rpc_user, $rpc_pass, $rpc_addy, $rpc_port);

    // Populate info vars
    $this->blockchaininfo = $this->WalletRPC->getblockchaininfo();
    $this->networkinfo = $this->WalletRPC->getnetworkinfo();
    $this->walletinfo = $this->WalletRPC->getwalletinfo();
    $this->site_content = $this->show_dashboard();
  }

  // rounding to chopping too many decimal places (i.e. difficulty)
  function round_up($number, $precision = 8)
  {
    $fig = (int) str_pad('1', $precision, '0');
    return (ceil($number * $fig) / $fig);
  }

  function round_down($number, $precision = 8)
  {
    $fig = (int) str_pad('1', $precision, '0');
    return (floor($number * $fig) / $fig);
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

  // Turn hashes into links for convenience
  function link_blockhash ($hash) { return "<a href=\"index.php?hash=".$hash."\" title=\"View Block Details\">".$hash."</a>"; }
  function link_txid ($txid) { return "<a href=\"index.php?txid=".$txid."\" title=\"View Transaction Details\">".$txid."</a>"; }
  function link_address ($address) { return "<a href=\"index.php?address=".$address."\" title=\"View Address Details\">".$address."</a>"; }
  function link_blockheight ($height) { return "<a href=\"index.php?height=".$height."\" title=\"View Block Details\">".$height."</a>"; }

  // Site Menu
  function site_menu()
  {
    $html = [];
    array_push($html, '<ul>');
    array_push($html, '    <li><a target="_blank" href="https://getlynx.io">Lynx Website</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://getlynx.io/news">Lynx News</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://getlynx.io/faq">Lynx FAQ</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://getlynx.io/downloads">Lynx Wallets</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://github.com/getlynx/LynxCI/releases/tag/v26">LynxCI ISO</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://www.coinomi.com/en/downloads">Coinomi Wallets</a></li>');

    array_push($html, '    <li><a target="_blank" href="https://explorer.getlynx.io">Block Explorer</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://github.com/getlynx/Lynx">Github</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://discord.gg/yTfCs5J">Discord</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://twitter.com/GetLynxIo">Twitter</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://reddit.com/r/lynx">Reddit</a></li>');

    array_push($html, '    <li><a target="_blank" href="https://coinmarketcap.com/currencies/lynx">CoinMarketCap</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://www.cryptocompare.com/coins/lynx/overview">CryptoCompare</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://www.livecoinwatch.com/price/Lynx-LYNX">LiveCoinWatch</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://walletinvestor.com/currency/lynx">WalletInvestor</a></li>');

    array_push($html, '    <li><a target="_blank" href="https://cryptopanic.com/news/lynx">CryptoPanic</a></li>');
    array_push($html, '    <li><a target="_blank" href="https://www.coinsignals.trade/?coin=LYNX%3ABTC">CoinSignal</a></li>');
    array_push($html, '</ul>');
    return join("", $html);
  }

  // Site Search Bar
  function site_search()
  {
    $html = [];
    array_push($html, '<form method="post" action="index.php">');
    array_push($html, '    <div class="form-group">');
    array_push($html, '        <input type="text" class="form-control" name="search" id="search" placeholder="Block Height / Block Hash / Tx ID ...">');
    array_push($html, '        <button id="button_search">GO!</button>');
    array_push($html, '    </div>');
    array_push($html, '</form>');
    return join("", $html);
  }

  // Site Header
  function site_header()
  {
    $html = [];
    array_push($html, '<!DOCTYPE html>');
    array_push($html, '<html lang="en">');
    array_push($html, '<head>');
    array_push($html, '  <title>Lynx Block Crawler</title>');
    array_push($html, '  <meta charset="UTF-8" />');
    array_push($html, '  <meta http-equiv="X-UA-Compatible" content="IE=edge">');
    array_push($html, '  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />');
    array_push($html, '  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous" />');
    array_push($html, '  <link rel="stylesheet" media="screen" href="css/style.css" />');
    array_push($html, '  <link rel="shortcut icon" href="http://austincaine.com/lynx/blockcrawler/redesign/img/lynx256.png" />');
    array_push($html, '</head>');
    array_push($html, '<body class="loading">');
    array_push($html, '<div id="wrapper">');
    array_push($html, '  <!-- DESKTOP Menu -->');
    array_push($html, '  <div class="d-none d-md-block">');
    array_push($html, '    <div class="col-sm-12">');
    array_push($html, '      <div class="col-12 button-links">');
    array_push($html, '        '. $this->site_menu());
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '  </div>');
    array_push($html, '  <!-- MOBILE Menu -->');
    array_push($html, '  <div class="d-block d-md-none">');
    array_push($html, '    <div class="col-sm-12">');
    array_push($html, '      <div id="mobile_menu" class="col-12 button-links" style="display:none;">');
    array_push($html, '        '. $this->site_menu());
    array_push($html, '      </div>');
    array_push($html, '      <div class="col-12 button-links">');
    array_push($html, '        <ul>');
    array_push($html, '          <li><a id="mobile_menu_btn" href="#">&#9660; Open Menu &#9660;</a></li>');
    array_push($html, '        </ul>');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '  </div>');
    array_push($html, '  <div id="site_container" class="container-fluid">');
    array_push($html, '    <div id="site_header" class="row">');
    array_push($html, '      <div class="col-12">');
    array_push($html, '        <div id="network_info" class="box-glow">');
    array_push($html, '          <div class="row">');
    array_push($html, '            <div class="col-12 col-sm-4"><strong>Block Count:</strong> <a href="index.php?height='.$this->blockchaininfo["blocks"].'">'. number_format($this->blockchaininfo["blocks"], 0, '.', ',') .'</a></div>');
    array_push($html, '            <div class="col-12 col-sm-4"><strong>Difficulty:</strong> <span class="text-glow">'. number_format($this->blockchaininfo["difficulty"], 8, '.', '') .'</span></div>');
    array_push($html, '            <div class="col-12 col-sm-4"><strong>Connections:</strong> <span class="text-glow">'. number_format($this->networkinfo["connections"], 0, '.', ',') .'</span></div>');
    array_push($html, '          </div>');
    array_push($html, '        </div>');
    array_push($html, '      </div>');
    array_push($html, '      <!-- DESKTOP Logo/Search -->');
    array_push($html, '      <div class="d-none d-md-block">');
    array_push($html, '        <div class="col-sm-12">');
    array_push($html, '          <a href="index.php"><img class="img-fluid" src="img/logo.png" /></a>');
    array_push($html, '        </div>');
    array_push($html, '        <div id="block_search">');
    array_push($html, '          '. $this->site_search());
    array_push($html, '        </div>');
    array_push($html, '        <div id="cmc_widget" class="d-none d-md-block">');
    array_push($html, '          <script type="text/javascript" src="https://files.coinmarketcap.com/static/widget/currency.js"></script>');
    array_push($html, '          <div class="coinmarketcap-currency-widget" data-currencyid="3099" data-base="USD"></div>');
    array_push($html, '        </div>');
    array_push($html, '      </div>');
    array_push($html, '      <!-- MOBILE Logo/Search -->');
    array_push($html, '      <div class="col-12 d-block d-md-none">');
    array_push($html, '        <div class="col-sm-12">');
    array_push($html, '          <a href="/"><img class="img-fluid" src="img/logo_mobile.png" /></a>');
    array_push($html, '        </div>');
    array_push($html, '        <div id="block_search_mobile" class="col-12">');
    array_push($html, '          '. $this->site_search());
    array_push($html, '        </div>');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '    <div id="site_body">');
    return join("", $html);
  }

  // Site Footer
  function site_footer()
  {
    $html = [];
    array_push($html, '    </div>');
    array_push($html, '    <div id="site_footer">');
    array_push($html, '      <div id="powered_by" class="box-glow">');
    array_push($html, '        <div class="row">');
    array_push($html, '          <div class="col-12 align-center"><span class="text-glow"><em>Powered by LYNX</em></span></div>');
    array_push($html, '        </div>');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '  </div>');
    array_push($html, '</div>');
    array_push($html, '<div id="particles-js"></div>');
    array_push($html, '<script src="https://code.jquery.com/jquery-2.2.4.js"></script>');
    array_push($html, '<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>');
    array_push($html, '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>');
    array_push($html, '<script src="js/particles.js"></script>');
    array_push($html, '<script src="js/xml2json.js"></script>');
    array_push($html, '<script src="js/functions.js"></script>');
    array_push($html, '<script>
      // Load Particles.js background
      (function() {
      $(function() {
          var n, e, t;
          return n = function() {
              setTimeout(function() {
                  return $("body").removeClass("loading")
              }, 1e3)
          }, 
          $(window).on("load", function() {
              return window.innerWidth > 620 ? n() : $("body").removeClass("loading")
          })
      })
      }).call(this);
      $(window).on("load", function() { particlesJS.load("particles-js", "js/particlesjs-config.json"); });
    ');
    array_push($html, '</script>');
    array_push($html, '</body>');
    array_push($html, '</html>');
    return join("", $html);
  }

  // Show the dashboard if nothing is requested...
  function show_dashboard()
  {
    $html = [];
    array_push($html, '
      <div class="row">

        <div id="feed_website" class="col-sm-6">
          <h3 class="header-glow"> <a target="_blank" href="https://getlynx.io">Website</a></h3>
          <div class="feed-box box-glow">
            <script type="text/javascript" language="javascript" src="https://www.rssdog.com/index.php?url=https%3A%2F%2Fgetlynx.io%2Ffeed%2F&amp;mode=javascript&amp;showonly=&amp;maxitems=0&amp;showdescs=1&amp;desctrim=1&amp;descmax=0&amp;tabwidth=100%25&amp;excltitle=1&amp;showdate=1&amp;nofollow=1&amp;utf8=1&amp;linktarget=_blank&amp;textsize=small&amp;bordercol=transparent&amp;headbgcol=transparent&amp;headtxtcol=%23ffffff&amp;titlebgcol=transparent&amp;titletxtcol=%23ffffff&amp;itembgcol=transparent&amp;itemtxtcol=%23336699&amp;ctl=0"></script>
            <noscript>Please enable JavaScript.</noscript>
          </div>
        </div>

        <div id="feed_reddit" class="col-sm-6">
          <h3 class="header-glow"> <a target="_blank" href="https://reddit.com/r/LYNX">/r/LYNX</a></h3>
          <div class="feed-box box-glow">
            <script type="text/javascript" language="javascript" src="https://www.rssdog.com/index.php?url=https%3A%2F%2Fwww.reddit.com%2Fr%2Flynx%2Fhot.rss&amp;mode=javascript&amp;showonly=&amp;maxitems=0&amp;showdescs=1&amp;desctrim=0&amp;descmax=0&amp;tabwidth=100%25&amp;excltitle=1&amp;showdate=1&amp;nofollow=1&amp;utf8=1&amp;linktarget=_blank&amp;textsize=small&amp;bordercol=transparent&amp;headbgcol=transparent&amp;headtxtcol=inherit&amp;titlebgcol=transparent&amp;titletxtcol=inherit&amp;itembgcol=transparent&amp;itemtxtcol=inherit&amp;ctl=0"></script>
            <noscript>Please enable JavaScript.</noscript>
          </div>
        </div>

        <div id="feed_twitter" class="col-sm-6">
          <h3 class="header-glow"> <a target="_blank" href="https://twitter.com/GetLynxIo">Twitter</a></h3>
          <div class="box-glow">
            <a class="twitter-timeline" data-theme="dark" data-height="20rem" href="https://twitter.com/GetlynxIo">&nbsp</a> 
            <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
          </div>
        </div>

        <div id="feed_discord" class="col-sm-6">
          <h3 class="header-glow"> <a target="_blank" href="https://discord.gg/yTfCs5J">Discord</a></h3>
          <div class="box-glow">
            <iframe src="https://discordapp.com/widget?id=400373936266936348&amp;theme=dark" width="100%" height="300" frameborder="0"></iframe>
          </div>
        </div>
      </div>

      
    ');
    return join("", $html);
  }

  function error($err_key)
  {
    $err_txt = "";

    if ($err_key == "invalid_block_height") { $err_txt = "Sorry, that is not a valid block height."; }

    if ( $err_txt != "" )
    {

      $html = [];
      array_push($html, '<div id="error" class="list-details">');
      array_push($html, '  <div class="row">');
      array_push($html, '    <div class="col-12">');
      array_push($html, '      <h3>ERROR!</h3>');
      array_push($html, '    </div>');
      array_push($html, '  </div>');
      array_push($html, '  <div class="row">');
      array_push($html, '    <div class="col-12 align-center">');
      array_push($html, '      <div class="box-glow">');
      array_push($html, '        <span class="text-glow">'.$err_txt.'</span><br/>');
      array_push($html, '        <strong>Please try your search again.</strong>');
      array_push($html, '      </div>');
      array_push($html, '    </div>');
      array_push($html, '  </div>');
      array_push($html, '</div>');
      array_push($html, '<br/><br/>');

      return join("", $html);
    }
  }

  // Return the block detail page
  function lookup_block($query, $is_hash=FALSE)
  {
    if ($is_hash) 
    {
      $raw_block = $this->WalletRPC->getblock($query);
    } else { 
      $block_hash = $this->WalletRPC->getblockhash(intval($query));
      $raw_block = $this->WalletRPC->getblock($block_hash);
    }

    if ($raw_block == Null)
    {
      return $this->error("invalid_block_height");
    }

    $block_date = date('m/d/Y \@ H:i:s', $raw_block["time"]);

    $html = [];

    array_push($html, '<div id="block_details" class="list-details">');
    array_push($html, '  <div class="row">');
    array_push($html, '    <div class="col-12">');
    array_push($html, '      <h3>Block <small>#</small> '.$raw_block["height"].'</h3>');
    array_push($html, '    </div>');
    array_push($html, '  </div>');
    array_push($html, '  <br/>');
    array_push($html, '  <div class="d-block d-sm-none">');
    array_push($html, '    <div class="row">');
    array_push($html, '      <div class="col-6 align-center">');
    array_push($html, '        <div class="big-button">');
    array_push($html, '          <a class="button" title="View Previous Block" href="'.$_SERVER["PHP_SELF"].'?hash='.$raw_block["previousblockhash"].'">&laquo; Prev</a> ');
    array_push($html, '        </div>');
    array_push($html, '      </div>');
    array_push($html, '      <div class="col-6 align-center">');
    array_push($html, '        <div class="big-button">');
    array_push($html, '          <a class="button" title="View Next Block" href="'.$_SERVER["PHP_SELF"].'?hash='.$raw_block["nextblockhash"].'">Next &raquo;</a> ');
    array_push($html, '        </div>');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '    <br/>');
    array_push($html, '    <div class="row">');
    array_push($html, '      <div class="col-12 align-center">');
    array_push($html, '        <div class="box-glow">');
    array_push($html, '          <span class="text-glow">'.$block_date.'</span> ');
    array_push($html, '        </div>');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '  </div>');
    array_push($html, '  <div class="d-none d-sm-block">');
    array_push($html, '    <div class="row">');
    array_push($html, '      <div class="col-3 align-center">');
    array_push($html, '        <div class="big-button">');
    array_push($html, '          <a class="button" title="View Previous Block" href="'.$_SERVER["PHP_SELF"].'?hash='.$raw_block["previousblockhash"].'">&laquo; Prev</a> ');
    array_push($html, '        </div>');
    array_push($html, '      </div>');
    array_push($html, '      <div class="col-6 align-center">');
    array_push($html, '        <div class="box-glow">');
    array_push($html, '          <span class="text-glow">'.$block_date.'</span> ');
    array_push($html, '        </div>');
    array_push($html, '      </div>');
    array_push($html, '      <div class="col-3 align-center">');
    array_push($html, '        <div class="big-button">');
    array_push($html, '          <a class="button" title="View Next Block" href="'.$_SERVER["PHP_SELF"].'?hash='.$raw_block["nextblockhash"].'">Next &raquo;</a> ');
    array_push($html, '        </div>');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '  </div>');
    array_push($html, '  <br/>');
    array_push($html, '  <div class="row">');
    array_push($html, '    <div class="col-12 align-center">');
    array_push($html, '      <div class="box-glow">');
    array_push($html, '        <strong>Block Hash:</strong><br/>');
    array_push($html, '        '.$this->link_blockhash($raw_block["hash"]));
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '  </div>');
    array_push($html, '  <br/>');
    array_push($html, '  <div class="row">');
    array_push($html, '    <div class="col-12 col-sm-4 align-center">');
    array_push($html, '      <div class="box-glow">');
    array_push($html, '        <strong>Block Version:</strong><br/>');
    array_push($html, '        <span class="text-glow">'.$raw_block["version"].'</span> ');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '    <div class="col-12 col-sm-4 align-center">');
    array_push($html, '      <div class="box-glow">');
    array_push($html, '        <strong>Block Size:</strong><br/>');
    array_push($html, '        <span class="text-glow">'.$raw_block["size"].'</span> ');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '    <div class="col-12 col-sm-4 align-center">');
    array_push($html, '      <div class="box-glow">');
    array_push($html, '        <strong>Confirmations:</strong><br/>');
    array_push($html, '        <span class="text-glow">'.$raw_block["confirmations"].'</span> ');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '  </div>');
    array_push($html, '  <br/>');
    array_push($html, '  <div class="row">');
    array_push($html, '    <div class="col-12 col-sm-4 align-center">');
    array_push($html, '      <div class="box-glow">');
    array_push($html, '        <strong>Block Bits:</strong><br/>');
    array_push($html, '        <span class="text-glow">'.$raw_block["bits"].'</span> ');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '    <div class="col-12 col-sm-4 align-center">');
    array_push($html, '      <div class="box-glow">');
    array_push($html, '        <strong>Block Nonce:</strong><br/>');
    array_push($html, '        <span class="text-glow">'.$raw_block["nonce"].'</span> ');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '    <div class="col-12 col-sm-4 align-center">');
    array_push($html, '      <div class="box-glow">');
    array_push($html, '        <strong>Difficulty:</strong><br/>');
    array_push($html, '        <span class="text-glow">0.'.number_format($raw_block["difficulty"], 8, '.', '').'</span> ');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '  </div>');
    array_push($html, '  <br/>');
    array_push($html, '  <div class="row">');
    array_push($html, '    <div class="col-12 align-center">');
    array_push($html, '      <div class="box-glow">');
    array_push($html, '        <strong>Merkle Root:</strong><br/>');
    array_push($html, '        <span class="text-glow">'.$raw_block["merkleroot"].'</span> ');
    array_push($html, '      </div>');
    array_push($html, '    </div>');
    array_push($html, '  </div>');
    array_push($html, '  <br/><br/>');

    if ( array_key_exists("tx", $raw_block) )
    {
      array_push($html, '  <div class="row">');
      array_push($html, '    <div class="col-12">');
      array_push($html, '      <h3>Transactions</h3>');
      array_push($html, '    </div>');
      array_push($html, '  </div>');
      array_push($html, '  <br/>');
      array_push($html, '  <div class="row">');
      array_push($html, '    <div class="col-12">');
      array_push($html, '      <div class="box-glow">');
      array_push($html, '        <ol>');
      foreach ($raw_block["tx"] as $index => $tx)
      {
        array_push($html, '          <li id="showtx_'.$index.'">'.$this->link_txid($tx).'</li>');
      }
      array_push($html, '        </ol>');
      array_push($html, '      </div>');
      array_push($html, '    </div>');
      array_push($html, '  </div>');
      array_push($html, '  <br/>');
      array_push($html, '  <br/>');
      array_push($html, '</div>');

    }
    
    return join("", $html);
  }

  // Decide what type of hash is being searched...
  function check_hash($hash)
  {
    $html = [];
    array_push($html, "Need to check if ".$hash." is a Block or TX hash...");
    return join("", $html);
    // Check for matching block hash...

    // If none, check for matching tx hash...

    // If none, send error
  }

  // Return the transaction detail page
  function lookup_txid($txid)
  {
    $raw_tx = $this->WalletRPC->getrawtransaction($txid);
    
    $html = [];
    array_push($html, '

      <h3>TXID: <small> '.$raw_tx["txid"].'</small></h3>

      <h3>Details</h3>
      TX Version: '.$raw_tx["version"].'<br>
      TX Time: '.date ("l F j, Y \@ H:i:s \(\U\T\C\)", $raw_tx["time"]).'<br>
      Lock Time: '.$raw_tx["locktime"].'<br>
      Confirmations: '.$raw_tx["confirmations"].'<br>
      Block Hash: '.$this->link_blockhash($raw_tx["blockhash"]).'<br>
    ');

    if (isset ($raw_tx["tx-comment"]) && $raw_tx["tx-comment"] != "")
    {
      array_push($html, 'TX Message: '.htmlspecialchars($raw_tx["tx-comment"]).'<br>');
    }
    array_push($html, 'HEX Data: '.$raw_tx["hex"].'<br>');
    array_push($html, '<h3>Inputs</h3>');
    
    foreach ($raw_tx["vin"] as $key => $txin)
    {
      array_push($html, 'Input #'.($key+1).'<br>');

      if (isset ($txin["coinbase"]))
      {
        array_push($html, 'Coinbase: '.$txin["coinbase"].'<br>');
        array_push($html, 'Sequence: '.$txin["sequence"].'<br>');
      }
      else
      {
        array_push($html, 'TX ID: '.$this->link_txid($txin["txid"]).'<br>');
        array_push($html, 'TX Output: '.$txin["vout"].'<br>');
        array_push($html, 'TX Sequence: '.$txin["sequence"].'<br>');
        array_push($html, 'Script Sig (ASM): '.$txin["scriptSig"]["asm"].'<br>');
        array_push($html, 'Script Sig (HEX): '.$txin["scriptSig"]["hex"].'<br>');
      }
    }

    array_push($html, '<h3>Outputs</h3>');
    
    foreach ($raw_tx["vout"] as $key => $txout)
    {
      array_push($html, '"Output #'.($key+1).'<br>');
      array_push($html, 'TX Value: '.$txout["value"].'<br>');
      array_push($html, 'TX Type: '.$txout["scriptPubKey"]["type"].'<br>');
      array_push($html, 'Required Sigs: '.$txout["scriptPubKey"]["reqSigs"].'<br>');
      array_push($html, 'Script Pub Key (ASM): '.$txout["scriptPubKey"]["asm"].'<br>');
      array_push($html, 'Script Pub Key (HEX): '.$txout["scriptPubKey"]["hex"].'<br>');
    
      if (isset ($txout["scriptPubKey"]["addresses"]))
      {
        foreach ($txout["scriptPubKey"]["addresses"] as $key => $address);
        {
          array_push($html, 'Address #'.$key+1, $address.'<br>');
        }
      }
      
    }
    
    array_push($html, '<h3>Raw Data</h3>');
    
    array_push($html, '<textarea name="rawtrans" rows="20" cols="100%">');
    array_push($html, print_r($raw_tx, true));
    array_push($html, '</textarea>');

    return join("", $html);
  }

  // Return the transaction detail page
  function lookup_address($address)
  {
    $html = [];
    array_push($html, 'Lookup Address: '.$address);
    return join("", $html);
  }





} // end of class BlockCrawler

?>