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
	const cms_job_db_version = 1.3;

	const label = 'JOB';
	const label_contractor_add = 'New Contractor';
	const label_contractor_edit = 'Edit Contractor';
	const label_contractor = 'Contractor';
	const label_contractors = 'Contractors';
	const label_categories = 'Categories';
	const label_category = 'Category';
	const label_category_add = 'New Category';
	const label_category_edit = 'Edit Category';
	const label_category_acl = 'Create/edit JOB Categories';
	const label_invoiceto_edit = 'Invoice To Supplimental';
	const label_items = 'Items';
	const label_item = 'Item';
	const label_item_acl = 'Create/edit JOB Items';
	const label_item_add = 'New Item';
	const label_item_edit = 'Edit Item';
	const label_job = 'Job';
	const label_job_add = 'New Job';
	const label_job_edit = 'Edit Job';
	const label_job_viewworkorder = 'View Workorder';
	const label_matrix = 'JOB Matrix';

	const label_template_workorder = 'JOB Order';

	const PDF_title = [
		0 => 'JOB Order',
		1 => 'Recurring JOB Order',
		2 => 'JOB Quote Request'

	];

	const job_type_order = 0;
	const job_type_recurring = 1;
	const job_type_quote = 2;

	const job_types = [
		0 => 'Order',
		1 => 'Recurring',
		2 => 'Quote'

	];

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

	const job_templates = [
		'template-workorder-send'

	];

	static $CONSOLE_FALLBACK = true;

	static protected $_CMS_JOB_VERSION = 0;

	static protected $_CMS_JOB_INVOICE_TO = '';

	static protected function cms_job_version($set = null) {
		$ret = self::$_CMS_JOB_VERSION;

		if ((float)$set) {
			$config = self::cms_job_config();

			$j = file_exists($config) ?
				json_decode(file_get_contents($config)) :
				(object)[];

			self::$_CMS_JOB_VERSION = $j->cms_job_version = $set;

			if (file_exists($config)) unlink($config);
			file_put_contents($config, json_encode($j, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
			chmod($config, 0777);
		}

		return $ret;
	}

	static function cms_job_checkdatabase() {
		if (self::cms_job_version() < self::cms_job_db_version) {
			$dao = new dao\dbinfo;
			$dao->dump($verbose = false);

			config::cms_job_version(self::cms_job_db_version);
		}

		// sys::logger( 'bro!');

	}

	static function cms_job_config() {
		return implode(DIRECTORY_SEPARATOR, [
			self::cms_job_store(),
			'cms_job.json'

		]);
	}

	static function cms_job_invoiceto($set = null) {
		$ret = self::$_CMS_JOB_INVOICE_TO;

		if ((string)$set) {
			$config = self::cms_job_config();

			$j = file_exists($config) ?
				json_decode(file_get_contents($config)) :
				(object)[];

			self::$_CMS_JOB_INVOICE_TO = $j->cms_job_invoice_to = $set;

			if (file_exists($config)) unlink($config);
			file_put_contents($config, json_encode($j, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
			chmod($config, 0777);
		}

		return $ret;
	}

	static function cms_job_init() {
		if (file_exists($config = self::cms_job_config())) {
			$j = json_decode(file_get_contents($config));

			if (isset($j->cms_job_version)) {
				self::$_CMS_JOB_VERSION = (float)$j->cms_job_version;
			};

			if (isset($j->cms_job_invoice_to)) {
				self::$_CMS_JOB_INVOICE_TO = $j->cms_job_invoice_to;
			};

			if (isset($j->console_fallback)) {
				self::$CONSOLE_FALLBACK = $j->console_fallback;
			};
		}
	}

	static function cms_job_status_verbatim(int $status): string {
		if (config::job_status_new == $status) {
			return 'new';
		} elseif (config::job_status_quote == $status) {
			return 'quote';
		} elseif (config::job_status_assigned == $status) {
			return 'assigned';
		}

		return (string)$status;
	}

	static function cms_job_store(): string {
		$_path = method_exists(__CLASS__, 'cmsStore') ? self::cmsStore() : self::dataPath();
		$path = implode(DIRECTORY_SEPARATOR, [
			rtrim($_path, '/ '),
			'job'

		]);

		if (!is_dir($path)) {
			if (!is_dir($path)) {
				mkdir($path, 0777);
				chmod($path, 0777);
			}
		}

		return $path;
	}

	static function cms_job_template(string $template, string $text = null): string {
		$ret = '';
		if (\in_array($template, self::job_templates)) {
			$path = implode(DIRECTORY_SEPARATOR, [
				self::cms_job_store(),
				$template . '.text'

			]);

			if (file_exists($path)) {
				$ret = \file_get_contents($path);
			}

			if (!\is_null($text)) {
				file_put_contents($path, $text);
			}
		}

		return $ret;
	}

	static function cms_job_PDF_title(int $type): string {
		if (in_array($type, [0, 1, 2])) {
			return self::PDF_title[$type];
		}

		return self::PDF_title[0];
	}

	static function cms_job_type_verbatim(int $type): string {
		if (in_array($type, [0, 1, 2])) {
			return self::job_types[$type];
		}

		return self::job_types[0];
	}
}

config::cms_job_init();
