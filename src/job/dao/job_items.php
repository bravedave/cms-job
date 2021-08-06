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

// use cms\job\config;
use dao\_dao;
use ParseCsv;

class job_items extends _dao {
  protected $_db_name = 'job_items';
  protected $template = __NAMESPACE__ . '\dto\job_items';

  public function getAll($fields = 'job_items.*, cat.category', $order = 'ORDER BY cat.category ASC, job_items.item ASC') {
    $sql = sprintf(
      'SELECT
        %s
      FROM
        `job_items`
          LEFT JOIN
        `job_categories` cat ON cat.id = job_items.job_categories_id %s',
      $fields,
      $order,

    );

    return $this->Result($sql);
  }

  public function getItemsForCategory(int $category, bool $distinct = false, string $item = '') {
    if ($distinct) {
      $sql = sprintf(
        'SELECT
          DISTINCT `item`
        FROM
          `job_items`
        WHERE
          `inactive` = 0
            AND `job_categories_id` = %d
        ORDER BY
          `item` ASC',
        $category

      );
    } elseif ($item) {
      $sql = sprintf(
        'SELECT
          *
        FROM
          `job_items`
        WHERE
          `inactive` = 0
            AND `job_categories_id` = %d
            AND item = %s
          ORDER BY
            `item` ASC, `description` ASC',
        $category,
        $this->quote($item)

      );
    } else {
      $sql = sprintf(
        'SELECT
          *
        FROM
          `job_items`
        WHERE
          `inactive` = 0
            AND `job_categories_id` = %d
        ORDER BY
          `item` ASC, `description` ASC',
        $category

      );
    }

    // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));
    return $this->Result($sql);
  }

  public function import_from_csv() {
    $path = implode(
      DIRECTORY_SEPARATOR,
      [
        dirname(__DIR__),
        'resources',
        'maintenance-items.csv'

      ]

    );

    if (file_exists($path)) {
      \sys::logger(sprintf('<importing %s> %s', $path, __METHOD__));

      $csv = new ParseCsv\Csv;
      $csv->auto($path);
      set_time_limit(300);

      \sys::logger(sprintf('<%s> %s', print_r($csv->getTotalDataRowCount(), true), __METHOD__));
      $categories = [];
      $daoCategories = new job_categories;
      foreach ($csv->data as $item) {

        // CATEGORY,ITEM,ISSUE

        $key = array_search($item['CATEGORY'], array_column($categories, 'category'));
        if (false === $key) {
          $categories[] =
            $dtoCategories = $daoCategories->getByCategory(
              $item['CATEGORY'],
              job_categories::autoadd
            );
        } else {
          $dtoCategories = $categories[$key];
        }

        $a = [
          'job_categories_id' => $dtoCategories->id,
          'item' => $item['ITEM'],
          'description' => $item['ISSUE']
        ];

        $this->Insert($a);

        // if ($t['properties_id']) {
        //     'keyset' => $t['keyset'],
        //     'properties_id' => $t['properties_id'],
        //     'updated' => \db::dbTimeStamp(),
        //     'created' => \db::dbTimeStamp()


        //   $a['keyset_type'] = config::keyset_management;

        //   $a['keyset_type'] = config::keyset_tenant;
        //   $this->Insert($a);
        // }
      }
    } else {
      \sys::logger(sprintf('<missing import file> %s', $path, __METHOD__));
      \sys::logger(sprintf('<%s> %s', $path, __METHOD__));
    }
  }

  public function search(string $term): array {

    $label = sprintf(
      'CONCAT( job_items.`item`, %s, job_items.`description`) `label`',
      $this->quote(' - ')
    );

    if ('sqlite' == \config::$DB_TYPE) {
      $label = sprintf(
        'job_items.`item` || %s || job_items.`description` `label`',
        $this->quote(' - ')
      );

    }


    $sql = sprintf(
      'SELECT
        job_items.`id`,
        job_items.`job_categories_id`,
        job_items.`item`,
        cat.`category`,
        job_items.`description`,
        %s
      FROM
        `job_items`
          LEFT JOIN
        `job_categories` cat ON cat.id = job_items.job_categories_id
      WHERE
        `inactive` = 0
        AND `description` LIKE %s
      ORDER BY
        cat.`category` ASC, job_items.`item` ASC
      LIMIT 10',
      $label,
      $this->quote('%' . $term . '%')

    );

    $ret = [];
    if ($res = $this->Result($sql)) {
      $ret = $res->dtoSet();
      if (count($ret) < 10) {
        $sql = sprintf(
          'SELECT
            job_items.`id`,
            job_items.`job_categories_id`,
            job_items.`item`,
            cat.`category`,
            job_items.`description`,
            %s
          FROM
            `job_items`
              LEFT JOIN
            `job_categories` cat ON cat.id = job_items.job_categories_id
          WHERE
            job_items.`inactive` = 0
            AND (
              job_items.`description` LIKE %s
                OR
              job_items.`item` LIKE %s)
          ORDER BY
            cat.`category` ASC, job_items.`item` ASC',
          $label,
          $this->quote('%' . $term . '%'),
          $this->quote('%' . $term . '%')

        );


        if ($res = $this->Result($sql)) {
          $ret = $res->dtoSet();
        }
      }
    }

    // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));
    return $ret;
  }
}
