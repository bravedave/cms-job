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

class config extends \config {
	const cms_job_db_version = 0.9;

  const label = 'JOB';
  const label_contractor_add = 'New Contractor';
  const label_contractor_edit = 'Edit Contractor';
  const label_contractor = 'Contractor';
  const label_contractors = 'Contractors';
  const label_categories = 'Categories';
  const label_category = 'Category';
  const label_category_add = 'New Category';
  const label_category_edit = 'Edit Category';
  const label_items = 'Items';
  const label_item = 'Item';
  const label_item_add = 'New Item';
  const label_item_edit = 'Edit Item';
  const label_job = 'Job';
  const label_job_add = 'New Job';
  const label_job_edit = 'Edit Job';
  const label_matrix = 'JOB Matrix';

	const job_type_order = 0;
	const job_type_recurring = 1;
	const job_type_quote = 2;

	const job_payment_owner = 0;
	const job_payment_tenant = 1;

	const job_status = [
		0 => 'new',
		5 => 'quote',
		10 => 'assigned',

	];

	const job_status_new = 0;
	const job_status_quote = 5;
	const job_status_assigned = 10;

  static protected $_CMS_JOB_VERSION = 0;

	static protected function cms_job_version( $set = null) {
		$ret = self::$_CMS_JOB_VERSION;

		if ( (float)$set) {
			$config = self::cms_job_config();

			$j = file_exists( $config) ?
				json_decode( file_get_contents( $config)):
				(object)[];

			self::$_CMS_JOB_VERSION = $j->cms_job_version = $set;

			file_put_contents( $config, json_encode( $j, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

		}

		return $ret;

	}

	static function cms_job_checkdatabase() {
		if ( self::cms_job_version() < self::cms_job_db_version) {
      $dao = new dao\dbinfo;
			$dao->dump( $verbose = false);

			config::cms_job_version( self::cms_job_db_version);

		}

		// sys::logger( 'bro!');

	}

	static function cms_job_config() {
		$path = method_exists(__CLASS__, 'cmsStore') ? self::cmsStore() : self::dataPath();
		return implode( DIRECTORY_SEPARATOR, [
			rtrim( $path, '/ '),
			'cms_job.json'

		]);

	}

  static function cms_job_init() {
		if ( file_exists( $config = self::cms_job_config())) {
			$j = json_decode( file_get_contents( $config));

			if ( isset( $j->cms_job_version)) {
				self::$_CMS_JOB_VERSION = (float)$j->cms_job_version;

			};

		}

	}

}

config::cms_job_init();
