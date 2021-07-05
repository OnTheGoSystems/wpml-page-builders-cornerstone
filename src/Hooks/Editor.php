<?php

namespace WPML\PB\Cornerstone\Hooks;

use WPML\FP\Obj;
use WPML\FP\Str;
use WPML\LIB\WP\Hooks;
use function WPML\FP\spreadArgs;

class Editor implements \IWPML_Frontend_Action {

	public function add_hooks() {
		Hooks::onFilter( 'wpml_pb_is_editing_translation_with_native_editor' )
			->then( spreadArgs( function( $isTranslationWithNativeEditor ) {
				return $isTranslationWithNativeEditor
					|| Str::includes( 'themeco/data/save', Obj::prop( 'REQUEST_URI', $_SERVER ) ) ;
			} ) );
	}
}
