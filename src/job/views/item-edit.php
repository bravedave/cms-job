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

use strings;
use theme;

$dto = $this->data->dto;  ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="action" value="item-save">
  <input type="hidden" name="id" value="<?= $dto->id ?>">
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
              <input type="text" class="form-control" maxlength="100" name="description" value="<?= $dto->description ?>">

            </div>

          </div>

          <div class="form-row mb-2">
            <div class="col">
              <select name="job_categories_id" class="form-control" required>
                <option>select <?= config::label_category ?></option>
                <?php
                foreach ($this->data->categories as $k => $v) {
                  printf(
                    '<option value="%s" %s>%s</option>',
                    $k,
                    $dto->job_categories_id == $k ? 'selected' : '',
                    $v

                  );
                } ?>

              </select>

            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">close</button>
          <button type="submit" class="btn btn-primary">Save</button>
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

              $('#<?= $_modal ?>').trigger('success');

            } else {
              _.growl(d);

            }

            $('#<?= $_modal ?>').modal('hide');

          });

          return false;

        });

      $('input[name="description"]', '#<?= $_form ?>').focus();

    }))(_brayworth_);
  </script>

</form>