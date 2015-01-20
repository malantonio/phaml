<?php
class TagTest extends PHPUnit_Framework_TestCase {

    public function testEmptyTagToString() {
        $tag = new Phaml\Tag("div");

        $this->assertEquals("<div></div>", $tag->toString());
    }

    public function testVoidElementTags() {
        $attr = array(
            "class" => "image",
            "src" => "/path/to/img.png"
        );
        $tag = new Phaml\Tag("img", $attr);

        $this->assertEquals('<img class="image" src="/path/to/img.png" />', $tag->toString());
    }

    public function testTagWithAttributesToString() {
        $attr = array(
            "class" => "test",
            "id" => "test_one"
        );
        
        $tag = new Phaml\Tag("p", $attr);
    
        $this->assertEquals('<p class="test" id="test_one"></p>', $tag->toString());
    }
}