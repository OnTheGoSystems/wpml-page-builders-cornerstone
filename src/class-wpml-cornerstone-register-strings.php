<?php

/**
 * Class WPML_Beaver_Builder_Register_Strings
 */
class WPML_Cornerstone_Register_Strings extends WPML_Page_Builders_Register_Strings {

	/**
	 * @param array $data_array
	 * @param array $package
	 */
	protected function register_strings_for_modules( array $data_array, array $package ) {
		foreach ( $data_array as $data ) {
			if ( is_array( $data ) ) {
				$this->register_strings_for_modules( $data, $package );
			} elseif ( isset( $data_array['_type'] ) && ! in_array( $data_array['_type'], array( 'section', 'column', 'row' ) ) ) {
				$this->register_strings_for_node( $this->get_node_id( $data_array ), $data_array, $package );
			}
		}
	}

	private function get_node_id( $data ) {
		return md5( serialize( $data ) );
	}
}
