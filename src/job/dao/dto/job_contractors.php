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

class job_contractors extends _dto {
  public $id = 0;
  public $trading_name = '';
  public $company_name = '';
  public $abn = '';
  public $services = '';
  public $primary_contact = '';
  public $primary_contact_role = '';
  public $primary_contact_name = '';
  public $primary_contact_phone = '';
  public $primary_contact_email = '';
  public $insurance_expiry_date = '';
  public $document_tags = '';

}
