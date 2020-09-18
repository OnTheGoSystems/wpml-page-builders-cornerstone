<?php
/**
 * Test_WPML_Cornerstone_Translatable_Nodes test file.
 *
 * @package wpml-page-builders-cornerstone
 */

/**
 * Class Test_WPML_Cornerstone_Translatable_Nodes
 *
 * @group page-builders
 * @group cornerstone
 * @group wpmltm-3081
 */
class Test_WPML_Cornerstone_Translatable_Nodes extends OTGS_TestCase {

	/**
	 * Test get method.
	 *
	 * @test
	 * @dataProvider dp_valid_nodes
	 *
	 * @param string $type                 Node type.
	 * @param string $field                Node field.
	 * @param string $expected_title       Expected title.
	 * @param string $expected_editor_type Expected editor type.
	 */
	public function it_handles_valid_nodes( $type, $field, $expected_title, $expected_editor_type ) {
		$wrap_tag = 'h2';

		\WP_Mock::wpPassthruFunction( '__' );

		$node_id  = mt_rand( 1, 100 );
		$settings = array(
			'_type' => $type,
			$field  => rand_str(),
		);
		if ( 'headline' === $type ) {
			$settings['text_tag'] = $wrap_tag;
		}

		$subject = new WPML_Cornerstone_Translatable_Nodes();
		$strings = $subject->get( $node_id, $settings );
		$this->assertCount( 1, $strings );
		$string = $strings[0];
		$this->assertEquals( $settings[ $field ], $string->get_value() );
		$this->assertEquals( $field . '-' . $settings['_type'] . '-' . $node_id, $string->get_name() );
		$this->assertEquals( $expected_title, $string->get_title() );
		$this->assertEquals( $expected_editor_type, $string->get_editor_type() );
		if ( 'headline' === $type ) {
			$this->assertEquals( $wrap_tag, $string->get_wrap_tag() );
		}
	}

	/**
	 * Data provider for it_handles_valid_nodes().
	 *
	 * @return array
	 */
	public function dp_valid_nodes() {
		return array(
			array( 'alert', 'alert_content', 'Alert Content', 'VISUAL' ),
			array( 'text', 'text_content', 'Text content', 'VISUAL' ),
			array( 'quote', 'quote_content', 'Quote content', 'VISUAL' ),
			array( 'counter', 'counter_number_prefix_content', 'Counter: number prefix', 'LINE' ),
			array( 'content-area', 'content', 'Content Area: content', 'AREA' ),
			array( 'breadcrumbs', 'breadcrumbs_home_label_text', 'Breadcrumbs: home label text', 'LINE' ),
			array( 'audio', 'audio_embed_code', 'Audio: embed code', 'VISUAL' ),
			array( 'headline', 'text_content', 'Headline text content', 'VISUAL' ),
			array( 'content-area-off-canvas', 'off_canvas_content', 'Canvas content', 'VISUAL' ),
			array( 'content-area-modal', 'modal_content', 'Modal content', 'VISUAL' ),
			array( 'content-area-dropdown', 'dropdown_content', 'Dropdown content', 'VISUAL' ),
			array( 'button', 'anchor_text_primary_content', 'Anchor text: primary content', 'LINE' ),
			array( 'video', 'video_embed_code', 'Video: embed code', 'LINE' ),
			array( 'search-inline', 'search_placeholder', 'Search Inline: placeholder', 'LINE' ),
			array( 'search-modal', 'search_placeholder', 'Search Modal: placeholder', 'LINE' ),
			array( 'search-dropdown', 'search_placeholder', 'Search Dropdown: placeholder', 'LINE' ),
		);
	}

	/**
	 * Test get method with invalid node.
	 *
	 * @test
	 */
	public function it_handles_invalid_nodes() {
		\WP_Mock::wpPassthruFunction( '__' );

		$node_id  = mt_rand( 1, 100 );
		$settings = array(
			'_type'              => 'invalid-node',
			'invalid_node_field' => rand_str(),
		);

		$subject = new WPML_Cornerstone_Translatable_Nodes();
		$strings = $subject->get( $node_id, $settings );
		$this->assertCount( 0, $strings );
	}

	/**
	 * Test update method.
	 *
	 * @test
	 */
	public function it_tests_update() {
		$node_id = mt_rand( 1, 100 );

		$settings = array(
			'_type'        => 'headline',
			'text_content' => rand_str(),
		);

		$translation = rand_str();

		$string = new WPML_PB_String( $translation, 'text_content-headline-' . $node_id, 'anything', 'anything' );

		$subject  = new WPML_Cornerstone_Translatable_Nodes();
		$settings = $subject->update( $node_id, $settings, $string );

		$this->assertEquals( $translation, $settings['text_content'] );
	}

	/**
	 * @test
	 * @group wpmlcore-7565
	 */
	public function it_gets_with_integration_class() {
		$nodeId   = '1a2b3c4d';
		$type     = 'slides';
		$original = 'The original text';

		$config = [
			$type => [
				'conditions'        => [ '_type' => $type ],
				'fields'            => [],
				'integration-class' => Test_Class_For_Module_With_Items::class,
			],
		];

		$settings    = [
			'_type'     => $type,
			WPML_Cornerstone_Module_With_Items::ITEMS_FIELD => [
				[ 'heading' => $original ],
			],
		];

		$stringName = md5( $original ) . '-' . 'heading' . '-' . $nodeId;

		$expectedString = new WPML_PB_String( $original, $stringName, 'Heading', 'LINE' );

		\WP_Mock::onFilter( 'wpml_cornerstone_modules_to_translate' )
		        ->with( WPML_Cornerstone_Translatable_Nodes::get_nodes_to_translate() )
		        ->reply( $config );

		$subject = new WPML_Cornerstone_Translatable_Nodes();
		$strings = $subject->get( $nodeId, $settings );

		$this->assertCount( 1, $strings );
		$this->assertEquals( $expectedString, $strings[0] );
	}

	/**
	 * @test
	 * @group wpmlcore-7565
	 */
	public function it_updates_with_fields_in_items_config() {
		$nodeId           = '1a2b3c4d';
		$type             = 'slides';
		$fieldToTranslate = 'title';
		$original         = 'The original text';
		$translation      = 'The text translation';

		$settings    = [
			'_type'     => $type,
			WPML_Cornerstone_Module_With_Items::ITEMS_FIELD => [
				[ $fieldToTranslate => $original ],
			],
		];

		$config = [
			$type => [
				'conditions'     => [ '_type' => $type ],
				'fields'         => [],
				'fields_in_item' => [
					WPML_Cornerstone_Module_With_Items::ITEMS_FIELD => [
						[
							'field'       => $fieldToTranslate,
							'type'        => 'The slide text',
							'editor_type' => 'LINE',
						],
					],
				],
			],
		];

		$stringName = md5( $original ) . '-' . $fieldToTranslate . '-' . $nodeId;

		$string = new WPML_PB_String( $translation, $stringName, 'anything', 'anything' );

		\WP_Mock::onFilter( 'wpml_cornerstone_modules_to_translate' )
		        ->with( WPML_Cornerstone_Translatable_Nodes::get_nodes_to_translate() )
		        ->reply( $config );

		$subject  = new WPML_Cornerstone_Translatable_Nodes();
		$settings = $subject->update( $nodeId, $settings, $string );

		$this->assertEquals( $translation, $settings[ WPML_Cornerstone_Module_With_Items::ITEMS_FIELD ][0][ $fieldToTranslate ] );
	}
}

class Test_Class_For_Module_With_Items extends WPML_Cornerstone_Module_With_Items {

	public function get_fields() {
		return [ 'heading' ];
	}

	protected function get_title( $field ) {
		return 'Heading';
	}

	protected function get_editor_type( $field ) {
		return 'LINE';
	}
}