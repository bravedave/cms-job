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
  // protected $_sql_getAll = 'SELECT %s FROM %s %s';
  protected $_sql_getAll =
    'SELECT
      jc.*,
      (SELECT
        COUNT(*) items
      FROM
        job_items ji
      WHERE
        ji.job_categories_id = jc.id) items
    FROM
      job_categories jc
    ORDER BY jc.category';

  protected $_db_name = 'job_categories';
  protected $template = __NAMESPACE__ . '\dto\job_categories';

  const autoadd = true;

  public function getAll($fields = '*', $order = 'ORDER BY `category`') {
    return parent::getAll($fields, $order);
  }

  public function getByCategory(string $category, bool $autoAdd = false): ?dto\job_categories {
    $sql = sprintf(
      'SELECT
        *
      FROM
        `%s`
      WHERE
        `category` = %s',
      $this->db_name(),
      $this->quote($category)

    );

    if ($res = $this->Result($sql)) {
      if ($dto = $res->dto($this->template)) {
        return $dto;
      } elseif ($autoAdd) {
        $id = $this->Insert(['category' => $category]);
        if ($dto = $this->getByID($id)) {
          return $dto;
        }
      }
    }

    return null;
  }

  public function getCategoriesOf(string $ids): array {
    if ($ids) {
      $sql = sprintf(
        'SELECT
          *
        FROM
          `%s`
        WHERE
          `id` in (%s)',
        $this->db_name(),
        $ids

      );

      // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));
      if ($res = $this->Result($sql)) {
        return $this->dtoSet($res);
      }
    }

    return [];
  }

  public static function getCategorySet() {
    $_set = [];
    $dao = new self;
    $_cats = $dao->dtoSet($dao->getAll());
    foreach ($_cats as $_cat) {
      $_set[$_cat->id] = $_cat->category;
    }

    return $_set;
  }
}
