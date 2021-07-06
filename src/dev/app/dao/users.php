<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dao;

use green;
use green\users\dao\dto\users as greenusersdto;
use config;
use strings;
use sys;
use dvc\session;

class users extends green\users\dao\users {
  public function getByEmail($email) {
    if (strings::IsEmailAddress($email)) {
      if ($res = $this->Result(sprintf("SELECT * FROM users WHERE `email` = '%s'", $this->escape($email)))) {
        $dto = $res->dto($this->template);
        return $dto;
      }
    }

    return (false);
  }

  public function getByResetKey($key) {
    if (substr($key, 0, 1) == '{' && substr($key, -1) == '}') {
      if ($res = $this->Result(sprintf("SELECT * FROM users WHERE `reset_guid` = '%s'", $this->escape($key)))) {
        if ($dto = $res->dto($this->template)) {
          // \sys::logger( time() - strtotime($dto->reset_guid_date));
          if (time() - strtotime($dto->reset_guid_date) < 3600) {
            // it's good for 1 hour
            return ($dto);
          }
        }
      }
    }

    return (false);
  }

  public function getByUsername($name) {
    if ($name) {
      $sql = sprintf("SELECT * FROM users WHERE `username` = '%s'", $this->escape($name));
      if ($res = $this->Result($sql)) {
        return $res->dto($this->template);
      }
    }

    return (false);
  }

  public function options($dto) {
    return new \cms\useroptions($dto->id, null);
  }

  public function sendResetLink($dto) {
    $guid = strings::getGUID();
    $this->UpdateByID([
      'reset_guid' => $guid,
      'reset_guid_date' => \db::dbTimeStamp()

    ], $dto->id);

    $mailMessage = sprintf(
      'Reset your password?<br />
    <br />
    If you requested a password reset click the link below.<br />
    If you didn\'t make this request, ignore this email.<br />
    <br />
    <a href="%s">Reset Password</a>',
      strings::url('recover/?k=' . $guid, $protocol = true)

    );

    $mail = sys::mailer();

    // $mail->AddReplyTo( $user->email, $user->name);
    $mail->Subject  = config::$WEBNAME . " Password Recovery";
    $mail->AddAddress($dto->email, $dto->name);

    $mail->MsgHTML($mailMessage);

    try {
      if ($mail->send()) {
        return (true);
      } else {
        sys::logger(sprintf('<Message could not be sent. Mailer Error: %s> %s', $mail->ErrorInfo, __METHOD__));
      }
    } catch (\Exception $e) {
      sys::logger(sprintf('<Could not send error email> %s', __METHOD__));
    }

    return false;
  }

  public function setLoggedOn(greenusersdto $dto): bool {
    session::edit();
    session::set('uid', $dto->id);
    session::close();

    return true;
  }

  public function validate(string $u, string $p): bool {
    $debug = false;
    $debug = true;
    if ($u && $p) {
      $dto = false;
      if (strings::IsEmailAddress($u)) {
        if ($dto = $this->getByEmail($u)) {
          if (password_verify($p, $dto->password) || $p == $dto->password) {
            if ($debug) \sys::logger(sprintf('<%s> %s', '@ valid', __METHOD__));
            return $this->setLoggedOn($dto);
          }
        }
      } elseif ($dto = $this->getByUsername($u)) {
        if (password_verify($p, $dto->password) || $p == $dto->password) {
          if ($debug) \sys::logger(sprintf('<%s> %s', 'valid', __METHOD__));
          return $this->setLoggedOn($dto);
        } else {
          if ($debug) \sys::logger(sprintf('<%s> %s', 'invalid', __METHOD__));
        }
      } else {
        if ($debug) \sys::logger(sprintf('<%s> %s', 'not found', __METHOD__));
      }
    }

    return (false);
  }
}
