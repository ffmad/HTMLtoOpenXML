<?php

namespace HTMLtoOpenXML;

class Parser
{

    private $_cleaner;
    private $_processProperties;
    private $_listIndex = 1;
    private $_listLevel = 0;
    private $_openXml = '';

    public function __construct() {
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
    public function fromHTML($html, $wrapContent = true) {
        $this->_openXml = $html;

        $this->_preProcessLtGt();

        $this->_openXml = $this->_cleaner->cleanUpHTML($this->_openXml);
        if ($wrapContent) {
            $this->_getOpenXML();
        }
        $this->_processListStyle();
        $this->_processBreaks();
        $this->_openXml = $this->_processProperties->processPropertiesStyle(
                $this->_openXml, 0
        );
        $this->_removeStartSpaces();
        $this->_removeEndSpaces();

        $this->_processSpaces();

        $this->_postProcessLtGt();

        return $this->_openXml;
    }

    /**
     * Remove empty blocks of XML from the end of the final output
     */
    private function _removeEndSpaces() {
        $regex = '/<w:t><\/w:t><\/w:r><\/w:p><w:p><w:r><w:t>$/';
        if (preg_match($regex, $this->_openXml)) {
            $this->_openXml = preg_replace($regex, '<w:t>', $this->_openXml);

            $this->_removeEndSpaces();
        }

    }

    /**
     * Remove empty blocks of XML from the start of the final output
     */
    private function _removeStartSpaces() {
        $regex = '/^<w:p><w:r><w:t><\/w:t><\/w:r><\/w:p>/';
        if (preg_match($regex, $this->_openXml)) {
            $this->_openXml = preg_replace($regex, '', $this->_openXml);

            $this->_removeStartSpaces();
        }

    }

    private function _getOpenXML() {
        $this->_openXml = "<w:p><w:r><w:t>" . $this->_openXml
                . "</w:t></w:r></w:p>";

    }

    /**
     * First we check if there are multiple levels of lists. Only tested with 2
     * levels, not sure if this will work with more than 2 lists
     */
    private function _preProcessNestedLists() {

        $this->_listLevel = 1;

        $this->_openXml = preg_replace_callback(
                '/<li>(.*?(?!<\/li>).*?)<(ul|ol).*?>(.*?)<\/\2.*?><\/li>/im',
                [$this, 'preProcessNestedList'], $this->_openXml
        );

        $this->_listLevel = 0;

    }

    public function preProcessNestedList($html) {

        $output = '';
        if ($html[1]) {
            $output = sprintf('<li>%s</li>', $html[1]);
        }
        $output .= $this->processList($html[3]);

        return $output;
    }

    private function _processListStyle() {

        $this->_openXml = preg_replace("/\n/", ' ', $this->_openXml);

        $this->_preProcessNestedLists();

        $this->_openXml = preg_replace_callback(
                '/<(ul|ol).*?>(.*?)<\/\1>/im', [$this, 'processList'],
                $this->_openXml
        );

    }

    public function processList($html) {
        $html = is_array($html) ? $html[2] : $html;

        $output = '';

        $output .= preg_replace_callback(
                '/<li.*?>(.*?)<\/li>/im', [$this, 'processListItem'], $html
        );


        if ($this->_listLevel === 0) {
            $output .= '</w:t></w:r></w:p><w:p><w:r><w:t>';

            // Add a blank line after the list, otherwise it's attached
            $output .= '</w:t></w:r></w:p><w:p><w:r><w:t>';
        }

        $this->_listIndex += 1;

        return $output;
    }

    public function processListItem($html) {

        $html = sprintf(
                "</w:t></w:r></w:p><w:p><w:pPr><w:pStyle w:val='ListParagraph'/><w:numPr><w:ilvl w:val='%d'/><w:numId w:val='%d'/></w:numPr></w:pPr><w:r><w:t xml:space='preserve'>%s",
                $this->_listLevel, $this->_listIndex, trim($html[1])
        );

        return $html;
    }

    private function _processBreaks() {
        $this->_openXml = preg_replace(
                "/(<\/p>)/mi", "</w:t></w:r></w:p><w:p><w:r><w:t>", $this->_openXml
        );
        $this->_openXml = preg_replace(
                "/(<br\s?\/?>)/mi", "</w:t></w:r></w:p><w:p><w:r><w:t>",
                $this->_openXml
        );

    }

    /**
     * @param $html
     *
     * @author © Alex Moore
     *
     * @return null|string|string[]
     */
    public function minifyHtml($html) {
        $re
                = '%# Collapse whitespace everywhere but in blacklisted elements.
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

    private function _processSpaces() {
        $this->_openXml = preg_replace("/(&nbsp;)/mi", " ", $this->_openXml);
        $this->_openXml = preg_replace(
                "/(<w:t>)/mi", "<w:t xml:space='preserve'>", $this->_openXml
        );

        $this->_openXml = $this->minifyHtml($this->_openXml);

    }

    /**
     * &lt; and &gt; need to be processed seprately because otherwise they're
     * parsed as < and >, which will break the XML
     */
    private function _preProcessLtGt() {
        $this->_openXml = preg_replace(
                '/\&(lt|gt);/im', '\$\$$1;', $this->_openXml
        );
        // Just in case also check for &amp;lt;
        $this->_openXml = preg_replace(
                '/\&amp;(lt|gt);/im', '\$\$$1;', $this->_openXml
        );
//        prd(($this->_openXml));
    }

    /**
     * Reset the values again
     */
    private function _postProcessLtGt() {
        $this->_openXml = preg_replace(
                '/\$\$(lt|gt);/', '&$1;', $this->_openXml
        );

    }

}