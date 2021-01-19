<?php

// Updated to Mirador V3 18/05/2020

$extensionList["mirador"] = "extensionMirador";

function extensionMirador ($d, $pd)
  {
	global $extraHTML;
	$workspace = false;
  $mans = '[]';
	$wo = '';
	$codeHTML = "";
	$codecaption = "The complete mirador JSON file used to define the manifests and images presented in this example.";
		
	if (isset($d["file"]) and file_exists($d["file"]))
		{
		$dets = getRemoteJsonDetails($d["file"], false, true);
			
		if (!$dets)
			{
			$dets = getRemoteJsonDetails($d["file"], false, false);
			$dets = explode(PHP_EOL, trim($dets));

			// Used to display the JSON used to create a given page for demos
			if (isset($d["displaycode"]))
				{$extraHTML .= displayCode ($dets, "The Mirador TXT File", "txt", $codecaption);}

			if (preg_match('/^http.+/', $dets[0]))
				{$mans = listToManifest ($dets);
				 $wo = '[{
					"manifestId": "'.$dets[0].'"
					}]';}
      }
    else {
			// Used to display the JSON used to create a given page for demos
			if (isset($d["displaycode"]))
				{$extraHTML .= displayCode ($dets, "The Mirador JSON File", "json", $codecaption);}
				
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
