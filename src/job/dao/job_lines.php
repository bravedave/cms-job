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
// use green;
// use strings;
// use sys;

class job_lines extends _dao {
  protected $_db_name = 'job_lines';
  protected $template = __NAMESPACE__ . '\dto\job_lines';

  public function getLinesOfJobID( int $id) : array {
    $sql = sprintf(
      'SELECT
        jl.*,
        ji.`job_categories_id`,
        jc.`category`,
        ji.`item`,
        ji.`description`
      FROM
        `job_lines` jl
        LEFT JOIN `job_items` ji ON ji.`id` = jl.`item_id`
        LEFT JOIN `job_categories` jc ON jc.`id` = ji.`job_categories_id`
      WHERE
        `job_id` = %d',
      $id

    );

    // \sys::logger( sprintf('<%s> %s', $sql, __METHOD__));

    if ( $res = $this->Result( $sql)) {
      return $this->dtoSet( $res);

    }

    return [];

  }

}
