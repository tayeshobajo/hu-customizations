<?php

namespace HUCustomizations\Core;

use WooCommerce;

class Init {

    public function __construct()
    {
        $this->init();
        add_action('wp_enqueue_scripts', array($this, 'enqueue_customization_styles'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_customization_styles'));
    }

    public function init() {
        PageRestrictions::get_instance();
        SpecialPriceGroups::get_instance();
        \HUCustomizations\Core\LMS\Init::get_instance();
        \HUCustomizations\Core\WooCommerce\Init::get_instance();
    }

    public function enqueue_customization_styles() {
        wp_enqueue_style( 'hu-customization', HU_CUSTOMIZATIONS_SYSTEM_ASSETS_URL. '/css/hu-customization.css', array(), time() );
    }

    public function admin_enqueue_customization_styles() {
        wp_enqueue_style( 'hu-customization-admin', HU_CUSTOMIZATIONS_SYSTEM_ASSETS_URL. '/css/hu-customization-admin.css', array(), time() );
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