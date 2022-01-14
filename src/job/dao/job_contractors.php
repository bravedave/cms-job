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
use cms\job\config;
use dao\_dao;
use dao\dto\_dto;
use green\{people\dao\people as dao_people};
use strings;
// use sys;

class job_contractors extends _dao {
  protected $_db_name = 'job_contractors';
  protected $template = __NAMESPACE__ . '\dto\job_contractors';

  public function getAllOthers(int $id): array {  // this is for merge
    $sql = sprintf(
      'SELECT
        id,
        trading_name
      FROM `job_contractors` c
      WHERE c.id <> %d
      ORDER BY c.trading_name',
      $id

    );

    // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));
    if ($res = $this->Result($sql)) {
      return $res->dtoSet();
    }
    return [];
  }

  public function getByTradingName(string $name): ?dto\job_contractors {
    $sql = sprintf(
      "SELECT
        *
      FROM
        `%s`
      WHERE
        `trading_name` = %s",
      $this->db_name(),
      $this->quote($name)

    );

    // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));

    if ($res = $this->Result($sql)) {
      if ($dto = $res->dto($this->template)) {
        return $dto;
      }
    }

    return null;
  }

  public function getGetContractorsForItem(int $itemid): array {
    $dao = new job_items;
    if ($item = $dao->getByID($itemid)) {
      $sql = sprintf(
        'SELECT
          `id`, `services`
        FROM
          `job_contractors`
        WHERE `services` != %s',
        $this->quote('')
      );

      // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));

      if ($res = $this->Result($sql)) {
        $candidates = $res->dtoSet(function ($dto) use ($item) {
          $services = explode(',', $dto->services);
          if (in_array($item->job_categories_id, $services)) {
            return $dto;
          }

          return false;
        });

        $ids = array_map(function ($candidate) {
          return $candidate->id;
        }, $candidates);

        if ($ids) {
          $sql = sprintf(
            'SELECT
              c.id,
              c.trading_name,
              c.services,
              c.primary_contact,
              p.name,
              p.mobile,
              p.telephone,
              p.telephone_business,
              p.email,
              p.salutation
            FROM
              `%s` c
                LEFT JOIN
              people p ON p.id = c.primary_contact
            WHERE
              c.id IN (%s)
            ORDER BY
              c.trading_name',
            $this->db_name(),
            implode(',', $ids)

          );

          if ($res = $this->Result($sql)) {
            return $res->dtoSet();
          }
        }

        // \sys::logger(sprintf('<%s> %s', implode(',', $ids), __METHOD__));
      }
    }

    return [];
  }

  public function merge(int $source, int $target): void {
    $sql = sprintf(
      'SELECT id FROM `job` WHERE `contractor_id` = %d',
      $source
    );

    if ($res = $this->Result($sql)) {
      \sys::logger(sprintf('<%s => %s> %s', $source, $target, __METHOD__));

      $jdao = new job;
      $res->dtoSet(function ($dto) use ($target, $jdao) {
        \sys::logger(sprintf('<%s => %s> %s', $dto->id, $target, __METHOD__));
        $jdao->UpdateByID(
          ['contractor_id' => $target],
          $dto->id
        );
      });

      $this->delete($source);
    }
  }

  public function getReportSet(): array {
    $sql = sprintf(
      'SELECT
        c.id,
        c.trading_name,
        c.services,
        c.primary_contact,
        c.document_tags,
        p.name,
        p.mobile,
        p.telephone,
        p.telephone_business,
        p.email,
        p.salutation,
        jc.jobs
      FROM
        `job_contractors` c
          LEFT JOIN
        people p ON p.id = c.primary_contact
          LEFT JOIN
        (SELECT
          contractor_id, COUNT(contractor_id) jobs
        FROM
          `job`
        WHERE contractor_id > 0 AND (paid > %s OR complete = 1)
        GROUP BY contractor_id) jc ON jc.contractor_id = c.id
      ORDER BY
        c.trading_name',
      $this->quote('')

    );

    // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));

    if ($res = $this->Result($sql)) {
      return $res->dtoSet(function ($dto) {
        $dto->insurance = false;
        if ($dto->document_tags) {
          if ($tags = (array)json_decode($dto->document_tags)) {
            if ($tags[config::job_contractor_tag_insurance_certificate] ?? false) {
              if ($store = realpath($this->store($dto, $create = false))) {
                if ($file = realpath(implode(DIRECTORY_SEPARATOR, [
                  $store,
                  $tags[config::job_contractor_tag_insurance_certificate]
                ]))) {
                  $dto->insurance = file_exists($file);
                }
              }
            }
          }
        }
        return $dto;
      });
    }
    return [];
  }

  public function getRichData(dto\job_contractors $contractor): dto\job_contractors {
    if ($contractor->primary_contact) {
      $dao = new dao_people;
      if ($dto = $dao->getByID($contractor->primary_contact)) {
        $contractor->primary_contact_name = $dto->name;
        $contractor->primary_contact_email = $dto->email;
        $contractor->primary_contact_phone =
          strings::isMobilePhone($dto->mobile) ?
          $dto->mobile : $dto->telephone;
      }
    }

    return $contractor;
  }

  public function import_from_console() {
    if ($creditors = cms\console\db::creditors()) {

      // $this->Q( 'UPDATE `job_contractors` set `services` = ""');  // disable this !

      $stats = (object)[
        'new' => 0,
        'updated' => 0,
        'existing' => 0,
        'missingphone' => 0,

      ];

      $creditors->dtoSet(function ($dto) use ($stats) {
        $tradingname = trim($dto->FileAs);
        if (in_array($tradingname, ['Amalgamated Pest Control', 'HydroKleen'])) {
          $tradingname .= ' - ' . trim($dto->Reference);
        }

        if ($_dto = $this->getByTradingName($tradingname)) {
          $a = [];
          if ($_dto->abn != $dto->ABN) $a['abn'] = $dto->ABN;
          if ($dto->Dissection_FileAs) {
            $JCdao = new job_categories;
            if ($JCdto = $JCdao->getByCategory(trim($dto->Dissection_FileAs), $autoAdd = true)) {
              $services = $_dto->services ? explode(',', $_dto->services) : [];
              if (!(in_array($JCdto->id, $services))) {

                $services[] = (string)$JCdto->id;
                $a['services'] = implode(',', $services);

                // \sys::logger( sprintf('<%s?%s> <%s> <%s> %s', $JCdto->id, $_dto->services, $a['services'], print_r( $services, true), __METHOD__));
                // die;

              }
            }
          }

          if ($_dto->console_contact_id != $dto->ContactID) $a['console_contact_id'] = $dto->ContactID;
          $CCdao = new cms\console\dao\console_contacts;
          if ($CCdto = $CCdao->getByContactID($dto->ContactID)) {
            if (!$CCdto->people_id) {
              $CCdao->reconcile_person($CCdto);
              $CCdto = $CCdao->getByContactID($dto->ContactID);
            }

            if ($CCdto->people_id) {
              if ($_dto->primary_contact != $CCdto->people_id) {
                $a['primary_contact'] = $CCdto->people_id;
                \sys::logger(sprintf('<updated person id %s> %s', $CCdto->people_id, __METHOD__));
              }

              $Pdao = new dao_people;
              if ($Pdto = $Pdao->getByID($CCdto->people_id)) {
                if ($CCdto->Salutation != $Pdto->salutation) {
                  $Pdao->UpdateByID(['salutation' => $CCdto->Salutation], $Pdto->id);
                  \sys::logger(sprintf('<%s> %s', 'update salutation !', __METHOD__));
                }
              }
            }
          }

          if ($a) {
            $this->UpdateByID($a, $_dto->id);
            $stats->updated++;
            // \sys::logger( sprintf('<%s/%s> <%s> <Updating> %s', $_dto->id, $tradingname, print_r( $a, true), __METHOD__));

          } else {
            $stats->existing++;
            // \sys::logger( sprintf('<%s> <Existing> %s', $tradingname, __METHOD__));

          }
        } else {
          $a = [
            'trading_name' => $tradingname,
            'abn' => $dto->ABN,

          ];

          if ($dto->Dissection_FileAs) {
            $JCdao = new job_categories;
            if ($JCdto = $JCdao->getByCategory(trim($dto->Dissection_FileAs), $autoAdd = true)) {
              $a['services'] = (string)$JCdto->id;
            }
          }

          $CCdao = new cms\console\dao\console_contacts;
          if ($CCdto = $CCdao->getByContactID($dto->ContactID)) {
            $a['primary_contact'] = $CCdto->people_id;
          }
          $stats->new++;
          $this->Insert($a);
          // \sys::logger( sprintf('<%s> <NEW> %s', $tradingname, __METHOD__));

        }
      });

      \sys::logger(sprintf('<new:%s> <updated:%s> <existing:%s> %s', $stats->new, $stats->updated, $stats->existing, __METHOD__));
    }
  }

  public function search(string $term, string $services = ''): array {
    $sql = sprintf(
      'SELECT id, trading_name, trading_name `label`, services FROM `job_contractors` WHERE `trading_name` LIKE %s',
      $this->quote('%' . $term . '%')

    );

    if ($res = $this->Result($sql)) {
      if ($services) {
        $requires = explode(',', $services);
        $a = [];
        while ($dto = $res->dto()) {
          if ($dto->services) {
            $provides = explode(',', $dto->services);
            $yes = true;
            foreach ($requires as $req) {
              if (!in_array($req, $provides)) {
                $yes = false;
                break;
              }
            }

            if ($yes) $a[] = $dto;
          }
        }

        // \sys::logger( sprintf('<required %s #%s> %s', $services, count( $a), __METHOD__));
        return $a;
      }

      return $res->dtoSet();
    }

    return [];
  }

  public function store(_dto $dto, bool $create = false) {
    $path = implode(DIRECTORY_SEPARATOR, [
      config::cms_job_contractor_store(),
      $dto->id

    ]);

    if (!is_dir($path) && $create) {
      mkdir($path, 0777);
      chmod($path, 0777);
    }

    // \sys::logger( sprintf('<%s> %s', $path, __METHOD__));

    return $path;
  }
}
