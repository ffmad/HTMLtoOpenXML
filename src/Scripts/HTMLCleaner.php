<?php

namespace HTMLtoOpenXML\Scripts;

class HTMLCleaner {

    /**
     * Clean up the HTML before process it.
     *
     * @param $htmlCode
     *
     * @return string
     */
    public function cleanUpHTML($htmlCode) {

        $cleanHtmlCode = html_entity_decode($htmlCode);
        $cleanHtmlCode = $this->cleanFirstDivIfAny($cleanHtmlCode);
        $cleanHtmlCode = $this->cleanUpFontTagsIfAny($cleanHtmlCode);
        $cleanHtmlCode = $this->cleanUpSpanTagsIfAny($cleanHtmlCode);
        $cleanHtmlCode = $this->cleanUpParagraphTagsIfAny($cleanHtmlCode);
        $cleanHtmlCode = $this->cleanUpEmTagsIfAny($cleanHtmlCode);
        $cleanHtmlCode = $this->cleanUpHeadTagsIfAny($cleanHtmlCode);
        $cleanHtmlCode = $this->cleanUpEmptyTags($cleanHtmlCode);
        $cleanHtmlCode = $this->cleanUpZeroWidthSpaceCodes($cleanHtmlCode);
        $cleanHtmlCode = $this->cleanBRTagsAtTheEndOfListItemsIfAny($cleanHtmlCode);
        $cleanHtmlCode = $this->_removeTrailingBreaksAndEmptyParagraphs($cleanHtmlCode);
        $cleanHtmlCode = $this->_fixAmpersands($cleanHtmlCode);

        return $cleanHtmlCode;
    }

    /**
     * The WYSIWYG can pack all his code surrounded by div container. They need to be remove
     * because a word wrap will be inserted.
     *
     * @param $input
     *
     * @return string
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
     * The WYSIWYG can add a <br> tag at the end of list items (<li>). They need to be remove
     * because a word wrap will be inserted and an empty item will be created in the doc file.
     *
     * @param $input
     *
     * @return string
     */
    private function cleanBRTagsAtTheEndOfListItemsIfAny($input) {
        $output = preg_replace("/<br><\/li>/mi", "</li>", $input);
        return $output;
    }

    /**
     * The WYSIWYG can generate <font> tags. They need to clean up them.
     *
     * @param $input
     *
     * @return string
     */
    private function cleanUpFontTagsIfAny($input) {
        $output = preg_replace("/(<font[a-zA-Z0-9_.=,:;#'\"\- \(\)]*>)/mi", "", $input);
        $output = preg_replace("/(<\/font>)/mi", "", $output);
        return $output;
    }

    /**
     * The WYSIWYG can generate <span> tags. They need to clean up them.
     *
     * @param $input
     *
     * @return string
     */
    private function cleanUpSpanTagsIfAny($input) {
        $output = preg_replace("/(<span[a-zA-Z0-9_.=,:;#'\"\- \(\)]*>)/mi", "", $input);
        $output = preg_replace("/(<\/span>)/mi", "", $output);
        return $output;
    }

    /**
     * The WYSIWYG can generate <p> tags. They need to clean up them.
     *
     * @param $input
     *
     * @return string
     */
    private function cleanUpParagraphTagsIfAny($input) {
        $output = preg_replace("/(<p[a-zA-Z0-9_.=,:;#'\"\- \(\)]*>)/mi", "", $input);
        $output = preg_replace("/(<\/p>)/mi", "<br>", $output);
        return $output;
    }

    /**
     * The WYSIWYG can generate <em> tags. They need to clean up them.
     *
     * @param $input
     *
     * @return string
     */
    private function cleanUpEmTagsIfAny($input) {
        $output = preg_replace("/(<em[a-zA-Z0-9_.=,:;#'\"\- \(\)]*>)/mi", "<i>", $input);
        $output = preg_replace("/(<\/em>)/mi", "</i>", $output);
        return $output;
    }

    /**
     * The WYSIWYG can generate <h> tags. They need to clean up them.
     *
     * @param $input
     *
     * @return string
     */
    private function cleanUpHeadTagsIfAny($input) {
        $output = preg_replace("/(<h[a-zA-Z0-9_.=,:;#'\"\- \(\)]*>)/mi", "", $input);
        $output = preg_replace("/(<\/h[a-zA-Z0-9_.=,:;#'\"\- \(\)]>)/mi", "", $output);
        return $output;
    }

    /**
     * The WYSIWYG can generate zero-width spaces(&#8203;). They need to clean up them.
     *
     * @param $input
     *
     * @return string
     */
    private function cleanUpZeroWidthSpaceCodes($input) {
        $output = preg_replace("/&#8203;/mi", "", $input);
        return $output;
    }

    /**
     * Cleans up the HTML empty tag like <p></p> inserted by the WYSIWYG tool.
     *
     * @param $input
     *
     * @return string
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

    private function _removeTrailingBreaksAndEmptyParagraphs($html) {
        $html = trim($html);
        $empty_p_regex = '/<p>\s?<\/p>$/i';
        $has_empty_p = preg_match($empty_p_regex, $html);

        $empty_br_regex = '/<br>$/i';
        $has_empty_br = preg_match($empty_br_regex, $html);

        if ($has_empty_p) {
            $html = preg_replace($empty_p_regex, '', $html);
            return $this->_removeTrailingBreaksAndEmptyParagraphs($html);
        } elseif ($has_empty_br) {
            $html = preg_replace($empty_br_regex, '', $html);
            return $this->_removeTrailingBreaksAndEmptyParagraphs($html);
        }

        return $html;
    }

    /**
     * For XML, ampersands need to be &amp;
     *
     * @param $html
     *
     * @return null|string|string[]
     */
    private function _fixAmpersands($html){
        return preg_replace('/&(?!amp;)/i', '&amp;', $html);
    }
}
