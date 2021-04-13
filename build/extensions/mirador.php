<?php

// Updated to Mirador V3 18/03/2021
// Adding extra manifests in the Text example as the catalog rather than a manifest variable

// Last updated 13 Apr 2021

$extensionList["mirador"] = "extensionMirador";

function extensionMirador ($d, $pd)
  {
	global $extraHTML;
	$workspace = false;
  $mans = '[]';
	$wo = '';
	$codeHTML = "";
	$codecaption = "The complete mirador JSON file used to define the manifests and images presented in this example.";
	$cats = ""; // This was added to preload manifests in V3 for text only list - need to update other configs to follow V3 rather than V2 	
	if (isset($d["file"]) and file_exists($d["file"]))
		{
		$dets = getRemoteJsonDetails($d["file"], false, true);
			
		if (!$dets)
			{
			$dets = getRemoteJsonDetails($d["file"], false, false);
			$dets = explode(PHP_EOL, trim($dets));

			if (preg_match('/^http.+/', $dets[0]))
				{$mans = listToManifest ($dets);
				 $wo = '[{
					"manifestId": "'.$dets[0].'"
					}]';
				 $cats = "\"catalog\": [{\"manifestId\": \"".implode("\"}, {\"manifestId\": \"", $dets)."\"}],";}
				else
					{$cats = "";}
      }
    else {				
			$mans = json_encode($dets["manifests"]);			 
			 
			if (isset($dets["workspace"]))
			 {$workspace = "workspace: ".json_encode($dets["workspace"]);}			 

			if (isset($dets["windows"]))
			 {$wo = json_encode($dets["windows"]);}
			else
			 {$manifestIds = array_keys($dets["manifests"]);
				$manifestId = $manifestIds[0];				

			  $wo = '[{
					"manifestId": "'.$manifestId.'"
					}]';}
      }
    }

	$pd["extra_css"] .= ".fixed-top {z-index:1111;}";
	$mirador_path = "https://unpkg.com/mirador@3.0.0/dist/";
	$pd["extra_js_scripts"][] = $mirador_path."mirador.min.js";

	ob_start();			
	echo <<<END
	$(function() {

var myMiradorInstance = Mirador.viewer({
       id: "mirador",
       windows: $wo,
       manifests: $mans,
       $cats
       $workspace
       });     
     });
END;
	$pd["extra_js"] .= ob_get_contents();
	ob_end_clean(); // Don't send output to client

	$d["content"] = positionExtraContent ($d["content"], '<div class="row" style="padding-left:16px;padding-right:16px;"><div class="col-12 col-lg-12"><div style="height:500px;" id="mirador"></div></div></div>'.$codeHTML);

  return (array("d" => $d, "pd" => $pd));
  }

	 
function listToManifest ($list)
	{
	$manifests = "{";

	foreach ($list as $k => $url)
		{$manifests .= "
".json_encode($url).":{\"provider\":\"Undefined\"},";}
	
	return($manifests."}");
	}    
?>
