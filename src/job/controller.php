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

use FilesystemIterator;
use MatthiasMullie;
use Json, Response, strings, sys, cms\leasing;

use green\{
  people\dao\people as dao_people,
  search
};

class controller extends \Controller {
  protected $label = config::label;
  protected $viewPath = __DIR__ . '/views/';

  protected function _index() {
    $this->render([
      'title' => $this->title = $this->label,
      'primary' => 'blank',
      'secondary' => 'index'

    ]);
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
        Json::ack($action);
      } else {
        $dao->Insert($a);
        Json::ack($action);
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

          $dao = new dao\job_categories;
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
        if ( $dto = $dao->getByID($id)) {
          $dto = $dao->getRichData($dto);
          Json::ack($action)
            ->add('data', $dto->keys);

        } else {
          Json::nak($action);
        }
      } else {
        Json::nak($action);
      }
    } elseif ('invoiceto-save' == $action) {
      config::cms_job_invoiceto( $this->getPost('invoiceto'));
      Json::ack( $action);

    } elseif ('item-delete' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job_items;
        $dao->delete($id);
        Json::ack($action);
      } else {
        Json::nak($action);
      }
    } elseif ('item-save' == $action) {

      if ($description = $this->getPost('description')) {
        $a = [
          'description' => $description,
          'item' => $this->getPost('item'),
          'job_categories_id' => $this->getPost('job_categories_id')

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
    } elseif ('job-save' == $action) {

      if ($description = $this->getPost('description')) {
        $a = [
          'updated' => \db::dbTimeStamp(),
          'contractor_id' => (int)$this->getPost('contractor_id'),
          'properties_id' => (int)$this->getPost('properties_id'),
          'job_type' => (int)$this->getPost('job_type'),
          'status' => (int)$this->getPost('status'),
          'due' => $this->getPost('due'),
          'job_payment' => (int)$this->getPost('job_payment'),
          'description' => (string)$this->getPost('description'),

        ];

        $dao = new dao\job;
        if ($id = (int)$this->getPost('id')) {
          $dao->UpdateByID($a, $id);
        } else {
          $a['created'] = $a['updated'];
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

        Json::ack($action);
      } else {
        Json::nak($action);
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
    } elseif ('search-properties' == $action) {
      if ($term = $this->getPost('term')) {
        Json::ack($action)
          ->add('term', $term)
          ->add('data', search::properties($term));
      } else {
        Json::nak($action);
      }
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
      'categories' => dao\job_categories::getCategorySet()

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

  public function contractor_edit($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job_contractors();
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

  public function job_edit($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job;
      if ($dto = $dao->getByID($id)) {
        $dto = $dao->getRichData($dto);

        // \sys::logger(sprintf('<found street> <%s> %s', $dto->address_street, __METHOD__));

        $this->data = (object)[
          'title' => $this->title = config::label_job_edit,
          'dto' => $dto,
          'categories' => dao\job_categories::getCategorySet()

        ];

        $this->load('job-edit');
      } else {
        $this->load('not-found');
      }
    } else {
      $this->data = (object)[
        'title' => $this->title = config::label_job_add,
        'dto' => new dao\dto\job,
        'categories' => dao\job_categories::getCategorySet()

      ];

      $this->load('job-edit');
    }
  }

  public function invoiceto_edit() {
    $this->load('invoiceto-edit');

  }

  public function matrix() {
    $dao = new dao\job;
    $this->data = (object)[
      'title' => $this->title = config::label_matrix,
      'res' => $dao->getMatrix()

    ];

    $this->render([
      'primary' => 'matrix',
      'secondary' => 'index',

    ]);
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

  public function workorder($id = 0) {
    if ($id = (int)$id) {
      $dao = new dao\job;
      if ($dto = $dao->getByID($id)) {
        $dto = $dao->getRichData($dto);

        $this->data = (object)[
          'title' => $this->title = config::label_job_viewworkorder,
          'dto' => $dto,

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
}
