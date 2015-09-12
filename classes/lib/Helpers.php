<?php

class Aplazame_Helpers
{
    public static function render_to_template($template_name, $args=array())
    {
        $template_path = WC()->template_path() . '/aplazame/';
        $default_path = plugin_dir_path(__FILE__) . '../../templates/';

        return wc_get_template($template_name, $args, $template_path, $default_path);
    }

    public static function is_redirect()
    {
        return get_the_ID() === static::redirect_ID();
    }

    public static function redirect_ID()
    {
        $posts = get_posts(array(
            'post_type' => 'page',
            'meta_key' => 'aplazame-redirect',
            'meta_value' => 'true',
            'numberposts'=> -1
        ));

        if (empty($posts)) {
            $defaults = array(
                'post_title' => __('Aplazame Redirect'),
                'post_type' => 'page',
                'post_status' => 'publish'
            );

            $post_id = array(wp_insert_post($defaults))[0];
            add_post_meta($post_id, 'aplazame-redirect', 'true');
        } else {
            $post_id = $posts[0]->ID;
        }

        return $post_id;
    }

    public static function payload()
    {
        if (static::is_redirect()) {
            static::render_to_template('gateway/redirect.php');
        }
    }

    public static function get_payment_method($order_id)
    {
        return get_post_meta($order_id, '_payment_method', true);
    }
}
