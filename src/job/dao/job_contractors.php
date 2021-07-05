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
use green\{people\dao\people as dao_people};
use strings;
// use sys;

class job_contractors extends _dao {
  protected $_db_name = 'job_contractors';
  protected $template = __NAMESPACE__ . '\dto\job_contractors';

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

  public function getReportSet() {
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
        LEFT JOIN people p ON p.id = c.primary_contact
      ORDER BY
        c.trading_name',
      $this->db_name()

    );

    // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));

    return $this->Result($sql);
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
}
