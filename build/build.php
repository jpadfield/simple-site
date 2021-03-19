<?php

// Last updated 04 Feb 2021

// simple array "extensionClassName => newFunctionName"
$extensionList = array();
$html_path = "../docs/";

$default_scripts = array(
	"js-scripts" => array (
		"jquery" => "https://unpkg.com/jquery@3.6.0/dist/jquery.min.js",
		"tether" => "https://unpkg.com/tether@1.4.7/dist/js/tether.min.js",
		"bootstrap" => "https://unpkg.com/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"),
		"css-scripts" => array(
			"fontawesome" => "https://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css",
			"bootstrap" => "https://unpkg.com/bootstrap@4.6.0/dist/css/bootstrap.min.css",
			"main" => "css/main.css"
		)
	);

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
	if (!is_array($site) or count($site) < 1) {
		exit("\nERROR: Sorry your site.json file has not been opened correctly please check you json formatting and try vaildating it using a web-site similar to https://jsonlint.com/\n\n");
	}

	// The original system used an additional sub-pages file, so just add any extra sub-pages still listed there
	if (is_file("sub-pages.json")) {
		$expages = getRemoteJsonDetails("sub-pages.json", false, true);
	} else {
		$expages = array();
	}

	// Variable used to add additional code at the bottom of a page -
	// the main purpose of this is to append the json code when the
	// system is used to present demo pages.
	$extraHTML = "";

	$pnames = array();
	$pages = getRemoteJsonDetails("pages.json", false, true);
	if (!is_array($pages) or count($pages) < 1) {
		exit("\nERROR: Sorry your pages.json file has not been opened correctly please check you json formatting and try vaildating it using a web-site similar to https://jsonlint.com/\n\n");
	} else {
		$pages = pagesCheck(array_merge($expages, $pages));
	}

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

	function pagesCheck($pages) {
		global $pnames;

		$default = array(
			"parent"=>"", "class"=>"", "file"=>"",
			"title"=>"", "content"=>"", "content right"=>""
		);

		foreach ($pages as $k => $a) {
			$pages[$k] = array_merge($default, $a);
			if (!$pages[$k]["parent"]){
				$pnames[] = $k;
			}
		}

		return($pages);
	}


	/**
	 * [prg description]
	 * @param  boolean $exit   [description]
	 * @param  boolean $alt    [description]
	 * @param  boolean $noecho [description]
	 * @return [type]          [description]
	 */
	function prg($exit=false, $alt=false, $noecho=false) {
		if ($alt === false) {$out = $GLOBALS;}
		else {$out = $alt;}

		ob_start();

		if (php_sapi_name() === 'cli') {
			echo "\n";
		} else {
			echo "<pre class=\"wrap\">";
		}

		if (is_object($out)) {
			var_dump($out);
		} else {
			print_r ($out);
		}

		if (php_sapi_name() === 'cli') {
			echo "\n";
		} else {
			echo "</pre>";
		}

		$out = ob_get_contents();
		ob_end_clean(); // Don't send output to client

		if (!$noecho) {echo $out;}

		if ($exit) {
			exit;
		} else {
			return ($out);
		}
	}

	/**
	 * [getRemoteJsonDetails description]
	 * @param  [type]  $uri    [description]
	 * @param  boolean $format [description]
	 * @param  boolean $decode [description]
	 * @return [type]          [description]
	 */
	function getRemoteJsonDetails ($uri, $format=false, $decode=false)
	{
		if ($format) {
			$uri = $uri.".".$format;
		}
		$fc = file_get_contents($uri);
		if ($decode) {
			$output = json_decode($fc, true);
		} else {
			$output = $fc;
		}
		return ($output);
	}

	/**
	 * [countFootNotes description]
	 * @param  [type] $matches [description]
	 * @return [type]          [description]
	 */
	function countFootNotes($matches) {
		global $fcount, $footnotes;
		$footnotes[] = $matches[1];
		$out = '<sup><a id="ref' . $fcount . '" href="#section';
		$out .= $fcount . '">[' . $fcount . ']</a></sup>';
		$fcount++;
		return $out;
	}

	/**
	 * [addLinks description]
	 * @param [type] $matches [description]
	 */
	function addLinks($matches) {
		if (count($matches) > 1) {
			$out = "<a href='" . str_replace(' ', '%20', $matches[2]) . "'>$matches[1]</a>";
		} else {
			$out = "<a href='" . str_replace(' ', '%20', $matches[0]) . "'>$matches[0]</a>";
		}
		return $out;
	}

	/**
	 * [parseLinks description]
	 * @param  [type]  $text [description]
	 * @param  integer $sno  [description]
	 * @return [type]        [description]
	 */
	function parseLinks ($text, $sno=1) {
		global $fcount, $footnotes;
		$fcount = $sno;

		$text = preg_replace_callback('/\[([^\]]+)[|]([^\]]+)\]/', 'addLinks', $text);
		$text = preg_replace_callback('/\[[@][@]([^\]]+)\]/', 'countFootNotes', $text);

		//Extract the footnotes for this section of the text
		$use = array_slice($footnotes, ($sno-1), ($fcount-1));

		$text = $text . "<div class=\"foonote\"><ul>";
		if ($use) {
			$text = $text . "<hr/>";
		}
		foreach ($use as $j => $str) {
			$k = $j + $sno;
			$str = preg_replace_callback('/http[^\s]+/', 'addLinks', $str);
			$text = $text . "<li id=\"section${k}\"><a href=\"#ref${k}\">[${k}]</a> $str</li>";
		}
		$text = $text . "</ul></div>";

		return $text;
	}


	function buildSimpleBSGrid ($bdDetails = array()) {
		ob_start();

		if (isset($bdDetails["topjumbotron"])) {
			echo "<div class=\"jumbotron\">" . $bdDetails["topjumbotron"] . "</div>";
		}

		if (isset($bdDetails["rows"])) 	{
			foreach ($bdDetails["rows"] as $k => $row) {
				echo '<div class="row">';

				foreach ($row as $j => $col) {
					if (!isset($col["class"])) {
						$col["class"] ="col-6 col-lg-4";
					}
					if (!isset($col["content"])) {
						$col["content"] ="Default Text";
					}
					echo '<div class=\"$col[class]\">' . $col["content"] . '</div><!--/span-->';
				}

				echo "</div><!--/row-->";
			}
		}

		if (isset($bdDetails["bottomjumbotron"]) and $bdDetails["bottomjumbotron"]){
			echo "<div class=\"jumbotron\">" . $bdDetails["bottomjumbotron"] . "</div>";
		}	else {
			echo "<br/>";
		}

		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client

		return($html);
	}
	/**
	 * [loopMenus description]
	 * @param  [type] $str [description]
	 * @param  [type] $key [description]
	 * @param  [type] $arr [description]
	 * @param  [type] $no  [description]
	 * @return [type]      [description]
	 */
	function loopMenus ($str, $key, $arr, $no) {
		$str .= '<!-- Dropdown Loop '. $no .' -->';
		$str .= '<li class="dropdown-submenu">';
		$str .= '<a id="dropdownMenu' . $no;
		$str .= '" href="#" role="button" data-toggle="dropdown"';
		$str .= ' aria-haspopup="true" aria-expanded="false"';
		$str .= ' class="dropdown-item dropdown-toggle" title="Click to open the ';
		$str .= ucfirst($key) . ' menu">' . ucfirst($key) . '</a>';
		$str .= '<ul aria-labelledby="dropdownMenu' . $no;
		$str .=  '" class="dropdown-menu border-0 shadow">';
		$str .=  '<li><a href="' . str_replace(' ', '%20', $key);
		$str .= '.html" class="dropdown-item  top-item" title="Click to open the ';
		$str .=  ucfirst($key) . ' page">' . ucfirst($key) . '</a></li>';
		$str .= '<li class="dropdown-divider"></li>';

		foreach ($arr as $k => $a) {
			if (!$a) {
				$str .= '<li><a href="' . slugify($k) . '.html" class="dropdown-item">';
				$str .= ucfirst($k) . '</a></li>';
			} else {
				$str = loopMenus ($str, $k, $a, false, $no+1);
			}
		}

	$str .= '</ul></li>';
	$str .= '<!-- End Loop ' . $no . ' -->';
	return $str;
}


function slugify($text)
{
  // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, '-');

  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
}
/**
 * [buildTopNav description]
 * @param  [type]  $name [description]
 * @param  boolean $bcs  [description]
 * @return [type]        [description]
 */
function buildTopNav ($name, $bcs=false) {
	global $pages, $pnames, $menuList;
	$active = array("active", '<span class="sr-only">(current)</span>');
	$html = "<div class=\"collapse navbar-collapse\" id=\"navbarsExampleDefault\"><ul class=\"navbar-nav\">
	";

	$no = 1;

	foreach ($pnames as $pname) {
		if ($pname == "home") {
			$puse= "index";
		} else {
			$puse = $pname;
		}
		$puse = slugify($puse);

		if ($pname == $name) {
			$a = $active;
		} else {
			$a = array("", "");
		}

		if (isset($menuList[$pname])) {
			$html .= '<!-- Dropdown Loop ' . $no . ' --><li class="nav-item dropdown ';
			$html .= $a[0] . '"><a id="dropdownMenu' . $no;
			$html .= '" href="#" data-toggle="dropdown" aria-haspopup="true"';
			$html .= ' aria-expanded="false" class="nav-link dropdown-toggle"';
			$html .= ' title="Click to open the ' . ucfirst($pname) . ' menu" >';
			$html .= ucfirst($pname) . $a[1] . '</a>';
			$html .= '<ul aria-labelledby="dropdownMenu' . $no;
			$html .= '" class="dropdown-menu border-0 shadow">' . '<li><a href="';
			$html .= $puse . '.html" class="dropdown-item top-item" title="Click to open the ';
			$html .= ucfirst($pname) . ' page">' . ucfirst($pname) . '</a></li>';
			$html .= '<li class="dropdown-divider"></li>';
			foreach ($menuList[$pname] as $k => $a)
			{
				if (!$a) {
					$html .= '<li><a href="' . slugify($k) . '.html" class="dropdown-item">';
					$html .=  ucfirst($k) . '</a></li>';
				} else {
					$html = loopMenus ($html, $k, $a, $no+1);
				}
			}
			$html .= '</ul></li><!-- End Loop ' . $no . ' -->';
		} else {
			$html .= '<li class="nav-item ' . $a[0] . '"><a class="nav-link" href="';
			$html .= $puse . '.html">' . ucfirst($pname) . $a[1] . '</a></li>';
		}
	}

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

function buildExamplePages () {
	global $pages, $subpages, $html_path, $menuList;

	$files = glob($html_path."*.html");

	foreach ($files as $file) {
		unlink ($file);
	}

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

			foreach ($pages as $name => $d) {
				writePage ($name, $d);
			}
		}
	/**
	 * [buildBreadcrumbs description]
	 * @param  array $arr [description]
	 * @return [type]      [description]
	 */
	function buildBreadcrumbs (array $arr) {

		$html = false;

		// we do not need a link to the page we are on
		$ignore_last = array_pop($arr);

		if ($arr) {

			$list = "";
			foreach ($arr as $k => $v) {
				$V = slugify(ucfirst($v));
				$list .= '<li class="breadcrumb-item"><a href="';
				$list .= slugify($v) . '.html\">' .$V . '</a></li>';
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
	/**
	 * [writeTSPage description]
	 * @return [type] [description]
	 */
	function writeTSPage () {
		global $html_path;

		$ds = date("Y-m-d H:i:s");
		$myfile = fopen( $html_path . "${ds}.html", "w");
		$html = "<h2>Last updated on: $ds</h2>";
		fwrite($myfile, $html);
		fclose($myfile);
	}

	function writePage ($name, $d) {

		global $gdp, $menuList, $extensionList, $fcount, $footnotes, $extraHTML;

		$extraHTML = "";
		$footnotes = array();
		$pd = $gdp;

		if ($name == "home") {
			$use= "index";
		} else {
			$use = slugify($name);
		}
		$pd["page"] = "${use}.html";

		if ($d["parent"]) {
			$pd["topNavbar"] = buildTopNav ($d["bcs"][0]);
			$pd["breadcrumbs"] = buildBreadcrumbs ($d["bcs"]);
		}	else {
			$pd["topNavbar"] = buildTopNav ($name);
			$pd["breadcrumbs"] = "";
		}

		if (isset($d["class"]) and isset($extensionList[$d["class"]])) {
			$ta = buildExtensionContent($d, $pd);
			$content = $ta[0];
			$pd = $ta[1];
		} else {
			$content = parseLinks ($d["content"], 1);
		}

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
						)
					)
				);

				if ($d["content right"]){
					$d["content right"] = parseLinks ($d["content right"], $fcount);
					$pd["grid"]["rows"][1][0]["class"] = "col-lg-6";
					$pd["grid"]["rows"][1][1] = array (
						"class" => "col-lg-6",
						"content" => $d["content right"]
					);
				}

				// Used to display the JSON used to create a given page for demos
				if (isset($d["displaycode"])) {
					unset($d["bcs"]);
					$extraHTML .= displayCode ($d, "Page JSON Object");
					$pd["grid"]["rows"][2] = array(
						array (
							"class" => "col-12 col-lg-12",
							"content" => $extraHTML
						)
					);
				}

				$pd["body"] = buildSimpleBSGrid ($pd["grid"]);
				$html = buildBootStrapNGPage ($pd);
				$myfile = fopen("../docs/${use}.html", "w");
				fwrite($myfile, $html);
				fclose($myfile);
			}

function buildBootStrapNGPage ($pageDetails = array()) {
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
foreach ($pageDetails["js_scripts"] as $k => $path) {
	$jsScripts .="
<script src=\"$path\"></script>";
}

if ($pageDetails["licence"]){
	$tofu = '<div class="licence">'.$pageDetails["licence"].'</div>';
} else {
	$tofu = '<div>This site is based on the <a href=" https://github.com/jpadfield/simple-site">simple-site</a> project.</div>';
}

$extra_logos = "";
$exlno = 1;
foreach ($pageDetails["extra_logos"] as $k => $lds) {
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

		if ($pageDetails["GoogleAnalyticsID"]) {
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
		} else {
			$GoogleAnalytics = false;
		}
		$logo = $pageDetails['extra_logos']['github']['logo'];
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

		<meta property="og:locale" content="en" />
		<meta property="og:type" content="website" />
		<meta property="og:title" content="$pageDetails[metaTitle]" />
		<meta property="og:description" content="$pageDetails[metaDescription]" />
		<meta property="og:url" content="$pageDetails[site_url]" />
		<meta property="og:site_name" content="$pageDetails[site_name]" />
		<meta property="og:image" content="$logo" />
		<meta name="twitter:image" content="$logo" />


		<meta name="twitter:card" content="summary" />
		<meta name="twitter:description" content="$pageDetails[metaDescription]" />
		<meta name="twitter:title" content="$pageDetails[metaTitle]" />
		<meta name="twitter:site" content="$pageDetails[twitterHandle]" />
		<meta name="twitter:creator" content="$pageDetails[twitterHandle]" />
		<title>$pageDetails[metaTitle]</title>
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


	function positionExtraContent ($str, $extra)
	{
		$count = 0;
		$str = preg_replace('/\[[#][#]\]/', $extra, $str, -1, $count);
		if (!$count) {
			$str .= $extra;
		}
		return $str;
	}


	function buildExtensionContent ($d, $pd)
	{
		global $extensionList;
		$fn = $extensionList[$d["class"]];
		$out = call_user_func_array($fn, array($d, $pd));
		$content = parseLinks ($out["d"]["content"], 1);
		return array($content, $out["pd"]);
	}


	function displayCode ($array, $title=false, $format="json", $caption=false)
	{
		if ($format == "json") {
			$json = json_encode($array, JSON_PRETTY_PRINT);
			$json = htmlspecialchars ($json);
			$code = preg_replace('/[\\\\][\/]/', "/", $json);
		} else {
			$code = "";
			foreach($array as $value){
					$code .= trim($value) . "<br>";
			}
		}

		if ($title) {
			$title = "<h3>$title</h3>";
		}

		if (!$caption) {
			$caption = "The complete JSON object used to define this content and layout of this page.";
		}

		ob_start();
		echo <<<END
		<hr/>
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
