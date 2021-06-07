<?php

// Last update 07 June 2021

// simple array "extentionClassName => newFunctionName" 
$extensionList = array();
$html_path = "../docs/";

$default_scripts = array(
	"js-scripts" => array (
		"jquery" => "https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js\" integrity=\"sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=\" crossorigin=\"anonymous",
		"tether" => "https://cdn.jsdelivr.net/npm/tether@2.0.0/dist/js/tether.min.js\" integrity=\"sha256-cExSEm1VrovuDNOSgLk0xLue2IXxIvbKV1gXuCqKPLE=\" crossorigin=\"anonymous",
		"bootstrap" => "https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.min.js\" integrity=\"sha256-d+FygkWgwt59CFkWPuCB4RE6p1/WiUYCy16w1+c5vKk=\" crossorigin=\"anonymous"),
	"css-scripts" => array(
		"fontawesome" => "https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/css/all.min.css\" integrity=\"sha256-2H3fkXt6FEmrReK448mDVGKb3WW2ZZw35gI7vqHOE4Y=\" crossorigin=\"anonymous",
		"bootstrap" => "https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.css\" integrity=\"sha256-BNdodQbWHpU3HT8xGhkEusT4ch4HEjvwzcbDcVuHR+E=\" crossorigin=\"anonymous",
		"main" => "css/main.css"
		));

// In any available extension files
// each one should add a value to the extensionList
// and define its own newFunction
//
//$extensionList["newExtension"] = "extensionNewFunction";
//
//function extensionNewFunction ($d, $pd)  {		
//	if (isset($d["file"]) and file_exists($d["file"]))
//		{/*DO STUFF*/}	
//
//  return (array("d" => $d, "pd" => $pd)); }
//

$de = glob("extensions/*.php");
foreach($de as $file){
  require_once $file;
}


$site = getRemoteJsonDetails("site.json", false, true);
if (!is_array($site) or count($site) < 1)
	{exit("\nERROR: Sorry your site.json file has not been opened correctly please check you json formatting and try vaildating it using a web-site similar to https://jsonlint.com/\n\n");}

// The original system used an additional sub-pages file, so just add any extra sub-pages still listed there
if (is_file("sub-pages.json"))
	{$expages = getRemoteJsonDetails("sub-pages.json", false, true);}
else
	{$expages = array();}

// Variable used to add additional code at the bottom of a page -
// the main purpose of this is to append the json code when the
// system is used to present demo pages.
$extraHTML = "";
	
$pnames = array();
$dpnames = array();

$pages = getRemoteJsonDetails("pages.json", false, true);
if (!is_array($pages) or count($pages) < 1)
	{exit("\nERROR: Sorry your pages.json file has not been opened correctly please check you json formatting and try vaildating it using a web-site similar to https://jsonlint.com/\n\n");}
else
	{$pages = pagesCheck(array_merge($expages, $pages));}

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
	"metaImage" => false,
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
	"footer" => "&copy; Me 2021</p>",
	"footer2" => false,
	"licence" => false,
	"extra_logos" => array(),
	"breadcrumbs" => false,
	"GoogleAnalyticsID" => false
	);
				
$gdp = array_merge ($defaults, $site);   

$html_path = "../docs/";
		
buildExamplePages ();	

function pagesCheck($pages)
	{
	global $pnames, $dpnames;
	
	$default = array(
		"parent"=>"", "class"=>"", "file"=>"",
		"title"=>"", "content"=>"", "content right"=>""
		);

	foreach ($pages as $k => $a)
		{$pages[$k] = array_merge($default, $a);
		 if (!$pages[$k]["parent"])
			{$pnames[] = $k;}
		 if (isset($a["displayName"]))
			{$dpnames[$k] = trim($a["displayName"]);}}

	return($pages);
  }

	
function prg($exit=false, $alt=false, $noecho=false)
	{
	if ($alt === false) {$out = $GLOBALS;}
	else {$out = $alt;}
	
	ob_start();
  
  if (php_sapi_name() === 'cli')
    {echo "\n";}
  else
    {echo "<pre class=\"wrap\">";}
    
	if (is_object($out))
		{var_dump($out);}
	else
		{print_r ($out);}

  if (php_sapi_name() === 'cli') 
    {echo "\n";}
  else
    {echo "</pre>";}
    
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
		{$out = "<a href='".str_replace(' ', '%20', $matches[2])."'>$matches[1]</a>";}
	else
		{$out = "<a href='".str_replace(' ', '%20', $matches[0])."'>$matches[0]</a>";}
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
	if ($use) {$text = $text . "<hr/>";}
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
			{echo '<div class="p-2 bg-light border rounded-3 jumbotron">
      <div class="container-fluid py-4">'.$bdDetails["topjumbotron"].'</div></div>';}
		
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
			{echo '<div class="p-2 bg-light border rounded-3 jumbotron">
      <div class="container-fluid py-4">'.$bdDetails["bottomjumbotron"].'</div></div>';
      }
		else
			{echo "<br/>";}
		
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client		
		
		return($html);
		}

function loopMenusTEST ($str, $key, $arr, $no)
	{		
	global $dpnames;
	$str .=
		'<!-- Dropdown Loop '.$no.' -->';
		
	if (isset($dpnames[$key]))
		{$dkey = $dpnames[$key];}
	else
		{$dkey = $key;}
            
	$str .= '
<li class="dropdown-submenu">
	<div class="btn-group">
		<a href="'.str_replace(' ', '%20', $key).'.html"><button type="button" class="btn btn-secondary nav-link" title="Click to open the &ldquo;'.ucfirst($dkey).'&rdquo; page">
  '.ucfirst($dkey).'</button></a>
  <button type="button" class="btn btn-secondary nav-link dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false"  title="Click to open the &ldquo;'.ucfirst($dkey).'&rdquo; menu">
    <span class="visually-hidden">Toggle Dropdown</span>
  </button>
  <ul class="dropdown-menu">';

	foreach ($arr as $k => $a)
		{
		if (isset($dpnames[$k]))
			{$dk = $dpnames[$k];}
		else
			{$dk = $k;}
			
		if (!$a)
			{$str .= '<li><a href="'.str_replace(' ', '%20', $k).'.html" class="dropdown-item">'.
				ucfirst($dk).'</a></li>';}
		else
			{$str = loopMenus ($str, $k, $a, false, $no+1);}
		}

	$str .= '</ul></li><!-- End Loop '.$no.' -->'; 

	return ($str);
	}



function loopMenus ($str, $key, $arr, $no)
	{		
	global $dpnames;
	$str .=
		'<!-- Dropdown Loop '.$no.' -->';
		
	if (isset($dpnames[$key]))
		{$dkey = $dpnames[$key];}
	else
		{$dkey = $key;}
            
	$str .= '<li class="dropdown-submenu">
		<a id="dropdownMenu'.$no.'" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle" title="Click to open the &ldquo;'.ucfirst($dkey).'&rdquo; menu">'.ucfirst($dkey).'
		&nbsp;&nbsp;<i class="fas fa-caret-right"></i></a>
		
		<ul aria-labelledby="dropdownMenu'.$no.'" class="dropdown-menu border-0 shadow">
			<li><a href="'.str_replace(' ', '%20', $key).'.html" class="dropdown-item  top-item" title="Click to open the &ldquo;'.ucfirst($dkey).'&rdquo; page">
				'.ucfirst($dkey).'</a></li>
		<li class="dropdown-divider"></li>';

	foreach ($arr as $k => $a)
		{
		if (isset($dpnames[$k]))
			{$dk = $dpnames[$k];}
		else
			{$dk = $k;}
			
		if (!$a)
			{$str .= '<li><a href="'.str_replace(' ', '%20', $k).'.html" class="dropdown-item">'.
				ucfirst($dk).'</a></li>';}
		else
			{$str = loopMenus ($str, $k, $a, false, $no+1);}
		}

	$str .= '</ul></li><!-- End Loop '.$no.' -->'; 

	return ($str);
	}
	
function buildTopNav ($name, $bcs=false)
	{
	global $pages, $pnames, $dpnames, $menuList;
	
	//prg(0, $dpnames);
	
	$active = array("active", '<span class="sr-only">(current)</span>');
	$html = "<div class=\"collapse navbar-collapse\" id=\"navbarsExampleDefault\"><ul class=\"navbar-nav\">
";

	$no = 1;	
	
	foreach ($pnames as $pname)
		{if ($pname == "home") {$puse= "index";} 
		 else {$puse = $pname;}
		 
		 if (isset($dpnames[$pname]))
			{$dpname = $dpnames[$pname];}
		 else
			{$dpname = $pname;}
			
		 $puse = str_replace(' ', '%20', $puse);

		 if ($pname == $name) {$a = $active;}
		 else {$a = array("", "");}
		 
		 if (isset($menuList[$pname]))
			{
			/*$html .= '
<!-- Dropdown Loop '.$no.' -->
<li class="nav-item dropdown '.$a[0].'"></li>
	<a id="dropdownMenu'.$no.'" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"  class="nav-link dropdown-toggle" title="Click to open the &ldquo;'.ucfirst($dpname).'&rdquo; menu" >
		'.ucfirst($dpname).$a[1].'
	</a>
	<ul aria-labelledby="dropdownMenu'.$no.'" class="dropdown-menu border-0 shadow">
		<li>
			<a href="'.$puse.'.html" class="dropdown-item top-item" title="Click to open the &ldquo;'.ucfirst($dpname).'&rdquo; page">'.ucfirst($dpname).'</a>
		</li>
		<li class="dropdown-divider">
	</li>';*/
	
	
			$html .= '
<li class="nav-item dropdown '.$a[0].'">
<!-- Dropdown Loop '.$no.' -->
<div class="btn-group">
  <a href="'.$puse.'.html"><button type="button" class="btn nav-link" title="Click to open the &ldquo;'.ucfirst($dpname).'&rdquo; page">
  '.ucfirst($dpname).$a[1].'</button></a>
  <button type="button" class="btn nav-link dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false"  title="Click to open the &ldquo;'.ucfirst($dpname).'&rdquo; menu">
    <span class="visually-hidden">Toggle Dropdown</span>
  </button>
  <ul class="dropdown-menu">';
  
	
			foreach ($menuList[$pname] as $k => $a)
				{
				if (isset($dpnames[$k]))
					{$dk = $dpnames[$k];}
				else
					{$dk = $k;}
			
				if (!$a)
					{$html .= '<li><a href="'.str_replace(' ', '%20', $k).'.html" class="dropdown-item">'.ucfirst($dk).'</a></li>';}
				else
					{$html = loopMenus ($html, $k, $a, $no+1);}
				}

			$html .= '</ul></li><!-- End Loop '.$no.' -->'; 	
			}
		 else
			{$html .= '<li class="nav-item '.$a[0].'"><a class="nav-link" href="'.
			$puse.'.html">'.ucfirst($dpname).$a[1].'</a></li>';}}
	
	$html .= "</ul></div></li>";
	
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
	
function makeFlatMenuList ($arr=false)
	{	
	global $menuList, $menuFlatList;
			
	if (!$arr) {$arr=$menuList;}
		
	foreach ($arr as $k => $v)
		{
		if (is_array($v) and $v)
			{$menuFlatList[$k] = array_keys($v);
			 makeFlatMenuList ($v);}
		else
			{$menuFlatList[$k] = false;}		
		}
	}
	
function buildExamplePages ()
	{
	global $pages, $subpages, $html_path, $menuList;
	
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
	
	makeFlatMenuList();
	
	foreach ($pages as $name => $d)
		{writePage ($name, $d);}
	}

function parseBreadbrumbs ($str)
	{
	global $dpnames;
	
	$out = array();
	
	if (isset($dpnames[$str]))
		{$dv = $dpnames[$str];}
	else
		{$dv = $str;}
				
	$out[0] = ucfirst($dv);
	$out[1] = str_replace(' ', '%20', $str);
	
	return ($out);
	}
	
function buildBreadcrumbs ($arr)
	{
	global $dpnames;
	
	$html = false;
	
	// process current page differently
	$cp = parseBreadbrumbs (array_pop($arr));
	
	if ($arr) 
		{
		$list = "";
		foreach ($arr as $k => $v)
			{$pv = parseBreadbrumbs ($v);
			 $list .= "<li class=\"breadcrumb-item\"><a href=\"".$pv[1].
				".html\">$pv[0]</a></li>";}
			
		$list .= "<li class=\"breadcrumb-item active\" aria-current=\"page\">$cp[0]</li>";
			
		ob_start();			
		echo <<<END
		<div class="alert alert-light" role="alert" style="padding-bottom: 0px;border-color:#dee2e6;">
		<nav  style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
			<ol class="breadcrumb">
				$list
			</ol>
		</nav>
		</div>
END;
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client
		}
	
	return($html);
	}
	
function buildChildLinks ($arr)
	{	
	global $dpnames;
	
	$html = false;
			
	if ($arr) {
		
		$list = "";
		foreach ($arr as $k => $v)
			{
			if (isset($dpnames[$v]))
				{$dv = $dpnames[$v];}
			else
				{$dv = $v;}
				
			$V = ucfirst($dv);
			$v = str_replace(' ', '%20', $v);
			$list .= "<a class=\"list-group-item list-group-item-action\" href=\"${v}.html\">$V</a>";
			}
	ob_start();			
	echo <<<END
		<div class="alert alert-light" role="alert">
			<h3>Included Pages</h3>
			<div class="list-group">			
				$list
			</div>
		</div>
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
	
function writeRDPage ($pname, $target)
	{
	global $html_path;
	
	$myfile = fopen($html_path."${pname}.html", "w");
	$html = "<meta http-equiv=\"refresh\" content=\"0; URL='$target'\" />";
	fwrite($myfile, $html);
	fclose($myfile);
	}
	
function writePage ($name, $d)
	{	
	global $gdp, $menuList, $menuFlatList, $extensionList, $fcount, $footnotes, $extraHTML;

	$extraHTML = "";
	$footnotes = array();	
	$pd = $gdp;	
    
	if ($name == "home") {$use= "index";}
	else {$use = $name;}
	$pd["page"] = "${use}.html";
	$pd["use"] = $use;
	
	// This has been added to allow for redirects - if the names of existing 
	// pages need to be changed redirects from the old name can be included 
	// automatically.
	if (isset($d["aliases"]))
		{$als = explode(",", $d["aliases"]);
		 foreach ($als as $k => $pname)
			{writeRDPage (trim($pname), $pd["page"]);}
		}
		
	if ($d["parent"])
		{$pd["topNavbar"] = buildTopNav ($d["bcs"][0]);
		 $pd["breadcrumbs"] = buildBreadcrumbs ($d["bcs"]);}
	else
		{$pd["topNavbar"] = buildTopNav ($name);
		 $pd["breadcrumbs"] = "";}

	if (isset($d["class"]) and isset($extensionList[$d["class"]]))
		{$ta = buildExtensionContent($d, $pd);		 
		 $d = $ta[0];
		 $pd = $ta[1];}

	$content = parseLinks ($d["content"], 1);
		
	$pd["grid"] = array(
		"topjumbotron" => "<h3>$d[title]</h3>",
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
		 $pd["grid"]["rows"][1][0]["class"] = "col-lg-6";
		 $pd["grid"]["rows"][1][1] = 
				array (
					"class" => "col-lg-6",
					"content" => $d["content right"]);}

	if (isset($menuFlatList[$name]) and $menuFlatList[$name])
		{$childrenHTML = buildChildLinks ($menuFlatList[$name]);
		 $pd["grid"]["rows"][] = array( array (
					"class" => "col-12 col-lg-12",
					"content" => $childrenHTML));}
		
	// Used to display the JSON used to create a given page for demos
	if (isset($d["displaycode"]))
		{unset($d["bcs"]);			
		 unset($d["name"]);			
		 $codeHTML = displayCodeSection ($d, "Page JSON Object");		 
		 if (isset($d["file"]) and $d["file"])
			{$fcont = getRemoteJsonDetails($d["file"], false, true);
			 $format = "json";
			 if (!$fcont) {
				 $fcont = getRemoteJsonDetails($d["file"], false, false);
				 $fcont = explode(PHP_EOL, trim($fcont));
				 $format = "txt";
				 }
			 $codeHTML .= displayCodeSection ($fcont, "Extra extension file", 
				$format, "The complete extension file used to define extra content included in this page.");}
		
		 $codeHTML = displayCode($codeHTML);
			
		 $pd["grid"]["rows"][] = array( array (
					"class" => "col-12 col-lg-12",
					"content" => $codeHTML));}
					
	$pd["body"] = buildSimpleBSGrid ($pd["grid"]);
	$html = buildBootStrapNGPage ($pd);
	$myfile = fopen("../docs/${use}.html", "w");
	fwrite($myfile, $html);
	fclose($myfile);
	}
	
function buildBootStrapNGPage ($pageDetails=array())
	{
  global $default_scripts;

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
			{$tofu = '<div>This site is based on the <a href=" https://github.com/jpadfield/simple-site">simple-site</a> project.</div>';}
	
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
<div class="container-fluid">
      <button 
				class="navbar-toggler navbar-toggler-right" 
				type="button" 
				data-bs-toggle="collapse" 
				data-bs-target="#navbarsExampleDefault" 
				aria-controls="navbarsExampleDefault" 
				aria-expanded="false" 
				aria-label="Toggle navigation">
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
</div>
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

	if ($pageDetails["GoogleAnalyticsID"])
		{
		ob_start();			
		echo <<<END
<!-- Global site tag (gtag.js) - Google Analytics - Only added in if the 'GoogleAnalyticsID' i set in the site.json file-->
	<script async src="https://www.googletagmanager.com/gtag/js?id=$pageDetails[GoogleAnalyticsID]"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', '$pageDetails[GoogleAnalyticsID]');
	</script>
END;
		$GoogleAnalytics = ob_get_contents();
		ob_end_clean(); // Don't send output to client
		}  
	else
		{$GoogleAnalytics = false;}
		
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
    <meta name="image" content="$pageDetails[metaImage]" />
    <link rel="icon" href="$pageDetails[metaFavIcon]">
    <title>$pageDetails[metaTitle] - $pageDetails[use]</title>
    $cssScripts
    <style>
    $pageDetails[extra_css]
    </style>
    $GoogleAnalytics
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


function positionExtraContent ($d, $extra)
	{
	$count = 0;
	
	if (preg_match('/\[[#][#]\]/', $d["content"]))
		{$d["content"] = preg_replace('/\[[#][#]\]/', $extra, $d["content"], -1, $count);}
	else if (preg_match('/\[[#][#]\]/', $d["content right"]))
		{$d["content right"] = preg_replace('/\[[#][#]\]/', $extra, $d["content right"], -1, $count);}
	else
		{$d["content"] .= $extra;}

	return ($d);	
	}
	
function OLDpositionExtraContent ($str, $extra)
	{
	$count = 0;
	$str = preg_replace('/\[[#][#]\]/', $extra, $str, -1, $count);

	if (!$count)
		{$str .= $extra;}

	return ($str);	
	}

	
function buildExtensionContent ($d, $pd)
	{
  global $extensionList;
  $fn = $extensionList[$d["class"]];
	$out = call_user_func_array($fn, array($d, $pd));
  //$content = parseLinks ($out["d"]["content"], 1);
		
	return (array($out["d"], $out["pd"]));
	}
	
function OLDbuildExtensionContent ($d, $pd)
	{
  global $extensionList;
  $fn = $extensionList[$d["class"]];
	$out = call_user_func_array($fn, array($d, $pd));
  $content = parseLinks ($out["d"]["content"], 1);
		
	return (array($content, $out["pd"]));
	}

function displayCodeSection ($array, $title=false, $format="json", $caption=false)
	{		
	if ($format == "json")
		{$json = json_encode($array, JSON_PRETTY_PRINT);
		 $json = htmlspecialchars ($json);
		 $code = preg_replace('/[\\\\][\/]/', "/", $json);}
	else
		{$code = "";
		 foreach($array as $value){
     $code .= trim($value) . "<br>";}}

  if ($title)
		{$title = "<h3>$title</h3>";}

	if (!$caption)
		{$caption = "The complete JSON object used to define this content and layout of this page.";}
    
	ob_start();			
	echo <<<END
	$title
	<figure>
		<pre style="overflow-y: auto;overflow-x: hidden; border: 2px solid black;padding: 10px;max-height:400px;"><code>${code}</code></pre>
		<figcaption class=\"figure-caption\">$caption</figcaption>
	</figure>	
END;
		$codeHTML = ob_get_contents();
		ob_end_clean(); // Don't send output to client

  return ($codeHTML);
	}
	
function displayCode ($str)
	{  		
	ob_start();			
	echo <<<END
	<hr/>
	<style>
	details {
    border: 1px solid #aaa;
    border-radius: 4px;
    padding: .5em .5em 0;
}

summary {
    font-weight: bold;
    margin: -.5em -.5em 0;
    padding: .5em;
}

details[open] {
    padding: .5em;
}

details[open] summary {
    border-bottom: 1px solid #aaa;
    margin-bottom: .5em;
}
	</style>
	<details>
	<summary>Display the full code used to define this page.</summary>
	<br/>
	$str
	</details>
END;
	$codeHTML = ob_get_contents();
	ob_end_clean(); // Don't send output to client

  return ($codeHTML);
	}

?>
