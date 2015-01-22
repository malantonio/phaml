<?php
namespace Phaml;
abstract class Node {
    public $children = array();
    public $parent;

    public function addChild(Node $child) {
        array_push($this->children, $child);
        $child->addParent($this);
    }

    public function addChildren(array $children) {
        foreach($children as $child) {
            $this->addChild($child);
        }
    }

    public function addParent(Node $parent) {
        $this->parent = $parent;
    }

    public function getParent() {
        return $this->parent;
    }

    public function hasChildren() {
        return !empty($this->children);
    }

    public function hasParent() {
        return isset($this->parent);
    }
}