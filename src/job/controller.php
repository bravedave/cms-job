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

use Json;
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
    else {
      parent::postHandler();

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
      'secondary' => 'index'

    ]);

  }

  public function category_edit( $id) {
    if ( $id = (int)$id) {
      $dao = new dao\job_categories;
      if ( $dto = $dao->getByID( $id)) {
        $this->data = (object)[
          'dto' => $dto

        ];

        $this->load( 'category-edit');

      }
      else {
        $this->load( 'not-found');

      }

    }
    else {
      $this->load( 'invalid');

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
        'pageUrl' => strings::url($this->route)

      ],

    ]);

  }

  public function contractor_edit($id) {
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
      $this->load('invalid');

    }

  }

}