H2OXML : HTML to OpenXML Converter
==================================

Forked from https://github.com/ffmad/HTMLtoOpenXML

Simple PHP script which take HTML code and transform it into OpenXML Code. (for Docx)

This is the very first version. I coded this because i wanted to put text from a WYSIWYG editor into a .docx document.

INSTALLATION
============

`composer require rkorebrits/htmltoopenxml`

WHAT H2OXML CAN DO
==================

For now it can deal with :
  - Paragraphs
  - Bold, italic and underlined text (<strong|b|em|i|u>)
  - Bullet lists

Many more can be done (the wysiwyg editor had only simple functions)


HOW TO USE IT
=============

```
$parser = new HTMLtoOpenXML\Parser();

$ooXml = $parser->fromHTML($html);

```