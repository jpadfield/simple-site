<?php

require_once '../../d3-process-map/common.php';

$pages = getRemoteJsonDetails("pages.json", false, true);
$groups = getRemoteJsonDetails("groups.json", false, true);

$files = glob("../models/*/*-triples.csv");
$data = array();
foreach ($files as $f)
	{$data = array_merge($data, file($f));}
$errors = array();	

$raw = getRaw($data);
$fmods = array_keys($raw);

buildExamplePages ();

function getRaw($data)
	{	
	$model = array("all", "The full presentation of all of the data presented");
	$output = array();
	$output[$model[0]]["model"] = $model[0];
	$output[$model[0]]["comment"] = $model[1];	
	$output[$model[0]]["count"] = 0;	
	
	$no = 0;
	$bn = 0;
	$tn = 0;
	$ono = 0;
	$bnew = false;
	$bba = array();
	$bbano = 1;

	foreach ($data as $k => $line) 
		{	
		$trip = explode ("\t", $line);
		$trip = array_map('trim', $trip);
		// Increment triple number
		$tn++;
	
		if(preg_match("/^[\/][\/][ ]Model[:][\s]*([a-zA-Z0-9 ]+)[\s]*[\/][\/](.+)$/", $line, $m))
			{$model = array(trim($m[1]), trim($m[2]));
			 $output[$model[0]]["model"] = $model[0];
			 $output[$model[0]]["comment"] = $model[1];
			 $output[$model[0]]["count"] = 0;}
		
		if (isset($trip[2])) { // Ignore comments and empty lines
		
			// All Blank Nodes need to be numbered to be unique
			if ($trip[0] == "_Blank Node" and $trip[1] == "crm:P2.has type" and !$bnew)
				{$bn++;
				 $bnew=true;}
			
			// Ensure subsequent Blank Nodes are seen as new. 
			if ($trip[1] == "crm:P2.has type" AND $trip[0] != "_Blank Node")
				{$bnew=false;}
								
			if ($trip[0] == "_Blank Node")
				{$trip[0] = "_Blank Node-N".$bn;}
			else if (preg_match("/^_Blank Node[-]([0-9]+)$/", $trip[0], $m))
				{$trip[0] = "_Blank Node-N".($bn-$m[1]);}
				
			// Current process is assuming that the subject and the object can not both be Blank Nodes
			if ($trip[2] == "_Blank Node")
				{$trip[2] = "_Blank Node-N".$bn;
				 $bnew=false;}
			else if (preg_match("/^_Blank Node[-]([0-9]+)$/", $trip[2], $m))
				{$trip[2] = "_Blank Node-N".($bn-$m[1]);}
										
			$trip[1] = $trip[1]."-N".$tn;
			
			$output["all"]["triples"][] = $trip;
			$output["all"]["count"]++;
			$output[$model[0]]["triples"][] = $trip;
			$output[$model[0]]["count"]++;
		}
	else //Empty lines will force a new Blank node to be considered
		{$bnew=false;}
			
	if ($trip[0] == "// Stop")
		{break;}
	}	

	// Move "all" to the end of the list
	$output["all"] = array_shift($output);
	return ($output);
	}
	
	
function prg($exit=false, $alt=false, $noecho=false)
	{
	if ($alt === false) {$out = $GLOBALS;}
	
	//if ($alt[0] == "GLOBALS") {$out = $GLOBALS;}
	else {$out = $alt;}
	
	ob_start();
	echo "<pre class=\"wrap\">";
	if (is_object($out))
		{var_dump($out);}
	else
		{print_r ($out);}
	echo "</pre>";
	$out = ob_get_contents();
  ob_end_clean(); // Don't send output to client
  
	if (!$noecho) {echo $out;}
		
	if ($exit) {exit;}
	else {return ($out);}
	}
	

function getRemoteJsonDetails ($uri, $format=false, $decode=false)
	{if ($format) {$uri = $uri.".".$format;}
	 $fc = file_get_contents($uri);
	 if ($decode)
		{$output = json_decode($fc, true);}
	 else
		{$output = $fc;}
	 return ($output);}

$fcount = 1;

function countFootNotes($matches) {
  global $fcount;
  $out = '<sup><a id="ref'.$fcount.'" href="#section'.$fcount.'">['.$fcount.']</a></sup>';
  $fcount++;
  return($out);
}

function addLinks($matches) {
  $out = "<a href='$matches[0]'>$matches[0]</a>";
  return($out);
}

function parseFootNotes ($text, $footnotes, $sno=1)
	{
	global $fcount;
	$fcounts = $sno;
	
	$text = preg_replace_callback('/\[[@][@]\]/', 'countFootNotes', $text);
	$text = $text . "<div style=\"font-size:smaller;\"><ul>";
	foreach ($footnotes as $j => $str)
		{$k = $j + 1;
		 $str = preg_replace_callback('/http[^\s]+/', 'addLinks', $str);
		 $text = $text."<li id=\"section${k}\"><a href=\"#ref${k}\">[${k}]</a> $str</li>";}
	
	$text = $text . "</ul></div>";
	
	return ($text);	
	}		


function buildSimpleBSGrid ($bdDetails = array())
		{
		ob_start();
		
		if (isset($bdDetails["topjumbotron"]))
			{echo "<div class=\"jumbotron\">".$bdDetails["topjumbotron"].
				"</div>";}
		
		if (isset($bdDetails["rows"])) 
			{
			foreach ($bdDetails["rows"] as $k => $row)
				{
				echo "<div class=\"row\">";	
				
				foreach ($row as $j => $col)
					{if (!isset($col["class"])) {$col["class"] ="col-6 col-lg-4";}
					 if (!isset($col["content"])) {$col["content"] ="Default Text";}
					 echo "<div class=\"$col[class]\">".$col["content"]."</div><!--/span-->";}
				
				echo "</div><!--/row-->    ";
				}
			}
		
		if (isset($bdDetails["bottomjumbotron"]) and $bdDetails["bottomjumbotron"])
			{echo "<div class=\"jumbotron\">".$bdDetails["bottomjumbotron"].
				"</div>";}
		else
			{echo "<br/>";}
		
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client		
		
		return($html);
		}

function grouppage ($gds)//title, $comment, $group)
	{
	global $raw;
	
	$rows = array( 0 => 
			array (
				"class" => "col-12 col-lg-12",
				"content" => $gds["comment"]));

		$crows = "";
		
		foreach ($gds["models"] as $nm)// => $a)
			{
			$count = $raw[$nm]["count"];
			$tag = $raw[$nm]["comment"];
			
			ob_start();			
			echo <<<END
				<tr>
					<td><h4>$tag ($count - triples)</h4></td>
					<td style="text-align:right;white-space: nowrap;">
						<div class="btn-group" role="group" aria-label="Basic example">
						<a class="btn btn-outline-primary" href="models/d3_${nm}.html" role="button">D3 Model</a>
						<a class="btn btn-outline-success" href="models/mermaid_${nm}.html" role="button">Mermaid Model</a>
						</div
					</td>
				</tr>
END;
			$crows .= ob_get_contents();
			ob_end_clean(); // Don't send output to client			
			}	
			
		$rows[] = array (
				"class" => "col-12 col-lg-12",	
				"content" => '<table width="100%">'.$crows.'</table></br>');
					
		$grid = array(
			"topjumbotron" => "<h2>$gds[title]</h2>",
			"bottomjumbotron" => "",//<h1>Goodbye, world!</h1> <p>We hoped you liked this great page.</p>",
			"rows" => array($rows));
			
	return ($grid);
	}

function buildTopNav ($name)
	{
	global $pages;
	
	$pnames = array_keys($pages);
	$active = array("active", '<span class="sr-only">(current)</span>');
	$html = "<div class=\"collapse navbar-collapse\" id=\"navbarsExampleDefault\"><ul class=\"navbar-nav\">";
	
	foreach ($pnames as $pname)
		{if ($pname == "home") {$puse= "index";}
		 else {$puse = $pname;}
			 
		 if ($pname == $name) {$a = $active;}
			 else {$a = array("", "");}
			 $html .= '<li class="nav-item '.$a[0].'"><a class="nav-link" href="'.
				$puse.'.html">'.ucfirst($pname).$a[1].'</a></li>';}
	
	$html .= "</ul></div>";		
	return($html);
	}
	
function buildExamplePages ()
	{
	global $pages, $groups, $raw, $fmods, $dataset, $dataset_qs, $config, $data, $errors;
	
	$gpd = array(
    "extra_js_scripts" => array(),
    "extra_css_scripts" => array(),
    "metaDescription" => "NG API System",
    "metaKeywords" => "National Gallery|Paintings|Semantics|Open Linked Data|API|CIDOC|crm",
    "metaTitle" => "NG Example CRM Modelling",
    "extra_onload" => "",
    "extra_js" => "",
    "logo_link" => "./",
    "licence" => '<a href="https://www.nationalgallery.org.uk/terms-of-use"><img height="16" alt="National Gallery - Terms of Use" title="National Gallery - Terms of Use" src="graphics/ng-logo-black-100x40.png"/></a><a href="http://rightsstatements.org/vocab/InC-EDU/1.0/"><img height="16" alt="In Copyright - Educational Use Permitted" title="In Copyright - Educational Use Permitted" src="graphics/InC-EDU.dark-white-interior-blue-type.png"/></a><a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/4.0/"><img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-nd/4.0/88x31.png" /></a>'
		);    
	
	foreach ($raw as $name => $selected)
		{
		$pd = $gpd;			
		$pd["fluid"] = true;
    
    $def = Mermaid_formatData ($selected);
		$html = Mermaid_displayModel($def);
			
		$myfile = fopen("../docs/models/mermaid_${name}.html", "w");
		fwrite($myfile, $html);
		fclose($myfile);			
		
		$D3_data = D3_formatData($selected);
		
		$loc = "../docs/data";
		$loc = "data";
		
		if (!is_dir($loc."/${name}"))
			{mkdir($loc."/${name}");}
				
		if (!is_file($loc."/${name}/config.json")) {
			copy($loc."/config.json", $loc."/${name}/config.json");
			}
			
		$myfile = fopen($loc."/${name}/objects.json", "w");
		fwrite($myfile, "[\n");
		$ja = array();
		foreach ($D3_data as $nm => $a)
			{$ja[] = json_encode($a);}
		fwrite($myfile, implode(",\n", $ja));
		fwrite($myfile, "]");		
		fclose($myfile);
		
		$dataset = $name;
		$dataset_qs = "?dataset=$dataset";
				
		read_config();
		$config['jsonUrl'] = "d3_${name}.json";
		$json = json_encode($config);
		$html = D3_displayModel ($title, $dataset, $json);
		$myfile = fopen("../docs/models/d3_${name}.html", "w");
		fwrite($myfile, $html);
		fclose($myfile);
				
		read_data();
		$d3json = json_encode(array(
			'data'   => $data,
			'errors' => $errors));
	
		$myfile = fopen("../docs/models/d3_${name}.json", "w");
		fwrite($myfile, $d3json);
		fclose($myfile);
				
		$html = D3_displayList ($title, $dataset, $data);
		$myfile = fopen("../docs/models/d3_${name}_list.html", "w");
		fwrite($myfile, $html);
		fclose($myfile);
		}	
	
	foreach ($groups as $name => $d)
		{$pd = $gpd;		
		 $pd["topNavbar"] = buildTopNav ("models");
		 $pd["grid"] = grouppage ($d);
		 $pd["body"] = buildSimpleBSGrid ($pd["grid"]);
		 $html = buildBootStrapNGPage ($pd);
		 $myfile = fopen("../docs/${name}.html", "w");
		 fwrite($myfile, $html);
		 fclose($myfile);}

	foreach ($pages as $name => $d)
		{
		$pd = $gpd;		
		
		if ($name == "home") {$use= "index";}
		else {$use = $name;}
		
		$pd["topNavbar"] = buildTopNav ($name);
		$home = parseFootNotes ($d["content"], $d["footnotes"], 1);
				
		$pd["grid"] = array(
			"topjumbotron" => "<h2>$d[title]</h2>",
			"bottomjumbotron" => "",
			"rows" => array(
				array(
					array (
						"class" => "col-12 col-lg-12",
						"content" => $home)
					)));
							
		if ($d["content right"])
			{$pd["grid"]["rows"][0][0]["class"] = "col-6 col-lg-6";
			 $pd["grid"]["rows"][0][1] = 
					array (
						"class" => "col-6 col-lg-6",
						"content" => $d["content right"]);}
						
		if ($name == "models")
			{
			$crows = "";
			
			foreach ($groups as $g => $a)
				{
				ob_start();			
				echo <<<END
				<tr>
					<td style="text-align:right;">
						<a class="btn btn-outline-dark btn-block" href="${g}.html" role="button">$a[title]</a>
					</td>
				</tr>
END;
				$crows .= ob_get_contents();
				ob_end_clean(); // Don't send output to client			
				}
			
			$pd["grid"]["rows"][] = array(array (
				"class" => "col-12 col-lg-12",	
				"content" => '<table width="100%">'.$crows.'</table></br>'));						
			}
						
		$pd["body"] = buildSimpleBSGrid ($pd["grid"]);
		$html = buildBootStrapNGPage ($pd);
		$myfile = fopen("../docs/${use}.html", "w");
		fwrite($myfile, $html);
		fclose($myfile);
		}
	}
	
function buildBootStrapNGPage ($pageDetails=array())
	{	
	$default_scripts = array(
	"js-scripts" => array (
		"jquery" => "js/jquery-3.2.1.min.js",
		"tether" => "js/tether.js",
		"bootstrap" => "js/bootstrap.js"),
	"css-scripts" => array(
		"main" => "css/main.css",
		"bootstrap" => "css/bootstrap.min.css"));

	$defaults = array(
		"metaDescription" => "The National Gallery, London, ".
			"Scientific Department, is involved with research within a wide ".
			"range of fields, this page presents an example of some of the ".
			"work carried out.",
		"metaKeywords" => "The National Gallery, London, ".
			"National Gallery London, Scientific, Research, Heritage, Culture",
		"metaAuthor" => "Joseph Padfield| joseph.padfield@ng-london.org.uk |".
			"National Gallery | London UK | website@ng-london.org.uk |".
			" www.nationalgallery.org.uk",
		"metaTitle" => "NG Test Page",
		"metaFavIcon" => "https://www.nationalgallery.org.uk/custom/ng/img/icons/favicon.ico",
		"extra_js_scripts" => array(), 
		"extra_css_scripts" => array(),
		"extra_css" => "",
		"extra_js" => "",
		"logo_link" => "",
		"logo_path" => "graphics/ng-logo-white-100x40.png",
		"logo_style" => "",//"height='32px';",
		"extra_onload" => "",
		//"extra_resize" => "", // probably will not need this any more 
		"topNavbar" => "",
		"body" => "",
		"fluid" => false,
		"offcanvas" => false,
		"footer" => "&copy; The National Gallery 2020</p>",
		"footer2" => false,
		"licence" => false
		);
	 
	$pageDetails = array_merge($defaults, $pageDetails);

	$pageDetails["css_scripts"] = array_merge(
		$default_scripts["css-scripts"], $pageDetails["extra_css_scripts"]);
		
	$cssScripts = "";
	foreach ($pageDetails["css_scripts"] as $k => $path)
		{$cssScripts .="
	<link href=\"$path\" rel=\"stylesheet\" type=\"text/css\">";}
	
		
	$pageDetails["js_scripts"] = array_merge(
		$default_scripts["js-scripts"], $pageDetails["extra_js_scripts"]);
		
	$jsScripts = "";
	foreach ($pageDetails["js_scripts"] as $k => $path)
		{$jsScripts .="
	<script src=\"$path\"></script>";}

	if ($pageDetails["licence"])
			{$tofu = '<div style="white-space: nowrap;color:gray;">'.$pageDetails["licence"].'</div>';}
	else
			{$tofu = '<div>This site was developed and is maintained by: 
				<a href="mailto:joseph.padfield@ng-london.org.uk" 
					title="Joseph Padfield, The National Gallery Scientific Department">Joseph Padfield</a>.
					<a href="http://www.nationalgallery.org.uk/terms-of-use">Terms of Use</a></div>';}

	if ($pageDetails["topNavbar"])
		{
		ob_start();			
		echo <<<END
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
      <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <a class="navbar-brand"  href="$pageDetails[logo_link]">
  		<img id="page-logo" class="logo" title="Logo" src="$pageDetails[logo_path]" 
				style="$pageDetails[logo_style]" alt="The National Gallery"/>
		  </a>
			$pageDetails[topNavbar]
			
    <span class="navbar-text">
      <a href="http://www.iperionch.eu/">
				<img id="ex-logo1" class="logo" style="height:32px;" title="IPERION-CH" src="graphics/IPERION-CH_logo_trans.png" 
				style="$pageDetails[logo_style]" alt="IPERION CH | Integrated Platform for the European Research Infrastructure ON Cultural Heritage"/>
		  </a>
      <a href="https://sshopencloud.eu/">
				<img id="ex-logo2" class="logo" style="height:32px;" title="Logo" src="graphics/sshoc-logo.png" 
				style="$pageDetails[logo_style]" alt="SSHOC | Social Sciences & Humanities Open Cloud"/>
		  </a>
    </span>
    </nav>
END;
		$pageDetails["topNavbar"] = ob_get_contents();
		ob_end_clean(); // Don't send output to client
		}
			
	if($pageDetails["offcanvas"])
		{
		$oc = $pageDetails["offcanvas"];
		$offcanvasClass = "row-offcanvas row-offcanvas-right";
		$offcanvasToggle = "<p class=\"float-right hidden-md-up\"> ".
			"<button type=\"button\" class=\"btn btn-primary btn-sm\" ".
			"data-toggle=\"offcanvas\">{$pageDetails["offcanvas"][0]}</button>".
			"</p>";
		$sidepanel = "<div class=\"{$pageDetails["offcanvas"][2]} sidebar-offcanvas\" ".
			"id=\"{$pageDetails["offcanvas"][1]}\"><div class=\"list-group\">";
		
		$active = "active";	
		foreach ($pageDetails["offcanvas"][3] as $k => $a)
			{$sidepanel .= "<a href=\"$a[1]\" class=\"list-group-item link-extra $active\">".
				"$a[0]</a>";
			 $active = "";}
		$sidepanel .= "</div></div><!--/span-->";
		$ocw = "9";
		}
	else
		{$offcanvasClass = "";
		 $offcanvasToggle = "";
		 $sidepanel = "";
		 $ocw = "12";}
 	
	if ($pageDetails["footer"] or $pageDetails["licence"])
		{
		ob_start();			
		echo <<<END
  <footer>
		<div class="container-fluid">
			<div class="row">
				<div class="col-5" style="text-align:left;">$pageDetails[footer]</div>
				<div class="col-2" style="text-align:center;">$pageDetails[footer2]</div>
				<div class="col-5" style="text-align:right;">$pageDetails[licence]</div>
			</div>
		</div>        
  </footer>
END;
		$pageDetails["footer"] = ob_get_contents();
		ob_end_clean(); // Don't send output to client
		}
  
  if($pageDetails["fluid"]) {$containerClass = "container-fluid";}
  else {$containerClass = "container";}
  
  $fn = "function"; 
	ob_start();			
	echo <<<END
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="$pageDetails[metaDescription]" />
		<meta name="keywords" content="$pageDetails[metaKeywords]" />
    <meta name="author" content="$pageDetails[metaAuthor]" />
    <link rel="icon" href="$pageDetails[metaFavIcon]">
    <title>$pageDetails[metaTitle]</title>
    $cssScripts
    <style type="text/css">
    $pageDetails[extra_css]
    </style>
  </head>

  <body onload="onLoad();">
		<div class="$containerClass">
			$pageDetails[topNavbar]
			<div class="row $offcanvasClass">
			 <div class="col-12 col-md-$ocw">          
				$offcanvasToggle
				$pageDetails[body]
			</div><!--/span-->
			
			$sidepanel
			</div><!--/row-->
			
			$pageDetails[footer]
    </div><!--/.container-->
    
    $jsScripts
    <script type="text/javascript">
			$pageDetails[extra_js]
			$fn onLoad() {
				$pageDetails[extra_onload]
				}
    </script>
  </body>
</html>
END;
	$page_html = ob_get_contents();
	ob_end_clean(); // Don't send output to client

	return ($page_html);
	}	


	
function Mermaid_formatData ($selected)
	{
	ob_start();
	echo <<<END

graph LR

classDef crm stroke:#333333,fill:#DCDCDC,color:#333333,rx:5px,ry:5px;
classDef thing stroke:#2C5D98,fill:#D0E5FF,color:#2C5D98,rx:5px,ry:5px;
classDef event stroke:#6B9624,fill:#D0DDBB,color:#6B9624,rx:5px,ry:5px;
classDef oPID stroke:#2C5D98,fill:#2C5D98,color:white,rx:5px,ry:5px;
classDef ePID stroke:#6B9624,fill:#6B9624,color:white,rx:5px,ry:5px;
classDef aPID stroke:black,fill:#FFFF99,rx:20px,ry:20px;
classDef type stroke:red,fill:#B51511,color:white,rx:5px,ry:5px;
classDef name stroke:orange,fill:#FEF3BA,rx:20px,ry20px;
classDef literal stroke:black,fill:#FFB975,rx:2px,ry:2px,max-width:100px;
classDef classstyle stroke:black,fill:white;
classDef url stroke:#2C5D98,fill:white,color:#2C5D98,rx:5px,ry:5px;
classDef note stroke:#2C5D98,fill:#D8FDFF,color:#2C5D98,rx:5px,ry:5px;

END;
	$defTop = ob_get_contents();
	ob_end_clean(); // Don't send output to client	

	$defs = "";
	//$defs .= "<h1>".$selected["comment"]."</h1>";
	$defs .= "<div class=\"mermaid\">".$defTop;
	
	$things = array();
	$no = 0;
	$crm = 0;
		
	//
	foreach ($selected["triples"] as $k => $t) 
		{if(preg_match("/^(crm:E.+)$/", $t[2], $m))
			{$selected["triples"][$k][2] = $t[2]."-".$crm;
			 $crm++;}
		 if(preg_match("/^(.+)-N[0-9]+$/", $t[1], $m))
			{$selected["triples"][$k][1] = trim($m[1]);}}		//	*/
		
	foreach ($selected["triples"] as $k => $t) 
		{			
		if (count_chars($t[2]) > 60)
			{$use = wordwrap($t[2], 60, "<br/>", true);}
		else
			{$use = $t[2];}
			
		if(preg_match("/^(crm[:].+)[-][0-9]+$/", $use, $m))
			{$use = $m[1];}
			
		if(isset($t[3]))
			{$fcs = explode ("@@", $t[3]);}
		else
			{$fcs = array(false, false);}
								
		if (!isset($things[$t[0]]))
			{$things[$t[0]] = "O".$no;
			 $defs .= Mermaid_defThing($t[0], $no, $fcs[0]);
			 $no++;}
			 
		if (!isset($things[$t[2]]))
			{$things[$t[2]] = "O".$no;
			 $defs .= Mermaid_defThing($t[2], $no, $fcs[1]);
			 $no++;}		
					 					
		$defs .= $things[$t[0]]." -- ".$t[1]. " -->".$things[$t[2]]."[\"".$use."\"]\n";		
		}
	$defs .= ";</div>";
	
	return ($defs);
	}	

function Mermaid_defThing ($var, $no, $fc=false)
	{	
	$diagCmatches = array(
		"aat[:].+" => "type",
		"wd[:].+" => "type",
		"ulan[:].+" => "type",
		"tgn[:].+" => "type",
		"ng[:].+" => "oPID",
		"ngo[:].+" => "oPID",
		"ngi[:].+" => "oPID",
		"_Blank.+" => "thing",
		"http.+" => "url",
		"crm[:]E.+" => "crm",
		"[\"].+[\"]" => "note"
		);
		 
	if ($fc) {$cls = $fc;}
	else {
		$cls = "literal";
		foreach ($diagCmatches as $k => $cur)
			{
			if(preg_match("/^".$k."$/", $var, $m))
				{$cls = $cur;
				 break;}}}	 
	$code  = "O".$no;
	$str = "\n$code(\"$var\")\nclass $code $cls;\n";
		 
	if(preg_match("/^http.+$/", $var, $m))
		{$str .= "click ".$code." \"$var\" \"Tooltip\"\n";}		
	else if(preg_match("/^ngo[:]([0-9A-Z]{3}[-].+)$/", $var, $m))
		{$str .= "click ".$code." \"http://data.ng-london.org.uk/resource/$m[1]\" \"Tooltip\"\n";}
	else if(preg_match("/^ng[:]([0-9A-Z]{4}[-].+)$/", $var, $m))
		{$str .= "click ".$code." \"http://data.ng-london.org.uk/$m[1]\" \"Tooltip\"\n";}
	else if(preg_match("/^aat[:](.+)$/", $var, $m))
		{$str .= "click ".$code." \"http://vocab.getty.edu/aat/$m[1]\" \"Tooltip\"\n";}
	else if(preg_match("/^tgn[:](.+)$/", $var, $m))
		{$str .= "click ".$code." \"http://vocab.getty.edu/tgn/$m[1]\" \"Tooltip\"\n";}
	else if(preg_match("/^ulan[:](.+)$/", $var, $m))
		{$str .= "click ".$code." \"http://vocab.getty.edu/ulan/$m[1]\" \"Tooltip\"\n";}
	else if(preg_match("/^wd[:](.+)$/", $var, $m))
		{$str .= "click ".$code." \"https://www.wikidata.org/wiki/$m[1]\" \"Tooltip\"\n";}
	
	return ($str);
	}


function Mermaid_displayModel($defs, $title="")
	{
	global $d3Path;
	ob_start();
echo <<<END

body
{
  #background-color: #fcfcfc;
}


.list
{
	left:80px;
}

g a 
			{color:inherit;}

.nav-button {
    position: absolute;
    top: 8px;
    left: 8px;
}

.btn {
    display: inline-block;
    padding: 6px 12px;
    margin-bottom: 0;
    font-size: 14px;
    font-weight: normal;
    line-height: 1.428571429;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    cursor: pointer;
    background-image: none;
    border: 1px solid transparent;
    border-radius: 4px;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    -o-user-select: none;
    user-select: none;
}

.btn-default {
    color: #333333;
    background-color: #ffffff;
    border-color: #cccccc;
}

.btn-default:hover, .btn-default:focus, .btn-default:active, .btn-default.active, .open .dropdown-toggle.btn-default {
    color: #333333;
    background-color: #ebebeb;
    border-color: #adadad;
}

END;
	$styles = ob_get_contents();
	ob_end_clean(); // Don't send output to client	

	ob_start();
echo <<<END
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html> <!--<![endif]-->
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta charset="utf-8">
        <title>$title</title>
        
        <link rel="stylesheet" href="../css/mermaid.css">
    <style>			
		$styles
		</style>
  </head>
    <body>
    <div class="center-div">
        <div id="split-container">
            <a class="btn btn-default nav-button" id="nav-home" href="../index.html">
                Home
            </a>            
            <a class="btn btn-default nav-button" style="left:80px;" id="nav-models" href="../models.html">
                Models
            </a>
            <div id="graph-container">
                <div id="graph">	$defs</div>
            </div>
        </div>
        </div>
        
	
  <script src="../js/mermaid.min.js"></script>
  <script>mermaid.initialize({startOnLoad:true, flowchart: { 
    curve: 'basis'
  }});</script>  
    </body>
</html>
END;
	$html = ob_get_contents();
	ob_end_clean(); // Don't send output to client	

	return ($html);
	}

function D3_formatData($selected)
	{
	$output = array();

	foreach ($selected["triples"] as $k => $trip) 
		{
		$dtrip = $trip;
		
		//Hide the unique numbers on the nodes from the display
		foreach ($dtrip as $j => $v)
			{if(preg_match("/^(.+)-N[0-9]+$/", $v, $m))
				{$dtrip[$j] = trim($m[1]);}}
		
		if (count_chars($dtrip[2]) > 60)
		 {$dtrip[2] = wordwrap($dtrip[2], 60, "\n", true);}
							
		if (!isset($output[$trip[0]]))
			{$output[$trip[0]] = D3_processNode($trip[0], $dtrip[0]);}
		if (!isset($output[$trip[1]]))
			{$output[$trip[1]] = D3_processNode($trip[1], $dtrip[1], 1);}
		if (!isset($output[$trip[2]]))
			{$output[$trip[2]] = D3_processNode($trip[2], $dtrip[2]);}

		$output[$trip[1]]["depends"][] = $trip[0];
		$output[$trip[2]]["depends"][] = $trip[1];
		}	
		
	return($output);
	}	

function D3_processNode ($v, $dv, $prop=false)
	{	
	$diagclasses = array(
		"crm:E22.Man-Made Object" => "object",
		"crm:E31.Document" => "object",
		"ng:Further Events" => "ePID",
		"ngo:002-0432-0000" => "ePID"
		);
	
	$diagCmatches = array(
		"aat[:].+" => "type",
		"tgn[:].+" => "type",
		"ulan[:].+" => "type",
		"wd[:].+" => "type",
		"ng[:].+" => "oPID",
		"ngo[:].+" => "oPID",
		"_Blank.+" => "oPID",
		"http.+" => "url",
		"crm[:]E5[.].+" => "event",
		"crm[:]E12[.].+" => "event",
		"crm[:]E.+" => "object",
		"[\"].+[\"]" => "note"
		);
	
	if(preg_match("/^([a-z]+)[:][^\/].+$/", $v, $m))
		{$g = $m[1];}
	else if(preg_match("/^http[s]{0,1}[:].+$/", $v, $m))
		{$g = "url";}
	else if(preg_match("/^_Blank.+$/", $v, $m))
		{$g = "bn";}
	else if(preg_match("/^Note.+$/", $v, $m))
		{$g = "note";}
	else
		{$g = "lit";}
		
	if ($prop) {
		if ($g == "note")
			{$cls = "note";}
		else
			{$cls = "property";}}
	else {
		if(isset($diagclasses[$v])) {$cls = $diagclasses[$v];}
		else {
			$cls = "literal";
			foreach ($diagCmatches as $k => $cur)
				{if(preg_match("/^".$k."$/", $v, $m))
					{$cls = $cur;
					break;}
				}}}
	if (!$prop) {$g = false;}
		
	$output = array(
		"type" => $cls,
		"name" => $v,
		"display" => $dv,
		"group" => $g,
		"depends" => array()
		);
	
	return ($output);		
	}

function D3_displayList ($title, $dataset, $data)
	{
	$dstr = "";
	foreach ($data as $obj) {
    $id = get_id_string($obj['name']);
    $dstr .= "<div class=\"docs\" id=\"$id\">$obj[docs]</div>\n";
		}

	ob_start();
	echo <<<END
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html> <!--<![endif]-->
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta charset="utf-8">
        <title>$title</title>
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <link rel="stylesheet" href="../css/style.css">
        <link rel="stylesheet" href="../css/print.css">
    </head>
    <body>
				<a class="btn btn-default nav-button" id="nav-home" href="../">
                Home
            </a>            
            <a class="btn btn-default nav-button" style="left:80px;" id="nav-models" href="../models.html">
                Models
            </a>
            <a class="btn btn-default nav-button"  style="left:160px;"  id="nav-graph" href="d3_${dataset}.html">
                View Graph
            </a>
        <div id="docs-list">
					$dstr
        </div>
    </body>
</html>

END;
	$html = ob_get_contents();
	ob_end_clean(); // Don't send output to client	
		 
	return ($html);
	}	
		
function D3_displayModel ($title, $dataset, $json)
	{	
	ob_start();
	echo <<<END
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html> <!--<![endif]-->
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta charset="utf-8">
        <title>$title</title>
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <link rel="stylesheet" href="../css/style.css">
        <link rel="stylesheet" href="../css/svg.css">
    <style>
			body
{
  #background-color: #fcfcfc;
}
.center-div
{
  position: absolute;
  margin: auto;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  #background-color: #ccc;
  border-radius: 3px;
}

.list
{
	left:80px;
}
    </style>
    </head>
    <body>
        <!--[if lt IE 9]>
        <div class="unsupported-browser">
            This website does not fully support your browser.  Please get a
            better browser (Firefox or <a href="/chrome/">Chrome</a>, or if you
            must use Internet Explorer, make it version 9 or greater).
        </div>
        <![endif]-->
        <div class="center-div">
        <div id="split-container">
            <a class="btn btn-default nav-button" id="nav-home" href="../">
                Home
            </a>            
            <a class="btn btn-default nav-button" style="left:80px;" id="nav-models" href="../models.html">
                Models
            </a>
            <a class="btn btn-default nav-button"  style="left:160px;"  id="nav-list" href="d3_${dataset}_list.html">
                View list
            </a>
            <div id="graph-container">
                <div id="graph"></div>
            </div>
            <div id="docs-container">
                <a id="docs-close" href="#">&times;</a>
                <div id="docs" class="docs"></div>
            </div>
        </div>
        </div>
        <script src="../js/jquery-3.4.1.min.js"></script>
        <script src="https://d3js.org/d3.v3.js"></script>
        <script src="../js/colorbrewer.js"></script>
        <script src="../js/geometry.js"></script>
        <script>
            var config = $json;
        </script>
        <script src="../js/script_v2.0.js"></script>
    </body>
</html>

END;
	$html = ob_get_contents();
	ob_end_clean(); // Don't send output to client	
	
	 
	return ($html);
	}	
?>
