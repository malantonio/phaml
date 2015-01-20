<?php
namespace Phaml;
use Phaml;

class Parser {

    const NEW_LINE_REGEX = "/[\n]/g";
    const TAG_SELECTOR_REGEX = "/"
                             // get tag name, if any
                             . "(%[a-z]+)?"
                             
                             // get selectors, if any
                             . "([#\.][^#\.\(\)]+)?"
                             
                             // get attributes, if any
                             . "(\([^\)]*\))?"
                             
                             // close
                             . "/i";
                          

    public $elements = array();
    public $indent = "";

    public function parse_line($str) {
        if ( preg_match_all(self::TAG_SELECTOR_REGEX, $str, $m) ) {
            $tag_match = $m[1];
            $sel_match = $m[2];
            $attr_match = $m[3];

            $attributes = array();

            foreach($tag_match as $t) {
                // we'll only allow the first tag we encounter
                if ( !empty($t) ) { 
                    $tag_name = str_replace("%", "", $t);
                    break;
                }
            }

            $tag = new Phaml\Tag(isset($tag_name) ? $tag_name : Phaml::DEFAULT_ELEMENT);

            /**
             *  selectors for id (#) and class (.)
             */

            foreach($sel_match as $s) {
                if ( !empty($s) ) {
                    if ( preg_match("/^#/", $s) ) {
                        $tag->addAttribute("id", str_replace("#", "", $s));
                    } elseif ( preg_match("/^\./", $s) ) {
                        $tag->addAttribute("class", str_replace(".", "", $s));
                    }
                }
            }

            /**
             * other attributes
             */

            foreach($attr_match as $a) {
                if ( !empty($a) ) {
                    $a = preg_replace("/[\(\)]/", "", $a);
                    $split = preg_split("/,\s?/", $a);
                    foreach($split as $s) {
                        $s = str_replace('"', '', $s);
                        $e = preg_split("/\s?\=\>?\s?/", $s);
                        $tag->addAttribute($e[0], $e[1]);
                    }
                }
            }

            return $tag;
        }
    }
}