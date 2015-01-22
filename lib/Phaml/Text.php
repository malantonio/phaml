<?php
namespace Phaml;
class Text extends Node {

    private $content;

    public function __construct($content = "") {
        $this->setContent($content);
    }

    public function removeContent() {
        return $this->content = "";
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function toString() {
        return $this->content;
    }

    public function __toString() {
        return $this->toString();
    }
}