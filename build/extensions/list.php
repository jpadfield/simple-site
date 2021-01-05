<?php

// Last update 22 Dec 2020

$extensionList["list"] = "extensionCards";
$blank = array("groups" => array(), "ptitle" => "",
		"stitle" => "",  "comment" => "", "image" => "", "link" => "");
$defaultcard = "list";
$displaychecked = true;
		
// Still to do an optional table of contents for groups - model on https://github.com/IIIF/awesome-iiif

function buildContents ($groups)
	{
	$html = "<h3>Contents</h3><ul>";

	foreach ($groups as $gnm => $ga)
		{$tag = urlencode(strtolower($gnm));
 		 $html .= "<li>".
			"<a id=\"$tag-TBC\" class=\"offsetanchor\"></a>".
			"<a href=\"#$tag\">$gnm</a></li>";}
	
	$html .= "</ul>";

	return ($html);
	}

function extensionCards ($d, $pd)
  {
	global $blank, $defaultcard, $displaychecked ;
  $gcontent = "";
		
	if (isset($d["file"]) and file_exists($d["file"]))
		{
    //$d["file"] = "./awsomelist_groupsfull.json";
		$dets = getRemoteJsonDetails($d["file"], false, true);

    if (isset($dets["defaultcard"]))
      {$defaultcard = $dets["defaultcard"];}
      
    if (isset($dets["displaychecked"]))
      {$displaychecked = $dets["displaychecked"];}

    if (!isset($dets["list"]))
      {$dets["list"] = array();}

    // If a path to json files is provided they need to be loaded into an array
    if (!is_array($dets["list"] ))
      {
      $lfs = glob($dets["list"]);
      $dets["list"] = array();
      foreach($lfs as $file){
        $ja = getRemoteJsonDetails($file, false, true);
        if ($ja) {$dets["list"][] = $ja;}}
      }

    //prg(1, $dets);
		
		foreach ($dets["list"] as $lno => $la)
			{
			//ensure each of the currently required fields are present.
			$la = array_merge($blank, $la);
			
			foreach ($la["groups"] as $k => $gnm)
					{            
					/*$c = false;
					if (preg_match("/^Digital.+$/", $gnm, $m))
						{$c = true;
							echo "CCCCCCCCCCCCCCCCC\n"; prg(0, $gnm);}
					else
						{echo "@@@".$gnm."@@@\n";}*/
										
					if (!isset($dets["groups"] [$gnm] ))
						{$dets["groups"] [$gnm]  = array(
							"comment" => "",
							"card" => $defaultcard,
							"config" => array(),
							);}
														
					if (!isset($dets["groups"] [$gnm] ["card"]))
						{$dets["groups"] [$gnm] ["card"] = "list";}

					if (!function_exists("build".ucfirst($dets["groups"] [$gnm] ["card"])."Card"))
						{$cfn = "buildSimpleCard";}
					else
						{$cfn = "build".ucfirst($dets["groups"] [$gnm] ["card"])."Card";}
					
					if (!isset($dets["groups"] [$gnm] ["html"]))
						{//echo "########$gnm###############\n";
							$dets["groups"] [$gnm] ["html"] = startGroupHtml (
							$gnm, $dets["groups"] [$gnm] ["comment"],
							$dets["groups"] [$gnm] ["card"], $dets["tableofcontents"]);

							//if ($c) {prg(0, $dets["groups"] [$gnm] );}
			
						}
						
					$dets["groups"] [$gnm] ["html"] .= call_user_func_array($cfn, array($la));					
					}
			}

    // organise children
		foreach ($dets["groups"] as $gnm => $ga)
				{
        $pc = explode ("|", $gnm);

        if (count($pc) > 1)
          {
          if (in_array($dets["groups"] [$gnm] ["card"], array("list")))
            {$dets["groups"] [trim($pc[0])] ["html"] .=
                "</ul><br/>".$dets["groups"] [$gnm] ["html"] ;}
          else
            {$dets["groups"] [trim($pc[0])] ["html"] .=
                "</div><br/>".$dets["groups"] [$gnm] ["html"] ;}

          unset($dets["groups"] [$gnm]);
          }
        }
        
		foreach ($dets["groups"] as $gnm => $ga)
				{
				if (!isset($ga["html"]))
					{/*prg(0, $gnm);
					 prg(0, $dets["groups"][$gnm]);*/}
				 if (in_array($dets["groups"] [$gnm] ["card"], array("list")))
					{$gcontent .= $ga ["html"]."</ul><br/>";}
				else
					{$gcontent .= $ga ["html"]."</div><br/>";}
				}
			
		$pd["extra_css"] .= "
.card-img {
	width: auto;
  max-width: 100%;
  max-height:200px;
  display: block;
  margin-left: auto;
  margin-right: auto;
  padding: 10px;
}

.card-img-top {
	width: auto;
  max-width: 100%;    
  max-height:128px;
  display: block;
  margin-left: auto;
  margin-right: auto;
  padding: 10px;
}

.nodec:link, .nodec:visited, .nodec:hover, .nodec:active {
	text-decoration: none;
	color: inherit;
	}

.card-hov:hover {
	opacity: 0.7;
}

.offsetanchor {
	position: relative;
  top: -75px;
}

";

		// Check if a table of contents should be added.
		if (!isset($dets["tableofcontents"])) {$dets["tableofcontents"] = false;}
		if ($dets["tableofcontents"])
			{$tb = buildContents ($dets["groups"]);}
		else
			{$tb = "";}
			
    $d["content"] = positionExtraContent ($d["content"], $tb.$gcontent);
		}

  return (array("d" => $d, "pd" => $pd));
  }

function buildFullCard ($la)
  {	 
  if ($la["link"])
    {$ltop= "<a href=\"$la[link]\" class=\"stretched-link nodec\">";
      $lbottom = "</a>";
      $hclass =  "card-hov";}
  else
    {$ltop= "";
     $lbottom = "";
     $hclass =  "";}
				
  ob_start();			
  echo <<<END

<div class="card mb-3 $hclass" style="width: 100%;">
  $ltop<div class="row no-gutters">
    <div class="col-md-4  my-auto" >
      <img src="$la[image]" class="card-img" alt="$la[ptitle]">
    </div>
    <div class="col-md-8">
      <div class="card-body">
        <h4 class="card-title">$la[ptitle]</h4>
        <h5 class="card-title">$la[stitle]</h5>
        <p class="card-text">$la[comment]</p>
      </div>
    </div>
  </div>$lbottom
</div>

END;
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client

		return ($html);
		}

 function buildSimpleCard ($la) {			
		if ($la["link"])
				{$ltop= "<a href=\"$la[link]\" class=\"stretched-link nodec\">";
					$lbottom = "</a>"	;
      $hclass =  "card-hov";}
		else
				{$ltop= "";
					$lbottom = "";
      $hclass =  "";}
				
		ob_start();			
		echo <<<END
		
  <div class="col mb-4 $hclass";>
    <div class="card" title="$la[ptitle]">
			$ltop
      <img class="card-img-top" src="$la[image]" alt="$la[ptitle]">
      $lbottom
      <div class="card-body">
        <h5 class="card-title">$la[ptitle]</h5>
        <p class="card-text">$la[comment]</p>
      </div>
    </div>
  </div>

END;
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client

		return ($html);
		}
		
 function buildImageCard ($la) {			
		if ($la["link"])
				{$ltop= "<a href=\"$la[link]\" class=\"stretched-link nodec\">";
					$lbottom = "</a>"	;
      $hclass =  "card-hov";}
		else
				{$ltop= "";
					$lbottom = "";
      $hclass =  "";}
				
		ob_start();			
		echo <<<END
		
  <div class="col mb-4 $hclass">
    <div class="card" title="$la[ptitle]">
			$ltop
      <img class="card-img-top" src="$la[image]" alt="$la[ptitle]">
      $lbottom
    </div>
  </div>

END;
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client

		return ($html);
		}

 function buildListCard ($la) {
   	global $displaychecked ;
    
	 	if ($la["link"])
				{$ltop= "<a href=\"$la[link]\" class=\"\">";
					$lbottom = "</a>"	;}
		else
				{$ltop= "";
					$lbottom = "";}

		if ($la["comment"])
			{$la["comment"] = " - ".$la["comment"];}

    if ($displaychecked )
      {
      if (isset($la["checked"]) and $la["checked"])
        {$checked = "<span style=\"font-size:0.75em\">  ( last checked ".$la["checked"]." )</span>";}
      else
        {$checked = "<span style=\"font-size:0.75em\">  ( no checked date )</span>";}
      }
    else
      {$checked = "";}
				
		ob_start();			
		echo <<<END
<li>$ltop$la[ptitle]$lbottom$la[comment]$checked</li>
END;
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client

		return ($html);
		}

function startGroupHtml ($gnm, $comment, $card, $tbc)
	{
	$html = "";
  $tag = urlencode(strtolower($gnm));
  $hno = 3;
  
  $pc = explode ("|", $gnm);
  
  if (count($pc) > 1)
    {$tbc = false;
     $gnm = $pc[1];
     $hno = 4;}  

	if ($tbc)
		{$anchor = "<a id=\"$tag\" class=\"anchor offsetanchor\" ".
				"aria-hidden=\"true\" href=\"#${tag}-TBC\"></a>";
		 $alink = "<a class=\"anchor nodec\" aria-hidden=\"true\" ".
				"href=\"#${tag}-TBC\">$gnm</a>";}
	else
		{$anchor = "";
		 $alink = "$gnm";}

	$gtop = "<h${hno}>$anchor$alink</h${hno}><p>".$comment."</p>";

	if (in_array($card, array("image")))
		{$html  = "$gtop<div class=\"row row-cols-1 ".
			"row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5\">";}						
	else if (in_array($card, array("list")))
		{$html = "$gtop<ul>";}							
	else if (in_array($card, array("full")))
		{$html = "$gtop<div class=\"card-column\">";}
	else //if (in_array($card, array("simple"))) or anything else
		{$html = "$gtop<div class=\"card-deck\">";}

	return ($html);
	}
    
?>
