<?php

$extensionList["mirador"] = "extensionMirador";

function extensionMirador ($d, $pd)
  {
  $mans = '[]';
	$wo = '[{"annotationLayer" : false, "bottomPanelVisible" : false}]';
	$lo = '""';
		
	if (isset($d["file"]) and file_exists($d["file"]))
		{
		$dets = getRemoteJsonDetails($d["file"], false, true);
			
		if (!$dets)
			{
			$dets = getRemoteJsonDetails($d["file"], false, false);
			$dets = explode(PHP_EOL, trim($dets));

			if (preg_match('/^http.+/', $dets[0]))
				{$mans = listToManifest ($dets);
				 $use = $dets[0];
			   $wo = '[{ "loadedManifest":"'.$use.'", "slotAddress":"row1", '.
          '"viewType": "ImageView", "annotationLayer" : false, '.
          '"bottomPanelVisible" : false}]';
         $lo = '"1x1"';}
      }
    else {
			$mans = json_encode($dets["manifests"]);			 
			 
			if (isset($dets["windows"]))
			 {
				foreach ($dets["windows"]["slots"] as $k => $a)
					{$dets["windows"]["slots"][$k]["bottomPanelVisible"] = false;
					 $dets["windows"]["slots"][$k]["annotationLayer"] = false;}
						
				$wo = json_encode($dets["windows"]["slots"]);
			  $lo = json_encode($dets["windows"]["layout"]);}
			else
			 {$use = $dets["manifests"][0]["manifestUri"];
			  $wo = '[{ "loadedManifest":"'.$use.'", "slotAddress":"row1", '.
          '"viewType": "ImageView", "annotationLayer" : false, '.
          '"bottomPanelVisible" : false}]';
        $lo = '"1x1"';
        }
      }
    }

  // The mirador files could also be pulled from https://unpkg.com
	// But version 2.7.2 did not seem to work, will try again once V3 is
	// fully released. - jpadfield 30/03/20
	$pd["extra_css_scripts"][] =
		"https://tanc-ahrc.github.io/mirador/mirador/css/mirador-combined.css";
	$pd["extra_js_scripts"][] =
		"https://tanc-ahrc.github.io/mirador/mirador/mirador.min.js";
	$pd["extra_js"] .= '
	$(function() {
     myMiradorInstance = Mirador({
       id: "viewer",
       layout: '.$lo.',
       buildPath: "https://tanc-ahrc.github.io/mirador/mirador/",
       data: '.$mans.',
       "windowObjects": '.$wo.'
       });
     });';
  //use to hide the label used for the first line which is just in place to provide a margin/padding on the left.
	$pd["extra_css"] .= "
#viewer {       
      display: block;
      width: 100%;
      height: 600px;
      position: relative;
     }";

	$d["content"] = positionExtraContent ($d["content"], '<div id="viewer"></div>');

  return (array("d" => $d, "pd" => $pd));
  }
    
?>
