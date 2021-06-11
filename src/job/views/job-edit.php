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
  <input type="hidden" name="properties_id" value="<?= $dto->properties_id ?>">
  <input type="hidden" name="contractor_id" value="<?= $dto->contractor_id ?>">

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
            <div class="col">&nbsp;</div>
            <div class="col-auto small">created: <?= $dto->id ? strings::asLocalDate(($dto->created)) : 'new' ?></div>
            <?php if ( $dto->id) {
              printf(
                '<div class="col-auto small">updated:%s</div>',
                strings::asLocalDate($dto->updated)

              );

            }  ?>

          </div>

          <div class="form-row mb-2">
            <div class="col-3 col-form-label">status</div>

            <div class="col">
              <select name="status" class="form-control">
                <?php
                foreach (config::job_status as $k => $label) {
                  printf(
                    '<option value="%s" %s>%s</option>',
                    $k,
                    $k == $dto->status ? 'selected' : '',
                    $label

                  );
                } ?>

              </select>

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
              <input type="text" class="form-control" value="<?= $dto->address_street ?>" id="<?= $_uid = strings::rand() ?>">

            </div>

            <div class="col-auto mb-2 d-none" id="<?= $_uid ?>suburb_div">
              <div class="form-control" id="<?= $_uid ?>suburb"><?= $dto->address_suburb ?></div>
            </div>
            <div class="col-auto mb-2 d-none" id="<?= $_uid ?>postcode_div">
              <div class="form-control" id="<?= $_uid ?>postcode"><?= $dto->address_postcode ?></div>
            </div>
            <script>
              (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
                $('#<?= $_uid ?>').autofill({
                  autoFocus: true,
                  source: _.search.address,
                  select: (e, ui) => {
                    let o = ui.item;
                    // console.log( o);
                    $('input[name="properties_id"]', '#<?= $_form ?>').val(o.id);
                    $('#<?= $_uid ?>suburb').html(o.suburb);
                    $('#<?= $_uid ?>postcode').html(o.postcode);
                    $('#<?= $_uid ?>suburb_div, #<?= $_uid ?>postcode_div').removeClass('d-none');

                  },

                });

                if (Number($('input[name="properties_id"]', '#<?= $_form ?>').val()) > 0) {
                  $('#<?= $_uid ?>suburb_div, #<?= $_uid ?>postcode_div').removeClass('d-none');

                }

              }))(_brayworth_);
            </script>

          </div>

          <div class="form-row">
            <div class="col-md-3 col-form-label"><?= config::label_contractor ?></div>

            <div class="col-md mb-2">
              <input type="text" class="form-control" value="<?= $dto->contractor_trading_name ?>" id="<?= $_uid = strings::rand() ?>">

            </div>

            <script>
              (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
                $('#<?= $_uid ?>').autofill({
                  autoFocus: true,
                  source: (request, response) => {
                    _.post({
                      url: _.url('<?= $this->route ?>'),
                      data: {
                        action: 'search-contractor',
                        term: request.term,
                        services: ''

                      },

                    }).then(d => response('ack' == d.response ? d.data : []));

                  },
                  select: (e, ui) => {
                    let o = ui.item;
                    console.log(o);
                    $('input[name="contractor_id"]', '#<?= $_form ?>').val(o.id);

                  },

                });

                if (Number($('input[name="properties_id"]', '#<?= $_form ?>').val()) > 0) {
                  $('#<?= $_uid ?>suburb_div, #<?= $_uid ?>postcode_div').removeClass('d-none');

                }

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

      let newRow = () => {
        let row = $('<div class="form-row" item-row></div>');
        $('<input type="hidden" name="job_line_id[]" value="0">').appendTo(row);

        let cat = $('<select name="item_job_categories_id[]" class="form-control"></select>');
        cat.append('<option value="">select category</option>');

        $.each(_.catSort(cats), (i, c) => $('<option></option>').val(c[0]).html(c[1]).appendTo(cat));
        cat.on('change', e => row.trigger('category-change'));

        $('<div class="col mb-2" category></div>').append(cat).appendTo(row);

        let itemSub = $('<select name="item_sub[]" class="form-control" disabled></select>');
        itemSub.append('<option value="">select item</option>');
        itemSub.on('change', e => row.trigger('item-sub-change'));

        $('<div class="col mb-2" item></div>').append(itemSub).appendTo(row);

        let item = $('<select name="item_id[]" class="form-control" disabled></select>');
        item.append('<option value="">select issue</option>');

        $('<div class="col mb-2" item></div>').append(item).appendTo(row);

        let btnDelete = $('<div class="btn btn-light" type="button"><i class="bi bi-dash-circle-dotted text-danger"></i></div>');
        btnDelete.on('click', function(e) {
          e.stopPropagation();
          $(this).closest('div[item-row]').trigger('delete');

        });

        $('<div class="col-auto mb-2" item></div>').append(btnDelete).appendTo(row);

        row
          .on('category-change', function(e, callback) {
            let cat = $('select[name="item_job_categories_id\[\]"]', this);
            let itemSub = $('select[name="item_sub\[\]"]', this);
            let item = $('select[name="item_id\[\]"]', this);
            itemSub
              .prop('disabled', true)
              .find('option')
              .remove()
              .end()
              .append('<option value="" selected>select item</option>')
              .val('');

            item
              .prop('disabled', true)
              .find('option')
              .remove()
              .end()
              .append('<option value="" selected>select issue</option>')
              .val('');

            if ('' !== cat.val()) {

              _.post({
                url: _.url('<?= $this->route ?>'),
                data: {
                  action: 'get-items-of-category-distinctly',
                  category: cat.val()

                },

              }).then(d => {
                if ('ack' == d.response) {
                  $.each(d.data, (i, _item) => {
                    $('<option></option>')
                      .val(_item.item)
                      .html(_item.item)
                      .appendTo(itemSub)

                  });

                  itemSub.prop('disabled', false);
                  if ('function' == typeof callback) callback();

                } else {
                  _.growl(d);

                }

              });

            }

          })
          .on('item-sub-change', function(e, callback) {

            // console.log( e.type);

            let cat = $('select[name="item_job_categories_id\[\]"]', this);
            let itemSub = $('select[name="item_sub\[\]"]', this);
            let item = $('select[name="item_id\[\]"]', this);

            item
              .prop('disabled', true)
              .find('option')
              .remove()
              .end()
              .append('<option value="" selected>select issue</option>')
              .val('');

            if ('' !== cat.val()) {

              let sendData = {
                action: 'get-items-of-category-item',
                category: cat.val(),
                item: itemSub.val(),

              };

              _.post({
                url: _.url('<?= $this->route ?>'),
                data: sendData,

              }).then(d => {
                // console.log(sendData, d);
                if ('ack' == d.response) {
                  $.each(d.data, (i, _item) => {
                    $('<option></option>')
                      .val(_item.id)
                      .html(_item.description)
                      .appendTo(item)

                  });

                  item.prop('disabled', false);
                  if ('function' == typeof callback) callback();

                } else {
                  _.growl(d);

                }

              });

            }

          })
          .on('delete', function(e) {
            let _me = $(this);

            _.ask.alert({
              title: 'Confirm',
              text: 'Are you Sure ?',
              buttons: {
                yes: function(e) {
                  $(this).modal('hide');
                  _me.trigger('delete-confirmed');

                }

              }

            })

          })
          .on('delete-confirmed', function(e) {
            let jobline = Number($('input[name="job_line_id\[\]"]', this).val());
            console.log(jobline);

            if (jobline > 0) {
              _.post({
                url: _.url('<?= $this->route ?>'),
                data: {
                  action: 'job-line-delete',
                  id: jobline

                },

              }).then(d => {
                if ('ack' == d.response) {
                  $(this).remove();

                } else {
                  _.growl(d);

                }

              });

            } else {
              $(this).remove();

            }

          });

        row.appendTo('#<?= $_uidItemContainer ?>');

        $('> div[caption]', '#<?= $_uidItemContainer ?>').removeClass('d-none');

        return row;

      }

      $('#<?= $_form ?>')
        .on('item-add', e => newRow())
        .one('items-init', function(e) {
          let initItems = <?= json_encode($dto->lines) ?>;
          // console.log(initItems);

          $.each(initItems, (i, _item) => {
            let row = newRow();
            let jobline = $('input[name="job_line_id\[\]"]', row);
            let cat = $('select[name="item_job_categories_id\[\]"]', row);
            let itemSub = $('select[name="item_sub\[\]"]', row);
            let item = $('select[name="item_id\[\]"]', row);

            jobline.val(_item.id);
            cat.val(_item.job_categories_id);
            row.trigger('category-change', () => {
              itemSub.val(_item.item);
              row.trigger('item-sub-change', () => {
                item.val(_item.item_id);

              })

            });

          });

        })
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

          // console.table( _data);
          return false;

        });

      $('#<?= $_form ?>').trigger('items-init');

    }))(_brayworth_);
  </script>

</form>