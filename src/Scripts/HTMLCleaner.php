<?php

namespace HTMLtoOpenXML\Scripts;

class HTMLCleaner {

    private $_html;
    /**
     * Clean up the HTML before process it.
     *
     * @param $html
     *
     * @return string
     */
    public function cleanUpHTML($html) {
        $this->_html = html_entity_decode($html);

        $this->cleanFirstDivIfAny();
        $this->cleanUpFontTagsIfAny();
        $this->cleanUpSpanTagsIfAny();
        $this->cleanUpParagraphTagsIfAny();
        $this->cleanUpEmTagsIfAny();
        $this->cleanUpHeadTagsIfAny();
        $this->cleanUpEmptyTags();
        $this->cleanUpZeroWidthSpaceCodes();
        $this->cleanBRTagsAtTheEndOfListItemsIfAny();
        $this->_trimBreaksAndEmptyParagraphs(true);
        $this->_trimBreaksAndEmptyParagraphs(false);
        $this->_fixAmpersands();
        $this->_cleanEmptyLists();

        return $this->_html;
    }

    /**
     * The WYSIWYG can pack all his code surrounded by div container. They need to be remove
     * because a word wrap will be inserted.
     */
    private function cleanFirstDivIfAny() {

        if(strpos($this->_html, "<div") === 0) {
            $closeCharPos = strpos($this->_html, ">");
            $this->_html = substr_replace($this->_html, "", 0, $closeCharPos);
            $this->_html = substr_replace($this->_html, "", strlen($this->_html)-strlen("</div>"));
        }

    }

    /**
     * The WYSIWYG can add a <br> tag at the end of list items (<li>). They need to be remove
     * because a word wrap will be inserted and an empty item will be created in the doc file.
     */
    private function cleanBRTagsAtTheEndOfListItemsIfAny() {
        $this->_html = preg_replace("/<br><\/li>/mi", "</li>", $this->_html);
    }

    /**
     * The WYSIWYG can generate <font> tags. They need to clean up them.
     */
    private function cleanUpFontTagsIfAny() {
        $this->_html = preg_replace("/(<font[a-zA-Z0-9_.=,:;#'\"\- \(\)]*>)/mi", "", $this->_html);
        $this->_html = preg_replace("/(<\/font>)/mi", "", $this->_html);
    }

    /**
     * The WYSIWYG can generate <span> tags. They need to clean up them.
     */
    private function cleanUpSpanTagsIfAny() {
        $this->_html = preg_replace("/(<span[a-zA-Z0-9_.=,:;#'\"\- \(\)]*>)/mi", "", $this->_html);
        $this->_html = preg_replace("/(<\/span>)/mi", "", $this->_html);
    }

    /**
     * The WYSIWYG can generate <p> tags. They need to clean up them.
     */
    private function cleanUpParagraphTagsIfAny() {
        $this->_html = preg_replace("/(<p[a-zA-Z0-9_.=,:;#'\"\- \(\)]*>)/mi", "", $this->_html);
        $this->_html = preg_replace("/(<\/p>)/mi", "<br>", $this->_html);
    }

    /**
     * The WYSIWYG can generate <em> tags. They need to clean up them.
     */
    private function cleanUpEmTagsIfAny() {
        $this->_html = preg_replace("/(<em[a-zA-Z0-9_.=,:;#'\"\- \(\)]*>)/mi", "<i>", $this->_html);
        $this->_html = preg_replace("/(<\/em>)/mi", "</i>", $this->_html);
    }

    /**
     * The WYSIWYG can generate <h> tags. They need to clean up them.
     */
    private function cleanUpHeadTagsIfAny() {
        $this->_html = preg_replace("/(<h[a-zA-Z0-9_.=,:;#'\"\- \(\)]*>)/mi", "", $this->_html);
        $this->_html = preg_replace("/(<\/h[a-zA-Z0-9_.=,:;#'\"\- \(\)]>)/mi", "", $this->_html);
    }

    /**
     * The WYSIWYG can generate zero-width spaces(&#8203;). They need to clean up them.
     */
    private function cleanUpZeroWidthSpaceCodes() {
        $this->_html = preg_replace("/&#8203;/mi", "", $this->_html);
    }

    /**
     * Cleans up the HTML empty tag like <p></p> inserted by the WYSIWYG tool.
     */
    private function cleanUpEmptyTags() {
        $this->_html = preg_replace("/(<p[a-zA-Z0-9_.=,:;#'\"\- \(\)]*><\/p>)/mi", "", $this->_html);
        $this->_html = preg_replace("/<div[a-zA-Z0-9_.=,:;#'\"\- \(\)]*><\/div>/mi", "", $this->_html);
        $this->_html = preg_replace("/<span[a-zA-Z0-9_.=,:;#'\"\- \(\)]*><\/span>/mi", "", $this->_html);
        $this->_html = preg_replace("/<u><\/u>/mi", "", $this->_html);
        $this->_html = preg_replace("/<i><\/i>/mi", "", $this->_html);
        $this->_html = preg_replace("/<b[a-zA-Z0-9_.=,:;#'\"\- \(\)]*><\/b>/mi", "", $this->_html);

    }

    /*
     * We need to run this recursively over both ul and li due to potentially
     * nested lists that only become a match after a child is removed
     */
    private function _cleanEmptyLists() {
        $empty_li_regex = '/<li>\s?<\/li>/i';
        $empty_ul_regex = '/<ul>\s?<\/ul>/i';

        if($match_li = preg_match($empty_li_regex, $this->_html)) {
            $this->_html = preg_replace($empty_li_regex, '', $this->_html);
        }
        if($match_ul = preg_match($empty_ul_regex, $this->_html)) {
            $this->_html = preg_replace($empty_ul_regex, '', $this->_html);
        }

        if($match_li || $match_ul) {
            $this->_cleanEmptyLists();
        }

    }

    /**
     * Remove empty <p> and <br> tags
     *
     * @param bool $end Set where the regex should match, beginning or end
     */
    private function _trimBreaksAndEmptyParagraphs($end = true) {
        $empty_p_regex = '<p>\s?<\/p>';
        $empty_br_regex = '<br>';

        if($end) {
            $empty_p_regex = sprintf('/%s$/i', $empty_p_regex);
            $empty_br_regex = sprintf('/%s$/i', $empty_br_regex);
        } else {
            $empty_p_regex = sprintf('/^%s/i', $empty_p_regex);
            $empty_br_regex = sprintf('/^%s/i', $empty_br_regex);
        }

        $this->_html = trim($this->_html);

        $this->_html = preg_replace($empty_p_regex, '', $this->_html);
        $this->_html = preg_replace($empty_br_regex, '', $this->_html);

    }

    /**
     * For XML, ampersands need to be &amp;
     */
    private function _fixAmpersands(){
        $this->_html = preg_replace('/&(?!amp;)/i', '&amp;', $this->_html);
    }
}
