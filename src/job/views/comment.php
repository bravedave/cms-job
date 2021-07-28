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

use strings, theme;

$job = $this->data->job;  ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="action" value="comment-post">
  <input type="hidden" name="job_id" value="<?= $job->id ?>">
  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-row mb-2">
            <div class="col">
              <input type="text" class="form-control" value="<?= $job->address_street ?>" readonly>

            </div>

          </div>

          <div class="form-row">
            <div class="col">
              <textarea class="form-control" name="comment" rows="6" placeholder="add comment ..." required></textarea>

            </div>

          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-outline-primary">post</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
      $('#<?= $_form ?>')
        .on('submit', function(e) {
          let _form = $(this);
          let _data = _form.serializeFormJSON();
          _.post({
            url: _.url('<?= $this->route ?>'),
            data: _data,

          }).then(d => {
            if ('ack' == d.response) {
              $('#<?= $_modal ?>')
                .trigger('success')
                .modal('hide');

            } else {
              _.growl(d);

            }

          });


          return false;
        });

      $('textarea[name="comment"]', '#<?= $_form ?>').focus();

    }))(_brayworth_);
  </script>
</form>