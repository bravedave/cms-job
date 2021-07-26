<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

class currentuser extends dvc\currentUser {
	static function option($key, $value = null) {
		if ('google-sharer' == $key) {
			// return false;
			return 'yes';
		}

		return parent::option($key, $value);
	}

	static function restriction($key) {
		if ('open-user' == $key) {
			return true;
		} elseif ('can-add-job-categories' == $key) {
			// return false;
			return true;
		} elseif ('can-add-job-items' == $key) {
			// return false;
			return true;
		}

		return true;
	}

	static public function isRentalDelegate(): bool {
		return ((bool)self::user()->rental_delegate);
	}

	static public function name() {
		if (self::user())
			return self::user()->name;

		return ('');
	}

	protected static $__sms = false;
	static public function sms() {
		if (!(self::$__sms)) {
			self::$__sms = sms\config::smshandler();
		}

		return (self::$__sms);
	}
}
