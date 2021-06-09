<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\job\dao\dto;

use dao\dto\_dto;

class job extends _dto {
  public $id = 0;
  public $created = '';
  public $job_type = 0;
  public $description = '';

  public $properties_id = 0;
  public $address_street = '';
  public $address_suburb = '';
  public $address_postcode = '';

  public $lines = [];

}
