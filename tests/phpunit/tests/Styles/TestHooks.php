<?php

namespace WPML\PB\Cornerstone\Styles;

use WPML\LIB\WP\Post;
use WPML\LIB\WP\PostMock;

/**
 * @group styles
 */
class TestHooks extends \OTGS_TestCase {

	use PostMock;

	public function setUp() {
		parent::setUp();
		$this->setUpPostMock();
	}

	/**
	 * @test
	 */
	public function itShouldAddHooks() {
		$subject = $this->getSubject();

		\WP_Mock::expectActionAdded( 'save_post', [ $subject, 'invalidateStylesInTranslation' ] );

		$subject->add_hooks();
	}

	/**
	 * @test
	 * @dataProvider dpShouldNotInvalidateStyles
	 *
	 * @param bool $isHandlingPost
	 * @param bool $isTranslationEditor
	 */
	public function itShouldNotInvalidateStyles( $isHandlingPost, $isTranslationEditor ) {
		$postId    = 123;
		$metaValue = 'Some generated CSS';

		Post::updateMeta( $postId, Hooks::META_KEY, $metaValue );

		$lastEditMode = $this->getLastEditMode( $postId, $isTranslationEditor );
		$dataSettings = $this->getDataSettings( $postId, $isHandlingPost );
		$subject      = $this->getSubject( $lastEditMode, $dataSettings );

		$subject->invalidateStylesInTranslation( $postId );

		$this->assertSame( $metaValue, Post::getMetaSingle( $postId, Hooks::META_KEY, true ) );
	}

	/**
	 * @test
	 */
	public function itShouldInvalidateStyles() {
		$postId    = 123;

		Post::updateMeta( $postId, Hooks::META_KEY, 'Some generated CSS' );

		$lastEditMode = $this->getLastEditMode( $postId, true );
		$dataSettings = $this->getDataSettings( $postId, true );
		$subject      = $this->getSubject( $lastEditMode, $dataSettings );

		$subject->invalidateStylesInTranslation( $postId );

		$this->assertfalse( Post::getMetaSingle( $postId, Hooks::META_KEY, true ) );
	}

	public function dpShouldNotInvalidateStyles() {
		return [
			'not handled and not translation editor' => [ false, false ],
			'not handled'                            => [ false, true ],
			'not translation editor'                 => [ true, false ],
		];
	}

	private function getSubject( $lastEditMode = null, $dataSettings = null ) {
		$lastEditMode = $lastEditMode ?: $this->getLastEditMode();
		$dataSettings = $dataSettings ?: $this->getDataSettings();

		return new Hooks( $lastEditMode, $dataSettings );
	}

	private function getLastEditMode( $postId = 0, $isTranslationEditor = false ) {
		$lastEditMode = $this->getMockBuilder( '\WPML_PB_Last_Translation_Edit_Mode' )
			->setMethods( [ 'is_translation_editor' ] )
			->disableOriginalConstructor()->getMock();
		$lastEditMode->method( 'is_translation_editor' )->with( $postId )->willReturn( $isTranslationEditor );

		return $lastEditMode;
	}

	private function getDataSettings( $postId = 0, $isHandlingPost = false ) {
		$dataSettings = $this->getMockBuilder( '\WPML_Cornerstone_Data_Settings' )
			->setMethods( [ 'is_handling_post' ] )
			->disableOriginalConstructor()->getMock();
		$dataSettings->method( 'is_handling_post' )->with( $postId )->willReturn( $isHandlingPost );

		return $dataSettings;
	}
}
