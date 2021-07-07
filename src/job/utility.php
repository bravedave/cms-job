<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\job;

use application, cms, sys;
use dvc\{
  service,
  offertolease
};
use green;

class utility extends service {
  protected function _contractors_import() {
    $dao = new dao\job_contractors;

    $dao->import_from_console();
    echo (sprintf('%s: %s : %s%s', application::app()->timer()->elapsed(), 'import complete', __METHOD__, PHP_EOL));
  }

  protected function _contractors_reset() {
    $dao = new dao\job_contractors;
    $dao->Q('DROP TABLE IF EXISTS job_contractors');
    $dao->Q('DROP TABLE IF EXISTS job_categories');

    $dao = new dao\dbinfo;
    $dao->dump($verbose = false);
  }

  protected function _items_import() {
    $this->_items_reset();

    $dao = new dao\job_items;

    $dao->import_from_csv();
    echo (sprintf('%s: %s : %s%s', application::app()->timer()->elapsed(), 'import complete', __METHOD__, PHP_EOL));
  }

  protected function _items_reset() {
    $dbi = sys::dbi();
    $dbi->Q('DROP TABLE IF EXISTS job_categories');
    $dbi->Q('DROP TABLE IF EXISTS job_items');
    $dbi->Q(
      sprintf(
        'UPDATE `job_contractors` SET `services` = %s',
        $dbi->quote('')
      )
    );

    $dao = new dao\dbinfo;
    $dao->dump($verbose = false);
  }

  protected static function _devuser() {

    $db = \sys::dbi();

    $sql = sprintf(
      'SELECT `id` FROM users where `name` = %s',
      $db->quote('Administrator')

    );

    if (file_exists($passFile = sprintf('%s/dev-password.txt', config::dataPath()))) {
      if ($password = trim(file_get_contents($passFile))) {
        if ($res = $db->Result($sql)) {
          if ($dto = $res->dto()) {
            $a = [
              'password' => password_hash($password, PASSWORD_DEFAULT),
              'updated' => \db::dbTimeStamp()

            ];

            $db->Update(
              'users',
              $a,
              sprintf('WHERE `id` = %d', $dto->id)
            );
            \sys::logger('updated dev user');
          } else {
            $a = [
              'name' => 'Administrator',
              'email' => 'admin@domain.tld',
              'password' => password_hash($password, PASSWORD_DEFAULT),
              'active' => 1,
              'admin' => 1,
              'signature' => 'data:image/png;base64,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',
              'created' => \db::dbTimeStamp(),
              'updated' => \db::dbTimeStamp()

            ];

            $db->Insert('users', $a);
            \sys::logger('wrote dev user');
          }
          \sys::logger(sprintf('<u.admin@domain.tld/p.%s> %s', $password, __METHOD__));
        }
      } else {
        \sys::logger(sprintf('<please create a valid password file - %s> %s', $passFile, __METHOD__));
      }
    } else {
      \sys::logger(sprintf('<please create a password file - %s> %s', $passFile, __METHOD__));
    }
  }

  protected function _upgrade() {
    config::route_register('job', 'cms\\job\\controller');
    config::route_register('leasing', 'cms\\leasing\\controller');

    config::cms_job_checkdatabase();

    cms\keyregister\config::keyregister_checkdatabase();

    offertolease\config::offertolease_checkdatabase();

    green\baths\config::green_baths_checkdatabase();
    green\beds_list\config::green_beds_list_checkdatabase();

    green\properties\config::green_properties_checkdatabase();
    green\property_diary\config::green_property_diary_checkdatabase();
    green\property_type\config::green_property_type_checkdatabase();
    green\postcodes\config::green_postcodes_checkdatabase();
    green\users\config::green_users_checkdatabase();

    echo (sprintf('%s : %s%s', 'updated', __METHOD__, PHP_EOL));
  }

  protected function _upgrade_dev() {
    config::route_register('people', '');
    config::route_register('properties', 'green\\properties\\controller');
    config::route_register('beds', 'green\\beds_list\\controller');
    config::route_register('baths', 'green\\baths\\controller');
    config::route_register('property_type', 'green\\property_type\\controller');
    config::route_register('postcodes', 'green\\postcodes\\controller');
    config::route_register('users', 'green\\users\\controller');

    config::route_register('offertolease', 'dvc\\offertolease\\app');
    config::route_register('otl', 'dvc\\offertolease\\otlclient');
    config::route_register('banklink', 'cms\\banklink\controller');
    config::route_register('sms', 'sms\\controller');
    config::route_register('keyregister', 'cms\\keyregister\\controller');

    echo (sprintf('%s : %s%s', 'updated (dev)', __METHOD__, PHP_EOL));
  }

  static function devuser() {
    $app = new self(application::startDir());
    $app->_devuser();
  }

  static function contractors_import() {
    $app = new self(application::startDir());
    $app->_contractors_import();
  }

  static function contractors_reset() {
    $app = new self(application::startDir());
    $app->_contractors_reset();
  }

  static function items_import() {
    $app = new self(application::startDir());
    $app->_items_import();
  }

  static function items_reset() {
    $app = new self(application::startDir());
    $app->_items_reset();
  }

  static function upgrade() {
    $app = new self(application::startDir());
    $app->_upgrade();
  }

  static function upgrade_dev() {
    $app = new self(application::startDir());
    $app->_upgrade_dev();
  }
}
