<?php

class ProcessProperties {
	
	private static $_instance;
	
	public static function getInstance() {
		if (is_null(self::$_instance)){
			self::$_instance = new ProcessProperties();
		}
		return self::$_instance;
	}
	
	/*
	 * @ Main Function to convert style : processPropertiesStyle
	 * 
	 * This function check all of the html text for simple style
	 * If it find bold, italic or other style it will add it to an other 
	 * function which will create a new run section with the new property
	 *
	 */
	
	public function processPropertiesStyle($input, $nbre, $ppties) {
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
	
}
	
?>