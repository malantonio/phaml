<?php
namespace Phaml;
use Phaml;

class Tag extends Node {
    public $name;
    public $attributes;
    public $is_void = false;

    public function __construct($name = null, $attributes = array(), $children = array()) {
        if ( !$name ) { $name = Phaml::DEFAULT_ELEMENT; }
        
        $this->name = $name;
        $this->attributes = $attributes;
        $this->children = $children;

        if ( in_array($this->name, Phaml::$void_elements) ) {
            $this->is_void = true;
        }
    }

    /**
     *  appends an attribute + value
     *  if attribute is "class", the value is appended to the list
     *
     *  @param string   attribute name
     *  @param string   attribute value
     */


    public function addAttribute($key, $value = "") {
        $clean_key = strtolower($key);

        if ( $clean_key == "class" && isset($this->attributes['class']) ) {
            $this->attributes['class'] .= " " . $value;
        } else {
            $this->attributes[$clean_key] = $value;
        }
    }

    public function addAttributes(array $arr) {
        foreach($arr as $key => $val) {
            $this->addAttribute($key, $val);
        }
    }

    public function getAttribute($attr) {
        if ( isset($this->attributes[$attr]) ) {
            return $this->attributes[$attr];
        }

        return null;
    }

    public function hasAttribute($attr) {
        return isset($this->attributes[$attr]);
    }

    public function toString() {
        $str = "<" . $this->name;
        $str .= $this->attributesToString();

        // end void elements (els w/o closing tags)
        if ( $this->is_void ) { return $str . " />"; }

        $str .= ">";

        if ( $this->hasChildren() ) {}

        $str .= "</" . $this->name . ">";

        return $str;
    }


    /**
     *  private methods
     */

    private function attributesToString() {
        $out = "";
        foreach ( $this->attributes as $key => $value ) {
            $out .= " {$key}=\"{$value}\"";
        }

        return $out;
    }

}