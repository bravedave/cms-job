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

use cms;
use dao\_dao;

class job_items extends _dao {
  protected $_db_name = 'job_items';
  protected $template = __NAMESPACE__ . '\dto\job_items';

  public function getAll($fields = 'job_items.*, cat.category', $order = 'ORDER BY cat.category') {
    $sql = sprintf(
      'SELECT %s FROM `job_items` LEFT JOIN `job_categories` cat on cat.id = job_items.job_categories_id %s',
      $fields,
      $order,

    );

    return $this->Result($sql);

  }

}