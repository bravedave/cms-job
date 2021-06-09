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

$dto = $this->data->dto;
$categories = $this->data->categories;  ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="action" value="job-save">
  <input type="hidden" name="id" value="<?= $dto->id ?>">
  <input type="hidden" name="property_id" value="<?= $dto->property_id ?>">

  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-row mb-2">
            <div class="col-3">created</div>

            <div class="col">
              <?= $dto->id ? strings::asLocalDate(($dto->created)) : 'new' ?>

            </div>

          </div>

          <div class="form-row mb-2">
            <div class="col-3">type</div>

            <div class="col">
              <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="job_type" value="<?= config::job_type_order ?>" id="<?= $_uid = strings::rand() ?>" <?php if (config::job_type_order == $dto->job_type) print 'checked'; ?>>

                <label class="form-check-label" for="<?= $_uid ?>">
                  Order

                </label>

              </div>

              <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="job_type" value="<?= config::job_type_recurring ?>" id="<?= $_uid = strings::rand() ?>" <?php if (config::job_type_recurring == $dto->job_type) print 'checked'; ?>>

                <label class="form-check-label" for="<?= $_uid ?>">
                  Recurring

                </label>

              </div>

              <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="job_type" value="<?= config::job_type_quote ?>" id="<?= $_uid = strings::rand() ?>" <?php if (config::job_type_quote == $dto->job_type) print 'checked'; ?>>

                <label class="form-check-label" for="<?= $_uid ?>">
                  Quote

                </label>

              </div>

            </div>

          </div>

          <div class="form-row">
            <div class="col-md-3 col-form-label">description</div>

            <div class="col-md mb-2">
              <textarea class="form-control" name="description" placeholder="describe the need for this job ..." id="<?= $_uid = strings::rand() ?>"><?= $dto->description ?></textarea>

            </div>
            <script>
              (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => $('#<?= $_uid ?>').autoResize()))(_brayworth_);
            </script>

          </div>

          <div class="form-row">
            <div class="col-md-3 col-form-label">property</div>

            <div class="col-md mb-2">
              <input type="text" class="form-control" value="<?= $dto->property_street ?>" id="<?= $_uid = strings::rand() ?>">

            </div>

            <div class="col-auto mb-2 d-none" id="<?= $_uid ?>suburb_div">
              <div id="<?= $_uid ?>suburb"></div>
            </div>
            <div class="col-auto mb-2 d-none" id="<?= $_uid ?>postcode_div">
              <div id="<?= $_uid ?>postcode"></div>
            </div>
            <script>
              (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
                $('#<?= $_uid ?>').autofill({
                  autoFocus: true,
                  source: _.search.address,
                  select: (e, ui) => {
                    let o = ui.item;
                    // console.log( o);
                    $('input[name="property_id"]', '#<?= $_form ?>').val(o.id);
                    $('#<?= $_uid ?>suburb').html(o.suburb).addClass('form-control');
                    $('#<?= $_uid ?>postcode').html(o.postcode).addClass('form-control');
                    $('#<?= $_uid ?>suburb_div, #<?= $_uid ?>postcode_div').removeClass('d-none');

                  },

                });

              }))(_brayworth_);
            </script>

          </div>

          <div class="form-row">
            <div class="col" id="<?= $_uidItemContainer = strings::rand() ?>">
              <div class="border-bottom d-none" caption>items..</div>

            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" id="<?= $_btnAddItem = strings::rand() ?>"><i class="bi bi-plus"></i> item</button>
          <button type="button" class="btn btn-outline-secondary ml-auto" data-dismiss="modal">close</button>
          <button type="submit" class="btn btn-primary">Save</button>

        </div>

      </div>

    </div>

  </div>

  <script>
    (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
      $('#<?= $_btnAddItem ?>').on('click', e => $('#<?= $_form ?>').trigger('item-add'));

      let cats = <?= json_encode($categories) ?>;

      $('#<?= $_form ?>')
        .on('item-add', e => {
          let row = $('<div class="form-row" item-row></div>');

          let cat = $('<select name="item_job_categories_id[]" class="form-control"></select>');
          cat.append('<option value="">select category</option>');

          let catsSorted = Object.entries(cats).sort((a, b) => String(a[1]).toUpperCase().localeCompare(String(b[1]).toUpperCase()));

          $.each(_.catSort(cats), (i, c) => cat.append('<option value="' + c[0] + '">' + c[1] + '</option>'));
          cat.on('change', e => row.trigger( 'category-change'));

          $('<div class="col mb-2" category></div>').append(cat).appendTo(row);

          let item = $('<select name="item_id[]" class="form-control" disabled></select>');
          item.append('<option value="">select item</option>');

          $('<div class="col mb-2" item></div>').append(item).appendTo(row);

          row
            .on('category-change', function(e) {
              let cat = $('select[name="item_job_categories_id\[\]"]', this);
              let item = $('select[name="item_id\[\]"]', this);
              item
                .prop('disabled', true)
                .find('option')
                .remove()
                .end()
                .append('<option value="" selected>select item</option>')
                .val('');

              if ('' !== cat.val()) {

                _.post({
                  url: _.url('<?= $this->route ?>'),
                  data: {
                    action: 'get-items-of-category',
                    category: cat.val()

                  },

                }).then(d => {
                  console.log(d);
                  if ('ack' == d.response) {
                    $.each(d.data, (i, _item) => {
                      $('<option></option>')
                        .val(_item.id)
                        .html(_item.description)
                        .appendTo(item)

                    });

                    item.prop('disabled', false);

                  } else {
                    _.growl(d);

                  }

                });

              }

            });

          row.appendTo('#<?= $_uidItemContainer ?>');

          $('> div[caption]', '#<?= $_uidItemContainer ?>').removeClass('d-none');

        })
        .on('submit', function(e) {
          let _form = $(this);
          let _data = _form.serializeFormJSON();
          let _modalBody = $('.modal-body', _form);

          // console.table( _data);
          return false;

        });

    }))(_brayworth_);
  </script>

</form>