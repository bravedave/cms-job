<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\job\dao;

// use cms;
use dao\_dao;
use green\properties\dao\properties;

// use green;
// use strings;
// use sys;

class job extends _dao {
  protected $_db_name = 'job';
  protected $template = __NAMESPACE__ . '\dto\job';

  public function getMatrix() {
    $sql =
    'SELECT
        job.*,
        p.address_street
      FROM
        `job`
        LEFT JOIN properties p on p.id = job.properties_id';

    return $this->Result( $sql);

  }

  public function getRichData( dto\job $job) : dto\job {
    $dao = new job_lines;
    $job->lines = $dao->getLinesOfJobID( $job->id);

    if ( $job->properties_id) {
      $dao = new properties;
      if ( $prop = $dao->getByID( $job->properties_id)) {
        $job->address_street = $prop->address_street;
        $job->address_suburb = $prop->address_suburb;
        $job->address_postcode = $prop->address_postcode;

      }

    }

    return $job;

  }

}
