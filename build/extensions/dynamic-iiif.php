<?php

// Last update 7 Sept 2021

$extensionList["dynamic-iiif"] = "extensionDynamicIIIF";

// Expecting GET Variables in the form of
/* Array
(
    [root] => /ss-iiif/
    [display] => home
    [viewer] => mirador (or seadragon)
    [extra] => 1234 (search term)
)
* converted into the following GLOBALS:

$rootDisplayURL = $_GET["root"].$_GET["display"];
$getExtra = explode("/", $_GET["extra"]);

Uses Mirador Version 3
Or OpenSeadragon version 2.4.2 

* a config json file can be used to set:

 	"search-uri": "REQUIRED: The full search URI which only requires a search term to be added to the end, such as https://example.com/api.php?search=",
	"limit": 50, // optional extra limit number that can be added to the search uri if needed

The search uri needs to be set up to return a json document including an array of results
* 
		"limit": "numerical value of any limit applied,
		"from": "and offset to apply to which limited results are returned",
		"limited": "true is the search results are limited,
		"total": "the total number of search matches",
		"search": "the search term that was used",
		"what": "what has been returned: manifest or json.info files"
		"results": a simple list of manifests or info.json urls.
		"comment": "short comment that fits into the help text", // Results of "COMMENT TEXT" search for:"

NEED Notes on formatting of Manifest or Infor.josn list
*/

$bd = 32;
$buttons = array(
	"info" => '
	<button title="Further Information" type="button" style="margin-right:0px;padding:0px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#infoModal">
		<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 161 161" width="'.$bd.'" height="'.$bd.'">
			<g fill="#eeeeee">
				<path d="m80 15c-35.88 0-65 29.12-65 65s29.12 65 65 65 65-29.12 65-65-29.12-65-65-65zm0 10c30.36 0 55 24.64 55 55s-24.64 55-55 55-55-24.64-55-55 24.64-55 55-55z"/>
				<path d="m57.373 18.231a9.3834 9.1153 0 1 1 -18.767 0 9.3834 9.1153 0 1 1 18.767 0z" transform="matrix(1.1989 0 0 1.2342 21.214 28.75)"/>
				<path d="m90.665 110.96c-0.069 2.73 1.211 3.5 4.327 3.82l5.008 0.1v5.12h-39.073v-5.12l5.503-0.1c3.291-0.1 4.082-1.38 4.327-3.82v-30.813c0.035-4.879-6.296-4.113-10.757-3.968v-5.074l30.665-1.105"/>
			</g>
		</svg>
	</button>
	',
	"list" => '
	<button title="A list of the included images" type="button" style="margin-right:0px;padding:0px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#listModal">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="'.$bd.'" height="'.$bd.'">
			<g fill="#eeeeee">
				<path d="M6 26h4v-4h-4v4zm0 8h4v-4h-4v4zm0-16h4v-4h-4v4zm8 8h28v-4h-28v4zm0 8h28v-4h-28v4zm0-20v4h28v-4h-28z"/>
				<path d="M0 0h48v48h-48z" fill="none"/>
			</g>
		</svg>
	</button>',
	"search" => '
	<button title="Open the simple search form" type="button" style="margin-right:0px;padding:0px;" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#searchModal">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 55 55" width="'.$bd.'" height="'.$bd.'">
			<g id="XMLID_13_" transform="translate(-25.461,-22.738)" fill="#eeeeee">
								<path d="M 69.902,72.704 58.967,61.769 c -2.997,1.961 -6.579,3.111 -10.444,3.111 -10.539,0 -19.062,-8.542 -19.062,-19.081 0,-10.519 8.522,-19.061 19.062,-19.061 10.521,0 19.06,8.542 19.06,19.061 0,3.679 -1.036,7.107 -2.828,10.011 l 11.013,11.011 c 0.583,0.567 0.094,1.981 -1.076,3.148 l -1.64,1.644 c -1.17,1.167 -2.584,1.656 -3.15,1.091 z M 61.249,45.799 c 0,-7.033 -5.695,-12.727 -12.727,-12.727 -7.033,0 -12.745,5.694 -12.745,12.727 0,7.033 5.712,12.745 12.745,12.745 7.032,0 12.727,-5.711 12.727,-12.745 z"
								id="path9"/></g></svg>
	</button>'
	);
	
function extensionDynamicIIIF ($d, $pd)
  {
	global $extraHTML, $navExtra, $rootDisplayURL, $getExtra;
	
	if (isset($pd["licence"])) {$pd["licence"] = "<a href='http://rightsstatements.org/vocab/InC/1.0/'><img style='background-color:#318ac7;padding:2px;' height='24' alt='In Copyright - Please check the terms and conditions with the image providers' title='Assumed in Copyright - Please check the terms and conditions with the image providers' src='https://rightsstatements.org/files/buttons/InC.white.svg'/></a>";}
	if (isset($pd["footer"])) {$pd["footer"] = "";}
	
	// This is done to maximise the space for the viewer
	$d = convertContenttoInfo ($d);
	
	$pd["extra_css"] .= '
		.modal {z-index: 1112;}
		.fixed-top {z-index:1111;}
	';
	
	$note = "<div class=\"container-fluid\" style=\"padding-top:0px;\">";
	$maxlimit = 100;
	$limit = 25;
	$from = "";
	$page = 1;
	$morejs = false;
	$imagelist = false;
	$returnsBlocked = false;
	$sterm = false;
	$stermForm = false;
	
	if (is_array($d["file"]))
		{$config = $d["file"];}
	else if (isset($d["file"]) and file_exists($d["file"]))
		{$config = getRemoteJsonDetails($d["file"], false, true);
		 if (isset($config["limit"]) and $config["limit"])
			{$limit = intval($config["limit"]);}}
	else
		{$config = array("search-uri" => "");}
	
	if (isset($config["viewer"]) and $config["viewer"] == "mirador")
		{$mans = '[]';
		 $cats = "";
		 $what = "manifests";
		 $divID = "mirador";
		 $defaultStr = "Below is an empty instance of <a href=\"https://projectmirador.org/\">Mirdaor V3</a> - you can add <a href=\"https://iiif.io\">IIIF</a> Manifest or Collections in directly using the <b>Add Resource</b> option or new images can be loaded in automatically by running a simple search.";}
	else
		{$config["viewer"] = "openseadragon";
		 $tileSources = "[]";		 
		 $what = "info";
		 $divID = "openseadragonviewerdiv";
		 $defaultStr = "Below is an empty instance of <a href=\"https://openseadragon.github.io/\">OpenSeadragon</a> - images can be loaded in automatically by running a simple search.";}
    
  if ($getExtra[0] and $config["search-uri"])
		{
		if (isset($getExtra[1]) and intval($getExtra[1]) > 0)
			{$limit = intval($getExtra[1]);
			 if ($limit > $maxlimit) {$limit = $maxlimit;}}
			
		if (isset($getExtra[2]) and intval($getExtra[2]) > 0)
			{
			// Some APIs are limited to returning 10000 resources
			 if ((intval($getExtra[2]) * $limit) > 10000)
				{$getExtra[2] = floor(10000/$limit);
				 $returnsBlocked = true;}
				
			 $from = (intval($getExtra[2]) - 1) * $limit;			 			
			 if ($from > 0) {$from = "&from=".$from;
				 $page=intval($getExtra[2]);}
			 else {$from = "";$page=1;}}
		
		$extraTerms = "&limit=$limit".$from;
		$pageURI = "$rootDisplayURL/$getExtra[0]/$limit/";
		$sterm =	"?search=".rawurlencode($getExtra[0])."&what=$what";
		$stermForm =	htmlspecialchars($getExtra[0]);
		$epts = 1;
		
		if (!is_array($config["search-uri"])) {
			$config["search-uri"] = array($config["search-uri"]);}
		
		}
	else
		{$config["search-uri"] = array();
		$extraTerms = "";
		$pageURI = "$rootDisplayURL";
		$sterm =	"?search=&what=$what";
		$stermForm =	false;}
		
	$epts = count($config["search-uri"]);
		
	$dets = array(		
		"limit" => $limit * $epts,
		"from" => 0, 
		"limited" => false,
		"total" => false,
		"search" => $sterm,
		"what" => $what,
		"results" => array(),
		"comment" => array(),
		"missed" => 0, // used for debugging, when end-points return objects with no IIIF resources
		"objects" => 0, // things searched for, limited by the $limit value
		"resources" => 0 // IIIF resources related to the things returned
		);
			
	if ($epts > 1)
		{$dets["comment"][] = "Images drawn from a combination of sites.";}
		
	$maxPages = 1;
	$maxTotal = 0;
		
	$debug = "";
		
	$range = array(0,0);
			
	foreach ($config["search-uri"] as $k => $uri)
		{
		$trange = array(0,0);
		// need a temp $dets and combine the results
		$tdets = getExternalDetails($sterm, $uri, $extraTerms);
		if ($tdets["limited"]) {$dets["limited"] = true;}
			
		$dets["resources"] +=	count($tdets["results"]);
		if ($tdets["total"] > $maxTotal) {$maxTotal = $tdets["total"];}
		$dets["total"] += $tdets["total"];
		$dets["results"] = array_merge($dets["results"], $tdets["results"]);
				
		if (($tdets["total"] - $tdets["from"]) > $tdets["limit"])
			{
			// Full return
			$trange[0] = $tdets["from"];
			$trange[1] = $tdets["from"] + $tdets["limit"];
			$tnote = " <span style=\"color: #0d6efd;\"> Displaying resources from ". implode(" - ", $trange) ." of ".$tdets["total"]. " objects.</span>";
			$range[0] += $trange[0];
			$range[1] += $trange[1];
			$dets["objects"] += $tdets["limit"];				
			}
		else if (($tdets["total"] - $tdets["from"]) > 0)
			{
			// A few left
			$trange[0] = $tdets["from"];
			$trange[1] = $tdets["total"];
			$tnote = " <span style=\"color: #0d6efd;\"> Displaying resources from ". implode(" - ", $trange) ." of ".$tdets["total"]. " objects.</span>";
			$range[0] += $trange[0];
			$range[1] += $trange[1];
			$dets["objects"] += $tdets["total"] - $tdets["from"];}
		else
			{
			// All finished - can occur in combination presentations
			$range[0] += $tdets["total"];
			$range[1] += $tdets["total"];
			if ($tdets["total"] > 0)
				{$tnote = " <span style=\"color: red;\"> Displaying no resources from ".$tdets["total"]. " objects, all objects already returned.</span>";}
			else
				{$tnote = " <span style=\"color: red;\"> No objects returned for this search.</span>";}
			}
			
		$dets["comment"][] = $tdets["comment"] .$tnote;
		}

	$maxPages = ceil($maxTotal/$limit);
		
	if ($config["viewer"] == "mirador")
		{			
		$cats = MD_buildCatalog ($dets["results"]);
		$list = false;
		$pd["extra_js_scripts"][] = "https://cdn.jsdelivr.net/npm/mirador@3.2.0/dist/mirador.min.js\" integrity=\"sha256-e11UQD1U7ifc8OK9X0rVMshTXSKl7MafRxi3PTwXDHs=\" crossorigin=\"anonymous";			
		 
		ob_start();			
		echo <<<END
				$(function() {
var myMiradorInstance = Mirador.viewer({
       id: "mirador",
       "workspace": {
				"isWorkspaceAddVisible": true},     
       $cats
       });     
     });
END;
		$morejs = ob_get_contents();
		ob_end_clean(); // Don't send output to client			 
		}
	else			
		{			
		$imagelist = OSD_formatImageList($dets["results"]);
		$tileSources = "[ 
\t\t\t\"".implode("\",\n\t\t\t\"", $dets["results"])."\"
]";		
		$list = true;
		$pd["extra_js_scripts"][] = "https://cdn.jsdelivr.net/npm/openseadragon@2.4.2/build/openseadragon/openseadragon.min.js\" integrity=\"sha256-NMxPj6Qf1CWCzNQfKoFU8Jx18ToY4OWgnUO1cJWTWuw=\" crossorigin=\"anonymous";
			
		$extramorejs = "";
			
		if (isset($config["layout"]) and $config["layout"] == "simplegrid")
			{
			$rows = floor(sqrt($dets["resources"]));
			if ($rows > 4) {$rows = $rows - 1;}		
			$osdMode = "	
				collectionMode:       true,
				collectionRows:       $rows, 
				collectionTileSize:   1024,
				collectionTileMargin: 256,
				";
			}
		else if (isset($config["layout"]) and $config["layout"] == "grid")
			{
			$pd["extra_js_scripts"][] = "https://cdn.rawgit.com/Pin0/openseadragon-justified-collection/1.0.2/dist/openseadragon-justified-collection.min.js";
			$osdMode = "	
				collectionMode:       true,
				collectionRows:       1, 
				";
			$extramorejs = '
	var total = '.intval($dets["resources"]).';
	
	var osdw = $(openseadragonviewerdiv).width();
	var osdh = $(openseadragonviewerdiv).height();
	var cls = Math.round(Math.sqrt((osdw/osdh) * total));
  
  if (osdw > osdh)
		{myOSDInstance.collectionColumns = cls;}
	else
		{myOSDInstance.collectionColumns = cls - 1;}
		
	myOSDInstance.addHandler(\'open\', function() {
		myOSDInstance.world.arrange();
		myOSDInstance.viewport.goHome(true);
		});
				';
			}
		else
			{$osdMode = "
			 sequenceMode: true,
			 showReferenceStrip: true,";}
		
			 
		ob_start();			
		echo <<<END
			var myOSDInstance = OpenSeadragon({
				id: "openseadragonviewerdiv",
				prefixUrl:     "https://openseadragon.github.io/openseadragon/images/",
				imageLoaderLimit: 100,
				$osdMode
				tileSources:   $tileSources 
				}); 
			$extramorejs 
END;
		$morejs = ob_get_contents();
		ob_end_clean(); // Don't send output to client			 
		}
			
	if ($dets["resources"] > 50)
		{$exnote = "<span style=\"color: #0d6efd;\"> Also it can take some time for larger sets of images to appear in the viewer.</span>";}
	else
		{$exnote = "";}
		
	if ($returnsBlocked) 
		{$blockStr = "<span style=\"color: red;\"> (Access limited)</span>";
		 $exnote .= "<span style=\"color: red;\"> Additionally access has been limited to first 10000 records per end-point.</span>";}
	else {$blockStr = "";}

	$exnote .= "<br/><br/><b>These results include:</b><br/><ul><li>".implode("</li><li>", $dets["comment"])."</li></ul>";
	$opts = buildPagination ($pageURI, $page, $maxPages, $returnsBlocked);

	$sstr = "<span style=\"color: green;\"> Search for: <b>".$getExtra[0]."</b></span>";
	$dstr = "Displaying resources from objects <b>". implode(" - ", $range) ."</b> of <b>".$dets["total"]."</b>";

	if (!$epts)
		{$note .= "".buildModalButton ($defaultStr, true, false);}
	else if ($dets["limited"])
		{$note .= "".buildModalButton("$sstr. $dstr.$blockStr", true, $list, $opts);
		 $d["info"] .= "Please note your search has been limited, attempting to display <b>$dets[resources]</b> resources relating to <b>$dets[objects]</b> objects (<b>". implode(" - ", $range) ."</b> of <b>$dets[total]</b> results).$exnote";
			}
	else if (!$dets["total"])
		{$note .= "".buildModalButton (" Sorry, no results have been found for your search for <b>".$getExtra[0]."</b>, please try again.", true, false);}
	else
		{$note .= "".buildModalButton("$sstr. $dstr.", true, $list);}

	ob_start();			
	echo <<<END
	
	var submits = document.getElementsByClassName('searchsubmit');
	for (var i=0, len=submits.length|0; i<len; i=i+1|0) {
	  submits[i].addEventListener("click", formatSearchGet);}
	
	function formatSearchGet(e) {
		e.preventDefault();
		const svalue = document.getElementById(e.target.id+"-search");		
		const lvalue = document.getElementById(e.target.id+"-limit");
		
		if (lvalue.value != "...") {var lstr = "/" + lvalue.value;}
		else {var lstr = "";}
		
		const pvalue = document.getElementById(e.target.id+"-page");
		const pno = parseInt(pvalue.value)		
		if (pno) {var pstr = "/" + pno;}
		else {var pstr = "";}
		
		var vars = [];
		var pname = window.location.pathname
		var parts = pname.replace(/([\/])([^\/]+)/gi, function(m,key,value) {
			vars.push(value);});
		var new_url = "$rootDisplayURL" + "/" + svalue.value + lstr + pstr;
		//console.log(new_url);
		window.location.href = new_url;
		}

	$morejs
END;
	$pd["extra_js"] .= ob_get_contents();
	ob_end_clean(); // Don't send output to client
	
	if ($note)
		{$note = '<div class="alert alert-warning" role="alert" style="padding:0px 0.5rem 0px 0.5rem;">'.$note.'</div>';}
	
	$limits = array(25,50,75,100);
	$selStr = "";
	
	foreach ($limits as $k => $v)
		{
		if ($v == $limit)
			{$selected = "selected";}
		else
			{$selected = "";}
		$selStr .= "<option value=\"$v\" $selected>$v</option>";
		}
									
	ob_start();			
	echo <<<END
		$note</div>
		<div class="row justify-content-center flex-grow-1">
			<div class="h-100" style="position:relative;min-height:400px;" id="$divID">
			</div>
		</div>
	
<div class="modal fade" id="listModal" tabindex="-1" aria-labelledby="listModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="listModalLabel">Identified Details for Displayed Images</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        $imagelist
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="infoModalLabel">Further Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        $d[info]
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="searchModalLabel">Run New Search</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form class="d-flex  justify-content-center" style="padding:0.5rem 0px 0.5rem 0px;">
					<div class="bd-example">
						<label for="submit-modal-limit" class="form-label">Free text search term ("AND" operator used for multiple words)</label>
						<div class="input-group mb-3">
							<input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" id="submit-modal-search" name="submit-modal-search" value="$stermForm">
							<button class="btn btn-outline-success searchsubmit" id="submit-modal">Search</button>
						</div>
						<label for="submit-modal-limit" class="form-label">Object limit and page number <b>per end-point</b></label>
						<div class="input-group mb-3">        
							<label class="input-group-text" for="submit-modal-limit">Limit</label>
							<select class="form-select" id="submit-modal-limit">
								$selStr
							</select>
							<label class="input-group-text" for="submit-modal-page">Page No.</label>
							<input type="text" aria-label="Page Number" class="form-control" id="submit-modal-page" placeholder="1" value="$page">
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>
END;
	$extra_content = ob_get_contents();
	ob_end_clean(); // Don't send output to client
	
	$d = positionExtraContent ($d, $extra_content);

  return (array("d" => $d, "pd" => $pd));
  }

function MD_buildCatalog ($mans)
			{
			$cats = array();
			
			foreach ($mans as $k => $m)
				{$cats[] = '{"manifestId": "'.$m.'"}';}
			
			$catalog = '
			"catalog": [
				'.implode(',
				', $cats).'
				]';
				
			return($catalog);
			}
			
	
function buildModalButton ($comment=false, $info=false, $list=false, $nav=false)
	{
	global $buttons;
	
	if($info)
		{$infoButton = $buttons["info"];}
	else
		{$infoButton = '';}
		
	if($list)
		{$listButton = "<td>".$buttons["list"]."</td>";}
	else
		{$listButton = '';}
		
	if ($nav) {
		$nav = "<div class=\"row\"><div class=\"col d-flex justify-content-center\">".
			"$nav</div></div>";
		}
		
	$searchButton = $buttons["search"];
				
	ob_start();
	echo <<<END
			<div class="row" style="padding:0.5rem;">				
				<div class="col-xl-11 col-lg-10 col-md-9 col-7" style="padding-right:2px;display:table;" >
					<span style="display:table-cell;vertical-align: middle;">$comment</span>
				</div>
				<div class="col-xl-1 col-lg-2 col-md-3 col-5" style="padding-left:0px;" >
					<div class="container">
						<div class="row">
							<div class="col d-flex justify-content-center">
								<table>
									<tr>
										$listButton
										<td>$searchButton</td>
										<td>$infoButton</td>
									</tr>
								</table>
							</div>
						</div>
						$nav
					</div>
				</div>
			</div>
END;
	$html = ob_get_contents();
	ob_end_clean(); // Don't send output to client
			
	return ($html);
	}

function OSD_formatImageList($list)			
	{
	global $rootDisplayURL;

	$html = "<div class=\"container\">";
	$pstyle = "class=\"text-uppercase\" style=\"cursor:default;font-weight:bold;display:table-cell;vertical-align:bottom;";
	$pstyle2 = $pstyle."text-align:center;";
	
	echo <<<END
	<ul class="list-group list-group-flush">
	<li class="list-group-item">
	<div class="row">		
		<div class="col-lg-8 col-6" style="display:table;">
			<p $pstyle">Filename or ID</p>
		</div>
		<div class="col-lg-2 col-3" style="display:table;">
			<p $pstyle2" title="Links to open specific image in a new tab">View</p>
		</div>
		<div class="col-lg-2 col-3" style="display:table;">
			<p $pstyle2"  title="Links to open an image IIIF info.json file in a new tab">Info</p>
		</div>
	</div>
	</li>
END;
		$html .= ob_get_contents();
		ob_end_clean(); // Don't send output to client		

	foreach ($list as $k => $url)
		{		
		if (preg_match ("/^(.+[\/])([^\/]+)[\/]info.json$/", $url, $m))
			{$file = $m[2];
			 $search = "<a target=”_blank” href=\"$rootDisplayURL/$m[2]\" title=\"Open specific image in a new tab\">
				<img alt=\"$m[2]\" src=\"$m[1]/$m[2]/full/64,/0/native.jpg\" style=\"margin-left:auto;margin-right:auto;display:block;\">
			 </a>";}
		else
			{$file = "Name not Found";
			 $search = "Link not Found";}
			 
		ob_start();
	echo <<<END
	<li class="list-group-item">
	<div class="row">
		<div class="col-lg-8 col-6" style="display:table;overflow:hidden;">
			<p class="text-break">$file</p>
		</div>
		<div class="col-lg-2 col-3" style="display:table;">
			$search
		</div>
		<div class="col-lg-2 col-3" style="display:table;">
			<a target=”_blank” href="$url" title="Open image IIIF info.json file in a new tab">
				<img style="margin-left:auto;margin-right:auto;display:block;" alt="image icon" width="32" src="https://avatars.githubusercontent.com/u/5812589?s=32&v=4"></a>
		</div>
	</div>
			</li>
END;
		$html .= ob_get_contents();
		ob_end_clean(); // Don't send output to client		
		}
	
	$html .= "</ul></div>";	
	return($html);
	}
	
		
function getsslJSONfile ($uri, $decode=true)
	{
	$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,),);  

	$response = file_get_contents($uri, false, stream_context_create($arrContextOptions));
	
	if ($decode)
		{return (json_decode($response, true));}
	else
		{return ($response);}
	}
	
function getExternalDetails($searchterm, $uri="https://scientific.ng-london.org.uk/tools/md/api.php?search=", $extra="")
	{$uri = $uri.$searchterm.$extra;
	 $arr = getsslJSONfile($uri);
	 return($arr);}

?>
