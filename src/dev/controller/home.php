<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

class home extends cms\job\controller {
  protected function _authorize() {
    /**
     * curl -X POST -H "Accept: application/json" -d action="-system-logon-" -d u="john" -d p="" "http://localhost/"
     */

    $action = $this->getPost('action');

    if ('-system-logon-' == $action) {
      if ($u = $this->getPost('u')) {
        if ($p = $this->getPost('p')) {
          $dao = new dao\users;
          if ($dto = $dao->validate($u, $p)) {
            Json::ack($action);

          }
          else {
            Json::nak($action);

          }
          die;

        }

      }

    }
    elseif ('-send-password-' == $action) {
      /*
      * send a link to reset the password
      * curl -X POST -H "Accept: application/json" -d action="-send-password-" -d u="david@brayworth.com.au" -d "http://localhost/"
      */
      if ($u = $this->getPost('u')) {
        $dao = new dao\users;
        if ($dto = $dao->getByEmail($u)) {
          /*
          * this will only work for email addresses
          */
          if ($dao->sendResetLink($dto)) {
            sys::logger('-send-password-link--');
            // Json::ack('sent reset link');
            Json::ack('sent reset link')
                ->add('message', 'sent link, check your email and your junk mail');

            sys::logger('-sent-password-link--');
            die;

          } else { Json::nak($action); }

        } else { Json::nak($action); }

      } else { Json::nak($action); }

    }
    else { throw new dvc\Exceptions\InvalidPostAction; }

  }

  protected function authorize() {
    if ($this->isPost()) {
      $this->_authorize();

    }
    else { parent::authorize(); }

  }

}
