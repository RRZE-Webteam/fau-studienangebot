<?php

class rrze_Meta_Box_Sanitize {

    public $field;

    public $value;

    public function __construct($field, $value) {
        $this->field = $field;
        $this->value = $value;
        $this->object_id = rrze_Meta_Box::get_object_id();
        $this->object_type = rrze_Meta_Box::get_object_type();
    }

    public function __call($name, $arguments) {
        list( $value ) = $arguments;
        return $this->default_sanitization($value);
    }

    public function default_sanitization($value) {

        $updated = apply_filters('rrze_mb_validate_' . $this->field->type(), null, $value, $this->object_id, $this->field->args(), $this);

        if (null !== $updated)
            return $updated;

        switch ($this->field->type()) {
            case 'wysiwyg':
            case 'textarea_small':
                return $this->textarea($value);
            case 'taxonomy_select':
            case 'taxonomy_radio':
            case 'taxonomy_multicheck':
                if ($this->field->args('taxonomy')) {
                    return wp_set_object_terms($this->object_id, $value, $this->field->args('taxonomy'));
                }
            case 'multicheck':
            default:
                return is_array($value) ? array_map('sanitize_text_field', $value) : call_user_func('sanitize_text_field', $value);
        }
    }

    public function checkbox($value) {
        return $value === 'on' ? 'on' : false;
    }

    public function text_url($value) {
        $protocols = $this->field->args('protocols');
        $value = $value ? esc_url_raw($value, $protocols) : $this->field->args('default');
        return $value;
    }

    public function colorpicker($value) {
        $value = !$value || '#' == $value ? '' : esc_attr($value);       
        return $value;
    }

    public function text_email($value) {
        $value = trim($value);
        $value = is_email($value) ? $value : '';
        return $value;
    }

    public function text_money($value) {

        global $wp_locale;

        $search = array($wp_locale->number_format['thousands_sep'], $wp_locale->number_format['decimal_point']);
        $replace = array('', '.');

        $value = number_format_i18n((float) str_ireplace($search, $replace, $value), 2);

        return $value;
    }

    public function text_date_timestamp($value) {
        return is_array($value) ? array_map('strtotime', $value) : strtotime($value);
    }

    public function text_datetime_timestamp($value) {

        $test = is_array($value) ? array_filter($value) : '';
        if (empty($test))
            return '';

        $value = strtotime($value['date'] . ' ' . $value['time']);

        if ($tz_offset = $this->field->field_timezone_offset())
            $value += $tz_offset;

        return $value;
    }

    public function text_datetime_timestamp_timezone($value) {

        $test = is_array($value) ? array_filter($value) : '';
        if (empty($test))
            return '';

        $tzstring = null;

        if (is_array($value) && array_key_exists('timezone', $value))
            $tzstring = $value['timezone'];

        if (empty($tzstring))
            $tzstring = rrze_Meta_Box::timezone_string();

        $offset = rrze_Meta_Box::timezone_offset($tzstring, true);

        if (substr($tzstring, 0, 3) === 'UTC')
            $tzstring = timezone_name_from_abbr('', $offset, 0);

        $value = new DateTime($value['date'] . ' ' . $value['time'], new DateTimeZone($tzstring));
        $value = serialize($value);

        return $value;
    }

    public function textarea($value) {
        return is_array($value) ? array_map('wp_kses_post', $value) : wp_kses_post($value);
    }

    public function textarea_code($value) {
        return htmlspecialchars_decode(stripslashes($value));
    }

}
