<?php

namespace HUCustomizations\Core\LMS;

class Init {

    public function __construct()
    {
        if ( is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) ) {
            $this->init();
        }
    }

    public function init() {
        SingleCourse::get_instance();
        Quiz::get_instance();
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