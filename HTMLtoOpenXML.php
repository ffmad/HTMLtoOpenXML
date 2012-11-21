<?php

class HTMLtoOpenXML {
	
	/**
	 * The only one instance of this class.
	 * @var HTMLtoOpenXML
	 */
	private static $_instance;
	 
	/**
	 * Private constructor of singleton.
	 */
	private function __construct() {
	}
	
	/**
	 * Return the singleton instance. Creates one if no one.
	 */
	public static function getInstance() {
		if (is_null(self::$_instance)){
			self::$_instance = new HTMLtoOpenXML();
		}
		return self::$_instance;
	}
	
	/**
	 * Converts HTML to RTF.
	 *
	 * @param input
	 * 		the HTML formated input string
	 * @return The converted string.
	 */
	public function fromHTML($htmlCode) {
		$start = 0;
		$properties = array();
		$openxml = $htmlCode;
		$openxml = $this->cleanUpHTMLBeforeProcess($htmlCode);
		$openxml = $this->getOpenXML($htmlCode);
		$openxml = $this->processBreaks($openxml);
		$openxml = $this->processListStyle($openxml);
		$openxml = $this->processPropertiesStyle($openxml, $start, $properties);
		$openxml = $this->processSpaces($openxml);
		$openxml = $this->processStyle($openxml);
		
		return $openxml;
	}
	
	private function getOpenXML($text) {
		$text = "<w:p><w:r><w:t>$text</w:t></w:r></w:p>";
		return $text;
	}
	
	/*
	 * @ Main Function to convert style : processPropertiesStyle
	 * 
	 * This function check all of the html text for simple style
	 * If it find bold, italic or other style it will add it to an other 
	 * function which will create a new run section with the new property
	 *
	 */
	
	private function processPropertiesStyle($input, $nbre, $ppties) {
		$properties = array();
		$html = $input;
		$i0 = $nbre;
		for ($i=$i0; $i < strlen($html); $i++){
			//echo $html[$i]." ";
			if ($html[$i] == "<")
			{
				switch ($html[$i+1]) {
					case "i":
						list ($properties, $writeproperties) = $this->addProperty("italic", $properties); // change properties and write new text ppt
						$html = substr_replace($html, $writeproperties, $i , 3); // remove <i> and write properties
						$this->processPropertiesStyle($html, $i+strlen($writeproperties), $properties);
						break;
					case "b":
						list ($properties, $writeproperties) = $this->addProperty("bold", $properties);
						$html = substr_replace($html, $writeproperties, $i , 3); // remove <b> and write properties
						$this->processPropertiesStyle($html, $i+strlen($writeproperties), $properties);
						break;
					case "u":
						list ($properties, $writeproperties) = $this->addProperty("underlined", $properties);
						if ($html[$i+2] == ">"){
						$html = substr_replace($html, $writeproperties, $i , 3); // remove <u> and write properties
						$this->processPropertiesStyle($html, $i+strlen($writeproperties), $properties);
						break;
						} else break;
					default:
						break;
				}
			}else if ($html[$i] == "/")
			{
				switch ($html[$i+1]) {
					case "i":
						list ($properties, $writeproperties) = $this->removeProperty("italic", $properties);
						$html = substr_replace($html, $writeproperties, ($i-1) , 4); // remove </i> and write properties
						$this->processPropertiesStyle($html, $i+strlen($writeproperties), $properties);
						break;
					case "b":
						list ($properties, $writeproperties) = $this->removeProperty("bold", $properties);
						$html = substr_replace($html, $writeproperties, ($i-1) , 4); // remove </b> and write properties
						$this->processPropertiesStyle($html, $i+strlen($writeproperties), $properties);
						break;
					case "u":
						list ($properties, $writeproperties) = $this->removeProperty("underlined", $properties);
						$html = substr_replace($html, $writeproperties, ($i-1) , 4); // remove </u> and write properties
						$this->processPropertiesStyle($html, $i+strlen($writeproperties), $properties);
						break;
					default:
						break;
				}
			}
		}
		return $html;
	}
	
	// Add a new property to the properties'list
	private function addProperty($property, $properties) {
		array_push($properties, $property);
		return array( 
			$properties, 
			$this->setProperty($properties),
		);
	}
	
	// Remove a property of the properties'list
	private function removeProperty($property, $properties) {
		foreach ($properties as $key => $value) {
			if ($value == $property){
				unset($properties[$key]);
				$properties = array_values($properties);
			}
		}
		return array(
			$properties, 
			$this->setProperty($properties),
		);
	}
	
	// Set the properties for the next run section of text
	private function setProperty($properties) {
		$propertiesList = "</w:t></w:r><w:r><w:rPr>";
		foreach ($properties as $value) {
			switch ($value) {
				case "italic":
					$propertiesList = $propertiesList."<w:i/>";
					break;
				case "bold":
					$propertiesList = $propertiesList."<w:b/>";
					break;
				case "underlined":
					$propertiesList = $propertiesList."<w:u w:val='single'/>";
					break;
				default:
					break;
			}	
		}
		$propertiesList = $propertiesList."</w:rPr><w:t>";
		return $propertiesList;
	}
	
	private function processListStyle($input) {
		$output = preg_replace("/(<ul>)/mi", '</w:t></w:r></w:p><w:p><w:r><w:t>', $input);
		$output = preg_replace("/(<\/ul>)/mi", '</w:t></w:r></w:p><w:p><w:r><w:t>', $output);
		$output = preg_replace("/(<ol>)/mi", '</w:t></w:r></w:p><w:p><w:r><w:t>', $output);
		$output = preg_replace("/(<\/ol>)/mi", '</w:t></w:r></w:p><w:p><w:r><w:t>', $output);
		$output = preg_replace("/(<li>)/mi", "</w:t></w:r><startliste><w:r><w:t>", $output);
		$output = preg_replace("/(<\/li>)/mi", "", $output);
		return $output;
	}
	
	private function processBreaks($input) {
		$output = preg_replace("/(<br>)/mi", "</w:t></w:r></w:p><w:p><w:r><w:t>", $input);
		return $output;
	}
	
	private function processSpaces($input) {
		$output = preg_replace("/(&nbsp;)/mi", " ", $input);
		$output = preg_replace("/(<w:t>)/mi", "<w:t xml:space='preserve'>", $output);
		return $output;
	}
	
	private function processStyle($input) {
		$output = preg_replace("/(<w:p>)/mi", "<w:p><w:pPr><w:pStyle w:val='OurStyle2'/></w:pPr>", $input);
		$output = preg_replace("/(<startliste>)/mi", "</w:p><w:p><w:pPr><w:pStyle w:val='BulletStyle'/></w:pPr>", $output);
		return $output;
	}
	
	/**
	 *	Clean up the HTML before process it.
	 *
	 *	@param input
	 *		the HTML string
	 *	@return The result string.
	 */
	private function cleanUpHTMLBeforeProcess($htmlCode) {
		$cleanHtmlCode = $this->cleanFirstDivIfAny($htmlCode);
		$cleanHtmlCode = $this->cleanUpFontTagsIfAny($htmlCode);
		$cleanHtmlCode = $this->cleanUpEmptyTags($cleanHtmlCode);
		$cleanHtmlCode = $this->cleanUpZeroWidthSpaceCodes($cleanHtmlCode);
		$cleanHtmlCode = $this->cleanBRTagsAtTheEndOfListItemsIfAny($cleanHtmlCode);

		return $cleanHtmlCode;
	}

	/**
	 *	The WYSIWYG can pack all his code surrounded by div container. They need to be remove
	 *	because a word wrap will be inserted.
	 *
	 *	@param input
	 *		the HTML string
	 *	@return The result string.
	 */
	private function cleanFirstDivIfAny($input) {
		$output = $input;
		if(strpos($output, "<div") === 0) {
			$closeCharPos = strpos($output, ">");
			$output = substr_replace($output, "", 0, $closeCharPos);
			$output = substr_replace($output, "", strlen($output)-strlen("</div>"));
		}
		return $output;
	}

	/**
	 *	The WYSIWYG can add a <br> tag at the end of list items (<li>). They need to be remove
	 *	because a word wrap will be inserted and an empty item will be created in the doc file.
	 *
	 *	@param input
	 *		the HTML string
	 *	@return The result string.
	 */
	private function cleanBRTagsAtTheEndOfListItemsIfAny($input) {
		$output = preg_replace("/<br><\/li>/mi", "</li>", $input);
		return $output;
	}

	/**
	 *	The WYSIWYG can generate <font> tags. They need to clean up them.
	 *
	 *	@param input
	 *		the HTML string
	 *	@return The result string.
	 */
	private function cleanUpFontTagsIfAny($input) {
		$output = preg_replace("/(<font[a-zA-Z0-9_.=,:;#'\"\- \(\)]*>)/mi", "", $input);
		$output = preg_replace("/(<\/font>)/mi", "", $output);
		return $output;
	}

	/**
	 *	The WYSIWYG can generate zero-width spaces(&#8203;). They need to clean up them.
	 *
	 *	@param input
	 *		the HTML string
	 *	@return The result string.
	 */
	private function cleanUpZeroWidthSpaceCodes($input) {
		$output = preg_replace("/&#8203;/mi", "", $input);
		return $output;
	}
	
	/**
	 *	Cleans up the HTML empty tag like <p></p> inserted by the WYSIWYG tool.
	 *
	 *	@param input
	 *		the HTML string
	 *	@return The clean string.
	 */
	private function cleanUpEmptyTags($input) {
		$output = preg_replace("/(<p[a-zA-Z0-9_.=,:;#'\"\- \(\)]*><\/p>)/mi", "", $input);
		$output = preg_replace("/<div[a-zA-Z0-9_.=,:;#'\"\- \(\)]*><\/div>/mi", "", $output);
		$output = preg_replace("/<span[a-zA-Z0-9_.=,:;#'\"\- \(\)]*><\/span>/mi", "", $output);
		$output = preg_replace("/<u><\/u>/mi", "", $output);
		$output = preg_replace("/<i><\/i>/mi", "", $output);
		$output = preg_replace("/<b[a-zA-Z0-9_.=,:;#'\"\- \(\)]*><\/b>/mi", "", $output);

		return $output;
	}
}
	
?>