<?php
namespace Phaml;
use Phaml;

class Parser {

    const TOKEN_EXEC = "-";
    const TOKEN_ECHO = "=";
    const TOKEN_TAG = "%";

    const TOKEN_ID = "#";
    const TOKEN_CLASS = ".";

    const TOKEN_NEWLINE = "\n";
    const TOKEN_INDENT_SPACE = "^[ |\s]+";

    const TAG_REGEX = self::TOKEN_TAG . "([a-z]+)";
    const SELECTOR_REGEX = "([#\.][^#\.\(\)\s]+)+";
    const ATTRIBUTE_REGEX = "\(([^\)]*)\)";

    const REGEX_DELIMITER = "/";

    public $elements = array();
    public $indent = null;

    public function parse($str) {
        // first, replace all \n\r with \n's
        $str = preg_replace("/\n\r/", "\n", $str);

        // all \t's to 4 \s
        $str = preg_replace("/\t/", "    ", $str);

        // split on newlines
        $body = preg_split("/\n/", $str);
        
        /**
         *  offset we're expecting intervals of
         */

        $global_offset = 0;

        /**
         *  in order to keep track of all of the elements,
         *  we'll store their positions w/in an array. a tree
         *  that appears like this:
         *
         *      %ul
         *        %li
         *          %ul
         *            %li
         *        %li
         *
         *  will map out this array:
         *
         *      [
         *        [%ul], 
         *        [%li, %li], 
         *        [%ul], 
         *        [%li]
         *      ]
         *
         *  so that we can more easily find siblings when our position
         *  returns to the sibling level
         */

        $tree = array();
        
        /**
         *  our trail(s) of breadcrumbs
         */

        $current_node = null;
        $previous_node = null;
        $level = 0;
        $previous_level = 0;
        $line_number = 0;

        $indentReg = $this->toRegex(self::TOKEN_INDENT_SPACE);

        while ( $body ) {
            $line = array_shift($body);
            $line_number++;
            $previous_is_sibling = false;

            if ( $line == "" ) { continue; }

            // check for tags
            if ( $this->lineContainsTag($line) ) {
                $current_node = $this->makeTagNode($line);
            }

            // check for text
            $lineText = $this->lineRemoveTag($line);
            if ( $lineText != "" ) {
                if ( !$current_node ) {
                    $current_node = new Phaml\Text($lineText);
                } else {
                    $current_node->addChild(new Phaml\Text($lineText));
                }
            }

            /**
             *  let's see if our line is prefaced with some spaces
             *  which will determine if the element is a parent, sibling, or child
             */

            $offset = $this->getOffsetLength($line);
            if ( $offset === 0 ) { 
                $level = 0; 
            } else {
                if ( $global_offset === 0 ) { $global_offset = $offset; }
                if ( $offBy = $offset % $global_offset ) { 
                    $message = "[Line {$line_number}] "
                             . "Expecting indent size of {$global_offset} "
                             . "and got " . ($offBy + $global_offset);
                    throw new InvalidOffsetException($message);
                }
                
                $level = $offset / $global_offset;
            }

            if ( !isset($tree[$level]) ) { $tree[$level] = array(); }

            // child of previous element
            if ( $level > $previous_level ) {
                $previous_node->addChild($current_node);
                array_push($tree[$level], $current_node);
            } 

            // sibling
            elseif ( $level <= $previous_level && $level !== 0 ) {
                // get the last element of the sibling level array
                $previous_node = end($tree[$level]);

                // get THAT element's parent + add this element
                $previous_node->getParent()->addChild($current_node);
            }

            // tree-sibling
            else {
                array_push($tree[$level], $current_node);
            }

            // then actions (TODO)
            //if ( $this->lineContainsAction($line) ) {}

            $previous_node = $current_node;
            $current_node = null;
            $previous_level = $level;
        }

        return $tree[0];
    }

    /**
     *  determine how much space is between the beginning of the line
     *  and any content
     *
     *  @param  string
     *  @return int
     */

    protected function getOffsetLength($str) {
        $reg = $this->toRegex(self::TOKEN_INDENT_SPACE);
        preg_match($reg, $str, $m);

        return isset($m[0]) ? strlen($m[0]) : 0;
    }

    /**
     *  parses a haml tag into a Tag object + adds
     *  the attributes (incl. class + id)
     *
     *  @param  string
     *  @return Phaml\Tag
     */

    protected function makeTagNode($line) {
        $t = $this->parseTag($line);

        $tag = new Phaml\Tag($t);
        $tag->addAttributes($this->parseSelector($line));
        $tag->addAttributes($this->parseAttribute($line));

        return $tag;
    }

    /**
     *  creates a Text node object
     *
     *  @param  string
     *  @return Phaml\Text
     */

    protected function makeTextNode($line) {
        return new Phaml\Text(trim($line));
    }

    /**
     *  TODO: add support for executing PHP code w/in template
     *  via `=` and `-` markers
     */

    // protected function lineContainsAction($line) {
    //     return false;
    // }

    /**
     *  does this line contain a haml tag?
     *
     *  @param  string
     *  @return bool
     */

    protected function lineContainsTag($line) {
        return preg_match($this->toRegex(self::TAG_REGEX), $line)
            || preg_match($this->toRegex(self::SELECTOR_REGEX), $line)
            || preg_match($this->toRegex(self::ATTRIBUTE_REGEX), $line);
    }

    /**
     *  remove a haml tag + get the remaining bits of the line
     *
     *  @param  string
     *  @return string
     */

    protected function lineRemoveTag($line) {
        return trim(
            preg_replace(
                array(
                    $this->toRegex(self::TAG_REGEX, "i"),
                    $this->toRegex(self::SELECTOR_REGEX, "i"),
                    $this->toRegex(self::ATTRIBUTE_REGEX, "i")
                ), "", $line
            )
        );
    }

    /**
     *  match the attribute syntax and split into an associative array
     *  ex. 
     *      (this => "that", if => "then")
     *  or  
     *      (this="that", if="then")
     *  (or a combination of the two)
     *  will result in:
     *      array(
     *          "this" => "that",
     *          "if" => "then"
     *      )
     *
     *  @param  string  attribute to parse
     *  @return array   associative array
     */

    protected function parseAttribute($str) {
        $reg = $this->toRegex(self::ATTRIBUTE_REGEX);
        preg_match($reg, $str, $m);

        $out = array();
        if ( !isset($m[1]) ) { return $out; }

        $attr = strtolower($m[1]);

        // split the pairs
        $attr = preg_split("/,\s?/", $attr);
        
        foreach($attr as $a) {
            $a = str_replace('"', '', $a);
            $split = preg_split("/\s?\=\>?\s?/", $a);
            if ( $split[0] == "class" ) {
                $out['class'] .= " " . $split[1];
            } else {
                $out[$split[0]] = $split[1];
            }
        }

        return $out;
    }

    /**
     *  matches selector syntax for id (#) and class (.) and
     *  returns the values as an associative array, similar to
     *  that returned by Parser#parseAttribute
     *
     *  @param  string 
     *  @return array
     */

    protected function parseSelector($str) {
        $out = array();
        $reg = $this->toRegex(self::SELECTOR_REGEX);
        preg_match($reg, $str, $m);

        // preg_match stores the last matched if the sequence repeats,
        // so we'll grab the entire matching string and break that up
        if ( isset($m[0]) ) {
            $split = explode(".", $m[0]);
            foreach($split as $attr) {

                // if we get multiple ids, we only want the last
                if ( preg_match($this->toRegex("^" . self::TOKEN_ID), $attr) ) {
                    $ex = explode("#", $attr);
                    $out["id"] = end($ex);
                } else {
                    if ( !isset($out['class']) ) {
                        $out['class'] = $attr;
                    } else {
                        $out['class'] .= " " . $attr;
                    }
                }
            }
        }

        return $out;
    }

    /**
     *  parses out haml tag from string, returns the
     *  default element if none found
     *
     *  @param  string
     *  @return string
     */

    protected function parseTag($str) {
        $reg = $this->toRegex(self::TAG_REGEX, "i");
        preg_match($reg, $str, $m);

        if ( isset($m[1]) ) {
            return $m[1];
        } else {
            return Phaml::DEFAULT_ELEMENT;
        }
    }

    /**
     *  build a regex string
     *
     *  @param  string
     *  @param  string  **optional** flags to include
     *  @return string
     */

    protected function toRegex($str, $flags = "") {
        return self::REGEX_DELIMITER . $str . self::REGEX_DELIMITER . $flags;
    }
  
}