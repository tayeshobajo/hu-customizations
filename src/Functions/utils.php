<?php

function hazmat_fetch_completed_content( $content_id ) {
    global $wpdb;
    $content = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."grassblade_completions WHERE content_id = '%d' ORDER BY id DESC", $content_id), ARRAY_A);
    return $content;
}


function hazmat_get_xapi_content_ids( $activity_id, $post_id ) {
    global $wpdb;
    $content_ids = $wpdb->get_results($wpdb->prepare("SELECT * FROM  $wpdb->postmeta WHERE meta_key = 'xapi_activity_id' AND meta_value='%s'", $activity_id), ARRAY_A);
    return $content_ids;
}