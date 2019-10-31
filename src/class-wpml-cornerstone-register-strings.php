<?php

class WPML_Cornerstone_Register_Strings extends WPML_Page_Builders_Register_Strings {

	const MODULE_TYPE_PREFIX = 'classic:';

	/**
	 * @param array $data_array
	 * @param array $package
	 */
	protected function register_strings_for_modules( array $data_array, array $package ) {
		foreach ( $data_array as $data ) {
			if ( isset( $data['_type'] ) && ! $this->type_is_layout( $data['_type'] ) ) {
				$this->register_strings_for_node( $this->get_node_id( $data ), $data, $package );
			} elseif ( is_array( $data ) ) {
				$this->register_strings_for_modules( $data, $package );
			}
		}
	}

	/**
	 * @param array $data
	 * @return string
	 */
	private function get_node_id( $data ) {
		return md5( serialize( $data ) );
	}

	/**
	 * Check if the type is a layout type.
	 *
	 * @param string $type The type to check.
	 * @return bool
	 */
	private function type_is_layout( $type ) {
		// Remove the classic prefix before checking.
		$type = preg_replace( '/^' . self::MODULE_TYPE_PREFIX . '/', '', $type );

		return in_array(
			$type,
			[ 'bar', 'container', 'section', 'row', 'column', 'layout-row', 'layout-column', 'layout-grid', 'layout-cell' ],
			true
		);
	}

}
