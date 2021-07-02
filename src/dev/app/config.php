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

  const use_inline_logon = true;

  public static function cmsStore() {
    return self::dataPath();
  }

}
