<?php
class ParserTest extends PHPUnit_Framework_TestCase {
    
    public function setUp() {
        $this->p = new Phaml\Parser();
        $this->full_str = '%p#abc.one.two.three(this => "that", left="right") text content';
        $this->tag_no_el = '#abc.one.two.three(this => "that", left="right") text content';
        $this->tag_only_attr = '(this => "that", left="right") text content';
    }


    public function testParserSimple() {
        $expect = new Phaml\Tag("p", array("id" => "abc", "class" => "one two three", "this" => "that", "left" => "right"));
        $expect->addChild(new Phaml\Text("text content"));
        $parsed = $this->p->parse($this->full_str);

        $this->assertEquals(array($expect), $this->p->parse($this->full_str));
    }

    public function testParserMultipleLines() {
        $txt = "
%p#id
  %strong some bold text";
        // first dom el
        $p = new Phaml\Tag("p", array("id" => "id"));
        $s = new Phaml\Tag("strong", array(), array(
            new Phaml\Text("some bold text")
        ));
    
        $p->addChild($s);
        
        $expect = array($p);
        $parsed = $this->p->parse($txt);
        $this->assertEquals($expect, $parsed);
    }

    public function testParserMultipleChildren() {
        $txt = "
#id2
  %ul
    %li list item
    %li another item";
 
        $div = new Phaml\Tag("div", array("id" => "id2"));
        $ul = new Phaml\Tag("ul");
        $li1 = new Phaml\Tag("li");
        $li1->addChild(new Phaml\Text("list item"));
        $ul->addChild($li1);
        $li2 = new Phaml\Tag("li");
        $li2->addChild(new Phaml\Text("another item"));
        $ul->addChild($li2);
        $div->addChild($ul);

        $expect = array($div);
        $parsed = $this->p->parse($txt);
        $this->assertEquals($expect, $parsed);
    }

    public function testStaggeredChildren() {
        $txt = "
%ul
  %li
    %ul
      %li
  %li";

        $ul = new Phaml\Tag("ul", array(), array(
                new Phaml\Tag("li", array(), array(
                    new Phaml\Tag("ul", array(), array(
                        new Phaml\Tag("li")
                    ))
                )),
                new Phaml\Tag("li")
        ));

        $parsed = $this->p->parse($txt);
        $this->assertEquals(array($ul), $parsed);

    }

    /**
     * @expectedException Phaml\InvalidOffsetException
     * @expectedExceptionMessage [Line 4] Expecting indent size of 4 and got 7
     */

    public function testInvalidOffsetException() {
        $txt = "
%p
    %strong
           %broke";



        $this->p->parse($txt);
        $this->fail();
    }

    /**
     *  we shouldn't really be testing protected methods,
     *  instead testing the public ones that invoke these,
     *  but the regex minutia is killing my brain, so 
     *  this'll go through everything.
     *
     *  via: http://stackoverflow.com/a/2798203
     */
    
    protected static function getMethod($name) {
        $class = new ReflectionClass('Phaml\Parser');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testLineGetTagNode() {
        $lgn = self::getMethod("makeTagNode");
        $tag = new Phaml\Tag("p", array("id" => "abc", "class" => "one two three", "this" => "that", "left" => "right"));
    
        $this->assertEquals($tag, $lgn->invokeArgs($this->p, array("%p#abc.one.two.three(this => \"that\", left=\"right\")")));
    }

    public function testGetOffsetLength() {
        $gol = self::getMethod("getOffsetLength");
        $str = "    %p text content";
        $this->assertEquals(4, $gol->invokeArgs($this->p, array($str)));
    }

    public function testLineContainsTag() {
        $lct = self::getMethod("lineContainsTag");
        $this->assertTrue($lct->invokeArgs($this->p, array($this->full_str)));
        $this->assertFalse($lct->invokeArgs($this->p, array("    contains text    ")));
    }

    public function testLineRemoveTag() {
        $lrt = self::getMethod("lineRemoveTag");
        $expected = "text content";
        $this->assertEquals(
            $expected,
            $lrt->invokeArgs($this->p, array($this->full_str)),
            "full block with text"
        );

        $this->assertEquals(
            $expected,
            $lrt->invokeArgs($this->p, array($this->tag_no_el)),
            "block without % element declaration"
        );

        $this->assertEquals(
            $expected,
            $lrt->invokeArgs($this->p, array($this->tag_only_attr)),
            "only block attributes"
        );

        $this->assertEquals(
            $expected,
            $lrt->invokeArgs($this->p, array($expected)),
            "should leave string blocks intact"
        );
    }

    public function testParseTag() {
        $pt = self::getMethod("parseTag");
        $this->assertEquals("p", $pt->invokeArgs($this->p, array($this->full_str)));
        $this->assertEquals("div", $pt->invokeArgs($this->p, array("#id empty div")));
    }

    public function testParseSelector() {
        $ps = self::getMethod("parseSelector");
        $this->assertEquals(
            array("id" => "abc", "class" => "one two three"), 
            $ps->invokeArgs($this->p, array($this->full_str))
        );

        $this->assertEquals(
            array(),
            $ps->invokeArgs($this->p, array("%p"))
        );
    }

    public function testMultipleIDsReturnLast() {
        $ps = self::getMethod("parseSelector");
        $this->assertEquals(
            array("id" => "id2"),
            $ps->invokeArgs($this->p, array("%p#id1#id2"))
        );
    }

    public function testParseAttribute() {
        $pa = self::getMethod("parseAttribute");
        $this->assertEquals(
            array("this" => "that", "left" => "right"),
            $pa->invokeArgs($this->p, array($this->full_str))
        );
    }

    public function testParseDoctype() {
        $pd = self::getMethod("parseDoctype");
        $this->assertTrue($pd->invokeArgs($this->p, array("!!! XML")) instanceof Phaml\Doctype);
        $this->assertTrue($pd->invokeArgs($this->p, array("!!!")) instanceof Phaml\Doctype);
        $this->assertTrue($pd->invokeArgs($this->p, array("!! whatever")) instanceof Phaml\Text);
    }
}