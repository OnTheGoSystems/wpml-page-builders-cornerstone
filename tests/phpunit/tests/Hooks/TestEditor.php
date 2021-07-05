<?php

namespace WPML\PB\Cornerstone\Hooks;

use WPML\LIB\WP\OnActionMock;

/**
 * @group hooks
 * @group editor
 */
class TestEditor extends \OTGS_TestCase {

	use OnActionMock;

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

		$this->assertTrue( $this->runFilter( 'wpml_pb_is_editing_translation_with_native_editor', true ) );
	}

	/**
	 * @test
	 */
	public function itReturnsTrueIfTranslatingWithCornerstoneNativeEditor() {
		$_SERVER['REQUEST_URI'] = '/fr/wp-json/themeco/data/save?_locale=user';

		$subject = new Editor();
		$subject->add_hooks();

		$this->assertTrue( $this->runFilter( 'wpml_pb_is_editing_translation_with_native_editor', false ) );
	}
}
