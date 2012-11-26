<?php

	// this is a test page for HTMLtoOpenXML 
	
	require_once "Scripts/HTMLtoOpenXML.php";
	
	$html = "wouhou <b>génial</b> yeah !";
	
	$toOpenXML = HTMLtoOpenXML::getInstance()->fromHTML($html);
	
	echo htmlentities($toOpenXML);


?>