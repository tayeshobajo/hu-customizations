<?php

namespace HUCustomizations\Core\LMS;

class SingleCourse {

    public function __construct()
    {
        add_action('learndash-topic-quiz-row-before', [ $this, 'display_downloadable_materials' ], 10, 3);
    }

    public function display_downloadable_materials($topic_id, $course_id, $user_id ) {
        $downloadable_materials = get_field('downloadable_materials', $topic_id);
        ob_start();

        if(!empty($downloadable_materials)) {
    ?>
        <div class="ld-table-list-item  ld-learndash-topic-downloadable-materials">
            <p><span class="ld-icon ld-icon-materials"></span> Downloadable Materials</p>
            <ul>
                <?php
                foreach ($downloadable_materials as $downloadable_material) {
                    $material = $downloadable_material['material'];
                    $label = $downloadable_material['label'];
                    echo sprintf('<li><a href="%s" title="%s" download>%s</a></li>', $material['url'], $label, $label);
                }
                ?>
            </ul>
        </div>
        <style>
            .ld-learndash-topic-downloadable-materials {
                padding: 20px 0;
            }

            .ld-learndash-topic-downloadable-materials p {
                margin-bottom: 5px;
                font-size: 16px;
            }

            .ld-learndash-topic-downloadable-materials ul {
                width: 100%;
                padding-left: 0;
                list-style: none;
            }

            .ld-learndash-topic-downloadable-materials ul li a {
                font-weight: bold;
                font-size: 14px;
                color: #20409a;
            }

            .ld-learndash-topic-downloadable-materials ul li a:hover {
                color: #fcd31e;
            }

        </style>
    <?php
        }
        echo ob_get_clean();
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