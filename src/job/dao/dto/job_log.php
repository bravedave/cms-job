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

class job_log extends _dto {
  public $id = 0;
  public $created = '';
  public $updated = '';
  public $job_id = 0;
  public $comment = '';

}
