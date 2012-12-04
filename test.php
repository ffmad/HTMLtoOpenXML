<?php

	// this is a test page for HTMLtoOpenXML 
	
	require_once "HTMLtoOpenXML.php";
	
	$html = '<p class="MsoHeader" style="margin-left:14.2pt;text-align:justify;tab-stops:35.4pt"><span style="font-size:10.0pt;font-family:&quot;Times New Roman&quot;,&quot;serif&quot;;mso-bidi-font-weight:
bold;mso-no-proof:yes">La présente proposition est établie sur la demande de Monsieur
Josselin Martinez par le mail du 2 octobre 2012. Documents de référence
applicables&nbsp;:<o:p></o:p></span></p>

<h3 style="margin-left:49.65pt;text-indent:-18.0pt;mso-list:l0 level1 lfo1;
tab-stops:list 49.65pt"><!--[if !supportLists]--><span style="font-weight: normal;"><span style="font-size:10.0pt;
font-family:Symbol;mso-fareast-font-family:Symbol;mso-bidi-font-family:Symbol;
color:windowtext;mso-ansi-language:FR">·<span style="font-size: 7pt; font-family: "Times New Roman";">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</span></span><!--[endif]--></span><span style="font-size:10.0pt;color:windowtext;
mso-ansi-language:FR"><span style="font-weight: normal;">Cahier des charges d’Alstom Transport&nbsp;«&nbsp;CDC_Validation_DCS_Octobre2012
»</span><o:p></o:p></span></h3> ';
	
	echo htmlentities($html);
	echo "<br>";

	$toOpenXML = HTMLtoOpenXML::getInstance()->fromHTML($html);
	
	echo "<br>";
	echo htmlentities($toOpenXML);


?>