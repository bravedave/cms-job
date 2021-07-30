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
use dao\{
  properties,
  people
};

// use green;
use strings;
// use sys;

class job extends _dao {
  protected $_db_name = 'job';
  protected $template = __NAMESPACE__ . '\dto\job';

  protected function _getInvoicePath(int $jobID): string {
    $path = implode(DIRECTORY_SEPARATOR, [
      $this->_store($jobID),
      'invoice'

    ]);

    if (file_exists($path . '.jpg')) {
      $path .= '.jpg';
    } elseif (file_exists($path . '.png')) {
      $path .= '.png';
    } else {
      $path .= '.pdf';
    }

    return $path;
  }

  protected function _store(int $jobID): string {
    $path = implode(DIRECTORY_SEPARATOR, [
      config::cms_job_store(),
      $jobID

    ]);

    if (!is_dir($path)) {
      mkdir($path, 0777);
      chmod($path, 0777);
    }

    return $path;
  }

  public function getByID($id) {
    if ($dto = parent::getByID($id)) {
      $dto->has_invoice = file_exists($this->getInvoicePath($dto)) ? 1 : 0;

      if (!isset(config::job_status[$dto->status])) {
        $dto->status = 0;
      }

      if (1 == $dto->has_invoice && $dto->status < config::job_status_reviewed) {
        if ($dto->paid) {
          $dto->status = config::job_status_paid; // auto advance status
        } elseif ($dto->invoice_reviewed_by) {
          $dto->status = config::job_status_reviewed; // auto advance status
        } else {
          $dto->status = config::job_status_invoiced; // auto advance status
        }
      } elseif ($dto->complete && $dto->status < config::job_status_complete) {
        $dto->status = config::job_status_complete; // auto advance status
      }

      if ($dto->status < config::job_status_sent) {
        if (strtotime($dto->email_sent) > 0) {
          $dto->status = config::job_status_sent; // auto advance status
        }
      }
    }
    return $dto;
  }

  public function getInvoicePath(dto\job $job): string {
    return $this->_getInvoicePath($job->id);
  }

  public function getMatrix(bool $archived = false, $pid = 0) {
    $where = [];

    if ((int)$pid) {
      $where[] = sprintf('job.properties_id = %d', $pid);
    }

    if (!$archived) {
      $where[] = sprintf(
        '(COALESCE(job.`archived`,%s) = %s OR DATE( job.archived) <= %s)',
        $this->quote(''),
        $this->quote(''),
        $this->quote('0000-00-00')
      );
    }

    if ($where) {
      $where = sprintf('WHERE %s', implode(' AND ', $where));
    } else {
      $where = '';
    }

    $sql = sprintf(
      'SELECT
        job.*,
        p.`address_street`,
        p.`street_index`,
        p.`property_manager`,
        c.`trading_name` `contractor_trading_name`,
        CASE
        WHEN p.`property_manager` > 0 THEN u.`name`
        ELSE %s
        END pm
      FROM
        `job`
        LEFT JOIN `properties` p on p.id = job.`properties_id`
        LEFT JOIN `job_contractors` c on c.id = job.`contractor_id`
        LEFT JOIN `users` u ON u.id = p.`property_manager`
      %s',
      $this->quote(''),
      $this->quote(''),
      $where

    );

    if (config::$CONSOLE_FALLBACK) {
      $sql = sprintf(
        'SELECT
          job.*,
          p.`address_street`,
          p.`street_index`,
          p.`property_manager`,
          c.`trading_name` `contractor_trading_name`,
          CASE
          WHEN p.`property_manager` > 0 THEN u.`name`
          WHEN cp.`PropertyManager` > %s THEN uc.`name`
          ELSE %s
          END pm
        FROM
          `job`
          LEFT JOIN `properties` p on p.`id` = job.`properties_id`
          LEFT JOIN `job_contractors` c on c.`id` = job.`contractor_id`
          LEFT JOIN `console_properties` cp on cp.`properties_id` = p.`id`
          LEFT JOIN `users` u ON u.id = p.`property_manager`
          LEFT JOIN `users` uc ON uc.`console_code` = cp.`PropertyManager`
        %s',
        $this->quote(''),
        $this->quote(''),
        $where

      );
    }

    // \sys::logSQL(sprintf('<%s> %s', $sql, __METHOD__));

    $this->Q(
      sprintf(
        'CREATE TEMPORARY TABLE `matrix` AS %s',
        $sql

      )

    );

    $this->Q('ALTER TABLE `matrix` ADD COLUMN `lines` TEXT');
    $this->Q('ALTER TABLE `matrix` ADD COLUMN `has_invoice` INT');

    $sql = 'SELECT
        `id`,
        `status`,
        `complete`,
        `invoice_reviewed_by`,
        `paid_by`,
        `properties_id`,
        `address_street`,
        `street_index`
      FROM
        `matrix`';

    if ($res = $this->Result($sql)) {
      $res->dtoSet(function ($dto) {
        $set = [];
        if (file_exists($path = $this->_getInvoicePath($dto->id))) {
          $set[] = '`has_invoice` = 1';

          if ($dto->status < config::job_status_reviewed) {
            if ($dto->paid_by) {
              $dto->status = config::job_status_paid; // auto advance status
            } elseif ($dto->invoice_reviewed_by) {
              $dto->status = config::job_status_reviewed; // auto advance status
            } else {
              $dto->status = config::job_status_invoiced;
            }
            $set[] = sprintf('`status` = %s', $dto->status);
          }
        }

        if ($dto->complete) {
          if ($dto->status < config::job_status_complete) {
            $dto->status = config::job_status_complete;
            $set[] = sprintf('`status` = %s', config::job_status_complete);
          }
        }

        if (!$dto->street_index) {
          if ($street_index = strings::street_index($dto->address_street)) {
            $set[] = sprintf(
              '`street_index` = %s',
              $this->quote($street_index)

            );

            if ((int)$dto->properties_id) {
              $a = [
                'street_index' => $street_index
              ];
              $dao = new \dao\properties;
              $dao->UpdateByID($a, $dto->properties_id);
            }
          } else {
            $set[] = sprintf(
              '`street_index` = %s',
              $this->quote($dto->address_street)

            );
          }
        }

        if ($set) {
          $sql = sprintf(
            'UPDATE `matrix` SET %s  WHERE `id` = %d',
            implode(',', $set),
            $dto->id

          );

          $this->Q($sql);
        }
      });
    }

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

        $contractor = $dao->getRichData($contractor);

        $job->contractor_trading_name = $contractor->trading_name;
        $job->contractor_primary_contact_name = $contractor->primary_contact_name;
      }
    }

    if ($job->properties_id) {
      $dao = new properties;
      if ($prop = $dao->getByID($job->properties_id)) {
        $job->address_street = $prop->address_street;
        $job->address_suburb = $prop->address_suburb;
        $job->address_postcode = $prop->address_postcode;

        if ($prop->people_id) {
          $dao = new people;
          if ($person = $dao->getByID($prop->people_id)) {
            $job->owner_name = $person->name;
            // \sys::logger(sprintf('<%s> %s', $person->name, __METHOD__));
          } else {
            \sys::logger(sprintf('<person not found %s> %s', $prop->people_id, __METHOD__));
          }
        }

        if ($prop->property_manager) {
          $dao = new users;
          if ($user = $dao->getByID($prop->property_manager)) {
            $job->property_manager = $user->name;
            $job->property_manager_id = $user->id;
            $job->property_manager_email = $user->email;
            $job->property_manager_mobile = $user->mobile;
            $job->property_manager_telephone = $user->telephone ?? '';
          } else {
            \sys::logger(sprintf('<property manager not found %s> %s', $prop->property_manager, __METHOD__));
          }
        } else {
          if (config::$CONSOLE_FALLBACK) {
            /**
             * Look the user up by the console_code,
             * this part will die a natural death
             */
            $dao = new console_properties;
            if ($cprop = $dao->getByPropertiesID($prop->id)) {
              if ($cprop->PropertyManager) {
                $sql = sprintf(
                  'SELECT
                    `id`, `name`, `email`, `mobile`, `telephone`
                  FROM
                    `users`
                  WHERE
                    `console_code` = %s',
                  $this->quote($cprop->PropertyManager)

                );

                if ($res = $this->Result($sql)) {
                  if ($user = $res->dto()) {
                    $job->property_manager = $user->name;
                    $job->property_manager_id = $user->id;
                    $job->property_manager_email = $user->email;
                    $job->property_manager_mobile = $user->mobile;
                    $job->property_manager_telephone = $user->telephone ?? '';
                  } else {
                    \sys::logger(sprintf('<property manager (console) not found %s> %s', $cprop->PropertyManager, __METHOD__));
                  }
                }
              } else {
                \sys::logger(sprintf('<property manager (console) not specifed> %s', __METHOD__));
              }
            } else {
              \sys::logger(sprintf('<property (console) not found> %s', __METHOD__));
            }
          } else {
            \sys::logger(sprintf('<property manager not specifed> %s', __METHOD__));
          }
        }
      }

      $dao = new \cms\keyregister\dao\keyregister;
      $job->keys = [];
      if ($keys = $dao->getKeysForProperty($job->properties_id)) {
        foreach ($keys as $key) {
          if (\cms\keyregister\config::keyset_management == $key->keyset_type) {
            $job->keys[] = $key;
          }
        }
      }
    }

    $job->brief = strings::brief($job->description);
    $job->status_verbatim = config::cms_job_status_verbatim($job->status);
    $job->type_verbatim = config::cms_job_type_verbatim($job->job_type);

    $dao = new users;
    if ($job->invoice_reviewed_by) {
      if ($udto = $dao->getByID($job->invoice_reviewed_by)) {
        $job->invoice_reviewed_by_name = $udto->name;
      }
    }

    if ($job->updated_by) {
      if ($udto = $dao->getByID($job->updated_by)) {
        $job->updated_by_name = $udto->name;
      }
    }

    if ($job->created_by) {
      if ($udto = $dao->getByID($job->created_by)) {
        $job->created_by_name = $udto->name;
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
    return $this->_store($job->id);
  }
}
