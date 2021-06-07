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
use Json;
use Response;
use strings;

use green\{people\dao\people as dao_people};

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
    $action =$this->getPost('action');

    if ('category-save' == $action) {

      if ( $category = $this->getPost('category')) {
        $a = [
          'category' => $category

        ];

        $dao = new dao\job_categories;
        if ( $id = (int)$this->getPost('id')) {
          $dao->UpdateByID( $a, $id);
          Json::ack( $action);

        }
        else {
          $dao->Insert( $a);
          Json::ack( $action);

        }

      }
      else {
        Json::nak( $action);

      }

    }
    elseif ( 'contractor-save' == $action) {
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
      if ( $id) {
        $dao->UpdateByID($a, $id);
        Json::ack( $action);

      }
      else {
        $dao->Insert($a);
        Json::ack( $action);

      }

    }
    elseif ('item-delete' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\job_items;
        $dao->delete( $id);
        Json::ack($action);

      } else { Json::nak($action); }

    }
    elseif ('item-save' == $action) {

      if ($description = $this->getPost('description')) {
        $a = [
          'description' => $description,
          'job_categories_id' => $this->getPost('job_categories_id')

        ];

        $dao = new dao\job_items;
        if ($id = (int)$this->getPost('id')) {
          $dao->UpdateByID($a, $id);
          Json::ack($action);

        }
        else {
          $dao->Insert($a);
          Json::ack($action);

        }

      } else { Json::nak($action); }

    }
    elseif ('set-primary-contact' == $action) {
      if ( $id = (int)$this->getPost('id')) {
        $dao = new dao\job_contractors;
        $dao->UpdateByID([
          'primary_contact' => (int)$this->getPost('people_id')
        ], $id);

        Json::ack($action);

      } else { Json::nak($action); }

    }
    else {
      parent::postHandler();

    }

  }

  protected function render($params) {
    $params = \array_merge([
      'scripts' => [],

    ], $params);

    $params['scripts'][] = sprintf('<script type="text/javascript" src="%s"></script>', strings::url($this->route . '/js'));

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

  public function category_edit( $id = 0) {
    if ( $id = (int)$id) {
      $dao = new dao\job_categories;
      if ( $dto = $dao->getByID( $id)) {
        $this->data = (object)[
          'dto' => $dto

        ];

        $this->title = config::label_category_edit;
        $this->load( 'category-edit');

      }
      else {
        $this->load( 'not-found');

      }

    }
    else {
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
        'pageUrl' => strings::url( sprintf( '%s/contractors', $this->route))

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

        if ( $dto->primary_contact) {
          $dao = new dao_people;
          if ( $dto = $dao->getByID( $dto->primary_contact)) {
            $this->data->primary_contact = $dto;

          }

        }

        $this->title = config::label_contractor_edit;
        $this->load('contractor-edit');

      }
      else {
        $this->load('not-found');

      }

    }
    else {
      $this->title = config::label_contractor_add;

      $this->data = (object)[
        'dto' => new dao\dto\job_contractors,
        'primary_contact' => false,
        'categories' => dao\job_categories::getCategorySet()

      ];

      $this->load('contractor-edit');

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
        'pageUrl' => strings::url( sprintf( '%s/items', $this->route))

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

      }
      else {
        $this->load('not-found');

      }

    }
    else {
      $this->title = config::label_item_add;

      $this->data = (object)[
        'dto' => new dao\dto\job_items,
        'categories' => dao\job_categories::getCategorySet()

      ];

      $this->load('item-edit');
    }

  }

  public function js( string $lib = '') {
    if ( 'job' == $lib) {
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
      if ( false) {
      // if ($this->Request->ClientIsLocal()) {
        print $js;
      } else {
        $minifier = new MatthiasMullie\Minify\JS;
        $minifier->add($js);
        print $minifier->minify();
      }

    }
    else {
      parent::js( $lib);

    }

  }

}