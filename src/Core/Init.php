<?php

namespace HUCustomizations\Core;

class Init {

    public function __construct()
    {
        $this->init();
    }

    public function init() {
        PageRestriction::get_instance();
    }

    /**
     * @return self|null
     */
    public static function get_instance() {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}