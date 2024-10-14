<?php

namespace HUCustomizations\Core\WooCommerce;

class Init {

    public function __construct()
    {
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            $this->init();
        }
    }

    public function init() {
        QuantityDiscountRequests::get_instance();
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