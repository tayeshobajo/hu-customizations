<?php

namespace HUCustomizations\Core;

class SpecialPriceGroups {

    public function __construct()
    {
        add_filter( 'bulk_actions-edit-special-pricing-group', [$this, 'custom_generate_pdf_callback']);
        add_filter( 'handle_bulk_actions-edit-special-pricing-group', [$this, 'custom_generate_customers_pdf_callback_handler'], 10, 3 );
        add_filter( 'handle_bulk_actions-edit-special-pricing-group', [$this, 'custom_generate_products_pdf_action_handler'], 10, 3 );
        add_action( 'special-pricing-group_edit_form_fields', [$this, 'add_custom_customers_pdf_generate_callback']);
        add_action('admin_init', [$this, 'generate_custom_pdf']);
    }

    public function custom_generate_pdf_callback($bulk_actions) {
        $bulk_actions['generate_pdf_customers'] = __( 'Generate Customers PDF', 'hazmat-wp' );
        $bulk_actions['generate_pdf_products'] = __( 'Generate Products PDF', 'hazmat-wp' );
        return $bulk_actions;
    }

    public function add_custom_customers_pdf_generate_callback($term)
    {
        $customer_pdfs_url = add_query_arg([
            'action'    =>  'generate-customer-pdfs'
        ]);

        $products_pdfs_url = add_query_arg([
            'action'    =>  'generate-product-pdfs'
        ]);

        ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="related-users"><?php _e( 'Special Pricing Groups PDFs', 'hazmat-wp' ); ?></label></th>
                <td>
                    <ul style="margin: 0;">
                       <li>
                           <a class="button-primary" href="<?php echo $customer_pdfs_url; ?>" title="Generate Customer PDFs">
                               Generate Customers PDFs
                           </a>
                       </li>
                        <li>
                            <a class="button-primary" href="<?php echo $products_pdfs_url; ?>" title="Generate Product PDFs">
                                Generate Products PDFs
                            </a>
                        </li>
                    </ul>
                </td>
            </tr>
        <?php
    }

    public function generate_custom_pdf()
    {
        if(isset($_GET['action']) && $_GET['action'] == 'generate-customer-pdfs' && isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'special-pricing-group') {
            if(!isset($_GET['tag_ID'])) return;

            $term_id = $_GET['tag_ID'];

            require_once(HU_CUSTOMIZATIONS_SYSTEM_LIB_DIRECTORY. '/tcpdf/tcpdf.php');

            $pdf = new \TCPDF();
            $pdf->AddPage();

            // Set font
            $pdf->SetFont('helvetica', '', 14);
            $pdf->Cell(0, 10, 'Special Pricing Groups (Customers)', 0, 1, 'L');

            $terms = get_term( $term_id );
            $term_name = $terms->name;

            // Add a section header for each course
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, $term_name, 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 10);

            $pdf->Cell(20, 10, 'No.', 1);
            $pdf->Cell(60, 10, 'Customer Name', 1);
            $pdf->Cell(0, 10, 'Email', 1);
            $pdf->Ln();

            $user_ids = get_objects_in_term( $term_id, 'special-pricing-group' );
            var_dump($user_ids);

            if ( ! empty( $user_ids ) ) {
                $args = array(
                    'include' => $user_ids,
                );

                $user_query = new \WP_User_Query($args);

                $user_results = $user_query->get_results();
                if(!empty($user_results)) {
                    $count = 0;
                    foreach ($user_results as $user_result) {
                        $display_name = $user_result->first_name . ' ' . $user_result->last_name;
                        $email = $user_result->user_email;

                        $pdf->Cell(20, 10, ++$count, 1);
                        $pdf->Cell(60, 10, esc_html($display_name), 1);
                        $pdf->Cell(0, 10, esc_html($email), 1);
                        $pdf->Ln();
                    }

                    $pdf->Ln(2);
                }

            }

            // Set font back for table content
            $pdf->SetFont('helvetica', '', 10);


            // Add a larger gap after each group of coupons
            $pdf->Ln(10);

            ob_clean();

            // Get the PDF content
            $pdf->Output('pdf-generate-customers.pdf', 'D');

            echo ob_get_clean();
        }

        if(isset($_GET['action']) && $_GET['action'] == 'generate-product-pdfs' && isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'special-pricing-group') {
            if (!isset($_GET['tag_ID'])) return;

            $term_id = $_GET['tag_ID'];

            require_once(HU_CUSTOMIZATIONS_SYSTEM_LIB_DIRECTORY . '/tcpdf/tcpdf.php');

            $pdf = new \TCPDF();
            $pdf->AddPage();

            // Set font
            $pdf->SetFont('helvetica', '', 14);
            $pdf->Cell(0, 10, 'Special Pricing Groups (Products)', 0, 1, 'L');

            $terms = get_term( $term_id );
            $term_name = $terms->name;


            // Add a section header for each course
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, $term_name, 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 10);

            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
            );

            $products = new \WP_Query($args);

            $pdf->SetFont('helvetica', '', 10);

            $pdf->Cell(20, 10, 'No.', 1);
            $pdf->Cell(100, 10, 'Product Name', 1);
            $pdf->Cell(20, 10, 'Price', 1);
            $pdf->Ln();

            if($products->have_posts()) {
                $count = 0;
                foreach ($products->get_posts() as $product) {
                    $product_id = $product->ID;
                    $product_name = $product->post_title;

                    $special_pricing = get_field('special_pricing', $product_id);

                    if(!empty($special_pricing)) {
                        foreach ($special_pricing as $special_price) {
                            $pricing_group_name = $special_price['pricing_group_name'];
                            $special_price = $special_price['special_price'];

                            if($pricing_group_name == $term_id) {
                                $pdf->Cell(20, 10, ++$count, 1);
                                $pdf->Cell(100, 10, $product_name, 1, 0, '', false, '', 1);
                                $pdf->Cell(20, 10, sprintf('$%s', $special_price), 1);
                                $pdf->Ln();
                            }
                        }
                    }

                }
            }

            // Set font back for table content
            $pdf->SetFont('helvetica', '', 10);

            // Add a larger gap after each group of coupons
            $pdf->Ln(10);

            ob_clean();

            // Get the PDF content
            $pdf->Output('pdf-generate-products.pdf', 'D');

            echo ob_get_clean();

        }
    }

    public function custom_generate_customers_pdf_callback_handler( $redirect_to, $doaction, $term_ids )
    {
        if ( $doaction !== 'generate_pdf_customers' ) {
            return $redirect_to;
        }

        require_once(HU_CUSTOMIZATIONS_SYSTEM_LIB_DIRECTORY. '/tcpdf/tcpdf.php');

        $pdf = new \TCPDF();
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 14);
        $pdf->Cell(0, 10, 'Special Pricing Groups (Customers)', 0, 1, 'L');

        foreach ( $term_ids as $term_id ) {
            $terms = get_term( $term_id );
            $term_name = $terms->name;


            // Add a section header for each course
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, $term_name, 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 10);

            $pdf->Cell(20, 10, 'No.', 1);
            $pdf->Cell(60, 10, 'Customer Name', 1);
            $pdf->Cell(0, 10, 'Email', 1);
            $pdf->Ln();

            $user_ids = get_objects_in_term( $term_id, 'special-pricing-group' );

            if ( ! empty( $user_ids ) ) {
                $args = array(
                    'include' => $user_ids,
                );

                $user_query = new \WP_User_Query($args);

                $user_results = $user_query->get_results();
                if(!empty($user_results)) {
                    $count = 0;
                    foreach ($user_results as $user_result) {
                        $display_name = $user_result->first_name . ' ' . $user_result->last_name;
                        $email = $user_result->user_email;

                        $pdf->Cell(20, 10, ++$count, 1);
                        $pdf->Cell(60, 10, esc_html($display_name), 1);
                        $pdf->Cell(0, 10, esc_html($email), 1);
                        $pdf->Ln();
                    }

                    $pdf->Ln(2);
                }

            }

            // Set font back for table content
            $pdf->SetFont('helvetica', '', 10);


            // Add a larger gap after each group of coupons
            $pdf->Ln(10);
        }

        ob_clean();

        // Get the PDF content
        $pdf->Output('pdf-generate-customers.pdf', 'D');

        echo ob_get_clean();

        exit();
    }

    public function custom_generate_products_pdf_action_handler( $redirect_to, $doaction, $term_ids )
    {
        if ( $doaction !== 'generate_pdf_products' ) {
            return $redirect_to;
        }

        require_once(HU_CUSTOMIZATIONS_SYSTEM_LIB_DIRECTORY. '/tcpdf/tcpdf.php');

        $pdf = new \TCPDF();
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 14);
        $pdf->Cell(0, 10, 'Special Pricing Groups (Products)', 0, 1, 'L');

        foreach ( $term_ids as $term_id ) {
            $terms = get_term( $term_id );
            $term_name = $terms->name;
            $term_slug = $terms->slug;


            // Add a section header for each course
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, $term_name, 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 10);

            $args = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
            );

            $products = new \WP_Query($args);

            $pdf->SetFont('helvetica', '', 10);

            $pdf->Cell(20, 10, 'No.', 1);
            $pdf->Cell(100, 10, 'Product Name', 1);
            $pdf->Cell(20, 10, 'Price', 1);
            $pdf->Ln();

            if($products->have_posts()) {
                $count = 0;
                foreach ($products->get_posts() as $product) {
                    $product_id = $product->ID;
                    $product_name = $product->post_title;

                    $special_pricing = get_field('special_pricing', $product_id);

                    if(!empty($special_pricing)) {
                        foreach ($special_pricing as $special_price) {
                            $pricing_group_name = $special_price['pricing_group_name'];
                            $special_price = $special_price['special_price'];

                            if($pricing_group_name == $term_id) {
                                $pdf->Cell(20, 10, ++$count, 1);
                                $pdf->Cell(100, 10, $product_name, 1, 0, '', false, '', 1);
                                $pdf->Cell(20, 10, sprintf('$%s', $special_price), 1);
                                $pdf->Ln();
                            }
                        }
                    }

                }
            }

            // Set font back for table content
            $pdf->SetFont('helvetica', '', 10);

            // Add a larger gap after each group of coupons
            $pdf->Ln(10);
        }

        ob_clean();

        // Get the PDF content
        $pdf->Output('pdf-generate-products.pdf', 'D');

        echo ob_get_clean();
        exit();
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