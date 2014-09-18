<?php

class rrze_Meta_Box_types {

    public $iterator = 0;

    public $field;

    public function __construct($field) {
        $this->field = $field;
    }

    public function __call($name, $arguments) {
        do_action("rrze_mb_render_$name", $this->field->args(), $this->field->escaped_value(), $this->field->object_id, $this->field->object_type, $this);
    }

    public function render() {
        $this->_render();
    }

    protected function _render() {
        echo $this->{$this->field->type()}();
    }

    public function get_object_terms() {
        $object_id = $this->field->object_id;
        $taxonomy = $this->field->args('taxonomy');

        if (!$post = get_post($object_id)) {

            $cache_key = 'rrze-mb-cache-' . $taxonomy . '-' . $object_id;

            $cached = $test = get_transient($cache_key);
            if ($cached)
                return $cached;

            $cached = wp_get_object_terms($object_id, $taxonomy);

            $set = set_transient($cache_key, $cached, 60);
            return $cached;
        }

        return get_the_terms($post, $taxonomy);
    }

    public function get_file_ext($file) {
        $parsed = @parse_url($file, PHP_URL_PATH);
        return $parsed ? strtolower(pathinfo($parsed, PATHINFO_EXTENSION)) : false;
    }

    public function is_valid_img_ext($file) {
        $file_ext = $this->get_file_ext($file);

        $this->valid = empty($this->valid) ? (array) apply_filters('rrze_mb_valid_img_types', array('jpg', 'jpeg', 'png', 'gif', 'ico', 'icon')) : $this->valid;

        return ( $file_ext && in_array($file_ext, $this->valid) );
    }

    public function parse_args($args, $element, $defaults) {
        return wp_parse_args(apply_filters("rrze_mb_{$element}_attributes", $this->field->maybe_set_attributes($args), $this->field, $this), $defaults);
    }

    public function concat_attrs($attrs, $attr_exclude = array()) {
        $attributes = '';
        foreach ($attrs as $attr => $val) {
            if (!in_array($attr, (array) $attr_exclude, true))
                $attributes .= sprintf(' %s="%s"', $attr, $val);
        }
        return $attributes;
    }

    public function option($opt_label, $opt_value, $selected) {
        return sprintf("\t" . '<option value="%s" %s>%s</option>', $opt_value, selected($selected, true, false), $opt_label) . "\n";
    }

    public function concat_options($args = array(), $method = 'list_input') {

        $options = (array) $this->field->args('options');
        $saved_value = $this->field->escaped_value();
        $value = $saved_value ? $saved_value : $this->field->args('default');

        $_options = '';
        $i = 1;
        foreach ($options as $option_key => $option) {
            $opt_label = is_array($option) && array_key_exists('name', $option) ? $option['name'] : $option;
            $opt_value = is_array($option) && array_key_exists('value', $option) ? $option['value'] : $option_key;

            $is_current = $value == $opt_value;

            if (!empty($args)) {
                $this_args = $args;
                $this_args['value'] = $opt_value;
                $this_args['label'] = $opt_label;
                if ($is_current)
                    $this_args['checked'] = 'checked';

                $_options .= $this->$method($this_args, $i);
            } else {
                $_options .= $this->option($opt_label, $opt_value, $is_current);
            }
            $i++;
        }
        return $_options;
    }

    public function list_input($args = array(), $i) {
        $args = $this->parse_args($args, 'list_input', array(
            'type' => 'radio',
            'class' => 'rrze_mb_option',
            'name' => $this->_name(),
            'id' => $this->_id($i),
            'value' => $this->field->escaped_value(),
            'label' => '',
                ));

        return sprintf("\t" . '<li><input%s/> <label for="%s">%s</label></li>' . "\n", $this->concat_attrs($args, 'label'), $args['id'], $args['label']);
    }

    public function list_input_checkbox($args, $i) {
        unset($args['selected']);
        $saved_value = $this->field->escaped_value();
        if (is_array($saved_value) && in_array($args['value'], $saved_value)) {
            $args['checked'] = 'checked';
        }
        return $this->list_input($args, $i);
    }

    public function _desc($paragraph = false, $echo = false) {
        $tag = $paragraph ? 'p' : 'span';
        $desc = "\n<$tag class=\"rrze_mb_metabox_description\">{$this->field->args('description')}</$tag>\n";
        if ($echo)
            echo $desc;
        return $desc;
    }

    public function _name($suffix = '') {
        return $this->field->args('_name') . $suffix;
    }

    public function _id($suffix = '') {
        return $this->field->id() . $suffix;
    }

    public function input($args = array()) {
        $args = $this->parse_args($args, 'input', array(
            'type' => 'text',
            'class' => 'regular-text',
            'name' => $this->_name(),
            'id' => $this->_id(),
            'value' => $this->field->escaped_value(),
            'desc' => $this->_desc(true),
                ));

        return sprintf('<input%s/>%s', $this->concat_attrs($args, 'desc'), $args['desc']);
    }

    public function textarea($args = array()) {
        $args = $this->parse_args($args, 'textarea', array(
            'class' => 'rrze_mb_textarea',
            'name' => $this->_name(),
            'id' => $this->_id(),
            'cols' => 60,
            'rows' => 10,
            'value' => $this->field->escaped_value('esc_textarea'),
            'desc' => $this->_desc(true),
                ));
        return sprintf('<textarea%s>%s</textarea>%s', $this->concat_attrs($args, array('desc', 'value')), $args['value'], $args['desc']);
    }

    public function text() {
        return $this->input();
    }

    public function text_small() {
        return $this->input(array('class' => 'rrze_mb_text_small', 'desc' => $this->_desc()));
    }

    public function text_medium() {
        return $this->input(array('class' => 'rrze_mb_text_medium', 'desc' => $this->_desc()));
    }

    public function text_email() {
        return $this->input(array('class' => 'rrze_mb_text_email', 'type' => 'email'));
    }

    public function text_url() {
        return $this->input(array('class' => 'rrze_mb_text_url regular-text', 'value' => $this->field->escaped_value('esc_url')));
    }

    public function text_date() {
        return $this->input(array('class' => 'rrze_mb_text_small rrze_mb_datepicker', 'desc' => $this->_desc()));
    }

    public function text_time() {
        return $this->input(array('class' => 'rrze_mb_timepicker text_time', 'desc' => $this->_desc()));
    }

    public function text_money() {
        return (!$this->field->args('before') ? '$ ' : ' ' ) . $this->input(array('class' => 'rrze_mb_text_money', 'desc' => $this->_desc()));
    }

    public function textarea_small() {
        return $this->textarea(array('class' => 'rrze_mb_textarea_small', 'rows' => 4));
    }

    public function textarea_code() {
        return sprintf('<pre>%s</pre>', $this->textarea(array('class' => 'rrze_mb_textarea_code')));
    }

    public function wysiwyg($args = array()) {
        extract($this->parse_args($args, 'input', array(
                    'id' => $this->_id(),
                    'value' => $this->field->escaped_value('stripslashes'),
                    'desc' => $this->_desc(true),
                    'options' => $this->field->args('options'),
        )));

        wp_editor($value, $id, $options);
        echo $desc;
    }

    public function text_date_timestamp() {
        $meta_value = $this->field->escaped_value();
        $value = !empty($meta_value) ? date($this->field->args('date_format'), $meta_value) : '';
        return $this->input(array('class' => 'rrze_mb_text_small rrze_mb_datepicker', 'value' => $value));
    }

    public function text_datetime_timestamp($meta_value = '') {
        $desc = '';
        if (!$meta_value) {
            $meta_value = $this->field->escaped_value();
            $tz_offset = $this->field->field_timezone_offset();
            if (!empty($tz_offset)) {
                $meta_value -= $tz_offset;
            }
            $desc = $this->_desc();
        }

        $inputs = array(
            $this->input(array(
                'class' => 'rrze_mb_text_small rrze_mb_datepicker',
                'name' => $this->_name('[date]'),
                'id' => $this->_id('_date'),
                'value' => !empty($meta_value) ? date($this->field->args('date_format'), $meta_value) : '',
                'desc' => '',
            )),
            $this->input(array(
                'class' => 'rrze_mb_timepicker text_time',
                'name' => $this->_name('[time]'),
                'id' => $this->_id('_time'),
                'value' => !empty($meta_value) ? date($this->field->args('time_format'), $meta_value) : '',
                'desc' => $desc,
            ))
        );

        return implode("\n", $inputs);
    }

    public function text_datetime_timestamp_timezone() {
        $meta_value = $this->field->escaped_value();
        $datetime = unserialize($meta_value);
        $meta_value = $tzstring = false;

        if ($datetime && $datetime instanceof DateTime) {
            $tz = $datetime->getTimezone();
            $tzstring = $tz->getName();
            $meta_value = $datetime->getTimestamp() + $tz->getOffset(new DateTime('NOW'));
        }

        $inputs = $this->text_datetime_timestamp($meta_value);
        $inputs .= '<select name="' . $this->_name('[timezone]') . '" id="' . $this->_id('_timezone') . '">';
        $inputs .= wp_timezone_choice($tzstring);
        $inputs .= '</select>' . $this->_desc();

        return $inputs;
    }

    public function select_timezone() {
        $this->field->args['default'] = $this->field->args('default') ? $this->field->args('default') : rrze_Meta_Box::timezone_string();

        $meta_value = $this->field->escaped_value();

        return '<select name="' . $this->_name() . '" id="' . $this->_id() . '">' . wp_timezone_choice($meta_value) . '</select>';
    }

    public function colorpicker() {
        $meta_value = $this->field->escaped_value();
        $hex_color = '(([a-fA-F0-9]){3}){1,2}$';
        if (preg_match('/^' . $hex_color . '/i', $meta_value)) {
            $meta_value = '#' . $meta_value;
        }
        elseif (!preg_match('/^#' . $hex_color . '/i', $meta_value)) {
            $meta_value = "#";
        }

        return $this->input(array('class' => 'rrze_mb_colorpicker rrze_mb_text_small', 'value' => $meta_value));
    }

    public function title() {
        extract($this->parse_args(array(), 'title', array(
            'tag' => $this->field->object_type == 'post' ? 'h5' : 'h3',
            'class' => 'rrze_mb_metabox_title',
            'name' => $this->field->args('name'),
            'desc' => $this->_desc(true),
        )));

        return sprintf('<%1$s class="%2$s">%3$s</%1$s>%4$s', $tag, $class, $name, $desc);
    }

    public function select($args = array()) {
        $args = $this->parse_args($args, 'select', array(
            'class' => 'rrze_mb_select',
            'name' => $this->_name(),
            'id' => $this->_id(),
            'desc' => $this->_desc(true),
            'options' => $this->concat_options(),
                ));

        $attrs = $this->concat_attrs($args, array('desc', 'options'));
        return sprintf('<select%s>%s</select>%s', $attrs, $args['options'], $args['desc']);
    }

    public function taxonomy_select() {

        $names = $this->get_object_terms();
        $saved_term = is_wp_error($names) || empty($names) ? $this->field->args('default') : $names[0]->slug;
        $terms = get_terms($this->field->args('taxonomy'), 'hide_empty=0');
        $options = '';

        foreach ($terms as $term) {
            $selected = $saved_term == $term->slug;
            $options .= $this->option($term->name, $term->slug, $selected);
        }

        return $this->select(array('options' => $options));
    }

    public function radio($args = array(), $type = 'radio') {
        extract($this->parse_args($args, $type, array(
            'class' => 'rrze_mb_radio_list rrze_mb_list',
            'options' => $this->concat_options(array('label' => 'test')),
            'desc' => $this->_desc(true),
        )));

        return sprintf('<ul class="%s">%s</ul>%s', $class, $options, $desc);
    }

    public function radio_inline() {
        return $this->radio(array(), 'radio_inline');
    }

    public function multicheck($type = 'checkbox') {
        return $this->radio(array('class' => 'rrze_mb_checkbox_list rrze_mb_list', 'options' => $this->concat_options(array('type' => 'checkbox', 'name' => $this->_name() . '[]'), 'list_input_checkbox')), $type);
    }

    public function multicheck_inline() {
        $this->multicheck('multicheck_inline');
    }

    public function checkbox() {
        $meta_value = $this->field->escaped_value();
        $args = array('type' => 'checkbox', 'class' => 'rrze_mb_option rrze_mb_list', 'value' => 'on', 'desc' => '');
        if (!empty($meta_value)) {
            $args['checked'] = 'checked';
        }
        return sprintf('%s <label for="%s">%s</label>', $this->input($args), $this->_id(), $this->_desc());
    }

    public function taxonomy_radio() {
        $names = $this->get_object_terms();
        $saved_term = is_wp_error($names) || empty($names) ? $this->field->args('default') : $names[0]->slug;
        $terms = get_terms($this->field->args('taxonomy'), 'hide_empty=0');
        $options = '';
        $i = 1;

        if (!$terms) {
            $options .= '<li><label>' . __('Keine Begriffe') . '</label></li>';
        } else {
            foreach ($terms as $term) {
                $args = array(
                    'value' => $term->slug,
                    'label' => $term->name,
                );

                if ($saved_term == $term->slug) {
                    $args['checked'] = 'checked';
                }
                $options .= $this->list_input($args, $i);
                $i++;
            }
        }

        return $this->radio(array('options' => $options), 'taxonomy_radio');
    }

    public function taxonomy_radio_inline() {
        $this->taxonomy_radio();
    }

    public function taxonomy_multicheck() {

        $names = $this->get_object_terms();
        $saved_terms = is_wp_error($names) || empty($names) ? $this->field->args('default') : wp_list_pluck($names, 'slug');
        $terms = get_terms($this->field->args('taxonomy'), 'hide_empty=0');
        $name = $this->_name() . '[]';
        $options = '';
        $i = 1;

        if (!$terms) {
            $options .= '<li><label>' . __('Keine Begriffe') . '</label></li>';
        } else {

            foreach ($terms as $term) {
                $args = array(
                    'value' => $term->slug,
                    'label' => $term->name,
                    'type' => 'checkbox',
                    'name' => $name,
                );

                if (is_array($saved_terms) && in_array($term->slug, $saved_terms)) {
                    $args['checked'] = 'checked';
                }
                $options .= $this->list_input($args, $i);
                $i++;
            }
        }

        return $this->radio(array('class' => 'rrze_mb_checkbox_list rrze_mb_list', 'options' => $options), 'taxonomy_multicheck');
    }

    public function taxonomy_multicheck_inline() {
        $this->taxonomy_multicheck();
    }

}
