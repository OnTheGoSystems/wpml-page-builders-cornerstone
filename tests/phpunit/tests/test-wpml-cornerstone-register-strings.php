<?php

/**
 * Class Test_WPML_Cornerstone_Register_Strings
 *
 * @group cornerstone
 */
class Test_WPML_Cornerstone_Register_Strings extends OTGS_TestCase {

	/**
	 * @test
	 */
	public function it_registers_strings() {
		list( , $post, $package ) = $this->get_post_and_package( 'Cornerstone' );

		$string  = new WPML_PB_String( rand_str(), rand_str(), rand_str(), rand_str() );
		$strings = [ $string ];

		$data = [
			[
				'_type' => 'section',
				'_modules' => [
					[
						'_type' => 'classic:row',
						'_modules' => [
							[
								'_type' => 'layout-column',
								'_modules' => [
									[
										'_type' => 'headline',
										'field' => 'value',
									],
								],
							],
						],
					],
				],
			],
		];

		$node_id = md5( serialize( $data[0]['_modules'][0]['_modules'][0]['_modules'][0] ) );

		\WP_Mock::wpFunction( 'get_post_meta', array(
			'times'  => 1,
			'args'   => [ $post->ID, '_cornerstone_data', false ],
			'return' => [ json_encode( $data ) ],
		) );
		WP_Mock::expectAction( 'wpml_start_string_package_registration', $package );
		WP_Mock::expectAction( 'wpml_delete_unused_package_strings', $package );
		/** @var \WPML_Cornerstone_Translatable_Nodes|\PHPUnit_Framework_MockObject_MockObject $translatable_nodes */
		$translatable_nodes = $this->getMockBuilder( 'WPML_Cornerstone_Translatable_Nodes' )
		                           ->setMethods( [ 'get', 'initialize_nodes_to_translate' ] )
		                           ->disableOriginalConstructor()
		                           ->getMock();
		$translatable_nodes->method( 'get' )
		                   ->with( $node_id, $data[0]['_modules'][0]['_modules'][0]['_modules'][0] )
		                   ->willReturn( $strings );

		/** @var \WPML_Cornerstone_Data_Settings|\PHPUnit_Framework_MockObject_MockObject $data_settings */
		$data_settings = $this->getMockBuilder( 'WPML_Cornerstone_Data_Settings' )
		                      ->disableOriginalConstructor()
		                      ->getMock();
		$data_settings->method( 'get_meta_field' )
		              ->willReturn( '_cornerstone_data' );
		$data_settings->method( 'get_node_id_field' )
		              ->willReturn( 'id' );
		$data_settings->method( 'convert_data_to_array' )
		              ->with( [ json_encode( $data ) ] )
		              ->willReturn( json_decode( json_encode( $data ), true ) );

		/** @var \WPML_PB_String_Registration|\PHPUnit_Framework_MockObject_MockObject $string_registration */
		$string_registration = $this->getMockBuilder( 'WPML_PB_String_Registration' )
		                            ->setMethods( [ 'register_string' ] )
		                            ->disableOriginalConstructor()
		                            ->getMock();
		$string_registration->expects( $this->once() )
		                    ->method( 'register_string' )
		                    ->with(
			                    $package['post_id'],
			                    $string->get_value(),
			                    $string->get_editor_type(),
			                    $string->get_title(),
			                    $string->get_name() );

		$subject = new WPML_Cornerstone_Register_Strings( $translatable_nodes, $data_settings, $string_registration );
		$subject->register_strings( $post, $package );
	}

	private function get_post_and_package( $name = '' ) {
		if ( ! $name ) {
			$name = rand_str();
		}
		$post_id = rand();

		$post = $this->get_post_stub();
		$post->ID = $post_id;

		$package = [
			'kind'    => $name,
			'name'    => $post_id,
			'title'   => 'Page Builder Page ' . $post_id,
			'post_id' => $post_id,
		];

		return [ $name, $post, $package ];
	}

	private function get_post_stub() {
		return $this->getMockBuilder( 'WP_Post' )
					->disableOriginalConstructor()
					->getMock();
	}
}