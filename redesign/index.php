
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>BLOCK CRAWLER</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" media="screen" href="css/style.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">

</head>
<body class="loading">

<div id="wrapper">

	<!-- DESKTOP Menu -->
	<div class="d-none d-md-block">
		<div class="col-sm-12">
			<div class="col-12 button-links">
				<ul>

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

					<li><a target="_blank" href="https://cryptopanic.com">CryptoPanic</a></li>
					<li><a target="_blank" href="https://www.coinsignals.trade">CoinSignal</a></li>

				</ul>
			</div>
		</div>
	</div>
	
	<!-- MOBILE Menu -->
	<div class="d-block d-md-none">
		<div class="col-sm-12">
			<div class="col-12 button-links">
				<ul>
					<li><a href="#">&#9660; Open Menu &#9660;</a></li>
				</ul>
			</div>
			<div id="mobile_menu" class="col-12 button-links" style="display:none;">
				<ul>

					<li><a href="https://getlynx.io">Lynx Website</a></li>
					<li><a href="https://getlynx.io/news">Lynx News</a></li>
					<li><a href="https://getlynx.io/faq">Lynx FAQ</a></li>
					<li><a href="https://getlynx.io/downloads">Lynx Wallets</a></li>
					<li><a href="https://github.com/getlynx/LynxCI/releases/tag/v26">LynxCI</a></li>
					<li><a href="https://www.coinomi.com/en/downloads">Coinomi</a></li>

					<li><a href="https://explorer.getlynx.io">Block Explorer</a></li>
					<li><a href="https://github.com/getlynx/Lynx">Github</a></li>
					<li><a href="https://discord.gg/yTfCs5J">Discord</a></li>
					<li><a href="https://twitter.com/GetLynxIo">Twitter</a></li>
					<li><a href="https://reddit.com/r/lynx">Reddit</a></li>

					<li><a href="https://www.cryptocompare.com/coins/lynx/overview">CryptoCompare</a></li>
					<li><a href="https://coinmarketcap.com/currencies/lynx">CoinMarketCap</a></li>
					<li><a href="https://www.livecoinwatch.com/price/Lynx-LYNX">LiveCoinWatch</a></li>
					<li><a href="https://walletinvestor.com/currency/lynx">WalletInvestor</a></li>

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
					<img class="img-fluid" src="img/logo.png" />
				</div>
				<div id="block_search">
					<form>
						<div class="form-group">
							<input type="text" class="form-control" id="search" placeholder="Block Height / Block Hash / Tx ID ...">
							
						</div>
					</form>
				</div>
				<div id="cmc_widget" class="d-none d-md-block">
					<script type="text/javascript" src="https://files.coinmarketcap.com/static/widget/currency.js"></script>
					<div class="coinmarketcap-currency-widget" data-currencyid="3099" data-base="USD"></div>
				</div>
			</div>
			
			<!-- MOBILE Logo/Search -->
			<div class="col-12 d-block d-md-none">
				<div class="col-sm-12">
					<img class="img-fluid" src="img/logo_mobile.png" />
				</div>
				<div id="block_search_mobile" class="col-12">
					<form>
						<div class="form-group">
							<input type="text" class="form-control" id="search" placeholder="Block Height / Block Hash / Tx ID ...">
						</div>
					</form>
				</div>
			</div>

		</div>
		<div class="row site_body">

			<div id="feed_website" class="col-lg-4">
				<h3 class="header-glow"> Updates</h3>
				<div class="feed-box box-glow">
					<script type="text/javascript" language="javascript" src="https://www.rssdog.com/index.php?url=https%3A%2F%2Fgetlynx.io%2Ffeed%2F&amp;mode=javascript&amp;showonly=&amp;maxitems=0&amp;showdescs=1&amp;desctrim=0&amp;descmax=0&amp;tabwidth=100%25&amp;excltitle=1&amp;showdate=1&amp;nofollow=1&amp;utf8=1&amp;linktarget=_blank&amp;textsize=small&amp;bordercol=transparent&amp;headbgcol=transparent&amp;headtxtcol=%23ffffff&amp;titlebgcol=transparent&amp;titletxtcol=%23ffffff&amp;itembgcol=transparent&amp;itemtxtcol=%23336699&amp;ctl=0">
</script>
				</div>
			</div>

			<div id="feed_twitter" class="col-md-6 col-lg-4">
				<h3 class="header-glow"> Twitter</h3>
					
				<a class="twitter-timeline" data-theme="dark" data-height="25rem" href="https://twitter.com/GetlynxIo">Twitter: @GetLynxIo</a> 
				<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

			</div>

			<div id="feed_discord" class="col-md-6 col-lg-4">
				<h3 class="header-glow"> Discord</h3>
				<iframe src="https://discordapp.com/widget?id=400373936266936348&amp;theme=dark" width="100%" height="300" frameborder="0"></iframe>
			</div>



		</div>
	</div>

  
</div>

<div id="particles-js"></div>

<!-- js -->
<script src="https://code.jquery.com/jquery-2.2.4.js"></script>
<script src="js/scripts.js"></script>
<!-- particles.js -->
<script src="js/particles.js"></script>
<script>
particlesJS.load('particles-js', 'js/particlesjs-config.json', function() {
  console.log('callback - particles.js config loaded');
});
</script>


<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

<script>
$(window).on("load", function() {


	$.get("https://pro-api.coinmarketcap.com/v1/cryptocurrency/info?id=3099&CMC_PRO_API_KEY=6e0cc7dd-2522-438f-9a08-78d5938d413f", function(data, status){
        for(var key in data.data){
            var coin = data.data[key];
            console.log("coin: ",coin.name,"--price: ", coin.quotes.USD.price)
        }
    });

})
	
</script>

</body>
</html>