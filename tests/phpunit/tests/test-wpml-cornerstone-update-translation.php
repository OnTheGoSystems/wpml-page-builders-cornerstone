<?php
/**
 * Test_WPML_Cornerstone_Update_Translation Class file.
 *
 * @package wpml-page-builders-cornerstone
 */

/**
 * Class Test_WPML_Cornerstone_Update_Translation
 *
 * @group cornerstone
 * @group update-translation
 */
class Test_WPML_Cornerstone_Update_Translation extends OTGS_TestCase {

	/**
	 * It updates translation.
	 *
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
						'editor_type' => 'LINE',
					),
				),
			),
		);

		$meta_field_data = array(
			array(
				'_type' => 'custom-node',
				'text'  => 'value',
			),
		);

		$meta_field_translated_data = array(
			array(
				'_type' => 'custom-node',
				'text'  => $translation,
			),
		);

		$node_id = md5( serialize( $meta_field_data[0] ) );

		$string_translations = array(
			'text-custom-node-' . $node_id => array(
				$lang => array(
					'status' => 10,
					'value'  => $translation,
				),
			),
		);

		\WP_Mock::wpFunction(
			'get_post_meta',
			array(
				'times'  => 1,
				'args'   => array( $original_post_id, '_cornerstone_data', true ),
				'return' => $meta_field_data,
			)
		);

		\WP_Mock::wpFunction(
			'update_post_meta',
			array(
				'times' => 1,
				'args'  => array( $translated_post_id, '_cornerstone_data', $meta_field_translated_data ),
			)
		);

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
		              ->with( $meta_field_data )
		              ->willReturn( $meta_field_translated_data );
		$data_settings->method( 'get_fields_to_save' )
		              ->willReturn( array( '_cornerstone_data' ) );

		\WP_Mock::onFilter( 'wpml_cornerstone_modules_to_translate' )
		        ->with( $this->get_translatable_nodes() )
		        ->reply( $nodes );

		$subject = new WPML_Cornerstone_Update_Translation( $translatable_nodes, $data_settings );
		$subject->update( $translated_post_id, $original_post, $string_translations, $lang );
	}

	/**
	 * Add check of copy of meta field.
	 *
	 * @param int $translated_post_id Translated post id.
	 * @param int $original_post_id   Original post id.
	 */
	private function add_copy_meta_fields_checks( $translated_post_id, $original_post_id ) {
		foreach ( array( '_cornerstone_settings', '_cornerstone_version' ) as $meta_key ) {
			$value = rand_str();
			\WP_Mock::wpFunction(
				'get_post_meta',
				array(
					'times'  => 1,
					'args'   => array( $original_post_id, $meta_key, true ),
					'return' => $value,
				)
			);
			\WP_Mock::wpFunction(
				'update_post_meta',
				array(
					'times' => 1,
					'args'  => array( $translated_post_id, $meta_key, $value ),
				)
			);
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

	/**
	 * Get translatable nodes.
	 *
	 * @return array
	 */
	private function get_translatable_nodes() {
		return array(
			'alert'                    => array(
				'conditions' => array( '_type' => 'alert' ),
				'fields'     => array(
					array(
						'field'       => 'alert_content',
						'type'        => __( 'Alert Content', 'sitepress' ),
						'editor_type' => 'VISUAL',
					),
				),
			),
			'text'                     => array(
				'conditions' => array( '_type' => 'text' ),
				'fields'     => array(
					array(
						'field'       => 'text_content',
						'type'        => __( 'Text content', 'sitepress' ),
						'editor_type' => 'VISUAL',
					),
				),
			),
			'quote'                    => array(
				'conditions' => array( '_type' => 'quote' ),
				'fields'     => array(
					array(
						'field'       => 'quote_content',
						'type'        => __( 'Quote content', 'sitepress' ),
						'editor_type' => 'VISUAL',
					),
					array(
						'field'       => 'quote_cite_content',
						'type'        => __( 'Quote: cite content', 'sitepress' ),
						'editor_type' => 'LINE',
					),
				),
			),
			'counter'                  => array(
				'conditions' => array( '_type' => 'counter' ),
				'fields'     => array(
					array(
						'field'       => 'counter_number_prefix_content',
						'type'        => __( 'Counter: number prefix', 'sitepress' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'counter_number_suffix_content',
						'type'        => __( 'Counter: number suffix', 'sitepress' ),
						'editor_type' => 'LINE',
					),
				),
			),
			'content-area'             => array(
				'conditions' => array( '_type' => 'content-area' ),
				'fields'     => array(
					array(
						'field'       => 'content',
						'type'        => __( 'Content Area: content', 'sitepress' ),
						'editor_type' => 'AREA',
					),
				),
			),
			'breadcrumbs'              => array(
				'conditions' => array( '_type' => 'breadcrumbs' ),
				'fields'     => array(
					array(
						'field'       => 'breadcrumbs_home_label_text',
						'type'        => __( 'Breadcrumbs: home label text', 'sitepress' ),
						'editor_type' => 'LINE',
					),
				),
			),
			'audio'                    => array(
				'conditions' => array( '_type' => 'audio' ),
				'fields'     => array(
					array(
						'field'       => 'audio_embed_code',
						'type'        => __( 'Audio: embed code', 'sitepress' ),
						'editor_type' => 'VISUAL',
					),
				),
			),
			'headline'                 => array(
				'conditions' => array( '_type' => 'headline' ),
				'fields'     => array(
					array(
						'field'       => 'text_content',
						'type'        => __( 'Headline text content', 'sitepress' ),
						'editor_type' => 'VISUAL',
					),
				),
			),
			'classic:custom-headline'  => array(
				'conditions' => array( '_type' => 'classic:custom-headline' ),
				'fields'     => array(
					array(
						'field'       => 'content',
						'type'        => __( 'Classic headline text content', 'sitepress' ),
						'editor_type' => 'VISUAL',
					),
				),
			),
			'classic:feature-headline' => array(
				'conditions' => array( '_type' => 'classic:feature-headline' ),
				'fields'     => array(
					array(
						'field'       => 'content',
						'type'        => __( 'Classic feature headline text content', 'sitepress' ),
						'editor_type' => 'VISUAL',
					),
				),
			),
			'content-area-off-canvas'  => array(
				'conditions' => array( '_type' => 'content-area-off-canvas' ),
				'fields'     => array(
					array(
						'field'       => 'off_canvas_content',
						'type'        => __( 'Canvas content', 'sitepress' ),
						'editor_type' => 'VISUAL',
					),
				),
			),
			'content-area-modal'       => array(
				'conditions' => array( '_type' => 'content-area-modal' ),
				'fields'     => array(
					array(
						'field'       => 'modal_content',
						'type'        => __( 'Modal content', 'sitepress' ),
						'editor_type' => 'VISUAL',
					),
				),
			),
			'content-area-dropdown'    => array(
				'conditions' => array( '_type' => 'content-area-dropdown' ),
				'fields'     => array(
					array(
						'field'       => 'dropdown_content',
						'type'        => __( 'Dropdown content', 'sitepress' ),
						'editor_type' => 'VISUAL',
					),
				),
			),
			'button'                   => array(
				'conditions' => array( '_type' => 'button' ),
				'fields'     => array(
					array(
						'field'       => 'anchor_text_primary_content',
						'type'        => __( 'Anchor text: primary content', 'sitepress' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'anchor_text_secondary_content',
						'type'        => __( 'Anchor text: secondary content', 'sitepress' ),
						'editor_type' => 'LINE',
					),
				),
			),
			'video'                    => array(
				'conditions' => array( '_type' => 'video' ),
				'fields'     => array(
					array(
						'field'       => 'video_embed_code',
						'type'        => __( 'Video: embed code', 'sitepress' ),
						'editor_type' => 'LINE',
					),
				),
			),
			'search-inline'            => array(
				'conditions' => array( '_type' => 'search-inline' ),
				'fields'     => array(
					array(
						'field'       => 'search_placeholder',
						'type'        => __( 'Search Inline: placeholder', 'sitepress' ),
						'editor_type' => 'LINE',
					),
				),
			),
			'search-modal'             => array(
				'conditions' => array( '_type' => 'search-modal' ),
				'fields'     => array(
					array(
						'field'       => 'search_placeholder',
						'type'        => __( 'Search Modal: placeholder', 'sitepress' ),
						'editor_type' => 'LINE',
					),
				),
			),
			'search-dropdown'          => array(
				'conditions' => array( '_type' => 'search-dropdown' ),
				'fields'     => array(
					array(
						'field'       => 'search_placeholder',
						'type'        => __( 'Search Dropdown: placeholder', 'sitepress' ),
						'editor_type' => 'LINE',
					),
				),
			),
			'accordion'                => array(
				'conditions'        => array( '_type' => 'accordion' ),
				'fields'            => array(),
				'integration-class' => 'WPML_Cornerstone_Accordion',
			),
			'tabs'                     => array(
				'conditions'        => array( '_type' => 'tabs' ),
				'fields'            => array(),
				'integration-class' => 'WPML_Cornerstone_Tabs',
			),
		);
	}
}
