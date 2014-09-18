<?php

class rrze_Meta_Box_Filters {

    public static function check_id($display, $meta_box) {

        if (!isset($meta_box['show_on']['key']) || 'id' !== $meta_box['show_on']['key'])
            return $display;

        $object_id = is_admin() ? rrze_Meta_Box::get_object_id() : @get_the_id();

        if (!$object_id)
            return false;

        return in_array($object_id, (array) $meta_box['show_on']['value']);
    }

    public static function check_page_template($display, $meta_box) {

        if (!isset($meta_box['show_on']['key']) || 'page-template' !== $meta_box['show_on']['key'])
            return $display;

        $object_id = rrze_Meta_Box::get_object_id();

        if (!$object_id || rrze_Meta_Box::get_object_type() !== 'post')
            return false;

        $current_template = get_post_meta($object_id, '_wp_page_template', true);

        if ($current_template && in_array($current_template, (array) $meta_box['show_on']['value']))
            return true;

        return false;
    }

    public static function check_admin_page($display, $meta_box) {

        if (!isset($meta_box['show_on']['key']) || 'options-page' !== $meta_box['show_on']['key'])
            return $display;

        if (is_admin()) {

            if (!isset($_GET['page']))
                return $display;

            if (!isset($meta_box['show_on']['value']))
                return false;

            $pages = $meta_box['show_on']['value'];

            if (is_array($pages)) {
                foreach ($pages as $page) {
                    if ($_GET['page'] == $page)
                        return true;
                }
            } else {
                if ($_GET['page'] == $pages)
                    return true;
            }

            return false;
        }

        return true;
    }

}
