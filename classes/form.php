<?php
/**
 * Created by PhpStorm.
 * User: behrmann
 * Date: 12.04.2018
 * Time: 14:44
 */

namespace local_learning_analytics;

class form {

    private $params = [];

    private $required = [];

    private $missing = [];

    private $missing_count = 0;

    private $optional = [];

    public function __construct($params) {

        foreach ($params as $param => $options) {
            if(isset($options['required']) && $options['required']) {
                $this->required[$param] = $options;
                if (isset($_GET[$param])) {
                    $this->params[$param] = filter_input(INPUT_GET, $param, isset($options['filter']) ? $options['filter'] : FILTER_UNSAFE_RAW);
                } else {
                    $this->missing = $param;
                    $this->missing_count++;
                }
            } else {
                $this->optional[] = $param;

                if (isset($_GET[$param])) {
                    $this->params[$param] = filter_input(INPUT_GET, $param, isset($options['filter']) ? $options['filter'] : FILTER_UNSAFE_RAW);
                }
            }
        }
    }

    public function render() {
        return "";
    }


    public function get_required() {
        return $this->required;
    }

    public function get_missing() {
        return $this->missing;
    }

    public function get_missing_count() {
        return $this->missing_count;
    }

    public function get_parameters() {
        return $this->params;
    }
}