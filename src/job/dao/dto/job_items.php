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

class job_items extends _dto {
  public $id = 0;
  public $job_categories_id = '';
  public $item = '';
  public $description = '';
  public $inactive = 0;

}
