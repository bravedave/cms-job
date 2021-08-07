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

use DateTime, DateInterval;
use currentUser, strings;
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

  protected function _getQuotePath(int $jobID): string {
    $path = implode(DIRECTORY_SEPARATOR, [
      $this->_store($jobID),
      'quote'

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
      $dto->has_quote = file_exists($this->getQuotePath($dto)) ? 1 : 0;

      if (!isset(config::job_status[$dto->status])) {
        $dto->status = 0;
      }

      if ($dto->job_type == config::job_type_quote) {
        if (1 == $dto->has_quote && $dto->status < config::job_status_quoted) {
          $dto->status = config::job_status_quoted; // auto advance status

        }
      } elseif (1 == $dto->has_invoice && $dto->status < config::job_status_reviewed) {
        if ($dto->paid && strtotime($dto->paid) > 0) {
          $dto->status = config::job_status_paid; // auto advance status
        } elseif ($dto->invoice_senttoowner_by) {
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
    $whereRecurring = [];

    if ((int)$pid) {
      $where[] = sprintf('job.`properties_id` = %d', $pid);
      $whereRecurring[] = sprintf('job.`properties_id` = %d', $pid);
    }

    if (!$archived) {
      $where[] = sprintf(
        '(COALESCE(job.`archived`,%s) = %s OR DATE( job.`archived`) <= %s)',
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

    $whereRecurring[] = sprintf('job.`job_type` = %s', config::job_type_recurring);
    $whereRecurring[] = 'job.`job_recurrence_disable` = 0';
    $whereRecurring = sprintf('WHERE %s', implode(' AND ', $whereRecurring));

    $sqlSeed = sprintf(
      'SELECT
        job.*,
        p.`address_street`,
        p.`street_index`,
        p.`property_manager`,
        c.`trading_name` `contractor_trading_name`,
        c.`primary_contact` `contractor_primary_contact`,
        people.`name` `contractor_primary_contact_name`,
        CASE
        WHEN p.`property_manager` > 0 THEN u.`name`
        ELSE %s
        END pm
      FROM
        `job`
          LEFT JOIN
        `properties` p on p.id = job.`properties_id`
          LEFT JOIN
        `users` u ON u.id = p.`property_manager`
          LEFT JOIN
        `job_contractors` c on c.id = job.`contractor_id`
          LEFT JOIN
        `people` ON people.id = c.`primary_contact`',
      $this->quote(''),
      $this->quote('')

    );

    if (config::$CONSOLE_FALLBACK) {
      $sqlSeed = sprintf(
        'SELECT
          job.*,
          p.`address_street`,
          p.`street_index`,
          p.`property_manager`,
          c.`trading_name` `contractor_trading_name`,
          c.`primary_contact` `contractor_primary_contact`,
          people.`name` `contractor_primary_contact_name`,
          CASE
          WHEN p.`property_manager` > 0 THEN u.`name`
          WHEN cp.`PropertyManager` > %s THEN uc.`name`
          ELSE %s
          END pm
        FROM
          `job`
            LEFT JOIN
          `properties` p on p.`id` = job.`properties_id`
            LEFT JOIN
          `users` u ON u.id = p.`property_manager`
            LEFT JOIN
          `job_contractors` c on c.`id` = job.`contractor_id`
            LEFT JOIN
          `people` ON people.id = c.`primary_contact`
            LEFT JOIN
          `console_properties` cp on cp.`properties_id` = p.`id`
            LEFT JOIN
          `users` uc ON uc.`console_code` = cp.`PropertyManager`',
        $this->quote(''),
        $this->quote('')

      );
    }

    $sql = implode(' ', [$sqlSeed, $where]);
    $this->Q(
      sprintf(
        'CREATE TEMPORARY TABLE `matrix` AS %s',
        $sql

      )

    );

    $sql = implode(' ', [$sqlSeed, $whereRecurring]);
    if ($res = $this->Result($sql)) {
      $res->dtoSet(function ($dto) {
        $ignore = [
          'id',
          'created',
          'updated',
          'due',
          'archived',
          'complete',
          'invoice_reviewed',
          'invoice_reviewed_by',
          'invoice_senttoowner',
          'invoice_senttoowner_by',
          'paid',
          'paid_by',
          'updated_by',
          'created_by',
          'email_sent',
          'email_sent_by',

        ];

        $a = [];
        foreach ($dto as $fld => $val) {
          if (in_array($fld, $ignore)) continue;

          $a[$fld] = $val;
        }

        $lookAhead = date('Y-m-d', strtotime(sprintf('+%s months', config::cms_job_recurrence_lookahead())));
        if ($dto->job_recurrence_interval == config::job_recurrence_interval_week) {
          if ((int)$dto->job_recurrence_week_frequency) {

            // \sys::logger(sprintf('<%s> %s', 'weekly', __METHOD__));

            if (strtotime($dto->due) > 0) {

              $interval = new DateInterval(sprintf('P%sW', (int)$dto->job_recurrence_week_frequency));
              $due = new DateTime($dto->due);
              $daysOfWeek = explode(',', $dto->job_recurrence_day_of_week);
              $due->add($interval);

              while ($due->format('Y-m-d') <= $lookAhead && (strtotime($dto->job_recurrence_end) < 1 || $due->format('Y-m-d') <= $dto->job_recurrence_end)) {

                for ($i = 0; $i < 7; $i++) {
                  $_time = strtotime(sprintf('+%s days', $i), $due->getTimestamp());
                  if (in_array(date('N', $_time), $daysOfWeek)) {
                    $a['due'] = date('Y-m-d', $_time);
                    $a['job_recurrence_parent'] = $dto->id;
                    $this->db->Insert('matrix', $a);

                    // \sys::logger(sprintf('<%s> %s', date('Y-m-d : D', $_time), __METHOD__));
                  }
                }
                $due->add($interval);
              }
              // } else {
              //   \sys::logger(sprintf('<%s> %s', 'due is missing', __METHOD__));
            }
          }
        } elseif ($dto->job_recurrence_interval == config::job_recurrence_interval_month) {
          if ((int)$dto->job_recurrence_month_frequency) {

            // \sys::logger(sprintf('<%s> %s', 'weekly', __METHOD__));

            if (strtotime($dto->due) > 0) {

              $interval = new DateInterval(sprintf('P%sM', (int)$dto->job_recurrence_month_frequency));
              $due = new DateTime($dto->due);
              $due->add($interval);

              while ($due->format('Y-m-d') <= $lookAhead && (strtotime($dto->job_recurrence_end) < 1 || $due->format('Y-m-d') <= $dto->job_recurrence_end)) {
                $_due = clone $due;
                if ($dto->job_recurrence_on_business_day) {
                  // \sys::logger(sprintf('<%s> %s', $_due->format('Y-m-d > D(N)'), __METHOD__));
                  if (6 == (int)$_due->format('N')) {
                    $_due->add(new DateInterval('P2D'));
                    // \sys::logger(sprintf('<..%s> %s', $due->format('N'), __METHOD__));

                  } elseif (7 == (int)$due->format('N')) {
                    $_due->add(new DateInterval('P1D'));
                    // \sys::logger(sprintf('<.%s> %s', $due->format('N'), __METHOD__));

                  }
                }

                $a['due'] = $_due->format('Y-m-d');
                $a['job_recurrence_parent'] = $dto->id;
                $this->db->Insert('matrix', $a);

                $due->add($interval);
              }
            }
          }
        } elseif ($dto->job_recurrence_interval == config::job_recurrence_interval_year) {
          if ((int)$dto->job_recurrence_year_frequency) {

            // \sys::logger(sprintf('<%s> %s', 'weekly', __METHOD__));

            if (strtotime($dto->due) > 0) {

              $interval = new DateInterval(sprintf('P%sY', (int)$dto->job_recurrence_year_frequency));
              $due = new DateTime($dto->due);
              $due->add($interval);

              while ($due->format('Y-m-d') <= $lookAhead && (strtotime($dto->job_recurrence_end) < 1 || $due->format('Y-m-d') <= $dto->job_recurrence_end)) {
                $_due = clone $due;
                if ($dto->job_recurrence_on_business_day) {
                  // \sys::logger(sprintf('<%s> %s', $_due->format('Y-m-d > D(N)'), __METHOD__));
                  if (6 == (int)$_due->format('N')) {
                    $_due->add(new DateInterval('P2D'));
                    // \sys::logger(sprintf('<..%s> %s', $due->format('N'), __METHOD__));

                  } elseif (7 == (int)$due->format('N')) {
                    $_due->add(new DateInterval('P1D'));
                    // \sys::logger(sprintf('<.%s> %s', $due->format('N'), __METHOD__));

                  }
                }

                $a['due'] = $_due->format('Y-m-d');
                $a['job_recurrence_parent'] = $dto->id;
                $this->db->Insert('matrix', $a);

                $due->add($interval);
              }
            }
          }
        } else {
        }

        // $file = sprintf(
        //   '%s/sql-temp.sql',
        //   config::dataPath()

        // );

        // file_put_contents($file, json_encode($a, JSON_PRETTY_PRINT));
        // \sys::logger(sprintf('<%s> %s', $file, __METHOD__));
        // \sys::logger(sprintf('<%s> %s', $dto->id, __METHOD__));
      });
    }

    // \sys::logSQL(sprintf('<%s> %s', $sql, __METHOD__));


    $this->Q('ALTER TABLE `matrix` ADD COLUMN `lines` TEXT');
    $this->Q('ALTER TABLE `matrix` ADD COLUMN `has_invoice` INT');
    $this->Q('ALTER TABLE `matrix` ADD COLUMN `has_quote` INT');

    $sql = 'SELECT
        `id`,
        `job_type`,
        `status`,
        `complete`,
        `invoice_reviewed_by`,
        `invoice_senttoowner_by`,
        `paid_by`,
        `properties_id`,
        `address_street`,
        `street_index`
      FROM
        `matrix`
      WHERE `id` > 0';

    if ($res = $this->Result($sql)) {
      $res->dtoSet(function ($dto) {
        $set = [];
        if ($dto->job_type == config::job_type_quote) {
          if (file_exists($path = $this->_getQuotePath($dto->id))) {
            $set[] = '`has_quote` = 1';
            if ($dto->status < config::job_status_quoted) {
              $dto->status = config::job_status_quoted; // auto advance status
              $set[] = sprintf('`status` = %s', $dto->status);
            }
          }
          // \sys::logger( sprintf('<%s><%s> %s', $this->_getQuotePath($dto->id), $dto->status, __METHOD__));

        } else if (file_exists($path = $this->_getInvoicePath($dto->id))) {
          $set[] = '`has_invoice` = 1';

          if ($dto->status < config::job_status_reviewed) {
            if ($dto->paid_by || $dto->invoice_senttoowner_by) {
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

    // get lines for jobs
    $sql =
      'SELECT
        m.*,
        jl.item_id,
        ji.item,
        ji.description
      FROM `matrix` m
        LEFT JOIN `job_lines` jl ON jl.job_id = m.id
        LEFT JOIN `job_items` ji ON ji.id = jl.item_id
      WHERE
        m.`id` > 0
      ORDER BY
        m.id, ji.job_categories_id';

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

    // get lines for recurring jobs from their parents
    $sql =
      'SELECT
        m.*,
        jl.item_id,
        ji.item,
        ji.description
      FROM
        (SELECT
          DISTINCT job_recurrence_parent FROM `matrix`
        WHERE
          COALESCE( matrix.`id`,0) = 0 AND matrix.`job_recurrence_parent` > 0
        ) m
        LEFT JOIN `job_lines` jl ON jl.job_id = m.job_recurrence_parent
        LEFT JOIN `job_items` ji ON ji.id = jl.item_id
      ORDER BY
        m.job_recurrence_parent, ji.job_categories_id';

    if ($res = $this->Result($sql)) {
      // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));

      $items = [];
      $res->dtoSet(function ($dto) use (&$items) {
        if ($dto->item || $dto->description) {
          if (!isset($items[$dto->job_recurrence_parent])) $items[$dto->job_recurrence_parent] = [];
          $items[$dto->job_recurrence_parent][] = (object)[
            'item' => $dto->item,
            'description' => $dto->description,

          ];
        }

        return $dto;
      });

      // \sys::logSQL(sprintf('<%s> %s', count($items), __METHOD__));
      foreach ($items as $k => $v) {
        $sql = sprintf(
          'UPDATE `matrix` SET `lines` = %s WHERE `job_recurrence_parent` = %d',
          $this->quote(json_encode($v)),
          $k

        );

        // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));
        $this->Q($sql);
      }
    }

    // $this->Result('DROP TABLE IF EXISTS _matrix');
    // $this->Result('CREATE TABLE _matrix AS SELECT * FROM `matrix`');
    return $this->Result('SELECT * FROM `matrix` ORDER BY `due` asc');
  }

  public function getQuotePath(dto\job $job): string {
    return $this->_getQuotePath($job->id);
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

    if ($job->invoice_senttoowner_by) {
      if ($udto = $dao->getByID($job->invoice_senttoowner_by)) {
        $job->invoice_senttoowner_by_name = $udto->name;
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

  public function recur(dto\job $job, $due): int {
    $a = [
      'updated' => \db::dbTimeStamp(),
      'updated_by' => currentuser::id(),
      'contractor_id' => $job->contractor_id,
      'properties_id' => $job->properties_id,
      'job_type' => $job->job_type,
      'job_recurrence_interval' => $job->job_recurrence_interval,
      'job_recurrence_end' => $job->job_recurrence_end,
      'job_recurrence_day_of_week' => $job->job_recurrence_day_of_week,
      'job_recurrence_day_of_month' => $job->job_recurrence_day_of_month,
      'job_recurrence_on_business_day' => $job->job_recurrence_on_business_day,
      'job_recurrence_week_frequency' => $job->job_recurrence_week_frequency,
      'job_recurrence_month_frequency' => $job->job_recurrence_month_frequency,
      'job_recurrence_year_frequency' => $job->job_recurrence_year_frequency,
      'job_recurrence_parent' => $job->id,
      'due' => $due,
      'job_payment' => $job->job_payment,
      'description' => $job->description,
      'on_site_contact' => $job->on_site_contact,

    ];

    $a['created'] = $a['updated'];
    $a['created_by'] = $a['updated_by'];
    $id = $this->Insert($a);

    $this->UpdateByID([
      'job_recurrence_disable' => 1,
      'job_recurrence_child' => $id
    ], $job->id);

    $sql = sprintf(
      'INSERT INTO
        job_lines(`job_id`,`item_id`,`updated`,`created`)
        SELECT
          %d, `item_id`, %s `updated`, %s `created`
        FROM
          `job_lines`
        WHERE
          `job_id` = %d',
      $id,
      $this->quote(\db::dbTimeStamp()),
      $this->quote(\db::dbTimeStamp()),
      $job->id

    );
    $this->Q($sql);

    return $id;
  }

  public function store(dto\job $job): string {
    return $this->_store($job->id);
  }
}
