<?php
/**
 * @group media
 */
class Test_WPML_Cornerstone_Media_Node_With_Urls extends OTGS_TestCase {

	/**
	 * @test
	 */
	public function it_should_translate_identifiers() {
		$source_lang    = 'en';
		$target_lang    = 'fr';
		$original_id    = '17';
		$translated_id  = '18';
		$original_url   = $original_id . ':full';
		$translated_url = $translated_id . ':full';

		$settings = array(
			'foo'         => 'bar',
			'front_image' => $original_url,
			'_type'       => 'classic:card',
		);

		$expected_settings = array(
			'foo'         => 'bar',
			'front_image' => $translated_url,
			'_type'       => 'classic:card',
		);

		$media_translate = $this->getMockBuilder( 'WPML_Page_Builders_Media_Translate' )
			->disableOriginalConstructor()->getMock();

		\WP_Mock::onFilter( 'wpml_object_id' )
			->with( $original_id, 'attachment', true, $target_lang )
			->reply( $translated_id );

		$subject = new WPML_Cornerstone_Media_Node_Classic_Card( $media_translate );

		$this->assertEquals( $expected_settings, $subject->translate( $settings, $target_lang, $source_lang ) );
	}

}
