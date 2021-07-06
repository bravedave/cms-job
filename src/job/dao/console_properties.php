<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\job\dao;

use cms;

class console_properties extends cms\console\dao\console_properties {
  public function getByPropertiesID( int $id) {
    $sql = sprintf(
      'SELECT
        *
      FROM
        `%s`
      WHERE `properties_id` = %d',
      $this->db_name(),
      $id

    );

    if ( $res = $this->Result( $sql)) {
      return $res->dto();

    }

    return null;

  }

}