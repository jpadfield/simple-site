<?php

//require_once '../../d3-process-map/common.php';

$site = getRemoteJsonDetails("site.json", false, true);
$pages = getRemoteJsonDetails("pages.json", false, true);
$raw_subpages = getRemoteJsonDetails("sub-pages.json", false, true);
$subpages = array();

$bcs = array();
		
$gdp = array_merge ($site, array(
	"extra_js_scripts" => array(),
	"extra_css_scripts" => array(),
	"extra_onload" => "",
	"extra_js" => ""
	));   
		
buildExamplePages ();	
	
function prg($exit=false, $alt=false, $noecho=false)
	{
	if ($alt === false) {$out = $GLOBALS;}
	
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
	$fcount = $sno;
	
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
	
function loopBreadcrumbs ($name, $arr=array())
	{
	global $pages, $raw_subpages;
	
	$arr[] = $name;
	
	if (!isset($pages[$name]))
		{$arr = loopBreadcrumbs ($raw_subpages[$name]["parent"], $arr);}
		
	return ($arr);
	}
	
function buildExamplePages ()
	{
	global $gdp, $pages, $site, $raw_subpages, $subpages, $bcs;
	
	$files = glob("../docs/*.html");
	
	foreach ($files as $file)
		{unlink ($file);}
	
	//foreach ($pages as $name => $d) {$bsc[$name] = array();}
	
	foreach ($raw_subpages as $k => $a)
		{$a["name"] = $k;
		 $a["bcs"] = array_reverse(loopBreadcrumbs ($k));
		 $raw_subpages[$k] = $a;
		 $subpages[$a["parent"]][]= $a;}
	
	foreach ($raw_subpages as $k => $a)
		{writePage ($k, $a, false);}
		 
	foreach ($pages as $name => $d)
		{writePage ($name, $d, true);}
	}

function buildBreadcrumbs ($arr)
	{
	
	$html = false;
	
	// we do not need a link to the page we are on
	$ignore_last = array_pop($arr);
	
	if ($arr) {
		
		$list = "";
		foreach ($arr as $k => $v)
			{
			$V = ucfirst($v);
			$list .= "<li class=\"breadcrumb-item\"><a href=\"${v}.html\">$V</a></li>";
			}
	ob_start();			
	echo <<<END
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				$list
			</ol>
		</nav>
END;
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client
		}
	
	return($html);
	}
	
function writePage ($name, $d, $tnav=true)
	{
	global $subpages, $gdp;
	
	$pd = $gdp;
		
	if ($name == "home") {$use= "index";}
	else {$use = $name;}
		
	if ($tnav)
		{$pd["topNavbar"] = buildTopNav ($name);
		 $pd["breadcrumbs"] = "";}
	else
		{$pd["topNavbar"] = buildTopNav ($d["bcs"][0]);
		 $pd["breadcrumbs"] = buildBreadcrumbs ($d["bcs"]);}
	
	$home = parseFootNotes ($d["content"], $d["footnotes"], 1);
				
	$pd["grid"] = array(
		"topjumbotron" => "<h2>$d[title]</h2>",
		"bottomjumbotron" => "",
		"rows" => array(
			array(
				array (
					"class" => "col-12 col-lg-12",
					"content" => $pd["breadcrumbs"])
				),
			array(
				array (
					"class" => "col-12 col-lg-12",
					"content" => $home)
				)));
							
	if ($d["content right"])
		{$pd["grid"]["rows"][1][0]["class"] = "col-6 col-lg-6";
		 $pd["grid"]["rows"][1][1] = 
				array (
					"class" => "col-6 col-lg-6",
					"content" => $d["content right"]);}
						
	if (isset($subpages[$name]))
		{
		$crows = "";
			
		foreach ($subpages[$name] as $g => $a)
			{
			ob_start();			
			echo <<<END
			<tr>
				<td style="text-align:right;">
					<a class="btn btn-outline-dark btn-block" href="$a[name].html" role="button">$a[title]</a>
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
	
function buildBootStrapNGPage ($pageDetails=array())
	{	
	$default_scripts = array(
	"js-scripts" => array (
		"jquery" => "js/jquery-3.2.1.min.js",
		"tether" => "js/tether.min.js",
		"bootstrap" => "js/bootstrap.min.js"),
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
		"licence" => false,
		"extra_logos" => array(),
		"breadcrumbs" => false
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
	
	$extra_logos = "";
	$exlno = 1;
	foreach ($pageDetails["extra_logos"] as $k => $lds)
		{
		ob_start();			
		echo <<<END
			<a href="$lds[link]/">
				<img id="ex-logo${exlno}" class="logo" style="height:32px;" title="$k" src="$lds[logo]" 
				style="$pageDetails[logo_style]" alt="$lds[alt]"/>
		  </a>
END;
		$extra_logos .= ob_get_contents();
		ob_end_clean(); // Don't send output to client
		
		$exlno++;
		}
		
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
      $extra_logos
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
    <style>
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
    <script>
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

?>
