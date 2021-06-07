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
    <a href="<?= strings::url(sprintf('%s', $this->route)) ?>">
      <?= config::label ?>
    </a>

  </li>

  </li>

  <li class="nav-item d-none">
    <a class="nav-link" href="<?= strings::url(sprintf('%s/matrix', $this->route)) ?>">
      <?= config::label_matrix ?>

    </a>

  </li>

  <li class="nav-item">
    <a class="nav-link" href="<?= strings::url(sprintf('%s/contractors', $this->route)) ?>">
      <?= config::label_contractors ?>

    </a>

  </li>

  <li class="nav-item">
    <a class="nav-link pl-4" href="#" id="<?= $_uid = strings::rand() ?>">
      <i class="bi bi-person-plus"></i> New <?= config::label_contractor ?>

    </a>

  </li>
  <script>
    (_ => {
      let active = false;

      $('#<?= $_uid ?>').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        if (active) return;
        active = true;

        _.get.modal(_.url('<?= $this->route ?>/contractor_edit'))
          .then(m => m.on('success', e => _.nav('<?= $this->route ?>/contractors')))
          .then(m => active = false);

      });

    })(_brayworth_);
  </script>

  <li class="nav-item">
    <a class="nav-link" href="<?= strings::url(sprintf('%s/categories', $this->route)) ?>">
      <?= config::label_categories ?>

    </a>

  </li>

  <li class="nav-item">
    <a class="nav-link pl-4" href="#" id="<?= $_uid = strings::rand() ?>">
      <i class="bi bi-plus"></i> New <?= config::label_category ?>

    </a>

  </li>

  <script>
    (_ => {
      let active = false;

      $('#<?= $_uid ?>').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        if (active) return;
        active = true;

        _.get.modal(_.url('<?= $this->route ?>/category_edit'))
          .then(m => m.on('success', e => _.nav('<?= $this->route ?>/categories')))
          .then(m => active = false);

      });

    })(_brayworth_);
  </script>

  <li class="nav-item">
    <a class="nav-link" href="<?= strings::url(sprintf('%s/items', $this->route)) ?>">
      <?= config::label_items ?>

    </a>

  </li>

  <li class="nav-item">
    <a class="nav-link pl-4" href="#" id="<?= $_uid = strings::rand() ?>">
      <i class="bi bi-plus"></i> New <?= config::label_item ?>

    </a>

  </li>

  <script>
    (_ => {
      let active = false;

      $('#<?= $_uid ?>').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        if (active) return;
        active = true;

        _.get.modal(_.url('<?= $this->route ?>/item_edit'))
          .then(m => m.on('success', e => _.nav('<?= $this->route ?>/items')))
          .then(m => active = false);

      });

    })(_brayworth_);
  </script>

  <li class="nav-item h6 pt-3 pl-3">
    Reference Documents

  </li>

  <li class="nav-item small">
    <a class="nav-link pl-4" href="https://docs.google.com/document/d/1-M7o0YA7NGwqjCZHxHA1pf54DmJUERoclznsqC37Ue0/" target="_blank">
      <i class="bi bi-file-richtext text-primary"></i> JOB - Workorder Management

    </a>

  </li>

  <li class="nav-item small">
    <a class="nav-link pl-4" href="https://docs.google.com/document/d/13wwmQi9ZfyRIunOVLZl1ZKDJz-waNDm_65csP68Z_4o/" target="_blank">
      <i class="bi bi-file-richtext text-primary"></i> Contractors

    </a>

  </li>

  <li class="nav-item small">
    <a class="nav-link pl-4" href="https://docs.google.com/document/d/1lpiaBuKzN7BkupqLU3a7w99g2rJ6SCZmIecb0V_RwlE/" target="_blank">
      <i class="bi bi-file-richtext text-primary"></i> CMSS Maintenance - Job process

    </a>

  </li>

</ul>