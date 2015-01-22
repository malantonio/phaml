<?php
namespace Phaml;
class Doctype extends Text {

    const XHTML_XML = "<?xml version='1.0' encoding='utf-8' ?>";
    const XHTML_TRANSITIONAL = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
    const XHTML_1_1 = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">";
    const XHTML_1_1_BASIC = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML Basic 1.1//EN\" \"http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd\">";
    const XHTML_1_2_MOBILE =  "<!DOCTYPE html PUBLIC \"-//WAPFORUM//DTD XHTML Mobile 1.2//EN\" \"http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd\">";
    const XHTML_HTML5 = "<!DOCTYPE html>";
    const HTML5_XML = "";
    const HTML5 = "<!DOCTYPE html>";
    const HTML4_XML = "";
    const HTML4 = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
    const HTML4_FRAMESET = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\" \"http://www.w3.org/TR/html4/frameset.dtd\">";
    const HTML4_STRICT = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">";

    private $prolog_shorthand = array(
        "xhtml" => array(
            "XML" => self::XHTML_XML,
            "default" => self::XHTML_TRANSITIONAL,
            "1.1" => self::XHTML_1_1,
            "mobile" => self::XHTML_1_2_MOBILE,
            "basic" => self::XHTML_1_1_BASIC,
            "5" => self::XHTML_HTML5
        ),
        
        "html5" => array(
            "XML" => self::HTML5_XML,
            "default" => self::HTML5
        ),

        "html4" => array(
            "XML" => self::HTML4_XML,
            "default" => self::HTML4,
            "frameset" => self::HTML4_FRAMESET,
            "strict" => self::HTML4_STRICT
        )
    );

    public function __construct($which = "default", $config = "html5" ) {
        if ( $which === "" ) { $which = "default"; }
        $this->setContent($this->prolog_shorthand[$config][$which]);
    }

    private function noop() { return; }

    public function addChild(Node $child) { return $this->noop(); }
    public function addChildren(array $children) { return $this->noop(); }
    public function addParent(Node $parent) { return $this->noop(); }
}