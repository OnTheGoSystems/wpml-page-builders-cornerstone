<?php

/**
 * Class Test_WPML_Cornerstone_Update_Translation
 *
 * @group cornerstone
 * @group update-translation
 */
class Test_WPML_Cornerstone_Update_Translation extends OTGS_TestCase {

	/**
	 * @test
	 */
	public function it_updates_translation() {
		\WP_Mock::wpPassthruFunction( '__' );
		$translated_post_id = mt_rand();
		$original_post_id   = mt_rand();
		$original_post      = (object) array( 'ID' => $original_post_id );
		$lang               = 'en';
		$translation        = 'translation-value';

		$nodes = array(
			'custom-node' => array(
				'conditions' => array( '_type' => 'custom-node' ),
				'fields'     => array(
					array(
						'field'       => 'text',
						'type'        => __( 'Custom node text', 'sitepress' ),
						'editor_type' => 'LINE'
					),
				),
			),
		);

		$meta_field_data = [
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
										'_type' => 'custom-node',
										'text'  => 'value',
									],
								],
							],
						],
					],
				],
			],
		];

		$meta_field_translated_data = [
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
										'_type' => 'custom-node',
										'text'  => $translation,
									],
								],
							],
						],
					],
				],
			],
		];

		$node_id = md5( serialize( $meta_field_data[0]['_modules'][0]['_modules'][0]['_modules'][0] ) );

		$string_translations = array(
			'text-custom-node-' . $node_id => array(
				$lang => array(
					'status' => 10,
					'value'  => $translation
				)
			)
		);

		\WP_Mock::wpFunction( 'get_post_meta', array(
			'times'  => 1,
			'args'   => array( $original_post_id, '_cornerstone_data', true ),
			'return' => $meta_field_data,
		) );

		\WP_Mock::wpFunction( 'update_post_meta', array(
			'times' => 1,
			'args'  => array( $translated_post_id, '_cornerstone_data', $meta_field_translated_data ),
		) );

		$this->add_copy_meta_fields_checks( $translated_post_id, $original_post_id );

		$translatable_nodes = new WPML_Cornerstone_Translatable_Nodes();

		$data_settings = $this->getMockBuilder( 'WPML_Cornerstone_Data_Settings' )
		                      ->disableOriginalConstructor()
		                      ->getMock();
		$data_settings->method( 'get_meta_field' )
		              ->willReturn( '_cornerstone_data' );
		$data_settings->method( 'get_node_id_field' )
		              ->willReturn( 'id' );
		$data_settings->method( 'get_fields_to_copy' )
		              ->willReturn( array( '_cornerstone_settings', '_cornerstone_version' ) );
		$data_settings->method( 'convert_data_to_array' )
		              ->with( $meta_field_data )
		              ->willReturn( $meta_field_data );
		$data_settings->method( 'prepare_data_for_saving' )
		              ->with( $meta_field_translated_data )
		              ->willReturn( $meta_field_translated_data );
		$data_settings->method( 'get_fields_to_save' )
		              ->willReturn( array( '_cornerstone_data' ) );

		\WP_Mock::onFilter( 'wpml_cornerstone_modules_to_translate' )
		        ->with( WPML_Cornerstone_Translatable_Nodes::get_nodes_to_translate() )
		        ->reply( $nodes );

		$subject = new WPML_Cornerstone_Update_Translation( $translatable_nodes, $data_settings );
		$subject->update( $translated_post_id, $original_post, $string_translations, $lang );
	}

	private function add_copy_meta_fields_checks( $translated_post_id, $original_post_id ) {
		foreach ( array( '_cornerstone_settings', '_cornerstone_version' ) as $meta_key ) {
			$value = rand_str();
			\WP_Mock::wpFunction( 'get_post_meta', array(
				'times'  => 1,
				'args'   => array( $original_post_id, $meta_key, true ),
				'return' => $value,
			) );
			\WP_Mock::wpFunction( 'update_post_meta', array(
				'times' => 1,
				'args'  => array( $translated_post_id, $meta_key, $value ),
			) );
			\WP_Mock::onFilter( 'wpml_pb_copy_meta_field' )
			        ->with(
				        array(
					        $value,
					        $translated_post_id,
					        $original_post_id,
					        $meta_key
				        )
			        )
			        ->reply( $value );
		}
	}
}