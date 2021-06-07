<?php

// Last update 07 June 2021

// Simple use of OpenSeadragon (https://openseadragon.github.io/) to display images based on a simple list of info.json files or manifests

$extensionList["openseadragon"] = "extensionopenseadragon";

function extensionopenseadragon ($d, $pd)
  {
	global $extraHTML;

	$codecaption = "The complete text file used to define the IIIF manifests and images presented in this example.";
	$list = array();
	$tileSources = "[]";
	
	if (isset($d["file"]) and file_exists($d["file"]))
		{
		$dets = getRemoteJsonDetails($d["file"], false, true);
			
		if (!$dets) // was file not a json manifest
			{
			$dets = getRemoteJsonDetails($d["file"], false, false);
			$dets = explode(PHP_EOL, trim($dets));
			
			foreach ($dets as $k => $v)
				{
				if (preg_match('/^.+info[.]json$/', $v))
					{$list[] = $v;}
				else
					{$list = manifestToList ($v, $list);}
				}
			}
    }
   else
		{$list = manifestToList ($d["file"]);}
				
	$tileSources = listToTiles ($list);
    
  $pd["extra_js_scripts"][] = "https://cdn.jsdelivr.net/npm/openseadragon@2.4.2/build/openseadragon/openseadragon.min.js\" integrity=\"sha256-NMxPj6Qf1CWCzNQfKoFU8Jx18ToY4OWgnUO1cJWTWuw=\" crossorigin=\"anonymous";
	
		ob_start();			
	echo <<<END
	$(function() {

var myOSDInstance = OpenSeadragon({
					id:            "openseadragonviewerdiv",
          prefixUrl:     "https://openseadragon.github.io/openseadragon/images/",
          sequenceMode:  true,
          tileSources:   $tileSources
          });  
     });
END;
	$pd["extra_js"] .= ob_get_contents();
	ob_end_clean(); // Don't send output to client
	
	$pd["extra_css"] .= ".openseadragon
{
    
    height:     500px;
    border:     1px solid black;
    color:      #333; /* text color for messages */
    background-color: black;
}";
	
	$d = positionExtraContent ($d, '<div class="row" style="padding-left:16px;padding-right:16px;"><div class="col-12 col-lg-12"><div id="openseadragonviewerdiv" class="openseadragon"></div></div></div>');

  return (array("d" => $d, "pd" => $pd));
  }

function manifestToList ($manifest, $list=array())
	{		
	$mdets = getRemoteJsonDetails($manifest, false, true);
	
	if (isset($mdets["sequences"])) {
		foreach ($mdets["sequences"] as $k2 => $s) {
			foreach ($s["canvases"] as $k3 => $c) {
				foreach ($c["images"] as $k4 => $i) {
					if (preg_match('/^(.+).full.full.0.[a-z]+.jpg$/', $i["resource"]["@id"], $m))
						{$list[] = "$m[1]/info.json";}}}}}		
	
	return ($list);
	}
	 
function listToTiles ($list)
	{
	$tiles = "[";

	foreach ($list as $k => $url)
		{$tiles .= "\"".$url."\",
";}
	
	return($tiles."]");
	} 
?>
