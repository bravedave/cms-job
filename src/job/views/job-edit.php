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
  <input type="hidden" name="required_services">

  <style>
    #<?= $_form ?>button:focus {
      box-shadow: none;
    }

    @media (max-width: 768px) {
      [item-row] {
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
      }

    }
  </style>

  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>

        </div>

        <div class="modal-body">
          <?php if ($dto->id) {  ?>
            <div class="form-row mb-2">
              <div class="col">&nbsp;</div>
              <div class="col-auto small">created: <?= $dto->id ? strings::asLocalDate(($dto->created)) : 'new' ?></div>
              <?php if (strtotime($dto->updated) > strtotime($dto->created)) {
                printf(
                  '<div class="col-auto small">updated:%s</div>',
                  strings::asLocalDate($dto->updated)

                );
              }  ?>

            </div>

          <?php }  ?>

          <div class="form-row">
            <div class="col-md-3 col-xl-2 col-form-label">status</div>

            <div class="col mb-2">
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

            <div class="col-lg-6 mb-2">
              <div class="form-row">
                <div class="col-lg-auto col-form-label">due</div>

                <div class="col">
                  <div class="input-group">
                    <input name="due" class="form-control" type="date" id="<?= $_uid = strings::rand() ?>" value="<?php if (strtotime($dto->due) > 0) print $dto->due; ?>">

                    <div class="input-group-append">
                      <button type="button" class="btn input-group-text" id="<?= $_uid ?>7">7</button>
                    </div>

                    <div class="input-group-append">
                      <button type="button" class="btn input-group-text" id="<?= $_uid ?>14">14</button>
                    </div>

                    <div class="input-group-append">
                      <button type="button" class="btn input-group-text" id="<?= $_uid ?>28">28</button>
                    </div>

                  </div>

                  <script>
                    (_ => {
                      $('#<?= $_uid ?>7').on('click', e => $('#<?= $_uid ?>').val('<?= date('Y-m-d', strtotime('+7 days')) ?>'));
                      $('#<?= $_uid ?>14').on('click', e => $('#<?= $_uid ?>').val('<?= date('Y-m-d', strtotime('+14 days')) ?>'));
                      $('#<?= $_uid ?>28').on('click', e => $('#<?= $_uid ?>').val('<?= date('Y-m-d', strtotime('+28 days')) ?>'));

                    })(_brayworth_);
                  </script>

                </div>

              </div>

            </div>

          </div>

          <div class="form-row mb-2">
            <div class="col-md-3 col-xl-2 col-form-label">type</div>

            <div class="col pt-md-2">
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

          <div class="form-row mb-2">
            <div class="col-md-3 col-xl-2 col-form-label">payment</div>

            <div class="col pt-md-2">
              <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="job_payment" value="<?= config::job_payment_owner ?>" id="<?= $_uid = strings::rand() ?>" <?php if (config::job_payment_owner == $dto->job_payment) print 'checked'; ?>>

                <label class="form-check-label" for="<?= $_uid ?>">
                  Owner

                </label>

              </div>

              <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="job_payment" value="<?= config::job_payment_tenant ?>" id="<?= $_uid = strings::rand() ?>" <?php if (config::job_payment_tenant == $dto->job_payment) print 'checked'; ?>>

                <label class="form-check-label" for="<?= $_uid ?>">
                  Tenant

                </label>

              </div>

            </div>

          </div>

          <div class="form-row">
            <div class="col-md-3 col-xl-2 col-form-label">description</div>

            <div class="col-md mb-2">
              <textarea class="form-control" name="description" placeholder="describe the need for this job ..." required id="<?= $_uid = strings::rand() ?>"><?= $dto->description ?></textarea>

            </div>
            <script>
              (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => $('#<?= $_uid ?>').autoResize()))(_brayworth_);
            </script>

          </div>

          <!-- --[property]-- -->
          <div class="form-row">
            <div class="col-md-3 col-xl-2 col-form-label">property</div>

            <div class="col">
              <div class="form-row">
                <div class="col-md mb-2">
                  <input type="text" class="form-control" value="<?= $dto->address_street ?>" id="<?= $_uid = strings::rand() ?>">

                </div>

                <div class="col col-md-auto mb-2 d-none" id="<?= $_uid ?>suburb_div">
                  <div class="form-control" id="<?= $_uid ?>suburb"><?= $dto->address_suburb ?></div>
                </div>
                <div class="col-auto mb-2 d-none" id="<?= $_uid ?>postcode_div">
                  <div class="form-control" id="<?= $_uid ?>postcode"><?= $dto->address_postcode ?></div>
                </div>
                <script>
                  (_ => $('#<?= $_modal ?>')
                    .on('shown.bs.modal', () => {
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

                          $('#<?= $_form ?>')
                            .trigger('get-tenants')
                            .trigger('get-keyset')
                            .trigger('get-maintenance');

                        },

                      });

                      if (Number($('input[name="properties_id"]', '#<?= $_form ?>').val()) > 0) {
                        $('#<?= $_uid ?>suburb_div, #<?= $_uid ?>postcode_div').removeClass('d-none');

                      }

                    }))(_brayworth_);
                </script>

              </div>

              <div id="<?= $_uidMaintenanceRow = strings::rand() ?>"></div>

              <div id="<?= $_uidKeyRow = strings::rand() ?>"></div>

            </div>

          </div>

          <div class="form-row d-none" id="<?= $_uidTenants = strings::rand() ?>-envelope">
            <div class="col-md-3 col-xl-2 col-form-label">tenants</div>
            <div class="col" id="<?= $_uidTenants ?>"></div>

          </div>

          <!-- contractor -->
          <div class="form-row">
            <div class="col-md-3 col-xl-2 col-form-label"><?= strtolower(config::label_contractor) ?></div>

            <div class="col-md mb-2">
              <input type="text" class="form-control" value="<?= $dto->contractor_trading_name ?>" id="<?= $_uid = strings::rand() ?>">
              <div id="<?= $_missingServices = strings::rand() ?>"></div>

            </div>

            <script>
              (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
                let reqServices = $('input[name="required_services"]', '#<?= $_form ?>');
                $('#<?= $_uid ?>').autofill({
                  autoFocus: true,
                  source: (request, response) => {
                    _.post({
                      url: _.url('<?= $this->route ?>'),
                      data: {
                        action: 'search-contractor',
                        term: request.term,
                        services: reqServices.val()

                      },

                    }).then(d => response('ack' == d.response ? d.data : []));

                  },
                  select: (e, ui) => {
                    let o = ui.item;
                    $('input[name="contractor_id"]', '#<?= $_form ?>').val(o.id);
                    $('#<?= $_form ?>').trigger('qualify-contractor');

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
              <div class="d-none col-form-label" caption>items..</div>

            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" accesskey="N" id="<?= $_btnAddItem = strings::rand() ?>"><i class="bi bi-plus"></i> item</button>
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

        $('<div class="col-md mb-2" category></div>').append(cat).appendTo(row);

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

              $('#<?= $_form ?>').trigger('update-required-services');

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
              title: 'Confirm Row Delete',
              text: 'Note - action is immediate<br>(does not require saving)',
              buttons: {
                yes: function(e) {
                  $(this).modal('hide');
                  _me.trigger('delete-confirmed');

                }

              }

            });

          })
          .on('delete-confirmed', function(e) {
            let jobline = Number($('input[name="job_line_id\[\]"]', this).val());
            // console.log(jobline);

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
                  $('#<?= $_form ?>').trigger('update-required-services');

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
        .on('get-keyset', function(e) {
          let _form = $(this);
          let _data = _form.serializeFormJSON();

          // console.log('get-keyset');

          if (Number(_data.properties_id) > 0) {
            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'get-keys-for-property',
                id: _data.properties_id

              },

            }).then(d => {

              // console.log(d, _data.id);

              $('#<?= $_uidKeyRow ?>')
                .html('')
                .addClass('form-row mb-1');

              // $('<div class="col-2 pt-2">Keys</div>').appendTo('#<?= $_uidKeyRow ?>');
              let col = $('<div class="col pt-2"></div>').appendTo('#<?= $_uidKeyRow ?>');

              $.each(d.data, (i, keyset) => {
                let row = $('<div class="form-row"></div>').appendTo(col);
                let ig = $('<div class="input-group input-group-sm"></div>');
                ig.append('<div class="input-group-append"><div class="input-group-text">key</div></div>');

                $('<div class="form-control form-control-sm bg-light"></div>')
                  .html(keyset.keyset)
                  .appendTo(ig);

                $('<div class="col-md-2 mb-1"></div>')
                  .append(ig)
                  .appendTo(row)

                $('<div class="col mb-2"></div>')
                  .append(
                    $('<div class="form-control form-control-sm bg-light"></div>')
                    .html(keyset.people_id > 0 ? keyset.name : keyset.location)
                  )
                  .appendTo(row)


              });

            });

          } else {
            $('#<?= $_uidKeyRow ?>')
              .html('')
              .removeClass();

          }

        })
        .on('get-maintenance', function(e) {
          let _form = $(this);
          let _data = _form.serializeFormJSON();

          // console.log('get-maintenance');

          if (Number(_data.properties_id) > 0) {
            _.post({
              url: _.url('leasing'),
              data: {
                action: 'get-maintenance-instructions',
                id: _data.properties_id

              },

            }).then(d => {
              // console.log(d);

              let col = $('<div class="col"></div>');
              $('#<?= $_uidMaintenanceRow ?>')
                .html('')
                .addClass('form-row')
                .append(col);

              col.append('<h6>maintenance instructions</h6>');

              if ('ack' == d.response) {
                //~ console.log( d);
                if (d.data.length > 0) {
                  //~ $('#<?= $_uid ?>').closest( '.row').removeClass('d-none');
                  $.each(d.data, (i, sched) => {
                    let row = $('<div class="form-row"></div>');

                    let type = $('<div class="form-control form-control-sm bg-light"></div>').html(sched.Type);
                    let limit = $('<div class="form-control form-control-sm bg-light text-right"></div>').html(sched.Limit);
                    let notes = $('<div class="form-control form-control-sm bg-light h-auto"></div>').html(sched.Notes);

                    let fglimit = $('<div class="input-group input-group-sm"><div class="input-group-prepend"><div class="input-group-text">limit</div></div></div>');
                    fglimit.append(limit);

                    $('<div class="col-6 col-md-2 mb-1"></div>')
                      .append(type)
                      .appendTo(row);
                    $('<div class="col-6 col-md-3 mb-1"></div>')
                      .append(fglimit)
                      .appendTo(row);
                    $('<div class="col-md-7 mb-2"></div>')
                      .append(notes)
                      .appendTo(row);

                    col.append(row);
                    //~ console.log( sched);

                  });

                } else {
                  $('#<?= $_uidMaintenanceRow ?>')
                    .addClass('text-muted font-italic pt-2')
                    .html('no maintenance instructions found...');

                }

              } else {
                _.growl(d);

              }

            });

          }

        })
        .on('get-tenants', function(e) {
          let _form = $(this);
          let _data = _form.serializeFormJSON();

          if (_data.properties_id) {
            _.post({
              url: _.url('leasing'),
              data: {
                action: 'get-tenants-for-property',
                id: _data.properties_id

              }

            }).then(d => {
              if ('ack' == d.response) {
                $('#<?= $_uidTenants ?>').html('');
                $.each(d.tenants, (i, t) => {
                  // console.log(t);
                  let row = $('<div class="form-row"></div>').appendTo('#<?= $_uidTenants ?>');
                  $('<div class="col-md-2 mb-1"></div>')
                    .append(
                      $('<div class="form-control form-control-sm bg-light"></div>')
                      .html(t.name)
                    )
                    .appendTo(row);

                  let col = $('<div class="col-md-auto mb-1"></div>').appendTo(row);
                  let m = String(t.phone);
                  if (m.IsMobilePhone()) {
                    let ig = $('<div class="input-group input-group-sm"></div>').appendTo(col);
                    $('<input type="text" class="form-control" readonly>').val(m.AsMobilePhone()).appendTo(ig);

                    let btn = $('<button type="button" class="btn input-group-text"><i class="bi bi-chat-dots"></i></button>');
                    btn.on('click', function(e) {
                      e.stopPropagation();
                      e.preventDefault();

                      if (!!window._cms_) {
                        _cms_.modal.sms({
                          to: m
                        });

                      } else {
                        _.ask.warning({
                          title: 'Warning',
                          text: 'no SMS program'
                        });

                      }

                    });

                    $('<div class="input-group-append"></div>').append(btn).appendTo(ig);

                  } else if (m.IsPhone()) {
                    col.html(m.AsLocalPhone()).addClass('p-2');

                  }

                  col = $('<div class="col-md mb-2"></div>').appendTo(row);
                  if (String(t.email).isEmail()) {
                    let ig = $('<div class="input-group input-group-sm"></div>').appendTo(col);
                    $('<input type="text" class="form-control" readonly>').val(t.email).appendTo(ig);

                    let btn = $('<button type="button" class="btn input-group-text"><i class="bi bi-cursor"></i></button>');
                    btn.on('click', function(e) {
                      e.stopPropagation();
                      e.preventDefault();

                      if (!!_.email.activate) {
                        _.email.activate({
                          to: _.email.rfc922(t)
                        });

                      } else {
                        _.ask.warning({
                          title: 'Warning',
                          text: 'no email program'
                        });

                      }

                    });

                    $('<div class="input-group-append"></div>').append(btn).appendTo(ig);

                  } else {
                    col.html(t.email).addClass('p-2');

                  }

                });

                $('#<?= $_uidTenants ?>-envelope').removeClass('d-none');
                // console.log($('#<?= $_uidTenants ?>-envelope'));

              } else {
                _.growl(d);
                $('#<?= $_uidTenants ?>-envelope').addClass('d-none');

              }

            });

          } else {
            $('#<?= $_uidTenants ?>-envelope').addClass('d-none');

          }

        })
        .on('item-add', e => {
          let row = newRow();
          $('select[name="item_job_categories_id\[\]"]', row).focus();

        })
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

          $(this)
            .on('qualify-contractor', function(e) {
              let _form = $(this);
              let _data = _form.serializeFormJSON();

              if (Number(_data.contractor_id) > 0 && '' != _data.required_services) {

                _.post({
                  url: _.url('<?= $this->route ?>'),
                  data: {
                    action: 'get-contractor-by-id',
                    id: _data.contractor_id

                  },

                }).then(d => {
                  if ('ack' == d.response) {
                    let requiredServices = String(_data.required_services).split(',');
                    let contractorServices = String(d.data.services).split(',');
                    let missingServices = [];
                    let missingServicesNames = [];

                    $.each(requiredServices, (i, s) => {
                      if (contractorServices.indexOf(s) < 0 && missingServices.indexOf(s) < 0) {
                        missingServices.push(s);
                        missingServicesNames.push(cats[s]);
                      }
                    });
                    if (missingServices.length > 0) {
                      $('#<?= $_missingServices ?>')
                        .addClass('text-danger font-italic small pl-2')
                        .html('contractor does not provide ' + missingServicesNames.join(', '));

                    } else {
                      $('#<?= $_missingServices ?>').removeClass().html('');

                    }

                  } else {
                    _.growl(d);

                  }

                });

              }

            })
            .trigger('qualify-contractor');


          $(this).trigger('get-tenants');

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

        })
        .on('update-required-services', function(e) {

          let services = [];
          $('select[name="item_job_categories_id\[\]"]', this).each((i, el) => {
            let service = $(el).val();
            if (services.indexOf(service) < 0) services.push(service);
          });
          $('input[name="required_services" ]', this).val(services.join(','));
          $(this).trigger('qualify-contractor');
        });

      $('#<?= $_form ?>')
        .trigger('get-keyset')
        .trigger('get-maintenance')
        .trigger('items-init');
    }))(_brayworth_);
  </script>

</form>