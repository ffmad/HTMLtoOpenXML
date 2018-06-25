<?php

namespace HTMLtoOpenXML;

class Parser
{

    private $_cleaner;
    private $_processProperties;
    private $_listIndex = 1;
    private $_listLevel = 0;

    public function __construct()
    {
        $this->_cleaner = new Scripts\HTMLCleaner;
        $this->_processProperties = new Scripts\ProcessProperties;
    }

    /**
     * Converts HTML to RTF.
     *
     * @param string $htmlCode the HTML formatted input string
     * @param bool   $wrapContent
     *
     * @return string The converted string.
     */
    public function fromHTML($htmlCode, $wrapContent = true)
    {
        $start = 0;

        $openXml = $this->_cleaner->cleanUpHTML($htmlCode);
        if ($wrapContent) {
            $openXml = $this->getOpenXML($openXml);
        }
        $openXml = $this->processBreaks($openXml);
        $openXml = $this->_processListStyle($openXml);
        $openXml = $this->_processProperties->processPropertiesStyle($openXml, $start);
        $openXml = $this->_removeStartSpaces($openXml);
        $openXml = $this->_removeEndSpaces($openXml);

        $openXml = $this->processSpaces($openXml);

        return $openXml;
    }

    /**
     * Remove empty blocks of XML from the end of the final output
     *
     * @param $openXml
     *
     * @return string
     */
    private function _removeEndSpaces($openXml)
    {
        $regex = '/<w:t><\/w:t><\/w:r><\/w:p><w:p><w:r><w:t>$/';
        if (preg_match($regex, $openXml)) {
            $openXml = preg_replace($regex, '<w:t>', $openXml);

            return $this->_removeEndSpaces($openXml);
        }

        return $openXml;
    }

    /**
     * Remove empty blocks of XML from the start of the final output
     *
     * @param $openXml
     *
     * @return string
     */
    private function _removeStartSpaces($openXml)
    {
        $regex = '/^<w:p><w:r><w:t><\/w:t><\/w:r><\/w:p>/';
        if (preg_match($regex, $openXml)) {
            $openXml = preg_replace($regex, '', $openXml);

            return $this->_removeStartSpaces($openXml);
        }

        return $openXml;
    }

    private function getOpenXML($text)
    {
        $text = "<w:p><w:r><w:t>$text</w:t></w:r></w:p>";

        return $text;
    }


    /**
     * First we check if there are multiple levels of lists. Only tested with 2 levels, not sure if this will work with
     * more than 2 lists
     *
     * @param $html
     *
     * @return string
     */
    private function _preProcessNestedLists($html)
    {

        $this->_listLevel = 1;

        $output = preg_replace_callback('/<li>([^<]+)<(?:ul|ol).*?>(.*?)<\/(?:ul|ol).*?><\/li>/im',
                [$this, 'preProcessNestedList'], $html);

        $this->_listLevel = 0;

        return $output;
    }

    public function preProcessNestedList($html)
    {
        $output = '';
        if ($html[1]) {
            $output = sprintf('<li>%s</li>', $html[1]);
        }
        $output .= $this->processList($html);

        return $output;
    }

    private function _processListStyle($input)
    {

        $output = preg_replace("/\n/", ' ', $input);

        $output = $this->_preProcessNestedLists($output);

        $output = preg_replace_callback('/<(ul|ol).*?>(.*?)<\/(?:ul|ol)>/im', [$this, 'processList'], $output);

        return $output;
    }

    public function processList($html)
    {

        $output = '';

        $output .= preg_replace_callback('/<li.*?>(.*?)<\/li>/im', [$this, 'processListItem'], $html[2]);


        if ($this->_listLevel === 0) {
            $output .= '</w:t></w:r></w:p><w:p><w:r><w:t>';

            // Add a blank line after the list, otherwise it's attached
            $output .= '</w:t></w:r></w:p><w:p><w:r><w:t>';
        }

        $this->_listIndex += 1;

        return $output;
    }

    public function processListItem($html)
    {

        $html = sprintf("</w:t></w:r></w:p><w:p><w:pPr><w:pStyle w:val='ListParagraph'/><w:numPr><w:ilvl w:val='%d'/><w:numId w:val='%d'/></w:numPr></w:pPr><w:r><w:t xml:space='preserve'>%s",
                $this->_listLevel, $this->_listIndex, trim($html[1]));

        return $html;
    }

    private function processBreaks($input)
    {
        $output = preg_replace("/(<\/p>)/mi", "</w:t></w:r></w:p><w:p><w:r><w:t>", $input);
        $output = preg_replace("/(<br\s?\/?>)/mi", "</w:t></w:r></w:p><w:p><w:r><w:t>", $output);

        return $output;
    }

    /**
     * @param $html
     *
     * @author © Alex Moore
     *
     * @return null|string|string[]
     */
    public function minifyHtml($html)
    {
        $re = '%# Collapse whitespace everywhere but in blacklisted elements.
        (?>             # Match all whitespans other than single space.
          [^\S ]\s*     # Either one [\t\r\n\f\v] and zero or more ws,
        | \s{2,}        # or two or more consecutive-any-whitespace.
        ) # Note: The remaining regex consumes no text at all...
        (?=             # Ensure we are not in a blacklist tag.
          [^<]*+        # Either zero or more non-"<" {normal*}
          (?:           # Begin {(special normal*)*} construct
            <           # or a < starting a non-blacklist tag.
            (?!/?(?:textarea|pre|script)\b)
            [^<]*+      # more non-"<" {normal*}
          )*+           # Finish "unrolling-the-loop"
          (?:           # Begin alternation group.
            <           # Either a blacklist start tag.
            (?>textarea|pre|script)\b
          | \z          # or end of file.
          )             # End alternation group.
        )  # If we made it here, we are not in a blacklist tag.
        %Six';

        return preg_replace($re, "", $html);
    }

    private function processSpaces($input)
    {
        $output = preg_replace("/(&nbsp;)/mi", " ", $input);
        $output = preg_replace("/(<w:t>)/mi", "<w:t xml:space='preserve'>", $output);

        $output = $this->minifyHtml($output);

        return $output;
    }

}
