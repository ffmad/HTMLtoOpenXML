<?php

namespace HTMLtoOpenXML;

class Parser
{

	private $_cleaner;
	private $_processProperties;

	/**
	 * Private constructor of singleton.
	 */
	public function __construct() {
		$this->_cleaner = new Scripts\HTMLCleaner;
		$this->_processProperties = new Scripts\ProcessProperties;
	}

	/**
	 * Converts HTML to RTF.
	 *
	 * @param string $htmlCode the HTML formated input string
	 *
	 * @return string The converted string.
	 */
	public function fromHTML($htmlCode) {
		$start = 0;

		$openxml = $this->_cleaner->cleanUpHTML($htmlCode);
		$openxml = $this->getOpenXML($openxml);
		$openxml = $this->processBreaks($openxml);
		$openxml = $this->processListStyle($openxml);
		$openxml = $this->_processProperties->processPropertiesStyle($openxml, $start);
		$openxml = $this->processSpaces($openxml);
		$openxml = $this->processStyle($openxml);

		return $openxml;
	}

	private function getOpenXML($text) {
		$text = "<w:p><w:r><w:t>$text</w:t></w:r></w:p>";

		return $text;
	}

	private function processListStyle($input) {
		$output = preg_replace(
				"/(<ul>)/mi", '</w:t></w:r></w:p><w:p><w:r><w:t>', $input
		);
		$output = preg_replace(
				"/(<\/ul>)/mi", '</w:t></w:r></w:p><w:p><w:r><w:t>', $output
		);
		$output = preg_replace(
				"/(<ol>)/mi", '</w:t></w:r></w:p><w:p><w:r><w:t>', $output
		);
		$output = preg_replace(
				"/(<\/ol>)/mi", '</w:t></w:r></w:p><w:p><w:r><w:t>', $output
		);
		$output = preg_replace(
				"/(<li>)/mi", "</w:t></w:r><w:p startliste><w:r><w:t>", $output
		);
		$output = preg_replace("/(<\/li>)/mi", "", $output);

		return $output;
	}

	private function processBreaks($input) {
		$output = preg_replace(
				"/(<\/p>)/mi", "</w:t></w:r></w:p><w:p><w:r><w:t>", $input
		);
		$output = preg_replace(
				"/(<br\s?\/?>)/mi", "</w:t></w:r></w:p><w:p><w:r><w:t>", $output
		);

		return $output;
	}

	private function processSpaces($input) {
		$output = preg_replace("/(&nbsp;)/mi", " ", $input);
		$output = preg_replace(
				"/(<w:t>)/mi", "<w:t xml:space='preserve'>", $output
		);

		return $output;
	}

	private function processStyle($input) {
		$output = preg_replace(
				"/(<w:p>)/mi", "<w:p><w:pPr><w:pStyle w:val='OurStyle2'/></w:pPr>",
				$input
		);
		$output = preg_replace(
				"/(<w:p startliste>)/mi",
				"</w:p><w:p><w:pPr><w:pStyle w:val='BulletStyle'/><w:numPr><w:ilvl w:val='0'/><w:numId w:val='3'/></w:numPr></w:pPr>",
				$output
		);

		return $output;
	}


}
