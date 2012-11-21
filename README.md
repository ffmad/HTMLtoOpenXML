H2OXML : HTML to OpenXML Converter
==================================

Simple PHP script which take HTML code and transform it into OpenXML Code. (for Docx)

This is the very first version. I coded this because i wanted to put text from a WYSIWYG editor into a .docx document.

WHAT H2OXML CAN DO
==================

For now it can deals with :
  - Bold, italic and underlined text
  - Bulleted lists
...
Many more can be done (the wysiwyg editor had only simple functions)


HOW TO USE IT
=============

Load the script .../HTMLtoOpenXML.php';

Then if your html code is in the variable $html, use this function:

HTMLtoOpenXML::getInstance()->fromHTML($article)

You will obtain a string formated in OpenXML.

TO DO :
------------>

Deal with Styles