<?php

	require_once ("bc_daemon.php");
	require_once ("bc_layout.php");
	
	
//	If a block hash was provided the block detail is shown
	if (isset ($_REQUEST["block_hash"]))
	{
		site_header ("Lynx Explorer - Block Detail Page");
		
		block_detail ($_REQUEST["block_hash"], TRUE);
	}
	
//	If a block height is provided the block detail is shown
	elseif (isset ($_REQUEST["block_height"]))
	{
		site_header ("Lynx Explorer - Block Detail Page");
		
		block_detail ($_REQUEST["block_height"]);
	}
	
//	If a TXid was provided the TX Detail is shown
	elseif (isset ($_REQUEST["transaction"]))
	{
		site_header ("Lynx Explorer - Transaction Detail Page");
		
		tx_detail ($_REQUEST["transaction"]);
	}
	
//	If there were no request parameters the menu is shown
	else
	{
		site_header ("Lynx Explorer");
		
		echo "	<div id=\"node_info\">\n";
		echo "\n";
		
		$network_info = getinfo ();
		
		echo "		<div class=\"node_detail\">\n";
		echo "			<span class=\"node_desc\">Block Count:</span><br>\n";
		echo "			<a href=\"/?block_height=".$network_info["blocks"]."\">".$network_info["blocks"]."</a>\n";
		echo "		</div>\n";
		echo "\n";

		echo "		<div class=\"node_detail\">\n";
		echo "			<span class=\"node_desc\">Difficulty:</span><br>\n";
		echo "			".$network_info["difficulty"]."\n";
		echo "		</div>\n";
		echo "\n";

		echo "		<div class=\"node_detail\">\n";
		echo "			<span class=\"node_desc\">Connections:</span><br>\n";
		echo "			".$network_info["connections"]."\n";
		echo "		</div>\n";
		echo "\n";

		$net_speed = getnetworkhashps ();
		$realspeed = humanHashSpeed($net_speed);
		if ($net_speed != "")
		{
			echo "		<div class=\"node_detail\">\n";
			echo "			<span class=\"node_desc\">Network H/s:</span><br>\n";
			echo "			".round($realspeed["hashrate"],2)." ".$realspeed["hashspeed"]."/s\n";
			echo "		</div>\n";
			echo "\n";
		}
		
		echo "	</div>\n";
		echo "\n";

		echo "	<div id=\"site_menu\">\n";
		echo "\n";
		
		echo "		<div class=\"menu_item\">\n";
		echo "			<span class=\"menu_desc\">Enter a Block Index / Height</span><br>\n";
		echo "			<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\">\n";
		echo "				<input class=\"textfield\" type=\"text\" name=\"block_height\" value=\"".$network_info["blocks"]."\" size=\"40\">\n";
		echo "				<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Jump To Block\">\n";
		echo "			</form>\n";
		echo "		</div>\n";
		echo "\n";

		echo "		<div class=\"menu_item\">\n";
		echo "			<span class=\"menu_desc\">Enter A Block Hash</span><br>\n";
		echo "			<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\">\n";
		echo "				<input class=\"textfield\" type=\"text\" name=\"block_hash\" size=\"40\">\n";
		echo "				<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Jump To Block\">\n";
		echo "			</form>\n";
		echo "		</div>\n";
		echo "\n";

		echo "		<div class=\"menu_item\">\n";
		echo "			<span class=\"menu_desc\">Enter A Transaction ID</span><br>\n";
		echo "			<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\">\n";
		echo "				<input class=\"textfield\" type=\"text\" name=\"transaction\" size=\"40\">\n";
		echo "				<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Jump To TX\">\n";
		echo "			</form>\n";
		echo "		</div>\n";
		echo "\n";

		echo "	</div>\n";
		echo "\n";
	}
	
	
	site_footer ();

/******************************************************************************
	This script is Copyright © 2013 Jake Paysnoe.
	I hereby release this script into the public domain.
	Jake Paysnoe Jun 26, 2013
******************************************************************************/

function humanHashSpeed($hashPerSecond) {
		$hashspeed = 'H';
		$hashrate = $hashPerSecond;
		if ($hashPerSecond >= 1000) {
			$hashspeed = 'KH';
			$hashrate = $hashPerSecond / 1000;
		}
		if ($hashPerSecond >= 1000000) {
			$hashspeed = 'MH';
			$hashrate = $hashPerSecond / 1000 / 1000;
		}
		if ($hashPerSecond >= 1000000000) {
			$hashspeed = 'GH';
			$hashrate = $hashPerSecond / 1000 / 1000 / 1000;
		}
		if ($hashPerSecond >= 1000000000000) {
			$hashspeed = 'TH';
			$hashrate = $hashPerSecond / 1000 / 1000 / 1000 / 1000;
		}
		return array('hashrate'=>$hashrate, 'hashspeed'=>$hashspeed);
}

?>
