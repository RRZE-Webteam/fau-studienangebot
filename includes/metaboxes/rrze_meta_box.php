<?php

define('RRZE_META_BOX_ROOT', dirname(__FILE__));
define('RRZE_META_BOX_FILE_PATH', RRZE_META_BOX_ROOT . '/' . basename(__FILE__));
define('RRZE_META_BOX_URL', plugins_url('/', __FILE__));

spl_autoload_register('rrze_Meta_Box::autoload_helpers');

$meta_boxes = array();
$meta_boxes = apply_filters('rrze_meta_boxes', $meta_boxes);
foreach ($meta_boxes as $meta_box) {
    $my_box = new rrze_Meta_Box($meta_box);
}

class rrze_Meta_Box {

    const RRZE_MB_VERSION = '1.0';

    protected $_meta_box;

    protected static $mb_defaults = array(
        'id' => '',
        'title' => '',
        'type' => '',
        'pages' => array(),
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true,
        'show_on' => array('key' => false, 'value' => false),
        'rrze_mb_styles' => true,
        'fields' => array(),
    );

    protected $form_id = 'post';

    public static $field = array();

    protected static $object_id = 0;

    protected static $object_type = '';

    protected static $is_enqueued = false;

    protected static $nonce_added = false;

    protected static $mb_object_type = 'post';

    protected static $options = array();

    protected static $updated = array();

    function __construct($meta_box) {

        $meta_box = self::set_mb_defaults($meta_box);

        $allow_frontend = apply_filters('rrze_mb_allow_frontend', true, $meta_box);

        if (!is_admin() && !$allow_frontend)
            return;

        $this->_meta_box = $meta_box;

        self::set_mb_type($meta_box);

        $types = wp_list_pluck($meta_box['fields'], 'type');
        $upload = in_array('file', $types) || in_array('file_list', $types);

        global $pagenow;

        $filters = 'rrze_Meta_Box_Filters';
        foreach (get_class_methods($filters) as $filter) {
            add_filter('rrze_mb_show_on', array($filters, $filter), 10, 2);
        }

        add_action('admin_enqueue_scripts', array($this, 'register_scripts'), 8);

        if (self::get_object_type() == 'post') {
            add_action('admin_menu', array($this, 'add_metaboxes'));
            add_action('add_attachment', array($this, 'save_post'));
            add_action('edit_attachment', array($this, 'save_post'));
            add_action('save_post', array($this, 'save_post'), 10, 2);
            add_action('admin_enqueue_scripts', array($this, 'do_scripts'));

            if ($upload && in_array($pagenow, array('page.php', 'page-new.php', 'post.php', 'post-new.php'))) {
                add_action('admin_head', array($this, 'add_post_enctype'));
            }
        }
        if (self::get_object_type() == 'user') {

            $priority = 10;
            if (isset($meta_box['priority'])) {
                if (is_numeric($meta_box['priority']))
                    $priority = $meta_box['priority'];
                elseif ($meta_box['priority'] == 'high')
                    $priority = 5;
                elseif ($meta_box['priority'] == 'low')
                    $priority = 20;
            }
            add_action('show_user_profile', array($this, 'user_metabox'), $priority);
            add_action('edit_user_profile', array($this, 'user_metabox'), $priority);

            add_action('personal_options_update', array($this, 'save_user'));
            add_action('edit_user_profile_update', array($this, 'save_user'));
            if ($upload && in_array($pagenow, array('profile.php', 'user-edit.php'))) {
                $this->form_id = 'your-profile';
                add_action('admin_head', array($this, 'add_post_enctype'));
            }
        }
    }

    public static function autoload_helpers($class_name) {
        if (class_exists($class_name, false)) {
            return;
        }
        
        $dir = dirname(__FILE__);
        $class_name = strtolower($class_name);
        $file = "$dir/helpers/$class_name.php";
        if (file_exists($file)) {
            include($file);
        }
    }

    public function register_scripts() {
        if (self::$is_enqueued) {
            return;
        }

        global $wp_version;

        if (!is_admin()) {
            wp_register_script('iris', admin_url('js/iris.min.js'), array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'), self::RRZE_MB_VERSION);
        }

        wp_register_script('rrze-mb-datepicker', RRZE_META_BOX_URL . 'js/jquery.datePicker.min.js');
        wp_register_script('rrze-mb-timepicker', RRZE_META_BOX_URL . 'js/jquery.timePicker.min.js');
        wp_register_script('rrze-mb-scripts', RRZE_META_BOX_URL . 'js/rrze_mb.js', array('jquery', 'jquery-ui-core', 'rrze-mb-datepicker', 'rrze-mb-timepicker'), self::RRZE_MB_VERSION);

        wp_enqueue_media();

        wp_localize_script('rrze-mb-scripts', 'rrze_mb_l10', apply_filters('rrze_mb_localized_data', array(
            'script_debug' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG,
        )));

        wp_register_style('rrze-mb-styles', RRZE_META_BOX_URL . 'style.css', array(), self::RRZE_MB_VERSION);

        self::$is_enqueued = true;
    }

    public function do_scripts($hook) {
        if ($hook == 'post.php' || $hook == 'post-new.php' || $hook == 'page-new.php' || $hook == 'page.php') {
            wp_enqueue_script('rrze-mb-scripts');

            if ($this->_meta_box['rrze_mb_styles'])
                wp_enqueue_style('rrze-mb-styles');
        }
    }

    public function add_post_enctype() {
        echo '
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#' . $this->form_id . '").attr("enctype", "multipart/form-data");
			jQuery("#' . $this->form_id . '").attr("encoding", "multipart/form-data");
		});
		</script>';
    }

    public function add_metaboxes() {

        foreach ($this->_meta_box['pages'] as $page) {
            if (apply_filters('rrze_mb_show_on', true, $this->_meta_box))
                add_meta_box($this->_meta_box['id'], $this->_meta_box['title'], array($this, 'post_metabox'), $page, $this->_meta_box['context'], $this->_meta_box['priority']);
        }
    }

    public function post_metabox() {
        if (!$this->_meta_box)
            return;

        self::show_form($this->_meta_box, get_the_ID(), 'post');
    }

    public function user_metabox() {
        if (!$this->_meta_box)
            return;

        if ('user' != self::set_mb_type($this->_meta_box))
            return;

        if (!apply_filters('rrze_mb_show_on', true, $this->_meta_box))
            return;

        wp_enqueue_script('rrze-mb-scripts');

        if ($this->_meta_box['rrze_mb_styles'] != false) {
            wp_enqueue_style('rrze-mb-styles');
        }

        self::show_form($this->_meta_box);
    }

    public static function show_form($meta_box, $object_id = 0, $object_type = '') {
        $meta_box = self::set_mb_defaults($meta_box);

        $object_type = self::set_object_type($object_type ? $object_type : self::set_mb_type($meta_box) );

        $object_id = self::set_object_id($object_id ? $object_id : self::get_object_id() );

        if (!self::$nonce_added) {
            wp_nonce_field(self::nonce(), 'wp_meta_box_nonce', false, true);
            self::$nonce_added = true;
        }

        echo "\n<!-- Begin RRZE_MB Fields -->\n";
        do_action('rrze_mb_before_table', $meta_box, $object_id, $object_type);
        echo '<table class="form-table rrze_mb_metabox">';

        foreach ($meta_box['fields'] as $field_args) {

            $field_args['context'] = $meta_box['context'];

            $field_args['show_names'] = $meta_box['show_names'];

            $field = new rrze_Meta_Box_field($field_args);
            $field->render_field();

        }
        echo '</table>';
        do_action('rrze_mb_after_table', $meta_box, $object_id, $object_type);
        echo "\n<!-- End RRZE_MB Fields -->\n";
    }

    public function save_post($post_id, $post = false) {

        $post_type = $post ? $post->post_type : get_post_type($post_id);

        if (
            !isset($_POST['wp_meta_box_nonce']) || !wp_verify_nonce($_POST['wp_meta_box_nonce'], self::nonce())
            || defined('DOING_AUTOSAVE') && DOING_AUTOSAVE
            || ( 'page' == $_POST['post_type'] && !current_user_can('edit_page', $post_id) ) || !current_user_can('edit_post', $post_id)
            || !in_array($post_type, $this->_meta_box['pages'])
        ) {
            return $post_id;
        }
        
        self::save_fields($this->_meta_box, $post_id, 'post');
    }

    public function save_user($user_id) {
        if (!isset($_POST['wp_meta_box_nonce']) || !wp_verify_nonce($_POST['wp_meta_box_nonce'], self::nonce())) {
            return $user_id;
        }

        self::save_fields($this->_meta_box, $user_id, 'user');
    }

    public static function save_fields($meta_box, $object_id, $object_type = '') {
        $meta_box = self::set_mb_defaults($meta_box);

        $meta_box['show_on'] = empty($meta_box['show_on']) ? array('key' => false, 'value' => false) : $meta_box['show_on'];

        self::set_object_id($object_id);

        $object_type = self::set_object_type($object_type ? $object_type : self::set_mb_type($meta_box) );

        if (!apply_filters('rrze_mb_show_on', true, $meta_box)) {
            return;
        }

        self::$updated = array();

        foreach ($meta_box['fields'] as $field_args) {
            $field = new rrze_Meta_Box_field($field_args);
            self::save_field(self::sanitize_field($field), $field);
        }

        if ($object_type == 'options-page') {
            self::save_option($object_id);
        }

        do_action("rrze_mb_save_{$object_type}_fields", $object_id, $meta_box['id'], self::$updated, $meta_box);
    }

    public static function sanitize_field($field, $new_value = null) {
        $new_value = null !== $new_value ? $new_value : ( isset($_POST[$field->id(true)]) ? $_POST[$field->id(true)] : null );
        
        return $field->sanitization_cb($new_value);
    }

    public static function save_field($new_value, $field) {
        $name = $field->id();
        $old = $field->get_data();
        if (!empty($new_value) && $new_value != $old) {
            self::$updated[] = $name;
            return $field->update_data($new_value);
        } elseif (empty($new_value)) {
            if (!empty($old)) {
                self::$updated[] = $name;
            }
            return $field->remove_data();
        }
    }

    public static function get_object_id($object_id = 0) {

        if ($object_id) {
            return $object_id;
        }

        if (self::$object_id) {
            return self::$object_id;
        }

        switch (self::get_object_type()) {
            case 'user':
                $object_id = isset($GLOBALS['user_ID']) ? $GLOBALS['user_ID'] : $object_id;
                $object_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $object_id;
                break;

            default:
                $object_id = isset($GLOBALS['post']->ID) ? $GLOBALS['post']->ID : $object_id;
                $object_id = isset($_REQUEST['post']) ? $_REQUEST['post'] : $object_id;
                break;
        }

        self::set_object_id($object_id ? $object_id : 0 );

        return self::$object_id;
    }

    public static function set_object_id($object_id) {
        return self::$object_id = $object_id;
    }

    public static function set_mb_type($meta_box) {

        if (is_string($meta_box)) {
            self::$mb_object_type = $meta_box;
            return self::get_mb_type();
        }

        if (!isset($meta_box['pages'])) {
            return self::get_mb_type();
        }

        $type = false;

        if (self::is_options_page_mb($meta_box)) {
            $type = 'options-page';
        }
        elseif (is_string($meta_box['pages'])) {
            $type = $meta_box['pages'];
        }
        elseif (is_array($meta_box['pages']) && (count($meta_box['pages']) === 1)) {
            $type = is_string(end($meta_box['pages'])) ? end($meta_box['pages']) : false;
        }
        
        if (!$type) {
            return self::get_mb_type();
        }

        if ('user' == $type) {
            self::$mb_object_type = 'user';
        }
        elseif ('comment' == $type) {
            self::$mb_object_type = 'comment';
        }
        elseif ('options-page' == $type) {
            self::$mb_object_type = 'options-page';
        }
        else {
            self::$mb_object_type = 'post';
        }

        return self::get_mb_type();
    }

    public static function is_options_page_mb($meta_box) {
        return ( isset($meta_box['show_on']['key']) && 'options-page' === $meta_box['show_on']['key'] );
    }

    public static function get_object_type() {
        if (self::$object_type)
            return self::$object_type;

        global $pagenow;

        if ($pagenow == 'user-edit.php' || $pagenow == 'profile.php') {
            self::set_object_type('user');
        }
        elseif ($pagenow == 'edit-comments.php' || $pagenow == 'comment.php') {
            self::set_object_type('comment');
        }
        else {
            self::set_object_type('post');
        }

        return self::$object_type;
    }

    public static function set_object_type($object_type) {
        return self::$object_type = $object_type;
    }

    public static function get_mb_type() {
        return self::$mb_object_type;
    }

    public static function nonce() {
        return basename(__FILE__);
    }

    public static function set_mb_defaults($meta_box) {
        return wp_parse_args($meta_box, self::$mb_defaults);
    }

    public static function remove_option($option_key, $field_id) {

        self::$options[$option_key] = !isset(self::$options[$option_key]) || empty(self::$options[$option_key]) ? self::_get_option($option_key) : self::$options[$option_key];

        if (isset(self::$options[$option_key][$field_id])) {
            unset(self::$options[$option_key][$field_id]);
        }

        return self::$options[$option_key];
    }

    public static function get_option($option_key, $field_id = '') {

        self::$options[$option_key] = !isset(self::$options[$option_key]) || empty(self::$options[$option_key]) ? self::_get_option($option_key) : self::$options[$option_key];

        if ($field_id) {
            return isset(self::$options[$option_key][$field_id]) ? self::$options[$option_key][$field_id] : false;
        }

        return self::$options[$option_key];
    }

    public static function update_option($option_key, $field_id, $value, $single = true) {

        if (!$single) {
            self::$options[$option_key][$field_id][] = $value;
        } else {
            self::$options[$option_key][$field_id] = $value;
        }

        return self::$options[$option_key];
    }

    public static function _get_option($option_key, $default = false) {

        $test_get = apply_filters("rrze_mb_override_option_get_$option_key", 'rrze_mb_no_override_option_get', $default);

        if ($test_get !== 'rrze_mb_no_override_option_get') {
            return $test_get;
        }

        return get_option($option_key, $default);
    }

    public static function save_option($option_key) {

        $to_save = self::get_option($option_key);

        $test_save = apply_filters("rrze_mb_override_option_save_$option_key", 'rrze_mb_no_override_option_save', $to_save);

        if ($test_save !== 'rrze_mb_no_override_option_save') {
            return $test_save;
        }

        return update_option($option_key, $to_save);
    }

    public static function timezone_string() {
        $current_offset = get_option('gmt_offset');
        $tzstring = get_option('timezone_string');

        if (empty($tzstring)) {
            if (0 == $current_offset) {
                $tzstring = 'UTC+0';
            }
            elseif ($current_offset < 0) {
                $tzstring = 'UTC' . $current_offset;
            }
            else {
                $tzstring = 'UTC+' . $current_offset;
            }
        }

        return $tzstring;
    }

    public static function timezone_offset($tzstring) {
        if (!empty($tzstring) && is_string($tzstring)) {
            if (substr($tzstring, 0, 3) === 'UTC') {
                $tzstring = str_replace(array(':15', ':30', ':45'), array('.25', '.5', '.75'), $tzstring);
                return intval(floatval(substr($tzstring, 3)) * HOUR_IN_SECONDS);
            }

            $date_time_zone_selected = new DateTimeZone($tzstring);
            $tz_offset = timezone_offset_get($date_time_zone_selected, date_create());

            return $tz_offset;
        }

        return 0;
    }

}

function rrze_mb_get_option($option_key, $field_id = '') {
    return rrze_Meta_Box::get_option($option_key, $field_id);
}

function rrze_mb_get_field($field_args, $object_id = 0, $object_type = 'post') {
    $object_id = $object_id ? $object_id : get_the_ID();
    rrze_Meta_Box::set_object_id($object_id);
    rrze_Meta_Box::set_object_type($object_type);

    return new rrze_Meta_Box_field($field_args);
}

function rrze_mb_get_field_value($field_args, $object_id = 0, $object_type = 'post') {
    $field = rrze_mb_get_field($field_args, $object_id, $object_type);
    return $field->escaped_value();
}

function rrze_mb_print_metaboxes($meta_boxes, $object_id) {
    foreach ((array) $meta_boxes as $meta_box) {
        rrze_mb_print_metabox($meta_box, $object_id);
    }
}

function rrze_mb_print_metabox($meta_box, $object_id) {
    $rrze_mb = new rrze_Meta_Box($meta_box);
    if ($rrze_mb) {
        rrze_Meta_Box::set_object_id($object_id);

        if (!wp_script_is('rrze-mb-scripts', 'registered')) {
            $rrze_mb->register_scripts();
        }

        wp_enqueue_script('rrze-mb-scripts');

        if ($meta_box['rrze_mb_styles'] != false) {
            wp_enqueue_style('rrze-mb-styles');
        }

        rrze_Meta_Box::show_form($meta_box);
    }
}

function rrze_mb_save_metabox_fields($meta_box, $object_id) {
    rrze_Meta_Box::save_fields($meta_box, $object_id);
}

function rrze_mb_metabox_form($meta_box, $object_id, $echo = true) {

    $meta_box = rrze_Meta_Box::set_mb_defaults($meta_box);

    if (!apply_filters('rrze_mb_show_on', true, $meta_box)) {
        return '';
    }

    rrze_Meta_Box::set_object_type(rrze_Meta_Box::set_mb_type($meta_box));

    if (isset($_POST['submit-mb'], $_POST['object_id'], $_POST['wp_meta_box_nonce']) && wp_verify_nonce($_POST['wp_meta_box_nonce'], rrze_Meta_Box::nonce()) && $_POST['object_id'] == $object_id) {
        rrze_mb_save_metabox_fields($meta_box, $object_id);
    }

    ob_start();
    rrze_mb_print_metabox($meta_box, $object_id);
    $form = ob_get_contents();
    ob_end_clean();

    $form_format = apply_filters('rrze_mb_frontend_form_format', '<form class="rrze-mb-form" method="post" id="%s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%s">%s<input type="submit" name="submit-mb" value="%s" class="button-primary"></form>', $object_id, $meta_box, $form);

    $form = sprintf($form_format, $meta_box['id'], $object_id, $form, __('Sichern'));

    if ($echo) {
        echo $form;
    }

    return $form;
}
