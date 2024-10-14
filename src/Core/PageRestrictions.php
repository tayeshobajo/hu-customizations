<?php

namespace HUCustomizations\Core;

class PageRestrictions {

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_restrict_user_roles_meta_box') );
        add_action('save_post', array($this, 'save_restricted_user_roles') );
        add_action('template_redirect', array($this, 'restrict_page_by_user_role') );
    }

    public function add_restrict_user_roles_meta_box()
    {
        $post_type =  get_post_types( ['public' => true ], 'names' );
        add_meta_box(
            'restrict_user_roles_meta_box',
            'Restrict by User Role',
            array($this, 'display_restrict_user_roles_meta_box'),
            $post_type,
            'side',
            'high'
        );
    }

    public function display_restrict_user_roles_meta_box($post)
    {
        $roles = wp_roles()->get_names();
        $selected_roles = get_post_meta($post->ID, '_hu_customization_restricted_user_roles', true);

        // Nonce field for security
        wp_nonce_field(basename(__FILE__), 'hu_customization_restrict_user_roles_nonce');

        echo '<label>Select User Roles that can view this post/page:</label><br><br>';
        foreach ($roles as $role_value => $role_name) {
            $checked = (is_array($selected_roles) && in_array($role_value, $selected_roles)) ? 'checked' : '';
            echo '<input type="checkbox" name="restricted_user_roles[]" value="' . esc_attr($role_value) . '" ' . $checked . '> ' . esc_html($role_name) . '<br>';
        }
    }

    public function save_restricted_user_roles($post_id)
    {
        if (!isset($_POST['hu_customization_restrict_user_roles_nonce']) || !wp_verify_nonce($_POST['hu_customization_restrict_user_roles_nonce'], basename(__FILE__))) {
            return $post_id;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        $restricted_roles = isset($_POST['restricted_user_roles']) ? array_map('sanitize_text_field', $_POST['restricted_user_roles']) : [];

        update_post_meta($post_id, '_hu_customization_restricted_user_roles', $restricted_roles);
    }

    public function restrict_page_by_user_role()
    {
        $post_type =  get_post_types( ['public' => true ], 'names' );
        if (is_singular($post_type)) {
            global $post;

            $restricted_roles = get_post_meta($post->ID, '_hu_customization_restricted_user_roles', true);

            if (!empty($restricted_roles)) {
                $current_user = wp_get_current_user();

                // Check if the current user has any of the restricted roles
                $has_access = false;
                foreach ($current_user->roles as $role) {
                    if (in_array($role, $restricted_roles) || $role == 'administrator') {
                        $has_access = true;
                        break;
                    }
                }

                // Redirect to home if the user doesn't have access
                if (!$has_access) {
                    wp_redirect(home_url());
                    exit;
                }
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