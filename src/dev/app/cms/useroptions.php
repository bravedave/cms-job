<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms;

class useroptions {
	// protected $user_id = 0;
	// protected $options = null;

	function __construct( $user_id, $options = null) {
		// $this->user_id = $user_id;

		// gettype( $options) == 'string' ?
		// 	$this->options = (array)json_decode( $options) :
		// 	$this->options = $options;

	}

	function get( $key) {
		$ret = '';
		return ( $ret);

	}

	function set( $key, $val) {}

}
