<?php

namespace HUCustomizations\Core\LMS;

class Quiz {

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'show_students_results_box') );
        add_action( 'admin_init', array($this, 'export_students_results'));
        add_filter( 'bulk_actions-edit-sfwd-quiz', [$this, 'custom_generate_quiz_callback']);
        add_filter( 'handle_bulk_actions-edit-sfwd-quiz', [$this, 'custom_export_students_pdf_callback_handler'], 10, 3 );
    }

    public function show_students_results_box() {
        add_meta_box(
            'show_students_results_box',
            'Students Results',
            array($this, 'display_students_results_meta_box'),
            array('sfwd-quiz'),
            'normal',
            'high'
        );
    }

    public function custom_generate_quiz_callback()
    {
        $bulk_actions['export-student-results-pdf'] = __( 'Export Students Result (PDF)', 'hazmat-wp' );
        $bulk_actions['export-student-results-excel'] = __( 'Export Students Result (Excel)', 'hazmat-wp' );
        return $bulk_actions;
    }

    public function custom_export_students_pdf_callback_handler( $redirect_to, $doaction, $quiz_ids )
    {
        if ( $doaction !== 'export-student-results-pdf' ) {
            return $redirect_to;
        }

        require_once(HU_CUSTOMIZATIONS_SYSTEM_LIB_DIRECTORY. '/tcpdf/tcpdf.php');

        $pdf = new \TCPDF();
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 14);
        $pdf->Cell(0, 10, 'Student Results', 0, 1, 'L');

        foreach ( $quiz_ids as $quiz_id ) {

            $activity_id = get_post_meta($quiz_id, 'show_xapi_content', true);
            if(empty($activity_id)) continue;

            $completed_quizzes = hazmat_fetch_completed_content($activity_id);

            $post = get_post( $quiz_id );
            $post_title = $post->post_title;

            // Add a section header for each course
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, $post_title, 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 10);

            $generate_table = $this->generate_quizzes_table($completed_quizzes);

            $pdf->writeHTML($generate_table, true, false, false, false, '');

            // Set font back for table content
            $pdf->SetFont('helvetica', '', 10);


            // Add a larger gap after each group of coupons
            $pdf->Ln(10);
        }

        ob_clean();

        // Get the PDF content
        $pdf->Output('generate-student-results.pdf', 'D');

        echo ob_get_clean();
    }


    public function display_students_results_meta_box($post)
    {
        $quiz_id = $post->ID;
        //get the content ids
        $activity_id = get_post_meta($quiz_id, 'show_xapi_content', true);
        if(empty($activity_id)) return;

        $completed_quizzes = hazmat_fetch_completed_content($activity_id);
        ob_start();
        ?>
            <div class="hu-customization-students-results-box">
                <div class="actions-button">
                    <a class="button-primary" href="<?php echo add_query_arg(['hook' => 'generate-student-results', 'export' => 'pdf', 'quiz_id' => $quiz_id]); ?>">
                        Export (PDF)
                    </a>
                    <a class="button-secondary" href="<?php echo add_query_arg(['hook' => 'generate-student-results', 'export' => 'excel', 'quiz_id' => $quiz_id]); ?>">
                        Export (Excel)
                    </a>
                </div>
                <table>
                    <thead>
                    <tr>
                        <th>Students</th>
                        <th>Score (%)</th>
                        <th>Time Spent</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($completed_quizzes)){
                        foreach($completed_quizzes as $quiz) {
                            $user_id = $quiz['user_id'];
                            $user = get_user_by('id', $user_id);
                            $status = $quiz['status'];
                            $score = $quiz['score'];
                            $percentage = $quiz['percentage'];
                            $timespent = grassblade_seconds_to_time($quiz["timespent"]);
                            $timestamp = gb_datetime($quiz["timestamp"]);
                            ?>
                            <tr>
                                <td>
                                    <?php echo sprintf('<a href="%s" title="%s">%s</a>', get_edit_profile_url($user_id), $user->display_name, $user->display_name); ?>
                                </td>
                                <td>
                                    <?php echo $percentage; ?>
                                </td>
                                <td>
                                    <?php echo $timespent; ?>
                                </td>
                                <td>
                                    <?php echo $status; ?>
                                </td>
                                <td>
                                    <?php echo $timestamp; ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>

                    </tbody>
                </table>
            </div>

        <?php
        echo ob_get_clean();
    }

    public function generate_quizzes_table($completed_quizzes)
    {
        ob_start();
        ?>
            <style>
                table, th, td {
                    border: 1px solid black;
                    vertical-align: center;
                }

                tbody, tfoot, thead {
                    background-color: #fefefe
                    border: 1px solid #f1f1f1;
                }

                tfoot, thead {
                    background: #f8f8f8;
                    color: #0a0a0a;
                }

                tfoot td, tfoot th, thead td, thead th {
                    font-weight: 700;
                    padding: 0.5rem 0.625rem .625rem;
                    text-align: left;
                    font-size: 13px;
                }

                tbody td, tbody th, thead td, thead th {
                    padding: 0.5rem 0.625rem 0.625rem;
                }
            </style>
            <table>
            <thead>
            <tr>
                <th style="width: 20%;vertical-align: center;">Student Name</th>
                <th style="width: 30%;vertical-align: center;">Student Email</th>
                <th style="width: 10%;vertical-align: center;">Score (%)</th>
                <th style="width: 10%;vertical-align: center;">Time Spent</th>
                <th style="width: 10%;vertical-align: center;">Status</th>
                <th style="width: 20%;vertical-align: center;">Date</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if(!empty($completed_quizzes)){
                foreach($completed_quizzes as $quiz) {
                    $user_id = $quiz['user_id'];
                    $user = get_user_by('id', $user_id);
                    $status = $quiz['status'];
                    $score = $quiz['score'];
                    $percentage = $quiz['percentage'];
                    $timespent = grassblade_seconds_to_time($quiz["timespent"]);
                    $timestamp = gb_datetime($quiz["timestamp"]);
                    ?>
                    <tr>
                        <td style="width: 20%;vertical-align: center;">
                            <?php echo $user->display_name; ?>
                        </td>
                        <td style="width: 30%;vertical-align: center;">
                            <?php echo $user->user_email; ?>
                        </td>
                        <td style="width: 10%;vertical-align: center;">
                            <?php echo $percentage; ?>
                        </td>
                        <td style="width: 10%;vertical-align: center;">
                            <?php echo $timespent; ?>
                        </td>
                        <td style="width: 10%;vertical-align: center;">
                            <?php echo $status; ?>
                        </td>
                        <td style="width: 20%;vertical-align: center;">
                            <?php echo $timestamp; ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>

            </tbody>
        </table>
        <?php

        return ob_get_clean();
    }

    public function export_students_results()
    {
        if(isset($_GET['hook']) && $_GET['hook'] == 'generate-student-results') {
            if(!isset($_GET['quiz_id'])) return;

            $quiz_id = $_GET['quiz_id'];

            $activity_id = get_post_meta($quiz_id, 'show_xapi_content', true);
            if(empty($activity_id)) return;

            $completed_quizzes = hazmat_fetch_completed_content($activity_id);

            require_once(HU_CUSTOMIZATIONS_SYSTEM_LIB_DIRECTORY. '/tcpdf/tcpdf.php');

            $pdf = new \TCPDF();
            $pdf->AddPage();

            // Set font
            $pdf->SetFont('helvetica', '', 14);
            $pdf->Cell(0, 10, 'Student Results', 0, 1, 'L');

            $post = get_post( $quiz_id );
            $post_title = $post->post_title;

            // Add a section header for each course
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, $post_title, 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 10);

            $generate_table = $this->generate_quizzes_table($completed_quizzes);

            $pdf->writeHTML($generate_table, true, false, false, false, '');

            // Set font back for table content
            $pdf->SetFont('helvetica', '', 10);


            // Add a larger gap after each group of coupons
            $pdf->Ln(10);

            ob_clean();

            // Get the PDF content
            $pdf->Output('generate-student-results.pdf', 'D');

            echo ob_get_clean();
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