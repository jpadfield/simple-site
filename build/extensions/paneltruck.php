<?php

// Created 05/01/2021

$extensionList["paneltruck"] = "extensionPanelTruck";
/**
* [extensionPanelTruck description]
* @param  array  $d  [description]
* @param  array  $pd [description]
* @return array     [description]
*/
function extensionPanelTruck (array $d, array $pd) {
  global $extraHTML;
  $workspace = false;
  $mans = '[]';
  $wo = '';
  $codeHTML = "";
  $codecaption = "The complete panel truck JSON file used to define the images, positions and captions presented in this example.";

  if (isset($d["file"])) {
    $dets = getRemoteJsonDetails($d["file"], false, true);
    if ($dets and isset($d["displaycode"])) {
      $extraHTML .= displayCode ($dets, "The Panel Truck JSON File", "json", $codecaption);
    }
  }

  $pd["extra_js_scripts"] = array(
    "https://cdn.jsdelivr.net/npm/@webcomponents/webcomponentsjs/webcomponents-loader.js",
    "https://cdn.jsdelivr.net/npm/vue@2.6.12",
    "https://geoservices.leventhalmap.org/panel-truck/webcomponent-0.1/panel-truck.min.js",
  );

  ob_start();
  echo <<<END
  END;
  $pd["extra_js"] .= ob_get_contents();
  ob_end_clean(); // Don't send output to client

  $d["content"] = positionExtraContent ($d["content"], '<div style="width:100%;height:800px;"><!-- the panel-truck component will take the size of its parent element -->
  <panel-truck screenplay-src="'.$d["file"].'"></panel-truck>
  </div>'.$codeHTML);

  return (array("d" => $d, "pd" => $pd));
}
