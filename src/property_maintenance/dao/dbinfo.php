<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\property_maintenance\dao;

class dbinfo extends \dao\_dbinfo {
	/*
	 * it is probably sufficient to copy this file into the <application>/app/dao folder
	 *
	 * from there store you structure files in <application>/dao/db folder
	 */
	protected function check() {
		parent::check();

		\sys::logger( 'checking ' . dirname( __FILE__ ) . '/db/*.php' );

		if ( glob( dirname( __FILE__ ) . '/db/*.php')) {
			foreach ( glob( dirname( __FILE__ ) . '/db/*.php') as $f ) {
				\sys::logger( 'checking => ' . $f );
				include_once $f;

			}

		}

	}

}

