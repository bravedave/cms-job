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

use dao\_dao;

class job_log extends _dao {
  protected $_db_name = 'job_log';
  protected $template = __NAMESPACE__ . '\dto\job_log';

  static function getForJob( dto\job $job) : array {

    $_sql = sprintf(
      'SELECT
        jl.*,
        u.`name` username
      FROM
        `job_log` jl
            LEFT JOIN
          `users` u on u.`id` = jl.`user_id`
      WHERE
        jl.job_id = %d',
      $job->id

    );

    $dao = new self;
    if ( $res = $dao->Result( $_sql)) {
      return $dao->dtoSet( $res);

    }

    return [];

  }

}
