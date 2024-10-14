<?php

namespace HUCustomizations\Core\WooCommerce;

class QuantityDiscountRequests {

    public function __construct()
    {
        add_action('woocommerce_after_add_to_cart_button', array($this, 'add_request_button_at_category'), 10, 0);
        add_filter( 'gform_pre_render_5', array($this, 'populate_product_categories') );
        add_filter( 'gform_pre_validation_5', array($this, 'populate_product_categories') );
        add_filter( 'gform_pre_submission_filter_5', array($this, 'populate_product_categories') );
        add_filter( 'gform_admin_pre_render_5', array($this, 'populate_product_categories') );
    }

    public function populate_product_categories( $form )
    {
        foreach ( $form['fields'] as &$field ) {
            if ( $field->type != 'select' || strpos( $field->cssClass, 'populate-courses' ) === false ) {
                continue;
            }

            $terms = get_terms( array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'exclude' => 38
            ) );

            $choices = array();
            if ( !is_wp_error( $terms ) && !empty( $terms ) ) {
                foreach ( $terms as $term ) {
                    $choices[] = array( 'text' => $term->name, 'value' => $term->name );
                }
            }

            // Update 'Select a Post' to whatever you'd like the instructive option to be
            $field->placeholder = 'Select a Product';
            $field->choices = $choices;
        }

        return $form;
    }

    public function add_request_button_at_category() {
        // Check if we're on a product category page
        if (is_product_category() && (current_user_can( 'administrator') || current_user_can( 'customer'))) {
            // Get the current category
            $category = get_queried_object();

            if ($category && isset($category->name)) {
                $category_name = urlencode($category->name);

                // Construct the custom URL with the category name
                $custom_url = get_site_url().'/quantity-discount-requests/?request=' . $category_name;

                // Output the custom button
                echo '<a class="btn-atc" href="' . esc_url($custom_url) . '">' . esc_html__("DISCOUNT REQUEST") . '</a>';
            }
        }
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