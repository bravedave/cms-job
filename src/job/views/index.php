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

use currentUser, strings;  ?>

<ul class="nav flex-column" id="<?= $_nav = strings::rand() ?>">
  <li class="nav-item h6">
    <a href="<?= strings::url(sprintf('%s', $this->route)) ?>">
      <?= config::label ?>
    </a>

  </li>

  </li>

  <li class="nav-item">
    <a class="nav-link" href="<?= strings::url(sprintf('%s/matrix', $this->route)) ?>">
      <?= config::label_matrix ?>

    </a>

  </li>

  <?php if (false) { ?>

    <li class="nav-item">
      <a class="nav-link pl-4" href="#" id="<?= $_uid = strings::rand() ?>">
        <i class="bi bi-journal-plus"></i> New <?= config::label ?>

      </a>

    </li>
    <script>
      (_ => {
        let active = false;

        $('#<?= $_uid ?>')
          .on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            if (active) return;
            active = true;

            let _me = $(this);

            _.get.modal(_.url('<?= $this->route ?>/job_edit'))
              .then(m => m.on('success', (e, data) => {
                _.nav('<?= $this->route ?>/matrix?idx=' + data.id);

              }))
              .then(d => d.on('success-and-workorder', (e, data) => {
                _me
                  .trigger('create-workorder', data.id)

              }))
              .then(m => active = false);

          })
          .on('create-workorder', function(e, id) {

            let _me = $(this);

            _.hourglass.on();
            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'create-workorder',
                id: id

              },

            }).then(d => {
              _.hourglass.off();
              _.growl(d);
              if ('ack' == d.response) {
                _.nav('<?= $this->route ?>/matrix?v=workorder&idx=' + id);

              } else {
                _.ask.alert({
                  text: d.description

                });
              }

            });

          });

      })(_brayworth_);
    </script>

  <?php } ?>

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

  <?php if (currentUser::restriction('can-add-job-categories')) { ?>
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
  <?php } ?>

  <li class="nav-item">
    <a class="nav-link" href="<?= strings::url(sprintf('%s/items', $this->route)) ?>">
      <?= config::label_items ?>

    </a>

  </li>

  <?php if (currentUser::restriction('can-add-job-items')) { ?>
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
  <?php } ?>

  <li class="nav-item">
    <a class="nav-link" href="<?= strings::url('keyregister') ?>">
      <i class="bi bi-key"></i> Key Register

    </a>

  </li>

  <li class="nav-item">
    <a class="nav-link" href="#" id="<?= $_uid = strings::rand() ?>">
      <i class="bi bi-gear"></i> Invoice To

    </a>

  </li>
  <script>
    (_ => {
      $('#<?= $_uid ?>')
        .on('click', function(e) {
          e.stopPropagation();
          e.preventDefault();

          _.get.modal(_.url('<?= $this->route ?>/invoiceto_edit'));

        });

    })(_brayworth_);
  </script>

  <li class="nav-item h6 pt-3 pl-3">
    Templates

  </li>

  <li class="nav-item">
    <a class="nav-link pl-4" href="#" data-role="template-editor" data-template="template-workorder-send">
      <i class="bi bi-pencil"></i> <?= config::label_template_workorder ?>

    </a>

  </li>
  <script>
    (_ => {
      $('a[data-role="template-editor"]', '#<?= $_nav ?>')
        .on('click', function(e) {
          e.stopPropagation();
          e.preventDefault();

          let _me = $(this);
          let _data = _me.data();

          _me.prop('disabled', true);

          _.get.modal(_.url('<?= $this->route ?>/templateeditor/?t=' + encodeURIComponent(_data.template)))
            .then(m => _me.prop('disabled', false));

        });

    })(_brayworth_);
  </script>

  <?php if ('yes' == currentUser::option('google-sharer')) {  ?>
    <li class="nav-item h6 pt-3 pl-3">
      Reference Documents

    </li>

    <li class="nav-item">
      <a class="nav-link pl-4" href="https://docs.google.com/document/d/1-M7o0YA7NGwqjCZHxHA1pf54DmJUERoclznsqC37Ue0/" target="_blank">
        <i class="bi bi-file-richtext text-primary"></i> JOB - Workorder Management

      </a>

    </li>

    <li class="nav-item">
      <a class="nav-link pl-4" href="https://docs.google.com/document/d/13wwmQi9ZfyRIunOVLZl1ZKDJz-waNDm_65csP68Z_4o/" target="_blank">
        <i class="bi bi-file-richtext text-primary"></i> Contractors

      </a>

    </li>

    <li class="nav-item">
      <a class="nav-link pl-4" href="https://docs.google.com/document/d/1lpiaBuKzN7BkupqLU3a7w99g2rJ6SCZmIecb0V_RwlE/" target="_blank">
        <i class="bi bi-file-richtext text-primary"></i> CMSS Maintenance - Job process

      </a>

    </li>
  <?php  }  ?>

</ul>