<?php

/**
 * Class Test_WPML_Cornerstone_Integration_Factory
 *
 * @group cornerstone
 */
class Test_WPML_Cornerstone_Integration_Factory extends OTGS_TestCase {

	/**
	 * @test
	 */
	public function it_creates_instance_of_page_builders_integration() {
		$factory = \Mockery::mock( WPML_PB_Factory::class );

		$strategy = \Mockery::mock( WPML_PB_API_Hooks_Strategy::class );
		$strategy->shouldReceive( 'set_factory' )->with( $factory );

		\WP_Mock::userFunction( 'WPML\Container\make' )
		        ->with( WPML_PB_Factory::class )
		        ->andReturn( $factory );
		\WP_Mock::userFunction( 'WPML\Container\make' )
		        ->with( WPML_PB_API_Hooks_Strategy::class, [ ':name' => 'Cornerstone' ] )
		        ->andReturn( $strategy );
		\WP_Mock::userFunction( 'WPML\Container\make' )
		        ->with( WPML_PB_Reuse_Translations_By_Strategy::class, [ ':strategy' => $strategy ] )
		        ->andReturn( \Mockery::mock( WPML_PB_Reuse_Translations_By_Strategy::class ) );

		$subject = new WPML_Cornerstone_Integration_Factory();

		$string_registration = $this->getMockBuilder( 'WPML_PB_String_Registration' )
		                            ->disableOriginalConstructor()
		                            ->getMock();

		\Mockery::mock( 'overload:WPML_String_Registration_Factory' )->shouldReceive( 'create' )->andReturn( $string_registration );
		\Mockery::mock( 'overload:WPML_Action_Filter_Loader' )->shouldReceive( 'load' )->with( array(
			'WPML_PB_Cornerstone_Handle_Custom_Fields_Factory',
			'WPML_Cornerstone_Media_Hooks_Factory',
			\WPML\PB\Cornerstone\Config\Factory::class,
			\WPML\PB\Cornerstone\Styles\Hooks::class,
			\WPML\PB\Cornerstone\Hooks\Editor::class,
		) );

		$this->assertInstanceOf( 'WPML_Page_Builders_Integration', $subject->create() );
	}
}