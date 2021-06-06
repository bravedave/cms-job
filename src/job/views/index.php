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

use strings;  ?>

<ul class="nav flex-column">
  <li class="nav-item h6">
    <a href="<?= strings::url( sprintf( '%s', $this->route)) ?>">
      <?= config::label ?></li>
    </a>

  </li>

  <li class="nav-item d-none">
    <a class="nav-link" href="<?= strings::url( sprintf( '%s/matrix', $this->route)) ?>">
      <?= config::label_matrix ?>

    </a>

  </li>

  <li class="nav-item">
    <a class="nav-link" href="<?= strings::url( sprintf( '%s/contractors', $this->route)) ?>">
      <?= config::label_contractors ?>

    </a>

  </li>

  <li class="nav-item">
    <a class="nav-link" href="<?= strings::url( sprintf( '%s/categories', $this->route)) ?>">
      <?= config::label_categories ?>

    </a>

  </li>

</ul>

