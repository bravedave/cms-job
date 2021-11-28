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

use currentuser;
use FilesystemIterator;
use MatthiasMullie;
use cms\leasing, Json, Response, strings, sys;
use currentUser as GlobalCurrentUser;
use dvc\{
  fileUploader,
  session
};

use green\{
  people\dao\people as dao_people,
  search
};

class controller extends \Controller {
  protected $label = config::label;
  protected $viewPath = __DIR__ . '/views/';

  protected function _hasImage($img = '') {

    if (file_exists($__f = $this->viewPath . $img)) {
      return $__f;
    }

    return false;
  }

  protected function _index($view = '') {
    if (preg_match('@\.(png|jpg|svg)$@', $view) && $_img = $this->_hasImage($view)) {
      sys::serve($_img);
      return;
    }

    $this->matrix();
  }

  protected function before() {
    config::cms_job_checkdatabase();
    parent::before();
  }

  protected function postHandler() {
    $action = $this->getPost('action');

    if ('category-save' == $action) {

      if ($category = $this->getPost('category')) {
        $a = [
          'category' => $category

        ];

        $dao = new dao\job_categories;
        if ($id = (int)$this->getPost('id')) {
          $dao->UpdateByID($a, $id);
          Json::ack($action);
        } else {
          $dao->Insert($a);
          Json::ack($action);
        }
      } else {
        Json::nak($action);
      }
    } elseif ('category-delete' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job_categories;
        $dao->delete($id);

        Json::ack($action);
      } else {
        Json::nak($action);
      }
    } elseif ('check-has-invoice' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          Json::ack($action)
            ->add('invoice', file_exists($path = $dao->getInvoicePath($dto)) ? 'yes' : 'no');
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak(sprintf('%s - missing id', $action));
      }
    } elseif ('check-has-quote' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          Json::ack($action)
            ->add('quote', file_exists($path = $dao->getQuotePath($dto)) ? 'yes' : 'no');
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak(sprintf('%s - missing id', $action));
      }
    } elseif ('check-has-workorder' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          Json::ack($action)
            ->add('workorder', file_exists($path = $dao->getWorkOrderPath($dto)) ? 'yes' : 'no');
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak(sprintf('%s - missing id', $action));
      }
    } elseif ('confirm-recurrence' == $action) {
      if ($parent = (int)$this->getPost('job_recurrence_parent')) {
        if ((strtotime($due = $this->getPost('due'))) > 0) {
          $dao = new dao\job;
          if ($dtoParent = $dao->getByID($parent)) {
            if ($id = $dao->recur($dtoParent, $due)) {
              Json::ack($action)
                ->add('id', $id);
            } else {
              Json::nak(sprintf('%s - cannot find parent', $action));
            }
          } else {
            Json::nak(sprintf('%s - cannot find parent', $action));
          }
        } else {
          Json::nak($action);
        }
      } else {
        Json::nak($action);
      }
    } elseif ('comment-post' == $action) {
      $a = [
        'comment' => $this->getPost('comment'),
        'job_id' => $this->getPost('job_id'),
        'user_id' => currentUser::id(),
        'created' => \db::dbTimeStamp(),

      ];
      $a['updated'] = $a['created'];

      $dao = new dao\job_log;
      $dao->Insert($a);
      Json::ack($action);
    } elseif ('contractor-save' == $action) {
      $a = [
        'trading_name' => $this->getPost('trading_name'),
        'company_name' => $this->getPost('company_name'),
        'abn' => $this->getPost('abn'),
        'services' => $this->getPost('services'),
        'primary_contact' => $this->getPost('primary_contact'),
        'primary_contact_role' => $this->getPost('primary_contact_role'),

      ];

      $id = (int)$this->getPost('id');

      $dao = new dao\job_contractors;
      if ($id) {
        $dao->UpdateByID($a, $id);
      } else {
        $id = $dao->Insert($a);
      }
      Json::ack($action)
        ->add('id', $id);
    } elseif ('contractor-delete' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job_contractors;
        $dao->delete($id);
        Json::ack($action);
      } else {
        Json::nak($action);
      }
    } elseif ('contractor-merge' == $action) {
      if ($source = (int)$this->getPost('source')) {
        if ($target = (int)$this->getPost('target')) {
          $dao = new dao\job_contractors;
          $dao->merge($source, $target);
          Json::ack($action);
        } else {
          Json::nak(sprintf('missing target - %s', $action));
        }
      } else {
        Json::nak(sprintf('missing source- %s', $action));
      }
    } elseif ('create-workorder' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          $dto = $dao->getRichData($dto);

          $dto->tenants = [];
          if ($dto->properties_id) {
            $dao = new leasing\dao\tenants;
            $dto->tenants = $dao->getTenantsOfProperty($dto->properties_id);
          }

          if (workorder::create($dto)) {
            // $dao = new dao\job_categories;
            Json::ack($action);
            //   ->add('data', $dto);
            // ->add( 'services', $dao->getCategoriesOf($dto->services));
            // Json::nak(sprintf('%s - cool - but I\'m not ready', $action));
          } else {
            Json::nak(sprintf('%s - not found', $action));
          }
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak(sprintf('%s - missing id', $action));
      }
    } elseif ('get-contractor-by-id' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job_contractors;
        if ($dto = $dao->getByID($id)) {

          $dto = $dao->getRichData($dto);

          // $dao = new dao\job_categories;
          Json::ack($action)
            ->add('data', $dto);
          // ->add( 'services', $dao->getCategoriesOf($dto->services));

        } else {
          Json::nak($action);
        }
      } else {
        Json::nak($action);
      }
    } elseif ('get-items-of-category-distinctly' == $action) {
      if ($category = (int)$this->getPost('category')) {
        $dao = new dao\job_items;
        if ($items = $dao->getItemsForCategory($category, $distinct = true)) {
          Json::ack($action)
            ->add('data', $dao->dtoSet($items));
        } else {
          Json::nak($action);
        }
      } else {
        Json::nak($action);
      }
    } elseif ('get-items-of-category-item' == $action) {
      if ($category = (int)$this->getPost('category')) {
        if ($item = $this->getPost('item')) {
          $dao = new dao\job_items;
          if ($items = $dao->getItemsForCategory($category, $distinct = false, $item)) {
            Json::ack($action)
              ->add('data', $dao->dtoSet($items));
          } else {
            Json::ack($action)
              ->add('data', []);
          }
        } else {
          Json::nak($action);
        }
      } else {
        Json::nak($action);
      }
    } elseif ('get-keys' == $action) {
      /*
        (_ => {
          _.post({
            url : _.url('jobs'),
            data : {
              action : 'get-keys',
              id : 1

            },

          }).then( d => console.log(d));

        })(_brayworth_);
      */

      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          $dto = $dao->getRichData($dto);
          Json::ack($action)
            ->add('data', $dto->keys);
        } else {
          Json::nak($action);
        }
      } else {
        Json::nak($action);
      }
    } elseif ('get-keys-for-property' == $action) {
      /*
        (_ => {
          _.post({
            url : _.url('jobs'),
            data : {
              action : 'get-keys-for-property',
              id : 1

            },

          }).then( d => console.log(d));

        })(_brayworth_);
      */

      if ($id = (int)$this->getPost('id')) {
        $dao = new \cms\keyregister\dao\keyregister;
        $keys = [];
        if ($_keys = $dao->getKeysForProperty($id)) {
          foreach ($_keys as $_key) {
            if (\cms\keyregister\config::keyset_management == $_key->keyset_type) {
              $keys[] = $_key;
            }
          }
        }

        Json::ack($action)
          ->add('data', $keys);
      } else {
        Json::nak($action);
      }
    } elseif ('get-invoice-as-attachment' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {

          if (file_exists($src = $dao->getInvoicePath($dto))) {

            $dto = $dao->getRichData($dto);

            if (!($tmpdir = $this->getPost('tmpdir'))) {
              $tmpdir = strings::rand('email_') . '_' . time();
            }

            $_dir = config::tempdir() . $tmpdir;
            if (!is_dir($_dir)) {
              mkdir($_dir, 0777);
              chmod($_dir, 0777);
            }

            $target = sprintf('%s/%s', $_dir, basename($src));
            copy($src, $target);

            Json::ack($action)
              ->add('tmpdir', $tmpdir);
          } else {
            Json::nak(sprintf('%s - not found', $action));
          }
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak(sprintf('%s - missing id', $action));
      }
    } elseif ('get-workorder-and-attachment' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {

          if (file_exists($src = $dao->getWorkOrderPath($dto))) {

            $dto = $dao->getRichData($dto);

            if (!($tmpdir = $this->getPost('tmpdir'))) {
              $tmpdir = strings::rand('email_') . '_' . time();
            }

            $_dir = config::tempdir() . $tmpdir;
            if (!is_dir($_dir)) {
              mkdir($_dir, 0777);
              chmod($_dir, 0777);
            }

            $target = sprintf('%s/%s', $_dir, basename($src));
            copy($src, $target);

            Json::ack($action)
              ->add('tmpdir', $tmpdir)
              ->add(
                'subject',
                sprintf(
                  '%s - %s - %s',
                  $dto->address_street,
                  config::cms_job_PDF_title($dto->job_type),
                  workorder::reference($dto->id)
                )
              )
              ->add('text', workorder::expand_template($dto, config::cms_job_template('template-workorder-send')));
          } else {
            Json::nak(sprintf('%s - not found', $action));
          }
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak(sprintf('%s - missing id', $action));
      }
    } elseif ('get-workorder-text' == $action) {
      /*
        (_ => {
          _.post({
            url: _.url('<?= $this->route ?>'),
            data: {
              action: 'get-workorder-text',
              id : 3

            },

          }).then(d => console.log('ack' == d.response ? d.text : d));

        })(_brayworth_);
      */
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {

          $dto = $dao->getRichData($dto);

          Json::ack($action)
            ->add(
              'text',
              workorder::expand_template($dto, config::cms_job_template('template-workorder-send'))
            );
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak(sprintf('%s - missing id', $action));
      }
    } elseif ('invoiceto-save' == $action) {
      config::cms_job_invoiceto($this->getPost('invoiceto'));
      Json::ack($action);
    } elseif ('item-delete' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job_items;
        $dao->delete($id);
        Json::ack($action);
      } else {
        Json::nak($action);
      }
    } elseif ('item-mark-active' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job_items;
        if ($dto = $dao->getByID($id)) {
          $dao->UpdateByID(['inactive' => 0], $id);
          Json::ack($action);
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('item-mark-inactive' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job_items;
        if ($dto = $dao->getByID($id)) {
          $dao->UpdateByID(['inactive' => 1], $id);
          Json::ack($action);
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('item-save' == $action) {

      if ($description = $this->getPost('description')) {
        $a = [
          'description' => $description,
          'item' => $this->getPost('item'),
          'job_categories_id' => $this->getPost('job_categories_id'),
          'inactive' => 'yes' != $this->getPost('active') ? 1 : 0

        ];

        $dao = new dao\job_items;
        if ($id = (int)$this->getPost('id')) {
          $dao->UpdateByID($a, $id);
          Json::ack($action);
        } else {
          $dao->Insert($a);
          Json::ack($action);
        }
      } else {
        Json::nak($action);
      }
    } elseif ('job-archive' == $action || 'job-archive-undo' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          $dao->UpdateByID([
            'archived' => 'job-archive-undo' == $action ? '' : \db::dbTimeStamp()

          ], $id);

          Json::ack($action);
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('job-line-delete' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job_lines;
        $dao->delete($id);

        Json::ack($action);
      } else {
        Json::nak($action);
      }
    } elseif ('job-delete' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        $dao->delete($id);

        Json::ack($action);
      } else {
        Json::nak($action);
      }
    } elseif ('job-duplicate' == $action || 'job-invoke-order' == $action) {

      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          $a = [
            'contractor_id' => $dto->contractor_id,
            'properties_id' => $dto->properties_id,
            'job_type' => 'job-invoke-order' == $action ? config::job_type_order : $dto->job_type,
            'status' => config::job_status_new,
            'due' => $dto->due,
            'job_payment' => $dto->job_payment,
            'description' => $dto->description,
            'on_site_contact' => $dto->on_site_contact,
            'source_job' => $dto->id

          ];

          $a['updated'] = $a['created'] = \db::dbTimeStamp();
          $newID = $dao->Insert($a);

          $dao = new dao\job_lines;
          if ($lines = $dao->getLinesOfJobID($dto->id)) {
            foreach ($lines as $line) {
              $a = [
                'item_id' => $line->item_id,
                'job_id' => $newID,

              ];

              $a['updated'] = $a['created'] = \db::dbTimeStamp();
              $dao->Insert($a);
            }
          }

          if ('job-invoke-order' == $action) {
            $dao = new dao\job;
            $dao->UpdateByID([
              'archived' => \db::dbTimeStamp()

            ], $dto->id);
          }

          Json::ack($action)
            ->add('id', $newID);
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('job-invoice-delete' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          if (file_exists($path = $dao->getInvoicePath($dto))) {
            unlink($path);
          }
          Json::ack($action);
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('job-quote-delete' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          if (file_exists($path = $dao->getQuotePath($dto))) {
            unlink($path);
          }
          Json::ack($action);
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('job-mark-complete' == $action || 'job-mark-complete-undo' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          $a = [
            'complete' => 'job-mark-complete-undo' == $action ? 0 : 1
          ];
          if ('job-mark-complete' == $action && (config::job_type_quote == $dto->job_type || config::job_payment_none == $dto->job_payment)) {
            $a['archived'] = \db::dbTimeStamp();
          }
          $dao->UpdateByID($a, $dto->id);
          Json::ack($action);
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('job-mark-invoice-reviewed' == $action || 'job-mark-invoice-reviewed-undo' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          $a = 'job-mark-invoice-reviewed-undo' == $action ?
            [
              'invoice_reviewed' => '',
              'invoice_reviewed_by' => 0

            ]
            :
            [
              'invoice_reviewed' => \db::dbTimeStamp(),
              'invoice_reviewed_by' => currentuser::id()

            ];

          $dao->UpdateByID($a, $dto->id);
          Json::ack($action);
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('job-mark-invoice-senttoowner' == $action || 'job-mark-invoice-senttoowner-undo' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          $a = 'job-mark-invoice-senttoowner-undo' == $action ?
            [
              'invoice_senttoowner' => '',
              'invoice_senttoowner_by' => 0

            ]
            :
            [
              'archived' => \db::dbTimeStamp(),
              'invoice_senttoowner' => \db::dbTimeStamp(),
              'invoice_senttoowner_by' => currentuser::id()

            ];

          $dao->UpdateByID($a, $dto->id);
          Json::ack($action);
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('job-mark-paid' == $action || 'job-mark-paid-undo' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          $a = 'job-mark-paid-undo' == $action ?
            [
              'paid' => '',
              'paid_by' => 0

            ]
            :
            [
              'archived' => \db::dbTimeStamp(),
              'paid' => \db::dbTimeStamp(),
              'paid_by' => currentuser::id()

            ];

          $dao->UpdateByID($a, $dto->id);
          Json::ack($action);
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('job-mark-paid-selected' == $action) {
      if ($ids = $this->getPost('ids')) {
        $ids = explode(',', $ids);
        $dao = new dao\job;
        foreach ($ids as $id) {
          \sys::logger(sprintf('<%s #%s> %s', $action, $id, __METHOD__));
          $a = [
            'archived' => \db::dbTimeStamp(),
            'paid' => \db::dbTimeStamp(),
            'paid_by' => currentuser::id()

          ];
          $dao->UpdateByID($a, $id);
        }

        Json::ack($action);
      } else {
        Json::nak($action);
      }
    } elseif ('job-save' == $action) {

      if ($description = $this->getPost('description')) {
        $a = [
          'updated' => \db::dbTimeStamp(),
          'updated_by' => currentuser::id(),
          'contractor_id' => (int)$this->getPost('contractor_id'),
          'properties_id' => (int)$this->getPost('properties_id'),
          'job_type' => (int)$this->getPost('job_type'),
          'job_recurrence_interval' => (int)$this->getPost('job_recurrence_interval'),
          'job_recurrence_end' => date('Y-m-d', strtotime($this->getPost('job_recurrence_end'))),
          'job_recurrence_day_of_week' => implode(',', (array)$this->getPost('job_recurrence_day_of_week')),
          'job_recurrence_day_of_month' => implode(',', (array)$this->getPost('job_recurrence_day_of_month')),
          'job_recurrence_on_business_day' => (int)$this->getPost('job_recurrence_on_business_day'),
          'job_recurrence_week_frequency' => (int)$this->getPost('job_recurrence_week_frequency'),
          'job_recurrence_month_frequency' => (int)$this->getPost('job_recurrence_month_frequency'),
          'job_recurrence_year_frequency' => (int)$this->getPost('job_recurrence_year_frequency'),
          'due' => $this->getPost('due'),
          'job_payment' => (int)$this->getPost('job_payment'),
          'description' => (string)$this->getPost('description'),
          'on_site_contact' => (string)$this->getPost('on_site_contact'),

        ];

        $dao = new dao\job;
        if ($id = (int)$this->getPost('id')) {
          $dao->UpdateByID($a, $id);
        } else {
          $a['created'] = $a['updated'];
          $a['created_by'] = $a['updated_by'];
          $id = $dao->Insert($a);
        }

        if ($item_id = $this->getPost('item_id')) {
          $job_line_id = $this->getPost('job_line_id');

          for ($i = 0; $i < count($item_id); $i++) {
            $a = [
              'updated' => \db::dbTimeStamp(),
              'item_id' => $item_id[$i],
              'job_id' => $id,

            ];

            $dao = new dao\job_lines;
            if (isset($job_line_id[$i]) && $job_line_id[$i]) {
              $dao->UpdateByID($a, $job_line_id[$i]);
            } else {
              $a['created'] = $a['updated'];
              $dao->Insert($a);
            }
          }
        }

        Json::ack($action)
          ->add('id', $id);
      } else {
        Json::nak($action);
      }
    } elseif ('job-save-bump' == $action) {

      if ($id = (int)$this->getPost('id')) {

        $bump = $this->getPost('bump');
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          if (strtotime($bump) > strtotime($dto->due)) {
            $a = [
              'updated' => \db::dbTimeStamp(),
              'updated_by' => currentuser::id(),
              'due' => $this->getPost('bump'),

            ];

            $dao->UpdateByID($a, $id);

            $a = [
              'comment' => sprintf(
                'Bump %s => %s',
                strings::asLocalDate($dto->due),
                strings::asLocalDate($bump)
              ),
              'job_id' => $dto->id,
              'user_id' => currentUser::id(),
              'created' => \db::dbTimeStamp(),

            ];
            $a['updated'] = $a['created'];

            $dao = new dao\job_log;
            $dao->Insert($a);

            Json::ack($action)
              ->add('id', $id);
          } else {
            Json::nak(sprintf('cannot bump backwards : %s', $action));
          }
        } else {
          Json::nak(sprintf('job not found : %s', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('job-save-payment' == $action) {

      if ($id = (int)$this->getPost('id')) {
        $a = [
          'updated' => \db::dbTimeStamp(),
          'updated_by' => currentuser::id(),
          'job_payment' => (int)$this->getPost('job_payment'),

        ];

        $dao = new dao\job;
        $dao->UpdateByID($a, $id);

        Json::ack($action)
          ->add('id', $id);
      } else {
        Json::nak($action);
      }
    } elseif ('job-save-recurrence' == $action) {

      if ($id = (int)$this->getPost('id')) {
        $a = [
          'updated' => \db::dbTimeStamp(),
          'updated_by' => currentuser::id(),
          'job_recurrence_interval' => (int)$this->getPost('job_recurrence_interval'),
          'job_recurrence_end' => date('Y-m-d', strtotime($this->getPost('job_recurrence_end'))),
          'job_recurrence_day_of_week' => implode(',', (array)$this->getPost('job_recurrence_day_of_week')),
          'job_recurrence_day_of_month' => implode(',', (array)$this->getPost('job_recurrence_day_of_month')),
          'job_recurrence_on_business_day' => (int)$this->getPost('job_recurrence_on_business_day'),
          'job_recurrence_week_frequency' => (int)$this->getPost('job_recurrence_week_frequency'),
          'job_recurrence_month_frequency' => (int)$this->getPost('job_recurrence_month_frequency'),
          'job_recurrence_year_frequency' => (int)$this->getPost('job_recurrence_year_frequency'),
          'job_payment' => (int)$this->getPost('job_payment'),

        ];

        $dao = new dao\job;
        $dao->UpdateByID($a, $id);

        Json::ack($action)
          ->add('id', $id);
      } else {
        Json::nak($action);
      }
    } elseif ('mark-sent' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          $dao->UpdateByID([
            'email_sent' => \db::dbTimeStamp(),
            'email_sent_by' => currentuser::id()
          ], $id);
          Json::ack($action);
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('mark-sent-undo' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          $dao->UpdateByID([
            'email_sent' => '',
            'email_sent_by' => 0
          ], $id);
          Json::ack($action);
        } else {
          Json::nak(sprintf('%s - not found', $action));
        }
      } else {
        Json::nak($action);
      }
    } elseif ('matrix-include-archives' == $action || 'matrix-include-archives-undo' == $action) {
      session::edit();
      session::set('job-matrix-archived', 'matrix-include-archives' == $action ? 'yes' : null);
      session::close();

      Json::ack($action);
    } elseif ('matrix-refresh-row' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job;
        if ($dto = $dao->getByID($id)) {
          $dto = $dao->getRichData($dto);
          Json::ack($action)
            ->add('data', $dto);
        } else {
          Json::nak($action);
        }
      } else {
        Json::nak($action);
      }
    } elseif ('merge-jobs' == $action) {
      if ($src = (int)$this->getPost('src')) {
        if ($target = (int)$this->getPost('target')) {
          $dao = new dao\job;
          if ($dtoSrc = $dao->getByID($src)) {
            if ($dtoTarget = $dao->getByID($target)) {
              $dao->merge($dtoSrc, $dtoTarget);
              Json::ack($action)
                ->add('target', $dtoTarget->id);
            } else {
              Json::nak(sprintf('target not found : %s', $action));
            }
          } else {
            Json::nak(sprintf('source not found : %s', $action));
          }
        } else {
          Json::nak(sprintf('invalid target : %s', $action));
        }
      } else {
        Json::nak(sprintf('invalid source : %s', $action));
      }
    } elseif ('search-contractor' == $action) {
      if ($term = $this->getPost('term')) {
        $dao = new dao\job_contractors;
        Json::ack($action)
          ->add('term', $term)
          ->add('data', $dao->search($term, $this->getPost('services')));
      } else {
        Json::nak($action);
      }
    } elseif ('search-job-items' == $action) {
      if ($term = $this->getPost('term')) {
        $dao = new dao\job_items;
        Json::ack($action)
          ->add('term', $term)
          ->add('data', $dao->search($term));
      } else {
        Json::nak($action);
      }
    } elseif ('search-properties' == $action) {
      if ($term = $this->getPost('term')) {
        Json::ack($action)
          ->add('term', $term)
          ->add('data', search::properties($term));
      } else {
        Json::nak($action);
      }
    } elseif ('set-job-recurrence-lookahead' == $action) {
      config::cms_job_recurrence_lookahead((int)$this->getPost('months'));
      Json::ack($action)
        ->add('months', config::cms_job_recurrence_lookahead());
    } elseif ('set-primary-contact' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job_contractors;
        $dao->UpdateByID([
          'primary_contact' => (int)$this->getPost('people_id')
        ], $id);

        Json::ack($action);
      } else {
        Json::nak($action);
      }
    } elseif ('template-save' == $action) {
      $template = $this->getPost('template');
      if (\in_array($template, config::job_templates)) {
        $text = $this->getPost('text');
        config::cms_job_template($template, $text);
        Json::ack($action);
      } else {
        Json::nak($action);
      }
    } elseif ('upload-invoice' == $action) {
      if ($_FILES) {
        if ($id = (int)$this->getPost('id')) {
          $dao = new dao\job;
          if ($dto = $dao->getByID($id)) {
            if ($store = $dao->store($dto)) {
              foreach ($_FILES as $file) {
                $uploader = new fileUploader([
                  'path' => $store,
                  'accept' => [
                    'image/png',
                    'image/x-png',
                    'image/jpeg',
                    'image/pjpeg',
                    'application/pdf'

                  ]

                ]);

                if ($uploader->save(
                  $file,
                  $name = 'invoice',
                  $delete = ['invoice.png', 'invoice.jpg', 'invoice.jpeg', 'invoice.pdf']
                )) {
                  Json::ack($action);
                } else {
                  Json::nak($action);
                }

                break;  // only 1 file

              }
            } else {
              Json::nak($action);
            }
          } else {
            Json::nak($action);
          }
        } else {
          Json::nak($action);
        }
      } else {
        Json::nak($action);
      }
    } elseif ('upload-quote' == $action) {
      if ($_FILES) {
        if ($id = (int)$this->getPost('id')) {
          $dao = new dao\job;
          if ($dto = $dao->getByID($id)) {
            if ($store = $dao->store($dto)) {
              foreach ($_FILES as $file) {
                $uploader = new fileUploader([
                  'path' => $store,
                  'accept' => [
                    'image/png',
                    'image/x-png',
                    'image/jpeg',
                    'image/pjpeg',
                    'application/pdf'

                  ]

                ]);

                if ($uploader->save(
                  $file,
                  $name = 'quote',
                  $delete = ['quote.png', 'quote.jpg', 'quote.jpeg', 'quote.pdf']
                )) {
                  Json::ack($action);
                } else {
                  Json::nak($action);
                }

                break;  // only 1 file

              }
            } else {
              Json::nak($action);
            }
          } else {
            Json::nak($action);
          }
        } else {
          Json::nak($action);
        }
      } else {
        Json::nak($action);
      }
    } else {
      parent::postHandler();
    }
  }

  protected function render($params) {
    $params = \array_merge([
      'scripts' => [],

    ], $params);

    $params['scripts'][] = sprintf('<script type="text/javascript" src="%s"></script>', strings::url($this->route . '/js/job'));

    parent::render($params);
  }

  public function about() {
    $this->render([
      'title' => $this->title = $this->label,
      'primary' => '_news',
      'secondary' => 'index',
      'data' => (object)[
        'pageUrl' => $this->route . '/about'

      ],

    ]);
  }

  public function bump($id) {
    if ($id = (int)$id) {
      $dao = new dao\job;
      if ($dto = $dao->getByID($id)) {
        $dto = $dao->getRichData($dto);

        // \sys::logger(sprintf('<found street> <%s> %s', $dto->address_street, __METHOD__));

        $this->data = (object)[
          'title' => $this->title = config::label_job_bump,
          'dto' => $dto,

        ];

        $this->load('job-bump');
      } else {
        $this->load('not-found');
      }
    }
  }

  public function categories() {
    $dao = new dao\job_categories;
    $this->data = (object)[
      'res' => $dao->getAll()

    ];

    $this->render([
      'title' => $this->title = config::label_categories,
      'primary' => 'categories',
      'secondary' => 'index',
      'data' => (object)[
        'searchFocus' => false,
        'pageUrl' => strings::url(sprintf('%s/categories', $this->route))

      ],

    ]);
  }

  public function category_edit($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job_categories;
      if ($dto = $dao->getByID($id)) {
        $this->data = (object)[
          'dto' => $dto

        ];

        $this->title = config::label_category_edit;
        $this->load('category-edit');
      } else {
        $this->load('not-found');
      }
    } else {
      $this->data = (object)[
        'dto' => new dao\dto\job_categories

      ];

      $this->title = config::label_category_add;
      $this->load('category-edit');
    }
  }

  public function contractors() {
    $dao = new dao\job_contractors;
    $this->data = (object)[
      'res' => $dao->getReportSet(),
      'categories' => dao\job_categories::getCategorySet(),
      'idx' => $this->getParam('idx'),

    ];

    $this->render([
      'title' => $this->title = config::label_contractors,
      'primary' => 'contractors',
      'secondary' => 'index',
      'data' => (object)[
        'searchFocus' => false,
        'pageUrl' => strings::url(sprintf('%s/contractors', $this->route))

      ],

    ]);
  }

  public function contractorsfor($item) {
    $dao = new dao\job_contractors;
    $this->data = (object)[
      'title' => $this->title = 'Contractors for ..',
      'contractors' => $dao->getGetContractorsForItem($item),
      'categories' => dao\job_categories::getCategorySet()

    ];

    $this->load('contractors-for');
  }

  public function contractor_edit($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job_contractors;
      if ($dto = $dao->getByID($id)) {
        $this->data = (object)[
          'dto' => $dto,
          'primary_contact' => false,
          'categories' => dao\job_categories::getCategorySet()

        ];

        if ($dto->primary_contact) {
          $dao = new dao_people;
          if ($dto = $dao->getByID($dto->primary_contact)) {
            $this->data->primary_contact = $dto;
          }
        }

        $this->title = config::label_contractor_edit;
        $this->load('contractor-edit');
      } else {
        $this->load('not-found');
      }
    } else {
      $this->title = config::label_contractor_add;

      $this->data = (object)[
        'dto' => new dao\dto\job_contractors,
        'primary_contact' => false,
        'categories' => dao\job_categories::getCategorySet()

      ];

      $this->load('contractor-edit');
    }
  }

  public function contractor_merge($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job_contractors;
      if ($dto = $dao->getByID($id)) {
        $this->data = (object)[
          'title' => $this->title = config::label_contractor_merge,
          'dto' => $dto,
          'allOthers' => $dao->getAllOthers($id)
        ];

        $this->load('contractor-merge.php');
      } else {
        $this->load('not-found');
      }
    } else {
      $this->load('not-found');
    }
  }

  public function comment() {
    if ($id = (int)$this->getParam('property')) {
      $dao = new dao\job;
      if ($dto = $dao->getByID($id)) {
        $dto = $dao->getRichData($dto);
        $this->data = (object)[
          'title' => $this->title = 'Add Comment',
          'job' => $dto

        ];

        $this->load('comment');
      } else {
        $this->load('not-found');
      }
    } else {
      $this->load('not-found');
    }
  }

  public function job_edit($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job;
      if ($dto = $dao->getByID($id)) {
        $dto = $dao->getRichData($dto);

        // \sys::logger(sprintf('<found street> <%s> %s', $dto->address_street, __METHOD__));

        $this->data = (object)[
          'title' => $this->title = config::label_job_edit,
          'dto' => $dto,
          'log' => dao\job_log::getForJob($dto),
          'categories' => dao\job_categories::getCategorySet(),
          'hasWorkorder' => file_exists($path = $dao->getWorkOrderPath($dto)),
          'hasInvoice' => file_exists($path = $dao->getInvoicePath($dto)),
          'hasQuote' => file_exists($path = $dao->getQuotePath($dto)),

        ];

        $this->load('job-edit');
      } else {
        $this->load('not-found');
      }
    } else {
      $this->data = (object)[
        'title' => $this->title = config::label_job_add,
        'dto' => new dao\dto\job,
        'log' => [],
        'categories' => dao\job_categories::getCategorySet(),
        'hasWorkorder' => false,
        'hasInvoice' => false,

      ];

      $this->load('job-edit');
    }
  }

  public function index($view = '') {
    $this->isPost() ?
      $this->postHandler() :
      $this->_index($view);
  }

  public function invoiceto_edit() {
    $this->title = config::label_invoiceto_edit;
    $this->load('invoiceto-edit');
  }

  public function matrix() {
    $archived = 'yes' == session::get('job-matrix-archived');

    $dao = new dao\job;
    $this->data = (object)[
      'title' => $this->title = config::label_matrix,
      'res' => $dao->getMatrix($archived),
      'idx' => $this->getParam('idx'),
      'trigger' => $this->getParam('v'),
      'archived' => $archived,
      'hidepropertycolumn' => false,
      'property' => false,
      'showRefreshIcon' => false
    ];

    $this->render([
      'primary' => 'matrix',
      'secondary' => 'index',
      'data' => (object)[
        'searchFocus' => false,
        'pageUrl' => strings::url(sprintf('%s/matrix', $this->route))

      ],

    ]);
  }

  public function matrixOfProperty($pid) {
    $archived = 'yes' == session::get('job-matrix-archived');

    $property = false;
    if ($pid = (int)$pid) {
      $dao = new \dao\properties;
      $property = $dao->getByID($pid);
    }

    $dao = new dao\job;
    $this->data = (object)[
      'title' => $this->title = config::label_matrix,
      'res' => $dao->getMatrix($archived, $pid),
      'idx' => $this->getParam('idx'),
      'trigger' => $this->getParam('v'),
      'archived' => $archived,
      'hidepropertycolumn' => true,
      'property' => $property,
      'showRefreshIcon' => true
    ];

    $this->load('matrix');
  }

  public function invoice($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job;
      if ($dto = $dao->getByID($id)) {
        $dto = $dao->getRichData($dto);

        $this->data = (object)[
          'title' => $this->title = 'View Invoice',
          'dto' => $dto,
          'hasWorkorder' => file_exists($path = $dao->getWorkOrderPath($dto)),

        ];

        $this->load('job-view-invoice');
      } else {
        $this->load('not-found');
      }
    } else {
      $this->load('invalid');
    }
  }

  public function invoiceview($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job;
      if ($dto = $dao->getByID($id)) {
        if (file_exists($path = $dao->getInvoicePath($dto))) {
          sys::serve($path);
        } else {
          print file_get_contents(__DIR__ . '/views/not-found.html');
        }
      } else {
        print file_get_contents(__DIR__ . '/views/not-found.html');
      }
    } else {
      print file_get_contents(__DIR__ . '/views/invalid.html');
    }
  }

  public function items() {
    $dao = new dao\job_items;
    $this->data = (object)[
      'res' => $dao->getAll(),
      'categories' => dao\job_categories::getCategorySet()

    ];

    $this->render([
      'title' => $this->title = config::label_items,
      'primary' => 'items',
      'secondary' => 'index',
      'data' => (object)[
        'searchFocus' => false,
        'pageUrl' => strings::url(sprintf('%s/items', $this->route))

      ],

    ]);
  }

  public function item_edit($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job_items;
      if ($dto = $dao->getByID($id)) {
        $this->data = (object)[
          'dto' => $dto,
          'categories' => dao\job_categories::getCategorySet()

        ];

        $this->title = config::label_item_edit;
        $this->load('item-edit');
      } else {
        $this->load('not-found');
      }
    } else {
      $this->title = config::label_item_add;

      $this->data = (object)[
        'dto' => new dao\dto\job_items,
        'categories' => dao\job_categories::getCategorySet()

      ];

      $this->load('item-edit');
    }
  }

  public function js(string $lib = '') {
    if ('job' == $lib) {
      $s = [];
      $r = [];

      $s[] = '@{{route}}@';
      $r[] = $this->route;

      $js = [];
      $files = new FilesystemIterator(__DIR__ . '/js/');
      foreach ($files as $file) $js[] = file_get_contents($file->getRealPath());

      $js = implode("\n", $js);
      $js = preg_replace($s, $r, $js);

      Response::javascript_headers();
      if (false) {
        // if ($this->Request->ClientIsLocal()) {
        print $js;
      } else {
        $minifier = new MatthiasMullie\Minify\JS;
        $minifier->add($js);
        print $minifier->minify();
      }
    } else {
      parent::js($lib);
    }
  }

  public function merge($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job;
      if ($dto = $dao->getByID($id)) {
        if ($dto->contractor_id) {
          $dto = $dao->getRichData($dto);

          $this->data = (object)[
            'title' => $this->title = config::label_job_merge,
            'dto' => $dto,
            'otherjobs' => $dao->getForContractor($dto->contractor_id, $exclude = $dto->id),

          ];

          if ($this->data->otherjobs) {
            $this->load('merge');
          } else {
            $this->load('job-has-no-merge-possibilities');
          }
        } else {
          $this->load('job-has-no-contractor');
        }
      } else {
        $this->load('not-found');
      }
    } else {
      $this->load('invalid');
    }
  }

  public function quote($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job;
      if ($dto = $dao->getByID($id)) {
        $dto = $dao->getRichData($dto);

        $this->data = (object)[
          'title' => $this->title = 'View Quote',
          'dto' => $dto,
          'hasWorkorder' => file_exists($path = $dao->getWorkOrderPath($dto)),

        ];

        $this->load('job-view-quote');
      } else {
        $this->load('not-found');
      }
    } else {
      $this->load('invalid');
    }
  }

  public function quoteview($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job;
      if ($dto = $dao->getByID($id)) {
        if (file_exists($path = $dao->getQuotePath($dto))) {
          sys::serve($path);
        } else {
          print file_get_contents(__DIR__ . '/views/not-found.html');
        }
      } else {
        print file_get_contents(__DIR__ . '/views/not-found.html');
      }
    } else {
      print file_get_contents(__DIR__ . '/views/invalid.html');
    }
  }

  public function templateeditor() {
    $template = $this->getParam('t');
    if (\in_array($template, config::job_templates)) {
      $this->data = (object)[
        'template' => $template,
        'text' => config::cms_job_template($template)

      ];

      $this->load('template-editor');
    } else {
      $this->load('template-invalid');
    }
  }

  public function workorder($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job;
      if ($dto = $dao->getByID($id)) {
        $dto = $dao->getRichData($dto);

        $this->data = (object)[
          'title' => $this->title = config::cms_job_PDF_title($dto->job_type),
          'dto' => $dto,
          'hasInvoice' => file_exists($path = $dao->getInvoicePath($dto)),
          'hasQuote' => file_exists($path = $dao->getQuotePath($dto)),

        ];

        $this->load('job-view-workorder');
      } else {
        $this->load('not-found');
      }
    } else {
      $this->load('invalid');
    }
  }

  public function workorderpdf($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job;
      if ($dto = $dao->getByID($id)) {
        if (file_exists($path = $dao->getWorkOrderPath($dto))) {
          sys::serve($path);
        } else {
          print file_get_contents(__DIR__ . '/views/not-found.html');
        }
      } else {
        print file_get_contents(__DIR__ . '/views/not-found.html');
      }
    } else {
      print file_get_contents(__DIR__ . '/views/invalid.html');
    }
  }

  public function zipInvoices() {
    if ($ids = $this->getParam(('ids'))) {
      $ids = explode(',', $ids);
      $dao = new dao\job;

      $zipFileName = sprintf('%sjob-invoices-%s.zip', config::tempdir(), date('Ymdhis'));
      if (file_exists($zipFileName)) {
        unlink($zipFileName);
      }

      $iFiles = 0;
      $zip = new \ZipArchive;

      if ($zip->open($zipFileName, \ZipArchive::CREATE) !== TRUE) {
        \sys::logger(sprintf('<cannot open %s> : %s', $zipFileName, __METHOD__));
        printf('<cannot open archive> %s', __METHOD__);
      } else {

        foreach ($ids as $id) {
          if ($id = (int)$id) {
            if ($dto = $dao->getByID($id)) {
              $dto = $dao->getRichData($dto);

              if (file_exists($path = $dao->getInvoicePath($dto))) {
                $parts = pathinfo($path);
                $fileName = sprintf(
                  '%s%s-%s.%s',
                  config::job_payment_tenant == $dto->job_payment ? 'TENANT-' : '',
                  workorder::reference($dto->id),
                  preg_replace('@[^0-9a-zA-Z]@', '_', $dto->address_street),
                  $parts['extension']
                );

                $zip->addFile($path, $fileName);
                $iFiles++;
              }
            }
          }
        }
      }
      $zip->close();

      if ($iFiles) {
        sys::serve($zipFileName);
      } else {
        printf('<empty archive> %s', __METHOD__);
      }

      if (file_exists($zipFileName)) unlink($zipFileName);
    }
  }
}
