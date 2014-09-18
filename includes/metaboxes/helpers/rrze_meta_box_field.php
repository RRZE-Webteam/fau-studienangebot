<?php

class rrze_Meta_Box_field {

	public $object_id;

	public $object_type;

	public $args;

	public $value;

	public function __construct($field_args) {
		$this->object_id   = rrze_Meta_Box::get_object_id();
		$this->object_type = rrze_Meta_Box::get_object_type();
		$this->args        = $this->_set_field_defaults( $field_args );

		$this->value = apply_filters( 'rrze_mb_override_meta_value', 'rrze_mb_no_override_val', $this->object_id, $this->args(), $this->object_type, $this );

		$this->value = 'rrze_mb_no_override_val' === $this->value
			? $this->get_data()
			: $this->value;
	}

	public function __call( $name, $arguments ) {
		$key = isset( $arguments[0] ) ? $arguments[0] : false;
		return $this->args( $name, $key );
	}

	public function id( $raw = false ) {
		$id = $raw ? '_id' : 'id';
		return $this->args( $id );
	}

	public function args( $key = '', $_key = '' ) {
		$vars = $this->_data( 'args', $key );
		if ( $_key ) {
			return isset( $vars[ $_key ] ) ? $vars[ $_key ] : false;
		}
		return $vars;
	}

	public function value( $key = '' ) {
		return $this->_data( 'value', $key );
	}

	public function _data( $var, $key = '' ) {
		$vars = $this->$var;
		if ( $key ) {
			return isset( $vars[ $key ] ) ? $vars[ $key ] : false;
		}
		return $vars;
	}

	public function get_data( $field_id = '', $args = array() ) {
		if ( $field_id ) {
			$args['field_id'] = $field_id;
		}
		extract( $this->data_args( $args ) );

		$data = 'options-page' === $type
			? rrze_Meta_Box::get_option( $id, $field_id )
			: get_metadata( $type, $id, $field_id, ( $single || $repeat ));

		return $data;
	}

	public function update_data( $new_value, $single = true ) {
		extract( $this->data_args( array( 'new_value' => $new_value, 'single' => $single ) ) );

		$new_value = $repeat ? array_values( $new_value ) : $new_value;

		if ( 'options-page' === $type )
			return rrze_Meta_Box::update_option( $id, $field_id, $new_value, $single );

		if ( ! $single )
			return add_metadata( $type, $id, $field_id, $new_value, false );

		return update_metadata( $type, $id, $field_id, $new_value );
	}

	public function remove_data( $old = '' ) {
		extract( $this->data_args() );

		return 'options-page' === $type
			? rrze_Meta_Box::remove_option( $id, $field_id )
			: delete_metadata( $type, $id, $field_id, $old );
	}

	public function data_args( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'type'     => $this->object_type,
			'id'       => $this->object_id,
			'field_id' => $this->id( true ),
			'single'   => ! $this->args( 'multiple' ),
		) );
		return $args;
	}

	public function sanitization_cb( $meta_value ) {
		if ( empty( $meta_value ) )
			return $meta_value;

		$cb = $this->maybe_callback( 'sanitization_cb' );
		if ( false === $cb ) {
			return $meta_value;
		} elseif ( $cb ) {
			return call_user_func( $cb, $meta_value, $this->args(), $this );
		}

		$clean = new rrze_Meta_Box_Sanitize( $this, $meta_value );

		return $clean->{$this->type()}( $meta_value );
	}

	public function maybe_callback( $cb ) {
		$field_args = $this->args();
		if ( ! isset( $field_args[ $cb ] ) )
			return;

		$cb = false !== $field_args[ $cb ] && 'false' !== $field_args[ $cb ] ? $field_args[ $cb ] : false;

		if ( ! $cb )
			return false;

		if ( is_callable( $cb ) )
			return $cb;
	}

	public function escaping_exception() {
		return in_array( $this->type(), array(
			'multicheck',
			'text_datetime_timestamp_timezone',
		) );
	}

	public function escaped_value( $func = 'esc_attr', $meta_value = '' ) {

		if ( isset( $this->escaped_value ) )
			return $this->escaped_value;

		$meta_value = $meta_value ? $meta_value : $this->value();

		$cb = $this->maybe_callback( 'escape_cb' );
		if ( false === $cb || $this->escaping_exception() ) {
			return ! empty( $meta_value ) ? $meta_value : $this->args( 'default' );
		} elseif ( $cb ) {
			return call_user_func( $cb, $meta_value, $this->args(), $this );
		}

		$esc = apply_filters( 'rrze_mb_types_esc_'. $this->type(), null, $meta_value, $this->args(), $this );
		if ( null !== $esc ) {
			return $esc;
		}

		$func       = $func ? $func : 'esc_attr';
		$meta_value = ! empty( $meta_value ) ? $meta_value : $this->args( 'default' );

		if ( is_array( $meta_value ) ) {
			foreach ( $meta_value as $key => $value ) {
				$meta_value[ $key ] = call_user_func( $func, $value );
			}
		} else {
			$meta_value = call_user_func( $func, $meta_value );
		}

		$this->escaped_value = $meta_value;
		return $this->escaped_value;
	}

	public function field_timezone_offset() {
		return rrze_Meta_Box::timezone_offset( $this->field_timezone() );
	}

	public function field_timezone() {

		if ( $this->args( 'timezone' ) ) {
			return $this->args( 'timezone' ) ;
		}
		else if ( $this->args( 'timezone_meta_key' ) ) {
			return $this->get_data( $this->args( 'timezone_meta_key' ) );
		}

		return false;
	}

	public function render_field() {

		if ( ! is_admin() && ! $this->args( 'on_front' ) )
			return;

		if ( is_callable( $this->args( 'show_on_cb' ) ) && ! call_user_func( $this->args( 'show_on_cb' ), $this ) )
			return;

		$classes    = 'rrze-mb-type-'. sanitize_html_class( $this->type() );
		$classes   .= ' rrze_mb_id_'. sanitize_html_class( $this->id() );
		$classes   .= $this->args( 'inline' ) ? ' rrze-mb-inline' : '';
		$is_side    = 'side' === $this->args( 'context' );

		printf( "<tr class=\"%s\">\n", $classes );

		if ( 'title' == $this->type() || ! $this->args( 'show_names' ) || $is_side ) {
			echo "\t<td colspan=\"2\">\n";

			if ( ! $this->args( 'show_names' ) || $is_side ) {
				$style = ! $is_side || 'title' == $this->type() ? ' style="display:none;"' : '';
				printf( "\n<label%s for=\"%s\">%s</label>\n", $style, $this->id(), $this->args( 'name' ) );
			}
		} else {

			$style = 'post' == $this->object_type ? ' style="width:15%"' : '';
			$tag   = 'th';
			printf( '<%1$s%2$s><label for="%3$s">%4$s</label></%1$s>', $tag, $style, $this->id(), $this->args( 'name' ) );

			echo "\n\t<td>\n";
		}

		echo $this->args( 'before' );

		$this_type = new rrze_Meta_Box_types( $this );
		$this_type->render();

		echo $this->args( 'after' );

		echo "\n\t</td>\n</tr>";
	}

	public function replace_hash( $value ) {
		return str_ireplace( '{#}', ( $this->count() + 1 ), $value );
	}

	public function _set_field_defaults( $args ) {

		if ( ! isset( $args['name'] ) ) $args['name'] = '';
		if ( ! isset( $args['desc'] ) ) $args['desc'] = '';
		if ( ! isset( $args['before'] ) ) $args['before'] = '';
		if ( ! isset( $args['after'] ) ) $args['after'] = '';
		if ( ! isset( $args['protocols'] ) ) $args['protocols'] = null;
		if ( ! isset( $args['description'] ) ) {
			$args['description'] = isset( $args['desc'] ) ? $args['desc'] : '';
		}
		if ( ! isset( $args['default'] ) ) {
			$args['default'] = isset( $args['std'] ) ? $args['std'] : '';
		}
		if ( ! isset( $args['preview_size'] ) ) $args['preview_size'] = array( 50, 50 );
		if ( ! isset( $args['date_format'] ) ) $args['date_format'] = 'm\/d\/Y';
		if ( ! isset( $args['time_format'] ) ) $args['time_format'] = 'h:i A';

		$args['default']    = apply_filters( 'rrze_mb_default_filter', $args['default'], $args, $this->object_type, $this->object_type );
		$args['allow']      = 'file' == $args['type'] && ! isset( $args['allow'] ) ? array( 'url', 'attachment' ) : array();
		$args['save_id']    = 'file' == $args['type'] && ! ( isset( $args['save_id'] ) && ! $args['save_id'] );
		$args['multiple']   = isset( $args['multiple'] ) ? $args['multiple'] : false;
		$args['inline']     = isset( $args['inline'] ) && $args['inline'] || false !== stripos( $args['type'], '_inline' );
		$args['on_front']   = ! ( isset( $args['on_front'] ) && ! $args['on_front'] );
		$args['attributes'] = isset( $args['attributes'] ) && is_array( $args['attributes'] ) ? $args['attributes'] : array();
		$args['options']    = isset( $args['options'] ) && is_array( $args['options'] ) ? $args['options'] : array();

		$args['_id']        = $args['id'];
		$args['_name']      = $args['id'];

		if ( 'wysiwyg' == $args['type'] ) {
			$args['id'] = strtolower( str_ireplace( '-', '_', $args['id'] ) );
			$args['options']['textarea_name'] = $args['_name'];
		}

		$option_types = array( 'taxonomy_select', 'taxonomy_radio', 'taxonomy_radio_inline' );
		if ( in_array( $args['type'], $option_types, true ) ) {
			$args['show_option_all'] = isset( $args['show_option_all'] ) && ! $args['show_option_all'] ? false : true;
			$args['show_option_none'] = isset( $args['show_option_none'] ) && ! $args['show_option_none'] ? false : true;
		}

		return $args;
	}

	public function maybe_set_attributes( $attrs = array() ) {
		return wp_parse_args( $this->args['attributes'], $attrs );
	}

}
