<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

class config extends dvc\config {
  static $WEBNAME = 'Offer to Lease';
  static $PORTAL = 'http://localhost:8991/';
  static $PORTAL_ADMIN = 'http://localhost:8991/';

  const use_inline_logon = true;

  public static function cmsStore() {
    return self::dataPath();
  }

}
