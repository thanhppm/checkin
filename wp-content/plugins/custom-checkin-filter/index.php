<?php
/*
  Plugin Name: Tickera - attendees check-in filter
  Plugin URI: https://tickera.com/
  Description: Adds filter for checkins
  Author: Tickera.com
  Author URI: https://tickera.com/
  Version: 1.0

  Copyright 2017 Tickera (https://tickera.com/)
 */

add_action('restrict_manage_posts', 'tc_custom_add_checkedin_status_filter');
add_action('pre_get_posts', 'tc_custom_pre_get_posts_checkedin_status_filter');

function tc_custom_add_checkedin_status_filter() {
    global $post_type;
    if ($post_type == 'tc_tickets_instances') {
        $currently_selected = isset($_REQUEST['tc_checkedin_status_filter']) ? $_REQUEST['tc_checkedin_status_filter'] : '';
        ?>
        <select name="tc_checkedin_status_filter">
            <option value="0"><?php _e('Bất kỳ', 'tc'); ?></option>
            <?php
            $checkedin_statuses = array(
                'checkedin' => __('Đã check-in', 'tc'),
                'non_checkedin' => __('Chưa check-in', 'tc'),
            );

            unset($checkedin_statuses['any']);

            foreach ($checkedin_statuses as $checkedin_status_key => $$checkedin_status_value) {
                ?>
                <option value="<?php echo esc_attr($checkedin_status_key); ?>" <?php selected($currently_selected, $checkedin_status_key, true); ?>><?php echo esc_attr($$checkedin_status_value); ?></option>
                <?php
            }
            ?>
        </select>
        <?php
    }
}

function tc_custom_pre_get_posts_checkedin_status_filter($query) {
    global $post_type, $pagenow;
    if ($pagenow == 'edit.php' && $post_type == 'tc_tickets_instances') {

        if (isset($_REQUEST['tc_checkedin_status_filter']) && $query->query['post_type'] == 'tc_tickets_instances') {

            $tc_checkedin_status_filter = $_REQUEST['tc_checkedin_status_filter'];

            if ($tc_checkedin_status_filter !== '0') {

                if ($tc_checkedin_status_filter == 'non_checkedin') {

                    $query->set('meta_query', array(
                        'relation' => 'OR',
                        array(
                            'key' => 'tc_checkins',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key' => 'tc_checkins',
                            'value' => 'a:0:{}', //empty
                            'compare' => '=',
                        )
                            )
                    );
                } else {
                    $query->set('meta_query', array(
                        'relation' => 'AND',
                        array(
                            'key' => 'tc_checkins',
                            'compare' => 'EXISTS'
                        ),
                        array(
                            'key' => 'tc_checkins',
                            'value' => 'a:0:{}', //empty
                            'compare' => '!=',
                        )
                            )
                    );
                }
            }
        }
    }
    return $query;
}
?>