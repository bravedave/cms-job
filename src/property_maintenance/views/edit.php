<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\property_maintenance;

use strings, theme;

$dto = $this->data->dto;  ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="action" value="property-maintenance-save">
  <input type="hidden" name="id" value="<?= $dto->id ?>">
  <input type="hidden" name="people_id" value="<?= $dto->people_id ?>">
  <input type="hidden" name="contact_id" value="<?= $dto->contact_id ?>">

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

          <!-- type -->
          <div class="form-row mb-2">
            <div class="col-3 col-form-label">type</div>
            <div class="col">
              <select name="type" class="form-control" required>
                <option value=""></option>
                <?php
                foreach (config::property_maintenance_types as $type) {
                  printf(
                    '<option value="%s" %s>%s</option>',
                    $type,
                    $type == $dto->type ? 'selected' : '',
                    $type

                  );
                }
                ?>

              </select>

            </div>

          </div>

          <!-- limit -->
          <!-- div class="form-row mb-2">
            <div class="col-3 col-form-label">limit</div>
            <div class="col">
              <input type="text" name="limit" class="form-control" maxlength="42" value="<?= $dto->limit ?>">

            </div>

          </div -->

          <!-- property -->
          <div class="form-row mb-2">
            <div class="col-3 col-form-label">property</div>
            <div class="col">
              <select name="properties_id" class="form-control">
                <option value="0" <?= 0 == $dto->properties_id ? 'selected' : '' ?>>all</option>
                <?php
                foreach ($this->data->allProps as $prop) {
                  printf(
                    '<option value="%s" %s>%s</option>',
                    $prop->id,
                    $prop->id == $dto->properties_id ? 'selected' : '',
                    $prop->address_street

                  );
                }

                ?>
              </select>

            </div>

          </div>

          <!-- --[contact]-- -->
          <div class="form-row">
            <div class="col-3 col-form-label">Contact</div>

            <div class="col-md mb-2">
              <input type="text" class="form-control" value="<?= $dto->contact_name ?>" id="<?= $_uidContactName = strings::rand() ?>">

            </div>

            <script>
              (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
                $('#<?= $_uidContactName ?>')
                  .autofill({
                    autoFocus: true,
                    source: (request, response) => {
                      _.post({
                        url: _.url('<?= $this->route ?>'),
                        data: {
                          action: 'search-people',
                          term: request.term

                        },

                      }).then(d => response('ack' == d.response ? d.data : []));

                    },
                    select: (e, ui) => {
                      let o = ui.item;
                      $('input[name="contact_id"]', '#<?= $_form ?>')
                        .val(o.id);

                    },

                  });

              }))(_brayworth_);
            </script>

          </div>

          <!-- --[notes]-- -->
          <div class="form-row mb-2">
            <div class="col">
              <label>notes</label>
              <textarea name="notes" class="form-control"><?= $dto->notes ?></textarea>

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
    (_ => {

      $('#<?= $_modal ?>').on('shown.bs.modal', () => {

        $('textarea', '#<?= $_form ?>')
          .autoResize();

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
      })

    })(_brayworth_);
  </script>
</form>