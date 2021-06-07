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

use application;
use dvc\service;
use green;

class utility extends service {
  protected function _contractors_import() {
    $dao = new dao\job_contractors;

    $dao->import_from_console();
    echo( sprintf('%s: %s : %s%s', application::app()->timer()->elapsed(), 'import complete', __METHOD__, PHP_EOL));

  }

  protected function _contractors_reset() {
    $dao = new dao\job_contractors;
    $dao->Q( 'DROP TABLE IF EXISTS job_contractors');
    $dao->Q( 'DROP TABLE IF EXISTS job_categories');

    $dao = new dao\dbinfo;
    $dao->dump($verbose = false);

  }

  protected function _upgrade() {
    config::route_register( 'job', 'cms\\job\\controller');

    config::cms_job_checkdatabase();

    green\baths\config::green_baths_checkdatabase();
    green\beds_list\config::green_beds_list_checkdatabase();

    green\properties\config::green_properties_checkdatabase();
    green\property_diary\config::green_property_diary_checkdatabase();
    green\property_type\config::green_property_type_checkdatabase();
    green\postcodes\config::green_postcodes_checkdatabase();
    green\users\config::green_users_checkdatabase();

    echo( sprintf('%s : %s%s', 'updated', __METHOD__, PHP_EOL));

  }

  protected function _upgrade_dev() {
    config::route_register('people', '');
    config::route_register('properties', 'green\\properties\\controller');
    config::route_register('beds', 'green\\beds_list\\controller');
    config::route_register('baths', 'green\\baths\\controller');
    config::route_register('property_type', 'green\\property_type\\controller');
    config::route_register('postcodes', 'green\\postcodes\\controller');
    config::route_register('users', 'green\\users\\controller');

    echo (sprintf('%s : %s%s', 'updated (dev)', __METHOD__, PHP_EOL));
  }

  static function upgrade_dev() {
    $app = new self(application::startDir());
    $app->_upgrade_dev();
  }

  static function contractors_import() {
    $app = new self( application::startDir());
    $app->_contractors_import();

  }

  static function contractors_reset() {
    $app = new self( application::startDir());
    $app->_contractors_reset();

  }

  static function upgrade() {
    $app = new self( application::startDir());
    $app->_upgrade();

  }

}
