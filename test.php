<?php

	// this is a test page for HTMLtoOpenXML 
	
	require_once "HTMLtoOpenXML.php";
	
	$html = "wouhou <b>g&eacute;nial</b> yeah !";
	
	$toOpenXML = HTMLtoOpenXML::getInstance()->fromHTML($html);
	
	echo htmlentities($toOpenXML);


?>