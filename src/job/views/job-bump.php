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
  <input type="hidden" name="action" value="job-save-bump">
  <input type="hidden" name="id" value="<?= $dto->id ?>">

  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="col">
              <div class="input-group">
                <input type="date" name="bump" class="form-control" value="<?= strtotime($dto->due) > 0 ? $dto->due : '' ?>">

                <div class="input-group-prepend">
                  <btn class="btn input-group-text" id="<?= $_uid = strings::rand() ?>">7</btn>
                </div>
                <script>
                  (_ => {
                    $('#<?= $_uid ?>').on('click', e => {
                      e.stopPropagation();

                      let _form = $('#<?= $_form ?>');
                      let _data = _form.serializeFormJSON();

                      let d = _.dayjs(_data.bump);
                      if (d.isValid() && d.unix() > 0) {
                        $('input[name="bump"]', _form)
                          .val(d.add(7, 'days').format('YYYY-MM-DD'));

                      }

                    })

                  })(_brayworth_);
                </script>

              </div>

            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">bump</button>

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
            _.growl(d);
            if ('ack' == d.response) {
              $('#<?= $_modal ?>').trigger('success');

            }

            $('#<?= $_modal ?>').modal('hide');

          });


          return false;
        });

        $('input[name="bump"]', '#<?= $_form ?>').focus();

    }))(_brayworth_);
  </script>
</form>