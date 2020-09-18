<?php

namespace WPML\PB\Cornerstone\Modules;

/**
 * @group module
 */
class TestModuleWithItemsFromConfig extends \OTGS_TestCase {

	/**
	 * @test
	 */
	public function itShouldGetData() {
		$config = [
			[
				'field'       => 'title',
				'type'        => 'The slide title',
				'editor_type' => 'LINE',
			],
			[
				'field'       => 'link',
				'type'        => 'The slide link',
				'editor_type' => 'LINK',
			],
		];


		$subject = new ModuleWithItemsFromConfig( $config );

		$this->assertEquals(
			[ 'title', 'link' ],
			$subject->get_fields()
		);

		// Title field
		$this->assertEquals( 'The slide title', $subject->get_title( 'title' ) );
		$this->assertEquals( 'LINE', $subject->get_editor_type( 'title' ) );

		// Link field
		$this->assertEquals( 'The slide link', $subject->get_title( 'link' ) );
		$this->assertEquals( 'LINK', $subject->get_editor_type( 'link' ) );
	}
}
