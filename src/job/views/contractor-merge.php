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

$dto = $this->data->dto;  ?>
<form id="<?= $_form = strings::rand() ?>" autocomplete="off">

  <input type="hidden" name="action" value="contractor-merge">
  <input type="hidden" name="source" value="<?= $dto->id ?>">

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
          <div class="row">
            <div class="col-2 col-form-label">source</div>
            <div class="col mb-2">
              <input type="text" class="form-control" readonly value="<?= $dto->trading_name ?>">
              <?php if ($dto->company_name) { ?>
                <div class="form-text text-muted small">Company Name: <?= $dto->company_name ?></div>
              <?php } ?>
            </div>

          </div>

          <div class="row">
            <div class="col-2 col-form-label">target</div>
            <div class="col mb-2">
              <select name="target" class="custom-select" required>
                <option value="">select target</option>
                <?php
                foreach ($this->data->allOthers as $o) {
                  printf('<option value="%d">%s</option>', $o->id, $o->trading_name);
                }
                ?>
              </select>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">merge</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
      // $('select[name="target"]', '#<?= $_form ?>')
      //   .on('change', function(e) {

      //   })

      $('#<?= $_form ?>')
        .on('submit', function(e) {
          let _form = $(this);
          let _data = _form.serializeFormJSON();

          // console.table(_data);
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

      $('select[name="target"]', '#<?= $_form ?>').focus();
    }))(_brayworth_);
  </script>
</form>