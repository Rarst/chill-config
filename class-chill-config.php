<?php

use Symfony\Component\Yaml\Yaml;

/**
 * Experimental YAML config for WordPress.
 */
class Chill_Config {

	protected $config;

	/**
	 * @param string $path to config file
	 */
	public function __construct( $path ) {

		$this->config = (object) Yaml::parse( file_get_contents( $path ) );

		if ( ! empty( $this->config->constants ) ) {
			foreach ( $this->config->constants as $name => $value ) {
					define( $name, $value );
			}
		}

		if ( ! empty( $this->config->options ) ) {
			foreach ( $this->config->options as $name => $value ) {
				$this->add_filter( 'pre_option_' . $name, array( $this, 'option_return' ) );
			}
		}

		if ( ! empty( $this->config->transients ) ) {
			foreach ( $this->config->transients as $name => $value ) {
				$this->add_filter( 'pre_transient_' . $name, array( $this, 'transient_return' ) );
			}
		}

		if ( ! empty( $this->config->filters ) ) {
			foreach ( $this->config->filters as $name => $value ) {
				$this->add_filter( $name, array( $this, 'filter_return' ) );
			}
		}
	}

	/**
	 * @return mixed
	 */
	public function option_return() {

		$option = substr( current_filter(), 11 ); // offset by pre_option_

		return $this->config->options[$option];
	}

	/**
	 * @return mixed
	 */
	public function transient_return() {

		$transient = substr( current_filter(), 14 ); // offset by pre_transient_

		return $this->config->transients[$transient];
	}

	/**
	 * @return mixed
	 */
	public function filter_return() {

		return $this->config->filters[current_filter()];
	}

	/**
	 * Taken as-is from core.
	 *
	 * @param string   $tag
	 * @param callable $function_to_add
	 * @param int      $priority
	 * @param int      $accepted_args
	 *
	 * @return bool
	 */
	public function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		global $wp_filter, $merged_filters;

		$idx                              = $this->_wp_filter_build_unique_id( $tag, $function_to_add, $priority );
		$wp_filter[$tag][$priority][$idx] = array( 'function' => $function_to_add, 'accepted_args' => $accepted_args );
		unset( $merged_filters[$tag] );

		return true;
	}

	/**
	 * Taken as-is from core.
	 *
	 * @param string   $tag
	 * @param callable $function
	 * @param int      $priority
	 *
	 * @return array|bool|string
	 */
	function _wp_filter_build_unique_id( $tag, $function, $priority ) {
		global $wp_filter;
		static $filter_id_count = 0;

		if ( is_string( $function ) ) {
			return $function;
		}

		if ( is_object( $function ) ) {
			// Closures are currently implemented as objects
			$function = array( $function, '' );
		} else {
			$function = (array) $function;
		}

		if ( is_object( $function[0] ) ) {
			// Object Class Calling
			if ( function_exists( 'spl_object_hash' ) ) {
				return spl_object_hash( $function[0] ) . $function[1];
			} else {
				$obj_idx = get_class( $function[0] ) . $function[1];
				if ( ! isset( $function[0]->wp_filter_id ) ) {
					if ( false === $priority ) {
						return false;
					}
					$obj_idx .= isset( $wp_filter[$tag][$priority] ) ? count( (array) $wp_filter[$tag][$priority] ) : $filter_id_count;
					$function[0]->wp_filter_id = $filter_id_count;
					++$filter_id_count;
				} else {
					$obj_idx .= $function[0]->wp_filter_id;
				}

				return $obj_idx;
			}
		} else if ( is_string( $function[0] ) ) {
			// Static Calling
			return $function[0] . '::' . $function[1];
		}
	}
} 