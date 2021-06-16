<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

use dvc\session;

class user extends dvc\user {
  var $id = 0;
	var $admin = false;
  var $programmer = false;
  var $rental_delegate = false;

	protected $dto = false;

	public function __construct() {
		if ( ( $id = (int)session::get('uid')) > 0 ) {
			$dao = new dao\users;
			if ( $this->dto = $dao->getByID( $id)) {
				// this sets up what you expose about self (only to yourself)
				$this->id = $this->dto->id;
				$this->name = $this->dto->name;
				$this->email = $this->dto->email;
        $this->admin = $this->dto->admin;
        $this->rental_delegate = $this->dto->rental_delegate;
        if ( isset($this->dto->programmer)) {
          $this->programmer = $this->dto->programmer;

        }

			}

		}

	}

	public function valid() {
		/**
		 * if this function returns true you are logged in
		 */

		return ( $this->id > 0);

  }

  public function isadmin() {
		return ( $this->admin);

	}



}
