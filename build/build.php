<?php

$site = getRemoteJsonDetails("site.json", false, true);
if (!is_array($site) or count($site) < 1)
	{exit("\nERROR: Sorry your site.json file has not been opened correctly please check you json formatting and try vaildating it using a web-site similar to https://jsonlint.com/\n\n");}

// The original system used an additional sub-pages file, so just add any extra sub-pages still listed there
if (is_file("sub-pages.json"))
	{$expages = getRemoteJsonDetails("sub-pages.json", false, true);}
else
	{$expages = array();}

	
$pnames = array();	
$pages = getRemoteJsonDetails("pages.json", false, true);
if (!is_array($pages) or count($pages) < 1)
	{exit("\nERROR: Sorry your pages.json file has not been opened correctly please check you json formatting and try vaildating it using a web-site similar to https://jsonlint.com/\n\n");}
else
	{$pages = pagesCheck(array_merge($expages, $pages));}

$extensionPages = array("timeline", "mirador");

$menuList = array();
$subpages = array();
$bcs = array();

$fcount = 1;
$footnotes = array();

$defaults = array(
	"metaDescription" => "GitHub Project.",
	"metaKeywords" => "GitHub, PHP, Javascript, Clone",
	"metaAuthor" => "Me",
	"metaTitle" => "Test Page",
	"metaFavIcon" => "graphics/favicon.ico",
	"extra_js_scripts" => array(), 
	"extra_css_scripts" => array(),
	"extra_css" => "",
	"extra_js" => "",
	"logo_link" => "",
	"logo_path" => "graphics/github pages.png",
	"logo_style" => "",
	"extra_onload" => "",
	"topNavbar" => "",
	"body" => "",
	"fluid" => false,
	"offcanvas" => false,
	"footer" => "&copy; Me 2020</p>",
	"footer2" => false,
	"licence" => false,
	"extra_logos" => array(),
	"breadcrumbs" => false
	);
				
$gdp = array_merge ($defaults, $site);   

$html_path = "../docs/";
		
buildExamplePages ();	

function pagesCheck($pages)
	{
	global $pnames;
	
	$default = array(
		"parent"=>"", "class"=>"", "file"=>"",
		"title"=>"", "content"=>"", "content right"=>""
		);

	foreach ($pages as $k => $a)
		{$pages[$k] = array_merge($default, $a);
		 if (!$pages[$k]["parent"])
			{$pnames[] = $k;}}

	return($pages);
  }
	

	
function prg($exit=false, $alt=false, $noecho=false)
	{
	if ($alt === false) {$out = $GLOBALS;}
	else {$out = $alt;}
	
	ob_start();
	//echo "<pre class=\"wrap\">";
	if (is_object($out))
		{var_dump($out);}
	else
		{print_r ($out);}
	echo "\n";//</pre>";
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

function countFootNotes($matches) {
  global $fcount, $footnotes;
  $footnotes[] = $matches[1];
  $out = '<sup><a id="ref'.$fcount.'" href="#section'.$fcount.'">['.$fcount.']</a></sup>';
  $fcount++;
  return($out);
}

function addLinks($matches) {
	if (count($matches) > 1)
		{$out = "<a href='$matches[2]'>$matches[1]</a>";}
	else
		{$out = "<a href='$matches[0]'>$matches[0]</a>";}
  return($out);
}

function parseLinks ($text, $sno=1)
	{
	global $fcount, $footnotes;
	$fcount = $sno;
	
	$text = preg_replace_callback('/\[([^\]]+)[|]([^\]]+)\]/', 'addLinks', $text);
	$text = preg_replace_callback('/\[[@][@]([^\]]+)\]/', 'countFootNotes', $text);

	//Extract the footnotes for this section of the text
	$use = array_slice($footnotes, ($sno-1), ($fcount-1));
	
	$text = $text . "<div class=\"foonote\"><ul>";
	foreach ($use as $j => $str)
		{$k = $j + $sno;
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

function loopMenus ($str, $key, $arr, $no)
	{		
	$str .=
		'<!-- Dropdown Loop '.$no.' -->';
            
	$str .= '<li class="dropdown-submenu">
   <a id="dropdownMenu'.$no.'" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle" title="Click to open the '.ucfirst($key).' menu">'.ucfirst($key).'</a>
		<ul aria-labelledby="dropdownMenu'.$no.'" class="dropdown-menu border-0 shadow">'.
		'<li><a href="'.$key.'.html" class="dropdown-item  top-item" title="Click to open the '.ucfirst($key).' page">'.ucfirst($key).'</a></li>'.
		'<li class="dropdown-divider"></li>';

	foreach ($arr as $k => $a)
		{
		if (!$a)
			{$str .= '<li><a href="'.$k.'.html" class="dropdown-item">'.
				ucfirst($k).'</a></li>';}
		else
			{$str = loopMenus ($str, $k, $a, false, $no+1);}
		}

	$str .= '</ul></li><!-- End Loop '.$no.' -->'; 

	return ($str);
	}
	
function buildTopNav ($name, $bcs=false)
	{
	global $pages, $pnames, $menuList;
	
	$active = array("active", '<span class="sr-only">(current)</span>');
	$html = "<div class=\"collapse navbar-collapse\" id=\"navbarsExampleDefault\"><ul class=\"navbar-nav\">
";

	$no = 1;	
	
	foreach ($pnames as $pname)
		{if ($pname == "home") {$puse= "index";}
		 else {$puse = $pname;}
			 
		 if ($pname == $name) {$a = $active;}
		 else {$a = array("", "");}
		 
		 if (isset($menuList[$pname]))
			{
			$html .= '<!-- Dropdown Loop '.$no.' --><li class="nav-item dropdown '.$a[0].'">'.
				'<a id="dropdownMenu'.$no.'" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle" title="Click to open the '.ucfirst($pname).' menu" >'.ucfirst($pname).$a[1].'</a>';
			$html .= '<ul aria-labelledby="dropdownMenu'.$no.
				'" class="dropdown-menu border-0 shadow">'.'<li><a href="'.
				$puse.'.html" class="dropdown-item top-item" title="Click to open the '.ucfirst($pname).' page">'.ucfirst($pname).'</a></li>'.
				'<li class="dropdown-divider"></li>';
			foreach ($menuList[$pname] as $k => $a)
				{
				if (!$a)
					{$html .= '<li><a href="'.$k.'.html" class="dropdown-item">'.ucfirst($k).'</a></li>';}
				else
					{$html = loopMenus ($html, $k, $a, $no+1);}
				}

			$html .= '</ul></li><!-- End Loop '.$no.' -->'; 	
			}
		 else
			{$html .= '<li class="nav-item '.$a[0].'"><a class="nav-link" href="'.
			$puse.'.html">'.ucfirst($pname).$a[1].'</a></li>';}}
	
	$html .= "</ul></div>";
	
	return($html);
	}
	
function loopBreadcrumbs ($name, $arr=array())
	{
	global $pages;
	
	$arr[] = $name;
	
	if ($name and $pages[$name]["parent"])
		{$arr = loopBreadcrumbs ($pages[$name]["parent"], $arr);}	
	
	return ($arr);
	}
	
function buildExamplePages ()
	{
	global $gdp, $pages, $site, $raw_subpages, $subpages, $bcs,
		$html_path, $menuList;
	
	$files = glob($html_path."*.html");
	
	foreach ($files as $file)
		{unlink ($file);}

	// add a timestamp page to mark most recent update and to force github
	// to commit at least one new file as thus not return an error.
	writeTSPage ();

	foreach ($pages as $k => $a) {
		if (isset($a["parent"]) and $a["parent"]) {
			$a["name"] = $k;		 
			$a["bcs"] = array_reverse(loopBreadcrumbs ($k));
			$tml = implode ("']['", $a["bcs"]);
			$tml = "\$menuList['".$tml."'] = array();";
			eval($tml);	 
			$pages[$k] = $a;
			$subpages[$a["parent"]][]= $a;}}
		 
	foreach ($pages as $name => $d)
		{writePage ($name, $d);}
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

function writeTSPage ()
	{
	global $html_path;
	
	$ds = date("Y-m-d H:i:s");
	$myfile = fopen($html_path."${ds}.html", "w");
	$html = "<h2>Last updated on: $ds</h2>";
	fwrite($myfile, $html);
	fclose($myfile);
	}
	
function writePage ($name, $d)
	{	
	global $gdp, $menuList, $extensionPages, $fcount, $footnotes;

	$footnotes = array();	
	$pd = $gdp;
		
	if ($name == "home") {$use= "index";}
	else {$use = $name;}
		
	if ($d["parent"])
		{$pd["topNavbar"] = buildTopNav ($d["bcs"][0]);
		 $pd["breadcrumbs"] = buildBreadcrumbs ($d["bcs"]);}
	else
		{$pd["topNavbar"] = buildTopNav ($name);
		 $pd["breadcrumbs"] = "";}
	

	if (in_array($d["class"], $extensionPages))
		{$ta = buildExtensionContent($name, $d, $pd);
		 $content = $ta[0];
		 $pd = $ta[1];}
	else
		{$content = parseLinks ($d["content"], 1);}
				
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
					"content" => $content)
				)));
							
	if ($d["content right"])
		{$d["content right"] = parseLinks ($d["content right"], $fcount);
		 $pd["grid"]["rows"][1][0]["class"] = "col-6 col-lg-6";
		 $pd["grid"]["rows"][1][1] = 
				array (
					"class" => "col-6 col-lg-6",
					"content" => $d["content right"]);}
					
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
		"jquery" => "https://unpkg.com/jquery@3.4.1/dist/jquery.min.js",
		"tether" => "https://unpkg.com/tether@1.4.7/dist/js/tether.min.js",
		"bootstrap" => "https://unpkg.com/bootstrap@4.4.1/dist/js/bootstrap.min.js"),
	"css-scripts" => array(
		"fontawesome" => "https://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css",
		"bootstrap" => "https://unpkg.com/bootstrap@4.4.1/dist/css/bootstrap.min.css",
		"main" => "css/main.css"
		));

	ob_start();			
	echo <<<END
$(function() {
  // ------------------------------------------------------- //
  // Multi Level dropdowns
  // ------------------------------------------------------ //
  $("ul.dropdown-menu [data-toggle='dropdown']").on("click", function(event) {
    event.preventDefault();
    event.stopPropagation();

    $(this).siblings().toggleClass("show");


    if (!$(this).next().hasClass('show')) {
      $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
    }
    $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
      $('.dropdown-submenu .show').removeClass("show");
    });

  });
});
END;
	$pageDetails["extra_onload"] .= ob_get_contents();
	ob_end_clean(); // Don't send output to client

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
			{$tofu = '<div class="licence">'.$pageDetails["licence"].'</div>';}
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
				<img id="ex-logo${exlno}" class="logo" title="$k" src="$lds[logo]" 
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


function positionExtraContent ($str, $extra)
	{
	$count = 0;
	$str = preg_replace('/\[[#][#]\]/', $extra, $str, -1, $count);

	if (!$count)
		{$str .= $extra;}

	return ($str);	
	}

	
function buildExtensionContent ($name, $d, $pd)
	{
	$content = parseLinks ($d["content"], 1);
		
	if ($d["class"] == "mirador")
		{
		$mans = '[]';
		$wo = '[]';
		
		if (file_exists($d["file"]))
			{$dets = getRemoteJsonDetails($d["file"], false, true);
			 $mans = json_encode($dets["manifests"]);			 
			 
			 if (isset($dets["windows"]))
				{$wo = json_encode($dets["windows"]["slots"]);
				 $lo = json_encode($dets["windows"]["layout"]);}
			 else
			  {$use = $dets["manifests"][0]["manifestUri"];
				 $wo = '[{ "loadedManifest":"'.$use.'", "slotAddress":"row1", "viewType": "ImageView"}]';
				 $lo = '"1x1"';
				}}

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
         "windowObjects": '.$wo.',
         annotationEndpoint: {
           name:"Local Storage",
           module: "LocalStorageEndpoint" }
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

		$content = positionExtraContent ($content, '<div id="viewer"></div>');
		}
	else if ($d["class"] == "timeline")
		{			
		if (!file_exists($d["file"]))
			{die("ERROR: $d[file] missing\n");}
		else
			{
			$dets = getRemoteJsonDetails($d["file"], false, true);

			if (!isset($dets["start date"]))
				{die("ERROR: $d[file] format problems - 'start date' not found\n");}
		
			$start = $dets["start date"];
	
			$prefs = array_keys($dets["groups"]);
			$first = $prefs[0];

			if (!isset($dets["project"])) {$dets["project"] = "Please add a project title";}
			if (!isset($dets["margin"])) {$dets["margin"] = -3;}
		
			array_unshift($dets["groups"][$first]["stages"],
				array("Add as a margin", "", $dets["margin"], $dets["margin"]));
		
			$str = "";
			foreach ($dets["groups"] as $pref => $ga)
				{
				$str .= "\tsection $ga[title]\n";
				$no = 0;
				foreach ($ga["stages"] as $k => $a)
					{
					if ($a[1]) {$a[1] = "$a[1], ";}
					$str .= "\t\t".$a[0]." :$a[1]$pref$no, ".dA($a[2]).
						", ".dA($a[3])."\n";
					$no++;
					}
				}

			$pd["extra_js_scripts"][] =
				"https://unpkg.com/mermaid@8.4.8/dist/mermaid.min.js";
			$pd["extra_onload"] .= "
	mermaid.ganttConfig = {
    titleTopMargin:25,
    barHeight:20,
    barGap:4,
    topPadding:50,
    sidePadding:50
		}
//console.log(mermaid.render);
  mermaid.initialize({startOnLoad:true, flowchart: { 
    curve: 'basis' 
  }});";
			//use to hide the label used for the first line which is just in place to provide a margin/padding on the left.
			$pd["extra_css"] .= "
g a {color:inherit;}
#".$first."0-text {display:none;}";

		ob_start();
		echo <<<END
	<div class="mermaid">
gantt
       dateFormat  YYYY-MM-DD
       title $dets[project]	
       $str
	</div>
END;
			$mcontent = ob_get_contents();
			ob_end_clean(); // Don't send output to client

			$content = positionExtraContent ($content, $mcontent);
			}	
		}
	return (array($content, $pd));
	}
	
function dA ($v)
	{
	global $start;
	$a = explode(",", $v);
	$m = intval($a[0]);
	if(isset($a[1]))
		{$d = intval($a[1]);}
	else
		{$d = 0;}
	$date=new DateTime($start); // date object created.

	$invert = 0;
	if ($m < 0 or $d < 0)
		{$invert = 1;
		 $m = abs($m);
		 $d = abs($d);}
	$di = new DateInterval('P'.$m.'M'.$d.'D');
	$di->invert = $invert;
	$date->add($di); // inerval of 1 year 3 months added
	$new = $date->format('Y-m-d'); // Output is 2020-Aug-30
	return($new);
	}

?>
