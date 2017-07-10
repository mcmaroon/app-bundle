<?php
namespace App\AppBundle\Helper\Filters;

class IdFilter {

    private $separator = ',';
    private $rangeSign = '-';
    private $emptyInputReturn = '';
    private $elements = array("ids" => array(), "ranges" => array());

    public function __construct(){
        return $this;
    }
    
    public function parse($input = null) {
        if (is_null($input)) {
            return $emptyInputReturn;
        }
        $this->process($input);
        return $this;
    }

    private function process($input) {

        $elements = explode($this->separator, $input);
        $elementsCount = count($elements);

        for ($i = 0; $i < $elementsCount; $i++) {
            $this->processElement($elements[$i]);
        }

    }

    private function filterElement($element) {
        return trim($element);
    }

    private function processElement($element) {
        $element = $this->filterElement($element);

        if (is_numeric($element)) {
            $this->elements['ids'][] = (int) $element;
        } elseif (strpos($element, $this->rangeSign) !== false) {
            $boundaries = explode($this->rangeSign, $element);
            $boundaries = array_map("intval", $boundaries);
            if ($boundaries[0] === $boundaries[1]) {
                $this->elements['ids'][] = (int) $boundaries[0];
            } else {
                $this->elements['ranges'][] = $boundaries;
            }
        } else {
            return null;
        }
    }

    public function genSQL($field = "id") {
        if (!$this->elements) {
            return null;
        }

        $sqlParts = array();

        if($this->elements['ids']){
            $ids = array_unique($this->elements['ids']);
            sort($ids);
            $sqlParts[] = "$field IN (" . implode(",", $ids) . ")";
        }

        if ($this->elements['ranges']) {

            foreach ($this->elements['ranges'] as $range) {
                sort($range);
                $sqlParts[] = "($field >=  $range[0] AND $field <=  $range[1])";
            }
        }

        if($sqlParts){
            return implode(" OR ", $sqlParts);
        }else{
            return '';
        }
    }

}
