<?php

class Aplazame_Helpers
{
    public static function render_to_template($template_name, $args=array())
    {
        $template_path = WC()->template_path() . '/aplazame/';
        $default_path = plugin_dir_path(__FILE__) . '../../templates/';

        return wc_get_template($template_name, $args, $template_path, $default_path);
    }

    public static function payload()
    {
        if (Aplazame_Redirect::is_redirect()) {
            static::render_to_template('gateway/redirect.php');
        }
    }

    public static function get_payment_method($order_id)
    {
        return get_post_meta($order_id, '_payment_method', true);
    }
}
