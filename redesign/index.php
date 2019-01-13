<?php

/*
// METHOD 1... In Apache --> SetEnv APP_INCLUDE_PATH /root/.lynx
define('CONF', $_SERVER['APP_INCLUDE_PATH'] . '/lynx.conf');
*/

// METHOD 2...
define('INCLUDE_DIR', '/var/www/austincaine.com');

echo "Attempting to include ". INCLUDE_DIR . "/lynx.conf"."<br>";
$GLOBALS['config'] = parse_ini_file(INCLUDE_DIR . '/lynx.conf') or die('Error, cannot read CONF file...');

echo $GLOBALS['config']['rpcuser']."<br>";
echo $GLOBALS['config']['rpcpassword']."<br>";




/*
	// METHOD 3...

	// Instantiate local RPC containers
	$rpcuser = "";
	$rpcpassword = "";
	
	// Open lynx.conf (sitting well outside of WWW scope)
	$confpath = fopen("../../../../test.conf", "r") or die("Unable to read conf file...");
	
	// Iterate each line until end-of-file
	while(!feof($confpath)) {
		
		// Read in the full line
		$line = fgets($confpath);
		
		// Split the line at the = sign
		$array = explode("=", $line);

		// Capture RPC credentials
		if (trim($array[0]) == "rpcuser") {
			$rpcuser = str_replace('"', "", trim($array[1]));
		} else if (trim($array[0]) == "rpcpassword") {
			$rpcpassword = str_replace('"', "", trim($array[1]));
		}
	}

	// Close the file
	fclose($confpath);

	// Now $rpcuser and $rpcpassword contain the credentials, hidden from user view.
	// Just be sure to send them ENCRYPTED!!!
*/

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Lynx Block Crawler</title>

	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous" />

	<link rel="stylesheet" media="screen" href="css/style.css" />
	<link rel="shortcut icon" href="./img/lynx256.png" />

</head>
<body class="loading">
<div id="wrapper">

	<!-- DESKTOP Menu -->
	<div class="d-none d-md-block">
		<div class="col-sm-12">
			<div class="col-12 button-links">
				<ul>
					<?php $menuLinks = '

					<li><a target="_blank" href="https://getlynx.io">Lynx Website</a></li>
					<li><a target="_blank" href="https://getlynx.io/news">Lynx News</a></li>
					<li><a target="_blank" href="https://getlynx.io/faq">Lynx FAQ</a></li>
					<li><a target="_blank" href="https://getlynx.io/downloads">Lynx Wallets</a></li>
					<li><a target="_blank" href="https://github.com/getlynx/LynxCI/releases/tag/v26">LynxCI ISO</a></li>
					<li><a target="_blank" href="https://www.coinomi.com/en/downloads">Coinomi Wallets</a></li>

					<li><a target="_blank" href="https://explorer.getlynx.io">Block Explorer</a></li>
					<li><a target="_blank" href="https://github.com/getlynx/Lynx">Github</a></li>
					<li><a target="_blank" href="https://discord.gg/yTfCs5J">Discord</a></li>
					<li><a target="_blank" href="https://twitter.com/GetLynxIo">Twitter</a></li>
					<li><a target="_blank" href="https://reddit.com/r/lynx">Reddit</a></li>

					<li><a target="_blank" href="https://coinmarketcap.com/currencies/lynx">CoinMarketCap</a></li>
					<li><a target="_blank" href="https://www.cryptocompare.com/coins/lynx/overview">CryptoCompare</a></li>
					<li><a target="_blank" href="https://www.livecoinwatch.com/price/Lynx-LYNX">LiveCoinWatch</a></li>
					<li><a target="_blank" href="https://walletinvestor.com/currency/lynx">WalletInvestor</a></li>

					<li><a target="_blank" href="https://cryptopanic.com/news/lynx">CryptoPanic</a></li>
					<li><a target="_blank" href="https://www.coinsignals.trade/?coin=LYNX%3ABTC">CoinSignal</a></li>
					
					'; ?>
					<?php echo $menuLinks; ?>

				</ul>
			</div>
		</div>
	</div>
	
	<!-- MOBILE Menu -->
	<div class="d-block d-md-none">
		<div class="col-sm-12">
			<div id="mobile_menu" class="col-12 button-links" style="display:none;">
				<ul>

					<?php echo $menuLinks; ?>

				</ul>
			</div>
			<div class="col-12 button-links">
				<ul>
					<li><a id="mobile_menu_btn" href="#">&#9660; Open Menu &#9660;</a></li>
				</ul>
			</div>
		</div>
	</div>
	

	<div id="site_container" class="container-fluid">
		
		<div id="site_header" class="row">
			
			<div class="col-12">

				<div id="network_info" class="box-glow">
					<div class="row">
						<div class="col-12 col-sm-4"><strong>Block Count:</strong> <span class="text-glow">123457890</span></div>
						<div class="col-12 col-sm-4"><strong>Difficulty:</strong> <span class="text-glow">244.23487</span></div>
						<div class="col-12 col-sm-4"><strong>Connections:</strong> <span class="text-glow">45</span></div>
					</div>
				</div>

			</div>

			<!-- DESKTOP Logo/Search -->
			<div class="d-none d-md-block">
				<div class="col-sm-12">
					<a href="/"><img class="img-fluid" src="img/logo.png" /></a>
				</div>
				<div id="block_search">
					<?php $searchForm = '
					<form>
						<div class="form-group">
							<input type="text" class="form-control" id="search" placeholder="Block Height / Block Hash / Tx ID ...">
							<button id="button_search">GO!</button>
						</div>
					</form>
					'
					?>
					<?php echo $searchForm; ?>
				</div>
				<div id="cmc_widget" class="d-none d-md-block">
					<script type="text/javascript" src="https://files.coinmarketcap.com/static/widget/currency.js"></script>
					<div class="coinmarketcap-currency-widget" data-currencyid="3099" data-base="USD"></div>
				</div>
			</div>
			
			<!-- MOBILE Logo/Search -->
			<div class="col-12 d-block d-md-none">
				<div class="col-sm-12">
					<a href="/"><img class="img-fluid" src="img/logo_mobile.png" /></a>
				</div>
				<div id="block_search_mobile" class="col-12">
					<?php echo $searchForm; ?>
				</div>
			</div>

		</div>
		<div class="site_body">
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

			<div class="col-12">

				<div id="network_info" class="box-glow">
					<div class="row">
						<div class="col-12 align-center"><span class="text-glow"><em>Powered by LYNX</em></span></div>
					</div>
				</div>

			</div>

		</div>
	</div>

  
</div>

<div id="particles-js"></div>

<!-- js -->
<script src="https://code.jquery.com/jquery-2.2.4.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
<script src="js/particles.js"></script>
<script src="js/xml2json.js"></script>
<script src="js/functions.js"></script>
<script>
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
	$(window).on("load", function() { particlesJS.load('particles-js', 'js/particlesjs-config.json'); });
</script>
</body>
</html>