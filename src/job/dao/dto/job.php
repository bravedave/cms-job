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
  public $brief = '';
  public $description = '';
  public $status = 0;
  public $status_verbatim = '';
  public $complete = 0;
  public $invoice_reviewed = '';
  public $invoice_reviewed_by = 0;
  public $invoice_reviewed_by_name = '';
  public $due = '';
  public $job_payment = 0;
  public $archived = '';
  public $has_invoice = false;

  public $contractor_id = 0;
  public $contractor_trading_name = '';
  public $contractor_primary_contact_name = '';

  public $properties_id = 0;
  public $address_street = '';
  public $address_suburb = '';
  public $address_postcode = '';
  public $on_site_contact = '';
  public $property_manager = '';
  public $property_manager_id = 0;
  public $property_manager_email = '';
  public $property_manager_mobile = '';
  public $property_manager_telephone = '';
  public $owner_name = '';

  public $keys = [];
  public $lines = [];

}
