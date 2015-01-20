<?php
class ParserTest extends PHPUnit_Framework_TestCase {

    public $p;

    public function setUp() {
        $this->p = new Phaml\Parser();
    }

    public function testParseSimpleTag() {
        $str_plain = "%p";
        $expected = new Phaml\Tag("p");

        $this->assertEquals($expected, $this->p->parse_line($str_plain));
    }

    public function testParseComplexTag() {
        $strs = array(
            "%p#id.classname", 
            ".noTag",
            "%section.one.column",
            '%img(width => "100px", height="100px")',

        );

        $expected = array(
            new Phaml\Tag("p", array("id" => "id", "class" => "classname")),
            new Phaml\Tag("div", array("class" => "noTag")),
            new Phaml\Tag("section", array("class" => "one column")),
            new Phaml\Tag("img", array("width" => "100px", "height" => "100px")),
        );
        
        $testLength = count($strs);

        for ( $i = 0; $i < $testLength; $i++ ) {
            $this->assertEquals($expected[$i], $this->p->parse_line($strs[$i]));
        }
    }
}