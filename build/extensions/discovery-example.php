<?php

// Last update 7 Sept 2021

$extensionList["discovery-example"] = "extensionDiscoveryExample";
	
function autoChildViewerText ($name, $which="mirador")
	{
	if (in_array(strtolower($which), array("osd", "openseadragon")))
		{$w1 = "specific images (IIIF info.json files)";
		 $w2 = "OpenSeaDragon";}
	else
		{$w1 = "IIIF manifests";
		 $w2 = "Mirador";}
	
	return(array("$name Example ($w2)", "<p>This system has been designed to demonstrate a working implementation of a simple IIIF discovery system, displaying images in relation to paintings within the $name Collection. This page allows users to pull select $w1 based on a simple text search and add them into $w2.</p>"));	
	}
	
function createViewerData($name, $parent, $uri, $viewer="mirador")
	{
	$m = autoChildViewerText ($name, $viewer);
	$npage = array(
			"title" => "",
			"class" => "dynamic-iiif",
			"parent" => $parent,
			"file" => array (
				"search-uri" => $uri,
				"limit" => 25, 
				"viewer" => $viewer,
				"layout" => "grid"),
			"displayName" => $m[0],
			"content" => $m[1],
			"content right" => "",
			"fluid" => 1,
			"displaycode" => false		
			);	
			
	return ($npage);
	}
	
function extensionDiscoveryExample ($d, $pd, $addPages=false)
  {
	global $extraHTML, $navExtra, $rootDisplayURL, $getExtra;
	
	if (isset($d["file"]) and is_array($d["file"]))
		{$config = $d["file"];}
	else if (isset($d["file"]) and file_exists($d["file"]))
		{$config = getRemoteJsonDetails($d["file"], false, true);}
	else
		{$config = array();}
		
	if ($addPages)
		{
		$xpages["viewer-$config[tag]-m"] = createViewerData(
			$d["displayName"], $pd, $config["ep"], "mirador");
		$xpages["viewer-$config[tag]-osd"] = createViewerData(
			$d["displayName"], $pd, $config["ep"], "openseadragon");
					
	// Just to add the original aliases for the ng viewers
	if ($config["tag"] == "ng")
		{$xpages["viewer-m"] = $xpages["viewer-$config[tag]-m"];
		 $xpages["viewer-m"]["copy"] = true;
		 $xpages["viewer-osd"] = $xpages["viewer-$config[tag]-osd"];
		 $xpages["viewer-osd"]["copy"] = true;}
	
		return ($xpages);
		exit();	
		}
		
	if (is_array($config["logo"]))
		{
		$p = "(s)";
		$x1 = "<center>";
		$x2 = "</div></center></br>";
		$x3 = "";
		$ims = "";
		foreach ($config["logo"] as $lk => $lim)
			{$ims .= "[<img class=\"float-sm\" src=\"$lim\" style=\"".
				"margin:2px 10px 0px 0px !important; width: 128px;\" alt=\"".
				$config["link"][$lk]."\">|".$config["link"][$lk]."]";}
		$epbuttons = "";
		foreach ($config["ep"] as $ek => $url)
			{$epbuttons .= "<a class=\"btn btn-outline-secondary nav-button\" ".
				" style=\"margin-bottom: 10px;\" id=\"endpoint-link-$ek\" role=\"button\" ".
				"href=\"$url\">$url</a>";}				
		$epexample = "";
		}
	else
		{
		$p = "";
		$x1 = "";
		$x2 = "";
		$x3 = "</div>";
		$ims = "[<img class=\"float-start\" src=\"$config[logo]\" style=\"".
			"margin:2px 10px 0px 0px !important; width: 150px;\" alt=\"".
			"The $d[displayName]\">|$config[link]]";
		$epbuttons = "<a class=\"btn btn-outline-secondary nav-button\" ".
			"id=\"endpoint-link\" role=\"button\" ".
			"href=\"$config[ep]\">$config[ep]</a>";
		$epexample = "<br/>
		<figure class=\"figure\">
			<pre class=\"json-renderer\" style=\"overflow-y: auto;overflow-x: auto; border: 2px solid black;padding: 10px;max-height:400px;\">
				$config[ep]
			</pre>
			<figcaption class=\"figure-caption\">
				Default response from the $d[displayName] Simple IIIF Discovery end-point.
			</figcaption>
		</figure>	";
		}
		
	ob_start();			
		echo <<<END
		$x1<div class="clearfix">
			$ims
			$x2
			<p>$d[content]</p>
		$x3
		
		<h5>Search and display the Collection$p Using:</h5>
		<div class="clearfix w-100">
			<a style="color: #3b5998;" href="./viewer-$config[tag]-osd" role="button" title="OpenSeaDragon">
				<img class="float-start" src="./graphics/osd_logo.png" style="margin:0px 10px 0px 10px !important; width: 64px;" alt="OpenSeadragon Example">
			</a>
			<a style="color: #3b5998;" href="./viewer-$config[tag]-m" role="button"  title="Mirdaor">
				<img class="float-start" src="./graphics/Mirador Logo Black.png" style="margin:0px 10px 0px 10px !important; width: 64px;" alt="Project Mirador Example">
			</a>
		</div>
		</br>
		<h4>Simple IIIF Discovery End-Point$p</h4>
		$epbuttons
		$epexample
END;
		$d["content"] = ob_get_contents();
		ob_end_clean(); // Don't send output to client			 
		
			ob_start();			
		echo <<<END
		<figure class="figure">
			[##]
			<figcaption class="figure-caption" style="text-align:center;padding-top:5px;">
				Example images from the National Gallery Simple IIIF Discovery end-point.
			</figcaption>
		</figure>
END;
		$d["content right"] = ob_get_contents();
		ob_end_clean(); // Don't send output to client			 
	
	$d["file"] = $config["list"];
	$d["osd-viewer"] = "grid";
	$d["osd-background"] = "white";
		
	$osd = extensionopenseadragon ($d, $pd);
	$d = $osd["d"];
	$pd = $osd["pd"];

  return (array("d" => $d, "pd" => $pd));
  }
  
?>
