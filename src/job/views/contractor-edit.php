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

$_btnEditPrimaryContact = false;
$dto = $this->data->dto;
$categories = $this->data->categories;  ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="action" value="contractor-save">
  <input type="hidden" name="id" value="<?= $dto->id ?>">

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
            <label class="col-md-3 text-truncate col-form-label" for="<?= $_uid = strings::rand() ?>">
              T/As
            </label>

            <div class="col">
              <input type="text" class="form-control" name="trading_name" placeholder="trading name" value="<?= $dto->trading_name ?>" id="<?= $_uid ?>">

            </div>

          </div>

          <div class="form-row mb-2">
            <label class="col-3 text-truncate col-form-label" for="<?= $_uid = strings::rand() ?>">
              Company
            </label>

            <div class="col">
              <input type="text" class="form-control" name="company_name" placeholder="company name" value="<?= $dto->company_name ?>" id="<?= $_uid ?>">

            </div>

          </div>

          <div class="form-row mb-2">
            <label class="col-md-3 text-truncate col-form-label" for="<?= $_uid = strings::rand() ?>">
              ABN
            </label>

            <div class="col">
              <div class="input-group">
                <input type="text" class="form-control" name="abn" placeholder="abn" value="<?= $dto->abn ?>" id="<?= $_uid ?>">

                <div class="input-group-append">
                  <button type="button" class="btn btn-light" id="<?= $_uid = strings::rand() ?>"><i class="bi bi-search"></i></button>

                </div>

              </div>

              <script>
                (_ => $(document).ready(() => {
                  $('#<?= $_uid ?>').on('click', e => {
                    e.stopPropagation();
                    $('#<?= $_form ?>').trigger('abn-search');

                  });

                }))(_brayworth_);
              </script>

            </div>

          </div>

          <!-- Services -->
          <div class="form-row mb-2">
            <div class="col-md-3 text-truncate">
              Services
            </div>

            <div class="col">
              <input type="hidden" name="services" value="<?= $dto->services ?>">
              <?php
              if ($dto->services) {
                $services = explode(',', $dto->services);
                foreach ($services as $service) {
                  $text = isset($categories[$service]) ? $categories[$service] : $service;
                  $_uid = strings::rand();
                  printf(
                    '<div class="form-check">
                    <input type="checkbox" checked data-role="service" class="form-check-input" value="%s" id="%s">
                    <label class="form-check-label" for="%s">%s</label>
                    </div>',
                    $service,
                    $_uid,
                    $_uid,
                    $text

                  );
                }
              } ?>

              <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="<?= $_btnAddService = strings::rand() ?>" data-categories="<?= htmlspecialchars(json_encode($categories)) ?>">add service</button>

            </div>

          </div>

          <!-- Primary Contact -->
          <div class="form-row">
            <div class="col-md-3 text-truncate col-form-label d-flex">
              Primary Contact
              <?php if ($this->data->primary_contact) { ?>
                <button type="button" class="btn btn-light btn-sm ml-auto mb-auto rounded-circle" title="change primary contact" id="<?= $_btnEditPrimaryContact = strings::rand() ?>"><i class="bi bi-pencil"></i></button>
              <?php } ?>

            </div>

            <div class="col">
              <input type="hidden" name="primary_contact" value="<?= $dto->primary_contact ?>">

              <?php
              if ($this->data->primary_contact) {

                $primary_contact = $this->data->primary_contact;
                $primary_contact_name = $dto->trading_name == $primary_contact->name ? $primary_contact->salute : $primary_contact->name;
              ?>

                <div class="form-row mb-2">
                  <div class="col-md">
                    <div class="form-control">
                      <?= $primary_contact_name ?>

                    </div>

                  </div>

                  <div class="col-md">
                    <div class="input-group">

                      <div class="input-group-prepend">
                        <div class="input-group-text">role</div>
                      </div>

                      <input class="form-control" type="text" name="primary_contact_role" value="<?= $dto->primary_contact_role ?>">

                    </div>

                  </div>

                </div>

                <?php if ($primary_contact->mobile && strings::isPhone($primary_contact->mobile)) {  ?>
                  <div class="form-row mb-2">
                    <div class="col">
                      <div class="input-group">

                        <div class="input-group-prepend">
                          <div class="input-group-text">
                            <i class="bi bi-phone"></i>

                          </div>

                        </div>

                        <input type="text" class="form-control" value="<?= strings::asMobilePhone($primary_contact->mobile) ?>">

                        <?php if (strings::isMobilePhone($primary_contact->mobile)) {  ?>
                          <div class="input-group-append">
                            <button type="button" class="btn input-group-text" title="send sms" id="<?= $_btnSendSms = strings::rand() ?>">
                              <i class="bi bi-chat-dots"></i>

                            </button>

                          </div>

                          <script>
                            (_ => $(document).ready(() => {
                              $('#<?= $_btnSendSms ?>').on('click', function(e) {
                                e.stopPropagation();
                                e.preventDefault();

                                $('#<?= $_modal ?>').trigger('send-sms', {
                                  'name': <?= json_encode($primary_contact_name) ?>,
                                  'mobile': <?= json_encode(strings::cleanPhoneString($primary_contact->mobile)) ?>

                                });
                                $('#<?= $_modal ?>').modal('hide');

                              });

                            }))(_brayworth_);
                          </script>

                        <?php }  ?>

                      </div>

                    </div>

                  </div>

                <?php }  ?>

                <?php if ($primary_contact->telephone && strings::isPhone($primary_contact->telephone)) {  ?>
                  <div class="form-row mb-2">
                    <div class="col">
                      <div class="input-group">

                        <div class="input-group-prepend">
                          <div class="input-group-text">
                            <i class="bi bi-telephone"></i>

                          </div>

                        </div>

                        <input type="text" class="form-control" value="<?= strings::asLocalPhone($primary_contact->telephone) ?>">

                      </div>

                    </div>

                  </div>

                <?php }  ?>

                <?php if ($primary_contact->telephone_business && strings::isPhone($primary_contact->telephone_business)) {  ?>
                  <div class="form-row mb-2">
                    <div class="col">
                      <div class="input-group">

                        <div class="input-group-prepend">
                          <div class="input-group-text">
                            <i class="bi bi-telephone"></i>

                          </div>

                        </div>

                        <input type="text" class="form-control" value="<?= strings::asLocalPhone($primary_contact->telephone_business) ?>">

                      </div>

                    </div>

                  </div>

                <?php }  ?>

              <?php
              } else {  ?>
                <div class="form-row mb-2">
                  <div class="col-md">
                    <input type="search" name="primary_contact_name" class="form-control" id="<?= $_uid = strings::rand() ?>">
                    <script>
                      (_ => {
                        $('#<?= $_modal ?>').on('shown.bs.modal', e => {
                          $('#<?= $_uid ?>').autofill({
                            autoFocus: true,
                            source: (request, response) => {
                              _.post({
                                url: _.url('people'),
                                data: {
                                  action: 'search',
                                  term: request.term

                                },

                              }).then(d => {
                                if ('ack' == d.response) {
                                  response(d.data);

                                }

                              });

                            },
                            select: (e, ui) => {
                              var o = ui.item;
                              if (o.id > 0) {
                                $('input[name="primary_contact"]', '#<?= $_form ?>').val(o.id);

                              }

                            }

                          });

                        });

                      })(_brayworth_);
                    </script>

                  </div>

                  <div class="col-md">
                    <div class="input-group">

                      <div class="input-group-prepend">
                        <div class="input-group-text">role</div>
                      </div>

                      <input class="form-control" type="text" name="primary_contact_role" value="<?= $dto->primary_contact_role ?>">

                    </div>

                  </div>

                </div>

              <?php
              } ?>

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
      $('#<?= $_form ?> input[data-role="service"]').each((i, chk) => {
        $(chk).on('change', function(e) {
          let _me = $(this);
          _me.parent().remove();

          $('#<?= $_form ?>').trigger('check-services');

        });

      });

      $('#<?= $_btnAddService ?>').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        let _me = $(this);
        let _data = _me.data();

        let ctrl = $('<select class="form-control mt-2"></select>');
        $('option', ctrl).each((i, o) => $(o).remove());
        ctrl.append('<option>select service</option>');
        let services = String($('#<?= $_form ?> input[name="services"]').val()).split(',');
        $.each(_data.categories, (i, cat) => {
          if (services.indexOf(i) < 0) {
            ctrl.append('<option value="' + i + '">' + cat + '</option>')

          }

        });

        ctrl.on('change', function(e) {

          let id = Math.random().toString(36).slice(2);

          // console.log(this.value, this.options[this.selectedIndex].text);

          let chk = $('<input type="checkbox" class="form-check-input" checked data-role="service" value="' + this.value + '" id="' + id + '">');
          chk.on('change', function(e) {
            let _me = $(this);
            _me.parent().remove();

            $('#<?= $_form ?>').trigger('check-services');

          });

          $('<div class="form-check"></div>')
            .append(chk)
            .append('<label class="form-check-label" for="' + id + '">' + this.options[this.selectedIndex].text + '</labellabel>')
            .insertBefore(this);

          $('#<?= $_form ?>').trigger('check-services');
          $(this).remove();
          _me.removeClass('d-none');

        });

        $(this).addClass('d-none');
        ctrl.insertBefore(this);

      });

      $('#<?= $_form ?>')
        .on('abn-search', function(e) {
          let _form = $(this);
          let _data = _form.serializeFormJSON();

          let abn = String(_data.abn).replace(/[^0-9]/, '');

          if ('' != abn) {
            window.open('https://abr.business.gov.au/ABN/View?id=' + abn)

          }

        })
        .on('check-services', function(e) {
          let services = [];
          $('input[data-role="service"]', this)
            .each((i, chk) => services.push($(chk).val()));

          $('input[name="services"]', this).val(services.join(','));

        })
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

          // console.table( _data);

          return false;

        });

      <?php if ($_btnEditPrimaryContact) {  ?>
        $('input[type="checkbox"], input[type="text"]', '#<?= $_form ?>')
          .on('change', e => $('#<?= $_btnEditPrimaryContact ?>').addClass('d-none'));

        $('#<?= $_btnEditPrimaryContact ?>').on('click', function(e) {
          e.stopPropagation();
          e.preventDefault();

          $('#<?= $_modal ?>').trigger('edit-primary-contact');
          $('#<?= $_modal ?>').modal('hide');

        });

      <?php }  ?>

    }))(_brayworth_);
  </script>

</form>