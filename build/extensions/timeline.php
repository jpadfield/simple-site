<?php

$extensionList["timeline"] = "extensionTimeline";
$start = false;
	
function extensionTimeline ($d, $pd)
  {
	global $start;
	
  if (isset($d["file"]) and file_exists($d["file"]))
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

		$d["content"] = positionExtraContent ($d["content"], $mcontent);
    }

  return (array("d" => $d, "pd" => $pd));
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
