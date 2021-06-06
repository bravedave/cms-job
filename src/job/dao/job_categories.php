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
use green;
// use strings;
// use sys;

class job_categories extends _dao {
  protected $_db_name = 'job_categories';
  protected $template = __NAMESPACE__ . '\dto\job_categories';

  public function getByCategory( string $category, bool $autoAdd = false) : ?dto\job_categories {
    $sql = sprintf(
      'SELECT
        *
      FROM
        `%s`
      WHERE
        `category` = "%s"',
      $this->db_name(),
      $this->escape( $category)

    );

    if ( $res = $this->Result( $sql)) {
      if ( $dto = $res->dto($this->template)) {
        return $dto;

      }
      elseif ( $autoAdd) {
        $id = $this->Insert(['category' => $category]);
        if ( $dto = $this->getByID( $id)) {
          return $dto;

        }

      }

    }

    return null;

  }

  public static function getCategorySet() {
    $dao = new self;
    $_cats = $dao->dtoSet( $dao->getAll());
    $_set = [];
    foreach ($_cats as $_cat) {
      $_set[$_cat->id] = $_cat->category;

    }

    return $_set;

  }

}
