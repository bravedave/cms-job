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

use cms\job\config;
use dao\_dao;
use green\properties\dao\properties;

// use green;
// use strings;
// use sys;

class job extends _dao {
  protected $_db_name = 'job';
  protected $template = __NAMESPACE__ . '\dto\job';

  public function getMatrix() {
    $sql = sprintf(
      'SELECT
        job.*,
        p.address_street,
        p.property_manager,
        c.trading_name `contractor_trading_name`,
        CASE
        WHEN p.property_manager > 0 THEN u.name
        WHEN cp.PropertyManager > %s THEN uc.name
        ELSE %s
        END pm
      FROM
        `job`
        LEFT JOIN `properties` p on p.id = job.properties_id
        LEFT JOIN `job_contractors` c on c.id = job.contractor_id
        LEFT JOIN `console_properties` cp on cp.properties_id = p.id
        LEFT JOIN `users` u ON u.id = p.property_manager
        LEFT JOIN `users` uc ON uc.console_code = cp.PropertyManager',
      $this->quote(''),
      $this->quote('')

    );

    // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));

    $this->Q(
      sprintf(
        'CREATE TEMPORARY TABLE `matrix` AS %s',
        $sql

      )

    );

    $this->Q('ALTER TABLE `matrix` ADD COLUMN `lines` TEXT');

    $sql =
      'SELECT
        m.*,
        jl.item_id,
        ji.item,
        ji.description
      FROM `matrix` m
        LEFT JOIN `job_lines` jl ON jl.job_id = m.id
        LEFT JOIN `job_items` ji ON ji.id = jl.item_id
      ORDER BY m.id, ji.job_categories_id';

    if ($res = $this->Result($sql)) {
      $items = [];
      $res->dtoSet(function ($dto) use (&$items) {
        // \sys::logger(
        //   sprintf(
        //     '<%s: %s - %s> %s',
        //     $dto->item_id,
        //     $dto->item,
        //     $dto->description,
        //     __METHOD__

        //   )

        // );

        if ($dto->item || $dto->description) {
          if (!isset($items[$dto->id])) $items[$dto->id] = [];
          $items[$dto->id][] = (object)[
            'item' => $dto->item,
            'description' => $dto->description,

          ];
        }

        return $dto;
      });

      foreach ($items as $k => $v) {
        $sql = sprintf(
          'UPDATE `matrix` SET `lines` = %s WHERE `id` = %d',
          $this->quote(json_encode($v)),
          $k

        );

        // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));
        $this->Q($sql);
      }
    }

    return $this->Result('SELECT * FROM `matrix`');
  }

  public function getRichData(dto\job $job): dto\job {
    $dao = new job_lines;
    $job->lines = $dao->getLinesOfJobID($job->id);

    if ($job->contractor_id) {
      $dao = new job_contractors;
      if ($contractor = $dao->getByID($job->contractor_id)) {
        $job->contractor_trading_name = $contractor->trading_name;
      }
    }

    if ($job->properties_id) {
      $dao = new properties;
      if ($prop = $dao->getByID($job->properties_id)) {
        $job->address_street = $prop->address_street;
        $job->address_suburb = $prop->address_suburb;
        $job->address_postcode = $prop->address_postcode;
      }
    }

    return $job;
  }

  public function getWorkOrderPath(dto\job $job): string {
    $path = implode(DIRECTORY_SEPARATOR, [
      $this->store($job),
      'workorder.pdf'

    ]);

    return $path;
  }

  public function store(dto\job $job): string {
    $path = implode(DIRECTORY_SEPARATOR, [
      config::cms_job_store(),
      $job->id

    ]);

    if (!is_dir($path)) {
      mkdir($path, 0777);
      chmod($path, 0777);
    }

    return $path;
  }
}
