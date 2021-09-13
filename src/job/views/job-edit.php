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
$categories = $this->data->categories;
$readonly = $dto->complete || $dto->status > 0 || strtotime($dto->archived) > 0 || $this->data->hasInvoice;

$validActions = [
  'job-save',
  'job-save-recurrence',
  'job-save-payment'
];

$action = 'job-save';
if (config::job_status_paid == $dto->status) {
  $action = 'job-invalid-action';
} elseif ($readonly) {
  if (config::job_type_recurring == $dto->job_type) {
    $action = 'job-save-recurrence';
  } else {
    $action = 'job-save-payment';
  }
} ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="action" value="<?= $action ?>">
  <input type="hidden" name="id" value="<?= $dto->id ?>">
  <input type="hidden" name="properties_id" value="<?= $dto->properties_id ?>">
  <input type="hidden" name="contractor_id" value="<?= $dto->contractor_id ?>">
  <input type="hidden" name="required_services">

  <style>
    #<?= $_form ?>button:focus {
      box-shadow: none;
    }

    .upload-quote,
    .upload-invoice {
      margin-top: -3px !important;
      margin-bottom: -3px !important;
    }

    .upload-invoice .has-advanced-upload::before {
      content: "upload invoice";

    }

    .upload-quote .has-advanced-upload::before {
      content: "upload quote";

    }

    @media (max-width: 768px) {
      [item-row] {
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
      }

    }

    @media (min-width: 1200px) {
      #<?= $_uidStatBar = strings::rand() ?> {
        position: absolute;

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
            <div class="form-row">
              <div class="col position-relative">
                <div class="container-fluid" id="<?= $_uidStatBar ?>">
                  <div class="form-row mb-2">
                    <div class="col">&nbsp;</div>
                    <?php

                    // status
                    printf(
                      '<div class="col-auto">
                      <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                          <div class="input-group-text">status</div>
                        </div>
                        <div class="form-control">%s</div>
                      </div>
                    </div>',
                      config::job_status[$dto->status]

                    );

                    $_u = [strings::asShortDate($dto->created)];
                    $_title = '';
                    if ($dto->created_by_name) {
                      $_title = 'created by ' . $dto->created_by_name;
                      $_u[] = strings::initials($dto->created_by_name);
                    }

                    printf(
                      '<div class="col-auto" title="%s">
                      <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                          <div class="input-group-text">create</div>
                        </div>
                        <div class="form-control">%s</div>
                      </div>
                    </div>',
                      $_title,
                      implode(' / ', $_u)
                    );

                    if (strtotime($dto->updated) > strtotime($dto->created)) {
                      if ($dto->updated_by_name) {
                        $_title = 'updated by ' . $dto->updated_by_name;
                        $_u[] = strings::initials($dto->updated_by_name);
                      }
                      printf(
                        '<div class="col-auto" title="%s">
                        <div class="input-group input-group-sm">
                          <div class="input-group-prepend">
                            <div class="input-group-text">update</div>
                          </div>
                          <div class="form-control">%s</div>
                        </div>
                      </div>',
                        $_title,
                        implode(' / ', $_u)

                      );
                    }  ?>

                  </div>
                </div>
              </div>
            </div>

          <?php }  ?>

          <!-- --[due]-- -->
          <div class="form-row">
            <div class="col-3 col-xl-2 col-form-label">due</div>

            <div class="col-lg-3 col-xl-4">
              <div class="input-group">
                <input name="due" class="form-control" type="date" <?= $readonly ? 'disabled' : 'required' ?> id="<?= $_uid = strings::rand() ?>" value="<?= strtotime($dto->due) > 0 ? $dto->due : '' ?>">

                <?php if ($readonly) {
                  if (!$dto->complete) { ?>
                    <div class="input-group-append">
                      <button type="button" class="btn input-group-text" id="<?= $_uid ?>bump">bump</button>
                    </div>
                    <script>
                      $('#<?= $_uid ?>bump')
                        .on('click', e => {
                          e.stopPropagation();

                          $('#<?= $_form ?>').trigger('bump');

                        })
                    </script>
                  <?php }
                } else { ?>
                  <div class="input-group-append d-none">
                    <button type="button" class="btn input-group-text" id="<?= $_uid ?>7">7</button>
                  </div>

                  <div class="input-group-append d-none">
                    <button type="button" class="btn input-group-text" id="<?= $_uid ?>14">14</button>
                  </div>

                  <div class="input-group-append">
                    <button type="button" class="btn input-group-text" id="<?= $_uid ?>28">28</button>
                  </div>

                  <script>
                    $('#<?= $_uid ?>7')
                      .on('click', e => $('#<?= $_uid ?>').val('<?= date('Y-m-d', strtotime('+7 days')) ?>'));
                    $('#<?= $_uid ?>14')
                      .on('click', e => $('#<?= $_uid ?>').val('<?= date('Y-m-d', strtotime('+14 days')) ?>'));
                    $('#<?= $_uid ?>28')
                      .on('click', e => $('#<?= $_uid ?>').val('<?= date('Y-m-d', strtotime('+28 days')) ?>'));
                  </script>
                <?php } ?>

              </div>

            </div>

          </div>

          <!-- --[type/recurrence]-- -->
          <div class="form-row">
            <!-- --[type]-- -->
            <div class="col-xl-6">
              <div class="form-row mb-2">
                <div class="col-3 col-xl-4 col-form-label">type</div>

                <div class="col-lg pt-2">
                  <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="job_type" <?= $readonly ? 'disabled' : 'required' ?> value="<?= config::job_type_order ?>" id="<?= $_uid = strings::rand() ?>" <?= config::job_type_order == $dto->job_type ? 'checked' : ''; ?>>

                    <label class="form-check-label" for="<?= $_uid ?>">Order</label>

                  </div>

                  <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="job_type" <?= $readonly ? 'disabled' : 'required' ?> value="<?= config::job_type_recurring ?>" id="<?= $_uid = strings::rand() ?>" <?= config::job_type_recurring == $dto->job_type ? 'checked' : ''; ?>>

                    <label class="form-check-label" for="<?= $_uid ?>">Recur<span class="d-sm-none">...</span><span class="d-none d-sm-inline">ring</span></label>

                  </div>

                  <div class="form-check form-check-inline mr-0">
                    <input type="radio" class="form-check-input" name="job_type" <?= $readonly ? 'disabled' : 'required' ?> value="<?= config::job_type_quote ?>" id="<?= $_uid = strings::rand() ?>" <?= config::job_type_quote == $dto->job_type ? 'checked' : ''; ?>>

                    <label class="form-check-label" for="<?= $_uid ?>">Quote</label>

                  </div>

                </div>

              </div>

            </div>

            <!-- --[recurrence]-- -->
            <div class="col-xl" id="<?= $_uidRecurrenceCell = strings::rand() ?>">
              <div class="form-row">

                <div class="col-3 col-form-label">recurrence</div>
                <div class="col-lg pt-2">
                  <div class="form-row mb-2">
                    <div class="col">
                      <?php

                      $_template = '<div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" name="job_recurrence_interval" value="%s" id="%s" %s %s>
                        <label class="form-check-label" for="%s">%s</label>
                      </div>';

                      printf(
                        $_template,
                        config::job_recurrence_interval_week,
                        $_uid = strings::rand(),
                        config::job_recurrence_interval_week == $dto->job_recurrence_interval ? 'checked' : '',
                        $dto->job_recurrence_disable ? 'disabled' : '',
                        $_uid,
                        'Week'

                      );

                      printf(
                        $_template,
                        config::job_recurrence_interval_month,
                        $_uid = strings::rand(),
                        config::job_recurrence_interval_month == $dto->job_recurrence_interval ? 'checked' : '',
                        $dto->job_recurrence_disable ? 'disabled' : '',
                        $_uid,
                        'Month'

                      );

                      printf(
                        $_template,
                        config::job_recurrence_interval_year,
                        $_uid = strings::rand(),
                        config::job_recurrence_interval_year == $dto->job_recurrence_interval ? 'checked' : '',
                        $dto->job_recurrence_disable ? 'disabled' : '',
                        $_uid,
                        'Year'

                      );

                      ?>
                    </div>

                  </div>

                  <!-- --[end]-- -->
                  <div class="form-row mb-2">
                    <div class="col">
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <div class="input-group-text">end</div>
                        </div>

                        <?php
                        printf(
                          '<input type="date" class="form-control" name="job_recurrence_end" value="%s" id="%s" %s>',
                          strtotime($dto->job_recurrence_end) > 0 ? $dto->job_recurrence_end : '',
                          $_uid = strings::rand(),
                          $dto->job_recurrence_disable ? 'disabled' : ''

                        );  ?>

                        <?php if (!$dto->job_recurrence_disable) { ?>
                          <div class="input-group-append">
                            <button type="button" class="btn input-group-text" id="<?= $_uid ?>-reset" title="clear end date field"><i class="bi bi-x"></i></button>
                          </div>

                          <script>
                            (_ => {
                              $('#<?= $_uid ?>-reset').on('click', function(e) {
                                e.stopPropagation();

                                $('#<?= $_uid ?>').val('');

                              });

                            })(_brayworth_);
                          </script>

                        <?php } ?>

                      </div>

                    </div>

                  </div>

                  <div class="form-row mb-2 d-none" id="<?= $_uidRecurrenceWeekFrequency = strings::rand() ?>">
                    <div class="col">
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <div class="input-group-text">every</div>
                        </div>

                        <?php
                        printf(
                          '<input type="number" class="form-control" name="job_recurrence_week_frequency" value="%s" min="1" max="9" %s>',
                          $dto->job_recurrence_week_frequency,
                          $dto->job_recurrence_disable ? 'disabled' : ''

                        );
                        ?>

                        <div class="input-group-append">
                          <div class="input-group-text"> week/s</div>
                        </div>

                      </div>

                    </div>

                  </div>

                  <div class="form-row mb-2 d-none" id="<?= $_uidRecurrenceMonthFrequency = strings::rand() ?>">
                    <div class="col">
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <div class="input-group-text">every</div>
                        </div>

                        <?php
                        printf(
                          '<input type="number" class="form-control" name="job_recurrence_month_frequency" value="%s" min="1" max="9" %s>',
                          $dto->job_recurrence_month_frequency,
                          $dto->job_recurrence_disable ? 'disabled' : ''

                        );
                        ?>

                        <div class="input-group-append">
                          <div class="input-group-text"> month/s</div>
                        </div>

                      </div>

                    </div>

                  </div>

                  <div class="form-row mb-2 d-none" id="<?= $_uidRecurrenceYearFrequency = strings::rand() ?>">
                    <div class="col">
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <div class="input-group-text">every</div>
                        </div>

                        <?php
                        printf(
                          '<input type="number" class="form-control" name="job_recurrence_year_frequency" value="%s" min="1" max="9" %s>',
                          $dto->job_recurrence_year_frequency,
                          $dto->job_recurrence_disable ? 'disabled' : ''

                        );
                        ?>

                        <div class="input-group-append">
                          <div class="input-group-text"> years/s</div>
                        </div>

                      </div>

                    </div>

                  </div>

                  <div class="form-row mb-2 d-none" id="<?= $_uidRecurrenceDayOfWeek = strings::rand() ?>">
                    <div class="col">
                      <?php
                      $_template = '<div class="form-check form-check-inline"><input type="checkbox" class="form-check-input" name="job_recurrence_day_of_week[]" value="%s" id="%s" %s %s><label class="form-check-label" for="%s">%s</label></div>';
                      $recurrenceDays = explode(',', $dto->job_recurrence_day_of_week);

                      printf(
                        $_template,
                        config::job_recurrence_day_monday,
                        $_uid = strings::rand(),
                        in_array(config::job_recurrence_day_monday, $recurrenceDays) ? 'checked' : '',
                        $dto->job_recurrence_disable ? 'disabled' : '',
                        $_uid,
                        'Mon'

                      );

                      printf(
                        $_template,
                        config::job_recurrence_day_tuesday,
                        $_uid = strings::rand(),
                        in_array(config::job_recurrence_day_tuesday, $recurrenceDays) ? 'checked' : '',
                        $dto->job_recurrence_disable ? 'disabled' : '',
                        $_uid,
                        'Tue'

                      );

                      printf(
                        $_template,
                        config::job_recurrence_day_wednesday,
                        $_uid = strings::rand(),
                        in_array(config::job_recurrence_day_wednesday, $recurrenceDays) ? 'checked' : '',
                        $dto->job_recurrence_disable ? 'disabled' : '',
                        $_uid,
                        'Wed'

                      );

                      printf(
                        $_template,
                        config::job_recurrence_day_thursday,
                        $_uid = strings::rand(),
                        in_array(config::job_recurrence_day_thursday, $recurrenceDays) ? 'checked' : '',
                        $dto->job_recurrence_disable ? 'disabled' : '',
                        $_uid,
                        'Thur'

                      );

                      printf(
                        $_template,
                        config::job_recurrence_day_friday,
                        $_uid = strings::rand(),
                        in_array(config::job_recurrence_day_friday, $recurrenceDays) ? 'checked' : '',
                        $dto->job_recurrence_disable ? 'disabled' : '',
                        $_uid,
                        'Fri'

                      );

                      printf(
                        $_template,
                        config::job_recurrence_day_saturday,
                        $_uid = strings::rand(),
                        in_array(config::job_recurrence_day_saturday, $recurrenceDays) ? 'checked' : '',
                        $dto->job_recurrence_disable ? 'disabled' : '',
                        $_uid,
                        'Sat'

                      );

                      printf(
                        $_template,
                        config::job_recurrence_day_sunday,
                        $_uid = strings::rand(),
                        in_array(config::job_recurrence_day_sunday, $recurrenceDays) ? 'checked' : '',
                        $dto->job_recurrence_disable ? 'disabled' : '',
                        $_uid,
                        'Sun'

                      );

                      ?>
                    </div>

                  </div>

                  <div class="form-row mb-2 d-none" id="<?= $_uidRecurrenceOnBusinessDay = strings::rand() ?>">
                    <div class="col">
                      <div class="form-check">
                        <?php
                        printf(
                          '<input type="checkbox" class="form-check-input" name="%s" value="1" id="%s" %s %s>',
                          'job_recurrence_on_business_day',
                          $_uid = strings::rand(),
                          1 == $dto->job_recurrence_on_business_day ? 'checked' : '',
                          $dto->job_recurrence_disable ? 'disabled' : ''

                        );
                        ?>

                        <label class="form-check-label" for="<?= $_uid ?>">
                          recur on business day
                        </label>

                      </div>

                    </div>
                  </div>

                  <div class="form-row mb-2 d-none" id="<?= $_uidRecurrenceDayOfMonth = strings::rand() ?>">
                    <div class="col-6 col-lg-3 pb-2">
                      <?php
                      $_template = '<div class="form-check"><input type="checkbox" class="form-check-input" name="job_recurrence_day_of_month[]" value="%s" id="%s" %s %s><label class="form-check-label" for="%s">%s</label></div>';
                      $recurrenceDays = explode(',', $dto->job_recurrence_day_of_month);

                      for ($i = 1; $i <= 31; $i++) {
                        printf(
                          $_template,
                          $i,
                          $_uid = strings::rand(),
                          in_array($i, $recurrenceDays) ? 'checked' : '',
                          $dto->job_recurrence_disable ? 'disabled' : '',
                          $_uid,
                          $i

                        );

                        if (0 == $i % 8) print '</div><div class="col-6 col-lg-3 pb-2">';
                      } ?>

                    </div>

                  </div>

                </div>

              </div>

            </div>

          </div>

          <!-- --[payment]-- -->
          <div class="form-row mb-2">
            <div class="col-3 col-xl-2 col-form-label">payment</div>

            <div class="col pt-2">
              <?php
              $_template =
                '<div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" name="job_payment" value="%s" id="%s" %s %s>
                  <label class="form-check-label" for="%s">%s</label>
                </div>';

              printf(
                $_template,
                config::job_payment_owner,
                $_uid = strings::rand(),
                config::job_payment_owner == $dto->job_payment ? 'checked' : '',
                config::job_status_paid == $dto->status ? 'disabled' : '',
                $_uid,
                'Owner'

              );

              printf(
                $_template,
                config::job_payment_tenant,
                $_uid = strings::rand(),
                config::job_payment_tenant == $dto->job_payment ? 'checked' : '',
                config::job_status_paid == $dto->status ? 'disabled' : '',
                $_uid,
                'Tenant'

              );

              printf(
                $_template,
                config::job_payment_none,
                $_uid = strings::rand(),
                config::job_payment_none == $dto->job_payment ? 'checked' : '',
                config::job_status_paid == $dto->status ? 'disabled' : '',
                $_uid,
                'Not required'

              );

              ?>
            </div>

          </div>

          <!-- --[property]-- -->
          <div class="form-row">
            <div class="col-md-3 col-xl-2 col-form-label">property</div>

            <div class="col">
              <div class="form-row">
                <div class="col-md mb-2">
                  <input type="text" class="form-control" value="<?= $dto->address_street ?>" id="<?= $_uidAddress = strings::rand() ?>" <?= $readonly ? 'disabled' : '' ?>>

                </div>

                <div class="col col-md-auto mb-2 d-none" id="<?= $_uidAddress ?>suburb_div">
                  <div class="form-control" id="<?= $_uidAddress ?>suburb" <?= $readonly ? 'readonly' : '' ?>><?= $dto->address_suburb ?></div>
                </div>
                <div class="col-auto mb-2 d-none" id="<?= $_uidAddress ?>postcode_div">
                  <div class="form-control" id="<?= $_uidAddress ?>postcode" <?= $readonly ? 'readonly' : '' ?>><?= $dto->address_postcode ?></div>
                </div>
                <div class="col-auto mb-2 d-none" id="<?= $_uidKeyCell = strings::rand() ?>"></div>
                <script>
                  (_ => $('#<?= $_modal ?>')
                    .on('set-property', prop => {
                      $('input[name="properties_id"]', '#<?= $_form ?>').val(prop.id);
                      $('#<?= $_uidAddress ?>suburb').html(prop.suburb);
                      $('#<?= $_uidAddress ?>postcode').html(prop.postcode);
                      $('#<?= $_uidAddress ?>suburb_div, #<?= $_uidAddress ?>postcode_div').removeClass('d-none');

                      $('#<?= $_form ?>')
                        .trigger('get-tenants')
                        .trigger('get-keyset')
                        .trigger('get-maintenance');
                    })
                    .on('shown.bs.modal', () => {
                      $('#<?= $_uidAddress ?>').autofill({
                        autoFocus: true,
                        source: _.search.address,
                        select: (e, ui) => {
                          let o = ui.item;
                          // console.log( o);
                          $('input[name="properties_id"]', '#<?= $_form ?>').val(o.id);
                          $('#<?= $_uidAddress ?>suburb').html(o.suburb);
                          $('#<?= $_uidAddress ?>postcode').html(o.postcode);
                          $('#<?= $_uidAddress ?>suburb_div, #<?= $_uidAddress ?>postcode_div').removeClass('d-none');

                          $('#<?= $_form ?>')
                            .trigger('get-tenants')
                            .trigger('get-keyset')
                            .trigger('get-maintenance');

                        },

                      });

                      if (Number($('input[name="properties_id"]', '#<?= $_form ?>').val()) > 0) {
                        $('#<?= $_uidAddress ?>suburb_div, #<?= $_uidAddress ?>postcode_div').removeClass('d-none');

                      }

                    }))(_brayworth_);
                </script>

              </div>

            </div>

          </div>

          <!-- --[on_site_contact]-- -->
          <div class="form-row">
            <div class="col-md-3 col-xl-2 col-form-label">site contact</div>

            <div class="col">
              <div class="form-row">
                <div class="col-md mb-2">
                  <input type="text" class="form-control" name="on_site_contact" <?= $readonly ? 'disabled' : '' ?> value="<?= $dto->on_site_contact ?>" maxlength="100" id="<?= $_uid = strings::rand() ?>">

                </div>

              </div>

            </div>

          </div>

          <!-- --[description]-- -->
          <div class="form-row">
            <div class="col-md-3 col-xl-2 col-form-label">description</div>

            <div class="col-md mb-2">
              <textarea class="form-control" name="description" placeholder="describe the need for this job ..." <?= $readonly ? 'disabled' : 'required' ?> id="<?= $_uid = strings::rand() ?>"><?= $dto->description ?></textarea>

            </div>
            <script>
              (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => $('#<?= $_uid ?>').autoResize()))(_brayworth_);
            </script>

          </div>

          <div class="form-row mb-2 d-none" id="<?= $_uidMaintenanceRow = strings::rand() ?>"></div>

          <div class="form-row d-none" id="<?= $_uidTenants = strings::rand() ?>-envelope">
            <div class="col-md-3 col-xl-2 col-form-label">tenants</div>
            <div class="col" id="<?= $_uidTenants ?>"></div>

          </div>

          <!-- contractor -->
          <div class="form-row">
            <div class="col-md-3 col-xl-2 col-form-label"><?= strtolower(config::label_contractor) ?></div>

            <div class="col-md mb-2">
              <input type="text" class="form-control" value="<?= $dto->contractor_trading_name ?>" id="<?= $_uidContractorTradingName = strings::rand() ?>" <?= $readonly ? 'disabled' : '' ?>>
              <div id="<?= $_missingServices = strings::rand() ?>"></div>

            </div>

            <div class="col-md mb-2 d-none" id="<?= $_uidContractorTradingName ?>-primary-contact"></div>

            <script>
              (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
                let reqServices = $('input[name="required_services"]', '#<?= $_form ?>');
                $('#<?= $_uidContractorTradingName ?>').autofill({
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
                    $('input[name="contractor_id"]', '#<?= $_form ?>')
                      .val(o.id);
                    $('#<?= $_form ?>')
                      .trigger('validate-order-button')
                      .trigger('qualify-contractor');

                  },

                });

              }))(_brayworth_);
            </script>

          </div>

          <!-- items - add item -->
          <div class="form-row mb-1">
            <div class="col <?php if (!$readonly) print 'col-md-3'; ?> pt-2 font-weight-bold">items..</div>
            <?php if (!$readonly) { ?>
              <div class="col-auto col-md-7">
                <input type="search" class="form-control" placeholder="search for item" id="<?= $_uidSearch = strings::rand() ?>">
                <script>
                  (_ => {
                    if (!!_.search.jobitems) {
                      $('#<?= $_uidSearch ?>').autofill({
                        autoFocus: true,
                        source: _.search.jobitems,
                        select: (e, ui) => {
                          let o = ui.item;

                          $('#<?= $_form ?>')
                            .trigger('items-auto-add', o);

                          $('#<?= $_uidSearch ?>').val('');

                        },

                      });

                    } else {
                      $('#<?= $_uidSearch ?>').prop('disabled', true);
                      console.log('missing line sarch function ...');

                    }

                  })(_brayworth_);
                </script>

              </div>

              <div class="col-auto col-md-2 text-md-right">
                <button type="button" class="btn btn-outline-secondary" accesskey="I" id="<?= $_btnAddItem = strings::rand() ?>">
                  <i class="bi bi-plus d-none d-sm-inline"></i> <span style="text-decoration: underline;">I</span>tem
                </button>
                <script>
                  $('#<?= $_btnAddItem ?>').on('click', e => $('#<?= $_form ?>').trigger('item-add'));
                </script>

              </div>
            <?php } ?>
          </div>

          <!-- items -->
          <div class="form-row mb-2">
            <div class="col" id="<?= $_uidItemContainer = strings::rand() ?>"></div>

          </div>

          <?php if ($this->data->log) { ?>
            <!-- comments -->
            <div class="form-row">
              <div class="col">
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead class="small">
                      <tr>
                        <td colspan="3" class="border-0 font-weight-bold">comments..</td>
                      </tr>
                      <tr>
                        <td>Date</td>
                        <td>Comment</td>
                        <td>PM</td>
                      </tr>

                    </thead>
                    <tbody>
                      <?php
                      foreach ($this->data->log as $log) {
                        printf(
                          '<tr>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                          </tr>',
                          strings::asShortDate($log->created, $time = true),
                          strings::text2html($log->comment),
                          strings::initials($log->username)

                        );
                      } ?>

                    </tbody>
                  </table>

                </div>
              </div>

            </div>
          <?php } ?>

          <div class="form-row">
            <div class="offset-md-3 offset-xl-2 col upload-invoice" id="<?= $_uidInvoice = strings::rand() ?>"></div>

          </div>

        </div>

        <div class="modal-footer justify-content-start">
          <?php if ($readonly) { ?>
            <?php if ($this->data->hasWorkorder) { ?>
              <button type="button" class="btn btn-outline-secondary" accesskey="O" id="<?= $_uid = strings::rand() ?>" disabled order-button><i class="bi bi-file-pdf text-danger"></i> <span style="text-decoration: underline;">O</span>rder</button>
              <script>
                $('#<?= $_uid ?>').on('click', e => $('#<?= $_form ?>').trigger('view-workorder'));
              </script>
            <?php }
          } else { ?>
            <button type="button" class="btn btn-outline-secondary" accesskey="O" id="<?= $_uid = strings::rand() ?>" disabled order-button><i class="bi bi-file-pdf text-danger"></i> <span style="text-decoration: underline;">O</span>rder</button>
            <script>
              $('#<?= $_uid ?>').on('click', e => $('#<?= $_form ?>').trigger('submit-and-workorder'));
            </script>
          <?php } ?>

          <button type="button" class="btn btn-outline-secondary d-none" id="<?= $_btnInvoice = strings::rand() ?>">
            Invoice
          </button>

          <button type="button" class="btn btn-outline-secondary d-none" id="<?= $_btnQuote = strings::rand() ?>">
            Quote
          </button>

          <button type="button" class="btn btn-outline-secondary <?= $dto->id ? '' : 'mr-auto' ?>" accesskey="T" id="<?= $_btnTenants = strings::rand() ?>">
            <i class="bi bi-people d-none d-sm-inline"></i> <span style="text-decoration: underline;">T</span>enants
          </button>

          <?php if ($dto->id) { ?>
            <button type="button" class="btn btn-outline-secondary" id="<?= $_uid = strings::rand() ?>">
              comment
            </button>
            <script>
              (_ => {
                $('#<?= $_uid ?>')
                  .on('click', e => {
                    e.stopPropagation();
                    $('#<?= $_form ?>').trigger('add-comment')
                  });

              })(_brayworth_);
            </script>

            <div class="form-check">
              <?php

              printf(
                '<input type="checkbox" class="form-check-input" id="%s" %s %s>',
                $_uid = strings::rand(),
                1 == $dto->complete ? 'checked' : '',
                (int)$dto->paid_by > 0 ? 'disabled' : ''

              );

              printf(
                '<label class="form-check-label user-select-none" for="%s">complete</label>',
                $_uid

              );

              ?>

            </div>
            <script>
              $('#<?= $_uid ?>')
                .on('change', function(e) {
                  $('#<?= $_form ?>')
                    .trigger($(this).prop('checked') ? 'mark-complete' : 'mark-complete-undo')
                });
            </script>

          <?php } ?>

          <?php if (in_array($action, $validActions)) { ?>
            <button type="submit" class="btn btn-primary ml-auto" accesskey="S"><span style="text-decoration: underline;">S</span>ave and close</button>
          <?php } else {  ?>
            <button type="button" class="btn btn-outline-secondary ml-auto" data-dismiss="modal">close</button>
          <?php } ?>

        </div>

      </div>

    </div>

  </div>

  <script>
    (_ => {
      let cats = <?= json_encode($categories) ?>;

      let newRow = () => {
        let row = $('<div class="form-row" item-row></div>');
        $('<input type="hidden" name="job_line_id[]" value="0">').appendTo(row);

        let cat = $('<select name="item_job_categories_id[]" class="form-control" required></select>');
        cat.append('<option value="">select category</option>');

        $.each(_.catSort(cats), (i, c) => $('<option></option>').val(c[0]).html(c[1]).appendTo(cat));
        cat.on('change', e => row.trigger('category-change'));

        $('<div class="col-md mb-2" category></div>').append(cat).appendTo(row);

        let itemSub = $('<select name="item_sub[]" class="form-control" disabled></select>');
        itemSub.append('<option value="">select item</option>');
        itemSub.on('change', e => row.trigger('item-sub-change'));

        $('<div class="col mb-2"></div>').append(itemSub).appendTo(row);

        let item = $('<select name="item_id[]" class="form-control" disabled></select>');
        item.append('<option value="">select issue</option>');

        $('<div class="col mb-2" item></div>')
          .append(item)
          .appendTo(row);

        let btnDelete = $('<div class="btn btn-light" type="button"><i class="bi bi-dash-circle-dotted text-danger"></i></div>');
        btnDelete.on('click', function(e) {
          e.stopPropagation();
          $(this).closest('div[item-row]').trigger('delete');

        });

        $('<div class="col-auto mb-2" delete></div>')
          .append(btnDelete)
          .appendTo(row);

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

                  itemSub
                    .prop('disabled', false)
                    .prop('required', true);
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

                  item
                    .prop('disabled', false)
                    .prop('required', true);
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
                  $('#<?= $_form ?>')
                    .trigger('update-required-services')
                    .trigger('validate-order-button');

                } else {
                  _.growl(d);

                }

              });

            } else {
              $(this).remove();

            }

          })
          .on('read-only', function(e) {

            $('div[delete]', this).remove();
            [
              'select[name="item_job_categories_id\[\]"]',
              'select[name="item_sub\[\]"]',
              'select[name="item_id\[\]"]'
            ].forEach(element => $(element, this).prop('disabled', true).prop('required', false));

          });

        row
          .appendTo('#<?= $_uidItemContainer ?>');

        return row;

      }

      let saveForm = () => {
        <?php if (in_array($action, $validActions)) { ?>
          // console.log('saveForm > validAction');
          return new Promise(resolve => {
            let _form = $('#<?= $_form ?>');
            let _data = _form.serializeFormJSON();

            if (Number(_data.properties_id) < 1) {
              $('#<?= $_uidAddress ?>')
                .popover({
                  title: 'Missing',
                  content: 'Please fill this field'

                })
                .popover('show')
                .focus();

              return;

            } else {
              for (var i = 0; i < _form[0].elements.length; i++) {
                if (_form[0].elements[i].value === '' && _form[0].elements[i].hasAttribute('required')) {

                  $(_form[0].elements[i])
                    .popover({
                      title: 'Missing',
                      content: 'Please fill this field'

                    })
                    .popover('show')
                    .focus();
                  return;
                }
              }

            }

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: _data,

            }).then(resolve);

          });

        <?php } else { ?>
          // console.log('saveForm > invalidAction');
          return new Promise(resolve => resolve({
            response: 'ack',
            description: '<?= $action ?>'
          }));

        <?php } ?>

      };

      $('#<?= $_form ?>')
        .on('add-comment', function(e) {
          e.stopPropagation();
          saveForm()
            .then(d => {
              if ('ack' != d.response) {
                _.growl(d);

              }

              $('#<?= $_modal ?>')
                .trigger('add-comment')
                .modal('hide');

            });

        })
        .on('bump', function(e) {
          e.stopPropagation();

          console.log('bump');
          $('#<?= $_modal ?>')
            .trigger('bump')
            .modal('hide');

        })
        .on('check-job-type', function(e) {
          $('#<?= $_uidInvoice ?>').trigger('reconcile');
          $(this).trigger('check-recurrence');

        })
        .on('check-recurrence', function(e) {

          // the form may be readonly, so you can't rely on getting it's data
          let jobType = $('input[name="job_type"]:checked', this).val();

          if (<?= config::job_type_recurring ?> == jobType) {
            $('#<?= $_uidRecurrenceCell ?>').removeClass('d-none');
            $('input[name="job_recurrence_interval"]', this).prop('required', true);
            let jobRecurrenceInterval = $('input[name="job_recurrence_interval"]:checked', this).val();
            // console.log('check-recurrence', jobType, '=', <?= config::job_type_recurring ?>, jobRecurrenceInterval);

            if (<?= config::job_recurrence_interval_week ?> == jobRecurrenceInterval) {

              $('#<?= $_uidRecurrenceDayOfWeek ?>, #<?= $_uidRecurrenceWeekFrequency ?>').removeClass('d-none');
              $('#<?= $_uidRecurrenceDayOfMonth ?>, #<?= $_uidRecurrenceMonthFrequency ?>').addClass('d-none');
              $('#<?= $_uidRecurrenceYearFrequency ?>').addClass('d-none');
              $('#<?= $_uidRecurrenceOnBusinessDay ?>').addClass('d-none');
              $('input[name="job_recurrence_week_frequency"]', this).attr('min', 1);
              $('input[name="job_recurrence_month_frequency"]', this).attr('min', 0);
              $('input[name="job_recurrence_year_frequency"]', this).attr('min', 0);

            } else if (<?= config::job_recurrence_interval_month ?> == jobRecurrenceInterval) {

              $('#<?= $_uidRecurrenceDayOfWeek ?>, #<?= $_uidRecurrenceWeekFrequency ?>').addClass('d-none');
              // $('#<?= $_uidRecurrenceDayOfMonth ?>').removeClass('d-none');
              $('#<?= $_uidRecurrenceMonthFrequency ?>').removeClass('d-none');
              $('#<?= $_uidRecurrenceYearFrequency ?>').addClass('d-none');
              $('#<?= $_uidRecurrenceOnBusinessDay ?>').removeClass('d-none');
              $('input[name="job_recurrence_week_frequency"]', this).attr('min', 0);
              $('input[name="job_recurrence_month_frequency"]', this).attr('min', 1);
              $('input[name="job_recurrence_year_frequency"]', this).attr('min', 0);

            } else if (<?= config::job_recurrence_interval_year ?> == jobRecurrenceInterval) {
              $('#<?= $_uidRecurrenceDayOfWeek ?>, #<?= $_uidRecurrenceWeekFrequency ?>').addClass('d-none');
              $('#<?= $_uidRecurrenceDayOfMonth ?>, #<?= $_uidRecurrenceMonthFrequency ?>').addClass('d-none');
              $('#<?= $_uidRecurrenceYearFrequency ?>').removeClass('d-none');
              $('#<?= $_uidRecurrenceOnBusinessDay ?>').removeClass('d-none');
              $('input[name="job_recurrence_week_frequency"]', this).attr('min', 0);
              $('input[name="job_recurrence_month_frequency"]', this).attr('min', 0);
              $('input[name="job_recurrence_year_frequency"]', this).attr('min', 1);

            } else {

              $('#<?= $_uidRecurrenceDayOfWeek ?>, #<?= $_uidRecurrenceWeekFrequency ?>').addClass('d-none');
              $('#<?= $_uidRecurrenceDayOfMonth ?>, #<?= $_uidRecurrenceMonthFrequency ?>').addClass('d-none');
              $('#<?= $_uidRecurrenceYearFrequency ?>').addClass('d-none');
              $('#<?= $_uidRecurrenceOnBusinessDay ?>').removeClass('d-none');
              $('input[name="job_recurrence_week_frequency"]', this).attr('min', 0);
              $('input[name="job_recurrence_month_frequency"]', this).attr('min', 0);
              $('input[name="job_recurrence_year_frequency"]', this).attr('min', 0);

            }

          } else {
            $('#<?= $_uidRecurrenceCell ?>').addClass('d-none');
            $('input[name="job_recurrence_interval"]', this).prop('required', false);
            $('input[name="job_recurrence_week_frequency"]', this).attr('min', 0);
            $('input[name="job_recurrence_month_frequency"]', this).attr('min', 0);
            $('input[name="job_recurrence_year_frequency"]', this).attr('min', 0);

          }

        })
        .on('get-keyset', function(e) {
          e.stopPropagation();

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

              $('#<?= $_uidKeyCell ?>')
                .html('')
                .removeClass('d-none');

              $.each(d.data, (i, keyset) => {
                let row = $('<div class="form-row"></div>').appendTo('#<?= $_uidKeyCell ?>');
                let ig = $('<div class="input-group"></div>');
                ig.append('<div class="input-group-prepend"><div class="input-group-text"><i class="bi bi-key"></i></div></div>');

                $('<div class="form-control" readonly></div>')
                  .html(keyset.keyset)
                  .appendTo(ig);

                $('<div class="col"></div>')
                  .append(ig)
                  .appendTo(row)

              });

            });

          } else {
            $('#<?= $_uidKeyCell ?>')
              .html('')
              .addClass('d-none');

          }

        })
        .on('get-maintenance', function(e) {
          e.stopPropagation();

          let _form = $(this);
          let _data = _form.serializeFormJSON();

          // console.log('get-maintenance');

          if (Number(_data.properties_id) > 0) {
            _.post({
              url: _.url('property_maintenance'),
              data: {
                action: 'get-maintenance-instructions',
                id: _data.properties_id

              },

            }).then(d => {
              // console.log(d);

              let label = $('<div class="col-md-3 col-xl-2 col-form-label d-flex"><div class="text-truncate flex-fill">maintenance instructions</div><i class="bi bi-pencil"></i></div>');
              label
                .addClass('pointer')
                .on('click', function(e) {
                  e.stopPropagation();
                  e.preventDefault();

                  $('#<?= $_form ?>').trigger('property-maintenance');

                });

              let col = $('<div class="col"></div>');
              $('#<?= $_uidMaintenanceRow ?>')
                .html('')
                .append(label)
                .append(col)
                .removeClass('d-none');

              if ('ack' == d.response) {
                if (d.data.length > 0) {
                  $.each(d.data, (i, sched) => {
                    let row = $('<div class="form-row"></div>');

                    let type = $('<div class="form-control form-control-sm" readonly></div>')
                      .html(sched.type);
                    let limit = $('<div class="form-control form-control-sm text-right" readonly></div>')
                      .html(sched.limit);
                    let notes = $('<div class="form-control form-control-sm h-auto" readonly></div>')
                      .html(sched.notes);

                    let fglimit = $('<div class="input-group input-group-sm"><div class="input-group-prepend"><div class="input-group-text">limit</div></div></div>')
                      .append(limit);

                    $('<div class="col-6 col-md-2 mb-1"></div>')
                      .append(type)
                      .appendTo(row);
                    $('<div class="col-6 col-md-3 mb-1"></div>')
                      .append(fglimit)
                      .appendTo(row);
                    $('<div class="col-md-7 mb-2"></div>')
                      .append(notes)
                      .appendTo(row);

                    col
                      .append(row);

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
          e.stopPropagation();

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
                  let on_site_contact = [t.name];
                  let row = $('<div class="form-row"></div>').appendTo('#<?= $_uidTenants ?>');
                  $('<div class="col-md-2 mb-1"></div>')
                    .append(
                      $('<div class="form-control form-control-sm" readonly></div>')
                      .html(t.name)
                    )
                    .appendTo(row);

                  let col = $('<div class="col-md-auto mb-1"></div>').appendTo(row);
                  let m = String(t.phone);
                  if (m.IsMobilePhone()) {
                    let ig = $('<div class="input-group input-group-sm"></div>').appendTo(col);
                    $('<input type="text" class="form-control" readonly>').val(m.AsMobilePhone()).appendTo(ig);

                    let btn = $('<button type="button" class="btn input-group-text"><i class="bi bi-chat-dots"></i></button>');
                    btn.on('click', e => {
                      e.stopPropagation();

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
                    on_site_contact.push(m.AsMobilePhone());

                  } else if (m.IsPhone()) {
                    col.html(m.AsLocalPhone()).addClass('p-2');
                    on_site_contact.push(m.AsLocalPhone());

                  }

                  col = $('<div class="col-md mb-2"></div>').appendTo(row);
                  if (String(t.email).isEmail()) {
                    let ig = $('<div class="input-group input-group-sm"></div>').appendTo(col);
                    $('<input type="text" class="form-control" readonly>').val(t.email).appendTo(ig);

                    let btn = $('<button type="button" class="btn input-group-text"><i class="bi bi-cursor"></i></button>');
                    btn.on('click', function(e) {
                      e.stopPropagation();

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

                    on_site_contact.push(t.email);

                  } else {
                    col.html(t.email).addClass('p-2');

                  }

                  col = $('<div class="col-auto mb-2"></div>').appendTo(row);
                  $('<button type="button" class="btn btn-light btn-sm"><i class="bi bi-arrow-bar-up"></i></button>')
                    .attr('title', 'Assign as on Site contact')
                    .appendTo(col)
                    .on('click', e => {
                      e.stopPropagation();

                      $('input[name="on_site_contact"]', '#<?= $_form ?>')
                        .val(on_site_contact.join(', '));

                    });

                  col.on('click', e => console.log('ouch ..'));

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

          $('#<?= $_btnTenants ?>').remove();

        })
        .on('invoice-view', function(e) {
          e.stopPropagation();

          $('#<?= $_modal ?>')
            .trigger('invoice-view')
            .modal('hide');

        })
        .on('invoice-upload', function(e) {
          e.stopPropagation();

          $('#<?= $_modal ?>').trigger('invoice-upload');

        })
        .on('item-add', function(e) {
          e.stopPropagation();

          let row = newRow();
          $('select[name="item_job_categories_id\[\]"]', row)
            .focus();

          $(this)
            .trigger('validate-order-button');

        })
        .on('items-auto-add', function(e, _item) {
          e.stopPropagation();

          let row = newRow();
          let jobline = $('input[name="job_line_id\[\]"]', row);
          let cat = $('select[name="item_job_categories_id\[\]"]', row);
          let itemSub = $('select[name="item_sub\[\]"]', row);
          let item = $('select[name="item_id\[\]"]', row);

          // console.log(_item);
          cat.val(_item.job_categories_id);
          row
            .trigger('category-change', () => {
              itemSub.val(_item.item);
              row
                .trigger('item-sub-change', () => {
                  item.val(_item.id);
                });
            });
          // jobline.val(_item.id);

        })
        .one('items-init', function(e) {
          e.stopPropagation();

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
            row
              .trigger('category-change', () => {
                itemSub.val(_item.item);
                row
                  .trigger('item-sub-change', () => {
                    item.val(_item.item_id);

                    <?php if ($readonly) { ?>
                      row.trigger('read-only');

                    <?php } ?>

                  });

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


                    if (Number(d.data.primary_contact) > 0) {
                      let ig = $('<div class="input-group"></div>');

                      $('<div class="form-control text-truncate" readonly></div>')
                        .html(d.data.primary_contact_name)
                        .appendTo(ig)

                      if (String(d.data.primary_contact_phone).IsPhone()) {
                        let tel = String(d.data.primary_contact_phone).IsMobilePhone() ?
                          String(d.data.primary_contact_phone).AsMobilePhone() :
                          String(d.data.primary_contact_phone).AsLocalPhone();

                        $('<div class="input-group-append"></div>')
                          .append(
                            $('<div class="input-group-text"></div>')
                            .html(tel)
                          )
                          .appendTo(ig)

                        if (String(d.data.primary_contact_phone).IsMobilePhone()) {
                          $('<div class="input-group-append"></div>')
                            .append(
                              $('<button type="button" class="btn input-group-text" "send sms"><i class="bi bi-chat-dots"></i></button>')
                              .on('click', function(e) {
                                e.stopPropagation();

                                if (!!window._cms_) {
                                  _cms_.modal.sms({
                                    to: d.data.primary_contact_phone
                                  });

                                } else {
                                  _.ask.warning({
                                    title: 'Warning',
                                    text: 'no SMS program'
                                  });

                                }

                              })
                            )
                            .appendTo(ig)

                        }

                      }

                      $('#<?= $_uidContractorTradingName ?>-primary-contact')
                        .html('')
                        .append(ig)
                        .removeClass('d-none');

                    } else {
                      $('#<?= $_uidContractorTradingName ?>-primary-contact').addClass('d-none');

                    }

                  } else {
                    _.growl(d);

                  }

                });

              }

              $('#<?= $_form ?>')
                .trigger('validate-order-button');

            })
            .trigger('qualify-contractor')
            .trigger('check-job-type');

        })
        .on('quote-view', function(e) {
          e.stopPropagation();

          saveForm()
            .then(d => {
              if ('ack' == d.response) {
                $('#<?= $_modal ?>')
                  .trigger('success', d);

              } else {
                _.growl(d);

              }

              $('#<?= $_modal ?>')
                .trigger('quote-view')
                .modal('hide');

            });

        })
        .on('quote-upload', function(e) {
          e.stopPropagation();

          $('#<?= $_modal ?>').trigger('quote-upload');

        })
        .on('mark-complete', function(e) {
          e.stopPropagation();

          saveForm()
            .then(d => {
              if ('ack' != d.response) _.growl(d);

              _.post({
                url: _.url('<?= $this->route ?>'),
                data: {
                  action: 'job-mark-complete',
                  id: '<?= $dto->id ?>'

                },

              }).then(d => {
                _.growl(d);
                $('#<?= $_modal ?>')
                  .trigger('complete')
                  .trigger('edit-workorder')
                  .modal('hide');

              });

            });

        })
        .on('mark-complete-undo', function(e) {
          e.stopPropagation();

          saveForm()
            .then(d => {
              if ('ack' != d.response) _.growl(d);

              _.post({
                url: _.url('<?= $this->route ?>'),
                data: {
                  action: 'job-mark-complete-undo',
                  id: '<?= $dto->id ?>'

                },

              }).then(d => {
                _.growl(d);
                $('#<?= $_modal ?>')
                  .trigger('complete')
                  .trigger('edit-workorder')
                  .modal('hide');

              });

            });

        })
        .on('property-maintenance', function(e) {
          e.stopPropagation();

          // console.log('form >property-maintenance');

          saveForm()
            .then(d => {
              if ('ack' != d.response) {
                _.growl(d);

              }

              // console.log('form >property-maintenance [BAM]!');

              $('#<?= $_modal ?>')
                .trigger('property-maintenance', d)
                .modal('hide');

            });

        })
        .on('reload', function(e) {
          e.stopPropagation();

          saveForm()
            .then(d => {
              if ('ack' != d.response) {
                _.growl(d);

              }

              $('#<?= $_modal ?>')
                .trigger('edit-workorder')
                .modal('hide');

            });

        })
        .on('submit-and-workorder', function(e) {
          e.stopPropagation();

          saveForm()
            .then(d => {
              if ('ack' == d.response) {
                $('#<?= $_modal ?>')
                  .trigger('success-and-workorder', d);

              } else {
                _.growl(d);

              }

              $('#<?= $_modal ?>')
                .modal('hide');

            });

          // console.table( _data);
          return false;

        })
        .on('submit', function(e) {
          e.stopPropagation();

          saveForm()
            .then(d => {
              if ('ack' == d.response) {
                $('#<?= $_modal ?>')
                  .trigger('success', d);

              } else {
                _.growl(d);

              }

              $('#<?= $_modal ?>')
                .modal('hide');

            });

          // console.table( _data);
          return false;

        })
        .on('validate-order-button', function(e) {
          let i = $('select[name="item_id\[\]"]', this).length;

          if (i > 0) {
            let el = $('input[name="contractor_id"]', this);
            if (Number(el.val()) > 0) {
              $('button[order-button]', this).prop('disabled', false);

            } else {
              $('button[order-button]', this).prop('disabled', true);

            }

          } else {
            $('button[order-button]', this).prop('disabled', true);

          }

        })
        .on('view-workorder', function(e) {
          e.stopPropagation();

          saveForm()
            .then(d => {
              if ('ack' == d.response) {
                $('#<?= $_modal ?>')
                  .trigger('success', d);

              } else {
                _.growl(d);

              }

              $('#<?= $_modal ?>')
                .trigger('view-workorder')
                .modal('hide');

            });

        })
        .on('update-required-services', function(e) {
          e.stopPropagation();

          let services = [];
          $('select[name="item_job_categories_id\[\]"]', this).each((i, el) => {
            let service = $(el).val();
            if (services.indexOf(service) < 0) services.push(service);
          });
          $('input[name="required_services" ]', this).val(services.join(','));
          $(this).trigger('qualify-contractor');
        });

      <?php if ($dto->id) { ?>
        $('input[name="job_payment"]', '#<?= $_form ?>')
          .on('change', e => $('#<?= $_uidInvoice ?>').trigger('reconcile'));

        $('#<?= $_uidInvoice ?>')
          .on('reconcile', function(e) {

            $('#<?= $_btnInvoice ?>, #<?= $_btnQuote ?>')
              .addClass('d-none');

            $('#<?= $_uidInvoice ?>')
              .removeClass('upload-invoice upload-quote')
              .html('');
            let _form = $('#<?= $_form ?>');
            let _data = _form.serializeFormJSON();

            if (<?= config::job_type_quote ?> == Number(_data.job_type) || <?= $readonly && config::job_type_quote == $dto->job_type ? 'true' : 'false' ?>) {
              <?php if ($this->data->hasQuote) { ?>
                $('#<?= $_btnQuote ?>')
                  .removeClass('d-none')
                  .on('click', function(e) {
                    e.stopPropagation();

                    $('#<?= $_form ?>')
                      .trigger('quote-view');

                  });

              <?php } else { ?>
                $('#<?= $_uidInvoice ?>')
                  .addClass('upload-quote')
                  .html('');
                (c => {

                  $('#<?= $_uidInvoice ?>')
                    .append(c);

                  _.fileDragDropHandler.call(c, {
                    url: _.url('<?= $this->route ?>'),
                    queue: false,
                    multiple: false,
                    postData: {
                      action: 'upload-quote',
                      id: <?= $dto->id ?>
                    },
                    onError: d => {

                      $('#<?= $_uidInvoice ?>')
                        .html('');

                      $('<div class="alert alert-danger m-1"></div>')
                        .html(d.description)
                        .appendTo('#<?= $_uidInvoice ?>');

                    },
                    onUpload: d => {
                      if ('ack' == d.response) {
                        $('#<?= $_form ?>')
                          .trigger('quote-view')
                          .trigger('quote-upload');

                      } else {
                        console.log(d);

                      }

                    }

                  });

                })(_.fileDragDropContainer({
                  fileControl: true,
                  accept: 'image/jpeg,image/png,application/pdf'

                }));

              <?php } ?>

            } else {
              $('#<?= $_uidInvoice ?>')
                .addClass('upload-invoice');

              <?php if ($this->data->hasInvoice) {  ?>

                $('#<?= $_btnInvoice ?>')
                  .removeClass('d-none')
                  .on('click', function(e) {
                    e.stopPropagation();

                    $('#<?= $_form ?>')
                      .trigger('invoice-view');

                  });

                <?php if ((int)$dto->paid_by < 1) {  ?>
                  $('#<?= $_btnInvoice ?>')
                    .on('contextmenu', function(e) {
                      if (e.shiftKey)
                        return;

                      e.stopPropagation();
                      e.preventDefault();

                      _.hideContexts();

                      let _context = _.context();
                      let _me = $(this);

                      _context.append(
                        $('<a href="#"><i class="bi bi-trash"></i>delete</a>')
                        .on('click', function(e) {
                          e.stopPropagation();
                          e.preventDefault();

                          _context.close();
                          _me.trigger('delete');
                        })
                      );

                      _context
                        .addClose()
                        .open(e);

                    })
                    .on('delete', function(e) {
                      let _me = $(this);

                      _.ask.alert({
                        title: 'confirm delete',
                        text: 'Are you Sure ?',
                        buttons: {
                          yes: function(e) {
                            $(this).modal('hide');
                            _me.trigger('delete-confirmed');

                          }

                        }

                      });

                    })
                    .on('delete-confirmed', function(e) {
                      let _me = $(this);

                      _.post({
                        url: _.url('<?= $this->route ?>'),
                        data: {
                          action: 'job-invoice-delete',
                          id: <?= $dto->id ?>

                        },

                      }).then(d => {
                        _.growl(d);
                        if ('ack' == d.response) {
                          $('#<?= $_form ?>')
                            .trigger('invoice-upload')
                            .trigger('reload');
                        }

                      });

                    });
                <?php }  ?>

              <?php } else { ?>
                let payment = (() => {
                  let fld = $('input[name="job_payment"]:checked', '#<?= $_form ?>');
                  if (fld.length == 1) return Number(fld.val());

                  return 0;

                })();

                if (payment != <?= config::job_payment_none ?>) {
                  // console.log(payment, <?= config::job_payment_none ?>);
                  if ($('select[name="item_id\[\]"]', '#<?= $_form ?>').length > 0) {
                    let el = $('input[name="contractor_id"]', '#<?= $_form ?>');
                    if (Number(el.val()) > 0) {
                      // console.log('lines + contractor - so invoice upload ...');
                      (c => {

                        $('#<?= $_uidInvoice ?>')
                          .append(c);

                        _.fileDragDropHandler.call(c, {
                          url: _.url('<?= $this->route ?>'),
                          queue: false,
                          multiple: false,
                          postData: {
                            action: 'upload-invoice',
                            id: <?= $dto->id ?>
                          },
                          onError: d => {

                            $('#<?= $_uidInvoice ?>')
                              .html('');

                            $('<div class="alert alert-danger m-1"></div>')
                              .html(d.description)
                              .appendTo('#<?= $_uidInvoice ?>');

                          },
                          onUpload: d => {
                            if ('ack' == d.response) {
                              $('#<?= $_form ?>')
                                .trigger('invoice-view')
                                .trigger('invoice-upload');

                            } else {
                              console.log(d);

                            }

                          }

                        });

                      })(_.fileDragDropContainer({
                        fileControl: true,
                        accept: 'image/jpeg,image/png,application/pdf'

                      }));

                    } else {
                      // console.log('there is no contractor - so no invoice upload ...');
                      $('#<?= $_uidInvoice ?>').html('');

                    }
                  } else {
                    // console.log('there are no lines - so no invoice upload ...');
                    $('#<?= $_uidInvoice ?>').html('');

                  }

                } else {
                  // console.log('no payment required - so no invoice upload ...');
                  $('#<?= $_uidInvoice ?>').html('');

                }

              <?php } ?>

            }

          });
      <?php } ?>

      $('input[name="job_type"]', '#<?= $_form ?>')
        .on('change', e => $('#<?= $_form ?>').trigger('check-job-type'));
      $('input[name="job_recurrence_interval"]', '#<?= $_form ?>')
        .on('change', e => $('#<?= $_form ?>').trigger('check-recurrence'));

      $('#<?= $_btnTenants ?>')
        .on('click', e => $('#<?= $_form ?>').trigger('get-tenants'));

      $('#<?= $_modal ?>')
        .on('shown.bs.modal', () => {

          $('#<?= $_form ?>')
            .trigger('get-keyset')
            .trigger('get-maintenance')
            .trigger('items-init');

          $('select[name="status"]', '#<?= $_form ?>')
            .focus();

        })
    })(_brayworth_);
  </script>

</form>