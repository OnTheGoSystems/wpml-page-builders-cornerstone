<?php

namespace WPML\PB\Cornerstone\Hooks;

use tad\FunctionMocker\FunctionMocker;
use WPML\LIB\WP\OnActionMock;

/**
 * @group hooks
 * @group editor
 */
class TestEditor extends \OTGS_TestCase {

	use OnActionMock;

	const ORIGINAL_POST_ID = 123;
	const TRANSLATED_POST_ID = 456;

	public function setUp() {
		parent::setUp();
		$this->setUpOnAction();
	}

	public function tearDown() {
		unset( $_SERVER['REQUEST_URI'] );
		$this->tearDownOnAction();
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function itReturnsTrueIfAlreadyTranslatingWithNativeEditor() {
		$subject = new Editor();
		$subject->add_hooks();

		$this->assertTrue( $this->runFilter( 'wpml_pb_is_editing_translation_with_native_editor', true, self::TRANSLATED_POST_ID ) );
	}

	/**
	 * @test
	 * @dataProvider dpValidRawRestData
	 *
	 * @param string $rawRestData
	 */
	public function itReturnsTrueIfTranslatingWithCornerstoneNativeEditor( $rawRestData ) {
		$_SERVER['REQUEST_URI'] = '/fr/wp-json/themeco/data/save?_locale=user';

		FunctionMocker::replace( 'WP_REST_Server::get_raw_data', $rawRestData );

		$subject = new Editor();
		$subject->add_hooks();

		$this->assertTrue( $this->runFilter( 'wpml_pb_is_editing_translation_with_native_editor', false, self::TRANSLATED_POST_ID ) );
	}

	public function dpValidRawRestData() {
		$data = [
			'requests' => [
				'builder' => [
					'id' => (string) self::TRANSLATED_POST_ID,
				],
			],
		];

		return [
			'with gzip'    => [
				json_encode( [
					'gzip'    => 1,
					'request' => self::encodeGzipData( $data ),
				] ),
			],
			'without gzip' => [
				json_encode( [
					'request' => $data,
				] ),
			],
		];
	}

	/**
	 * @test
	 */
	public function itReturnsFalseIfEditingOriginalWithCornerstoneNativeEditor() {
		$_SERVER['REQUEST_URI'] = '/wp-json/themeco/data/save?_locale=user';

		$rawRestData = json_encode( [
			'request' => [
				'requests' => [
					'builder' => [
						'id' => (string) self::ORIGINAL_POST_ID,
					],
				],
			],
		] );

		FunctionMocker::replace( 'WP_REST_Server::get_raw_data', $rawRestData );

		$subject = new Editor();
		$subject->add_hooks();

		$this->assertFalse( $this->runFilter( 'wpml_pb_is_editing_translation_with_native_editor', false, self::TRANSLATED_POST_ID ) );
	}

	/**
	 * @test
	 * @dataProvider dpInvalidRawRestData
	 *
	 * @param string $rawRestData
	 */
	public function itReturnsFalseIfNOTTranslatingWithCornerstoneNativeEditor( $rawRestData ) {
		$_SERVER['REQUEST_URI'] = '/fr/wp-json/themeco/data/save?_locale=user';

		FunctionMocker::replace( 'WP_REST_Server::get_raw_data', $rawRestData );

		$subject = new Editor();
		$subject->add_hooks();

		$this->assertFalse( $this->runFilter( 'wpml_pb_is_editing_translation_with_native_editor', false, self::TRANSLATED_POST_ID ) );
	}

	public function dpInvalidRawRestData() {
		return [
			'No ID provided'      => [
				json_encode( [
					'request' => [
						'requests' => [
							'builder' => [],
						],
					],
				] ),
			],
			'No data in request'  => [
				json_encode( [
					'request' => [],
				] ),
			],
			'Invalid json_decode' => [ '{"invalid JSON":...}' ],
			'No raw REST data'    => [ null ],
		];
	}

	/**
	 * @param mixed $data
	 *
	 * @return string
	 */
	private static function encodeGzipData( $data ) {
		return base64_encode( gzencode( json_encode( $data ) ) );
	}
}
