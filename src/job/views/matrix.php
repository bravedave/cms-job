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

use strings, currentUser;

$tblID = strings::rand();
?>
<style>
  .icon-width {
    width: 1.8em;
  }

  #<?= $tblID ?>td[status] {
    display: block;
    width: 45px;
  }

  @media (max-width: 768px) {
    .constrain {
      width: 208px;
    }

    .constrained {
      max-width: 200px;
    }

  }

  @media (min-width: 768px) and (max-width: 1023px) {
    .constrain {
      width: 148px;
    }

    .constrained {
      max-width: 140px;
    }

  }

  @media (min-width: 1024px) and (max-width: 1439px) {
    .constrain {
      width: 178px;
    }

    .constrained {
      max-width: 170px;
    }

  }

  @media (min-width: 1440px) {
    .constrain {
      width: 228px;
    }

    .constrained {
      max-width: 220px;
    }

  }
</style>

<h1 class="d-none d-print-block"><?= $this->title ?></h1>
<div class="form-row mb-2 d-print-none">
  <div class="col">
    <input type="search" class="form-control" accesskey="S" autofocus id="<?= $srch = strings::rand() ?>" placeholder="search - press alt + s to focus anytime">

  </div>

  <div class="col-auto pt-2">
    <div class="form-check">
      <input type="checkbox" class="form-check-input" id="<?= $_uidArchived = strings::rand() ?>" <?= $this->data->archived ? 'checked' : '' ?>>

      <label class="form-check-label" for="<?= $_uidArchived ?>">
        archived
      </label>

    </div>

    <script>
      (_ => {
        $('#<?= $_uidArchived ?>').on('change', function(e) {
          let _me = $(this);

          _.hourglass.on();
          _.post({
            url: _.url('<?= $this->route ?>'),
            data: {
              action: _me.prop('checked') ? 'matrix-include-archives' : 'matrix-include-archives-undo'
            },

          }).then(d => {
            _.growl(d);
            if ('ack' == d.response) {
              $(document).trigger('job-matrix-reload');

            } else {
              _.hourglass.off();

            }

          });


        });

      })(_brayworth_);
    </script>

  </div>

  <?php if ($this->data->showRefreshIcon) { ?>
    <!-- --[refresh icon]-- -->
    <div class="col-auto">
      <button type="button" class="btn btn-light" title="reload jobs" id="<?= $_uid = strings::rand() ?>"><i class="bi bi-arrow-repeat"></i></button>
      <script>
        (_ => {
          $('#<?= $_uid ?>')
            .on('click', function(e) {
              e.preventDefault();

              $(this)
                .html('<div class="spinner-border spinner-border-sm"></div>')
                .prop('disabled', true);

              $(document)
                .trigger('job-matrix-reload');

            });

        })(_brayworth_);
      </script>

    </div>

  <?php } ?>

  <!-- --[add new job]-- -->
  <div class="col-auto">
    <button type="button" class="btn btn-light" title="add new job" id="<?= $_uid = strings::rand() ?>"><i class="bi bi-journal-plus"></i></button>
    <script>
      (_ => {
        let active = false;

        $('#<?= $_uid ?>')
          .on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            if (active) return;
            active = true;

            let _me = $(this);

            _.get.modal(_.url('<?= $this->route ?>/job_edit'))
              .then(m => m.on('success', (e, data) => {
                $(document)
                  .trigger('job-matrix-reload', {
                    idx: data.id
                  });

              }))
              .then(m => m.on('success-and-workorder', (e, data) => {
                e.stopPropagation();

                _me
                  .trigger('create-workorder', data.id)

              }))
              .then(m => m.on('property-maintenance', (e, data) => {
                e.stopPropagation();

                $(document)
                  .trigger('job-matrix-reload', {
                    view: 'maintenance',
                    idx: data.id
                  });

                // _.get.modal(_.url('property_maintenance/property/' + _data.properties_id));

              }))
              .then(m => {

                <?php if ($this->data->property) { ?>
                  m.trigger('set-property', <?= json_encode((object)[
                                              'id' => $this->data->property->id,
                                              'street' => $this->data->property->address_street,
                                              'suburb' => $this->data->property->address_suburb,
                                              'postcode' => $this->data->property->address_postcode

                                            ]) ?>);
                <?php } ?>

                return m;

              })
              .then(m => active = false);

          })
          .on('create-workorder', function(e, id) {
            e.stopPropagation();

            let _me = $(this);

            _.hourglass.on();
            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'create-workorder',
                id: id

              },

            }).then(d => {
              _.hourglass.off();
              _.growl(d);
              if ('ack' == d.response) {
                $(document)
                  .trigger('job-matrix-reload', {
                    view: 'workorder',
                    idx: id
                  });

              } else {
                _.ask.alert({
                  text: d.description

                });
              }

            });

          });

      })(_brayworth_);
    </script>

  </div>

  <div class="col-auto d-md-none">
    <button type="button" class="btn btn-light" id="<?= $_uidMenu = strings::rand() ?>"><i class="bi bi-arrow-down"></i></button>
    <script>
      (_ => {
        $('#<?= $_uidMenu ?>').on('click', e => {
          e.stopPropagation();

          let el = $('#<?= $_uidMenu ?>-target');
          // console.log(el);
          if (el.length > 0) {
            el[0].scrollIntoView({
              behavior: "smooth",
              block: "center"
            }); // Object parameter

          }

        });

      })(_brayworth_);
    </script>

  </div>

</div>

<div class="form-row mb-2 d-print-none" id="<?= $stats = strings::rand() ?>"></div>

<div class="table-responsive">
  <table class="table table-sm fade" id="<?= $tblID ?>">
    <thead class="small">
      <tr>
        <td class="text-center" line-number>#</td>
        <td>Refer</td>
        <td class="constrain <?= $this->data->hidepropertycolumn ? 'd-none' : '' ?>" data-role="sort-header" data-key="street_index">Property</td>
        <td class="d-none d-md-table-cell constrain" data-role="sort-header" data-key="contractor_name">Contractor</td>
        <td class="d-none d-md-table-cell">Items</td>
        <td class="text-center" title="Order/Recurring/Quote" type>Type</td>
        <td class="text-center icon-width"><i class="bi bi-cursor"></i></td>
        <td class="text-center" title="has invoice/quote"><i class="bi bi-info-circle"></i></td>
        <td class="text-center" status data-role="sort-header" data-key="status">Status</td>
        <td class="text-center" due data-role="sort-header" data-key="due">Due</td>
        <td class="text-center" PM>User</td>

      </tr>

    </thead>

    <tbody>
      <?php while ($dto = $this->data->res->dto()) {
        $lines = json_decode($dto->lines) ?? [];
        $pm = strings::initials($dto->pm);
        if (strtotime($dto->email_sent) > 0) {
          if ($dto->status < config::job_status_sent) {
            $dto->status = config::job_status_sent; // auto advance status
          }
        }

        printf(
          '<tr
            class="%s"
            data-id="%s"
            data-refer="%s"
            data-job_recurrence_parent="%s"
            data-properties_id="%s"
            data-address_street="%s"
            data-street_index="%s"
            data-line_count="%s"
            data-contractor="%s"
            data-contractor_name="%s"
            data-contractor_primary_contact="%s"
            data-contractor_primary_contact_name="%s"
            data-due="%s"
            data-pm="%s"
            data-email_sent="%s"
            data-job_type="%s"
            data-status="%s"
            data-archived="%s",
            data-invoiced="%s"
            data-quote="%s"
            data-paid="%s">',
          $dto->id ? (strtotime($dto->archived) > 0 ? 'text-muted' : '') : 'text-info',
          $dto->id,
          workorder::reference((int)$dto->id),
          $dto->job_recurrence_parent,
          $dto->properties_id,
          htmlentities($dto->address_street),
          htmlentities($dto->street_index),
          count($lines),
          $dto->contractor_id,
          htmlentities($dto->contractor_trading_name),
          $dto->contractor_primary_contact,
          htmlentities($dto->contractor_primary_contact_name),
          $dto->due,
          $pm,
          strtotime($dto->email_sent) > 0 ? 'yes' : 'no',
          $dto->job_type,
          $dto->status,
          strtotime($dto->archived) > 0 ? 'yes' : 'no',
          1 == (int)$dto->has_invoice ? 'yes' : 'no',
          1 == (int)$dto->has_quote ? 'yes' : 'no',
          (int)$dto->paid_by > 0 ? 'yes' : 'no'

        );
      ?>
        <td class="small text-center" line-number></td>
        <td><?= str_pad($dto->id, 4, '0', STR_PAD_LEFT) ?></td>
        <td class="constrain <?= $this->data->hidepropertycolumn ? 'd-none' : '' ?>">
          <div class="constrained text-truncate" address>
            <?= $dto->address_street ?>

          </div>
          <div class="d-md-none constrained text-truncate" tradingname>
            <?= $dto->contractor_trading_name ?>

          </div>

        </td>

        <td class="d-none d-md-table-cell constrain">
          <div class="constrained text-truncate" tradingname>
            <?= $dto->contractor_trading_name ?>

          </div>

        </td>

        <td class="d-none d-md-table-cell" lines>
          <?php
          if ($lines) {
            foreach ($lines as $line) {
              // \sys::logger( sprintf('<%s> %s', print_r( $line, true), __METHOD__));
              printf(
                '<div class="form-row mb-1"><div class="col-4 col-md-3 text-truncate">%s</div><div class="col text-truncate">%s</div></div>',
                $line->item,
                $line->description

              );
            }
          } else {
            print strings::brief($dto->description);
          } ?>

        </td>

        <td class="text-center" type>
          <?php
          $_type = substr(config::cms_job_type_verbatim($dto->job_type), 0, 1);
          if (config::job_type_recurring == $dto->job_type) {
            if ($dto->job_recurrence_child) {
              print '<i class="bi bi-arrow-repeat text-success"></i>';
            } elseif ($dto->job_recurrence_disable) {
              print '<i class="bi bi-arrow-repeat text-warning"></i>';
            } else {
              print '<i class="bi bi-arrow-repeat"></i>';
            }
          } else {
            print $_type;
          }

          ?></td>

        <td class="text-center icon-width" email-sent>
          <?php
          if (strtotime($dto->email_sent) > 0) {
            printf(
              '<i class="bi bi-cursor" title="%s"></i>',
              strings::asLocalDate($dto->email_sent)
            );
          } else {
            print '&nbsp;';
          }
          ?>
        </td>

        <td class="text-center" invoiced>
          <?php
          if ($dto->job_type == config::job_type_quote) {
            if ($dto->has_quote) {
              print 'q';
            }
          } elseif ($dto->has_invoice) {
            print '&check;';
          }
          ?></td>

        <td class="text-center text-truncate" status>
          <?php if ($dto->id > 0) {
            print config::cms_job_status_verbatim($dto->status);
          } else {
            print '&nbsp;';
          } ?>
        </td>

        <td class="text-center" due><?= strings::asLocalDate($dto->due) ?></td>
        <td class="text-center" pm><?= $pm ?></td>

      <?php
        print '</tr>';
      } ?>

    </tbody>

  </table>

</div>

<div id="<?= $_uidMenu ?>-target"></div>

<script>
  (_ => {
    let jobTypes = <?= json_encode(config::job_types) ?>;
    let jobStatuses = <?= json_encode(config::job_status) ?>;

    $('#<?= $tblID ?> > thead > tr >td[line-number]')
      .on('archive-selected', function(e) {
        let _me = $(this);

        _.ask.alert({
          title: 'confirm action',
          text: 'archive selected JOBs ?',
          buttons: {
            yes: function(e) {
              _me.trigger('archive-selected-confirmed');
              $(this).modal('hide');

            }

          }

        });
      })
      .on('archive-selected-confirmed', function(e) {
        let _me = $(this);

        $('#<?= $tblID ?> > tbody > tr:not(.d-none)>td[line-number]>i').each((i, el) => {
          let _el = $(el);
          _el.closest('tr').trigger('archive');

        });

      })
      .on('contextmenu', function(e) {
        if (e.shiftKey)
          return;

        e.stopPropagation();
        e.preventDefault();

        _.hideContexts();

        let _me = $(this);
        let _context = _.context();

        _context.append(
          $('<a href="#">select all</a>')
          .on('click', e => {
            e.stopPropagation();
            e.preventDefault();

            $('#<?= $tblID ?>').trigger('select-visible');
            _context.close();

          }));

        let aS = $('<a href="#" class="d-none">archive selected</a>')
          .on('click', e => {
            e.stopPropagation();
            e.preventDefault();

            _context.close();
            _me.trigger('archive-selected');

          })
        _context.append(aS);

        let dl =
          $('<a href="#" class="d-none"></a>')
          .on('click', e => {
            e.stopPropagation();
            e.preventDefault();

            $('#<?= $tblID ?>').trigger('download-invoices');
            _context.close();

          });
        _context.append(dl);

        let mpS =
          $('<a href="#" class="d-none"></a>')
          .on('click', e => {
            e.stopPropagation();
            e.preventDefault();

            $('#<?= $tblID ?>').trigger('markpaid-selected');
            _context.close();

          });
        _context.append(mpS);

        let tot = 0;
        let totTot = 0;

        $('#<?= $tblID ?> > tbody > tr:not(.d-none)>td[line-number]>i').each((i, el) => {
          let _el = $(el);
          let _tr = _el.closest('tr');
          let _data = _tr.data();

          totTot++;
          if (Number(_data.id) > 0 && 'yes' == _data.invoiced) {
            tot++;

          }

        });

        if (tot > 0 && totTot == tot) {
          dl
            .html('download invoices')
            .removeClass('d-none');
          <?php if (currentUser::isRentalAdmin()) {  ?>
            mpS
              .html('markpaid selected')
              .removeClass('d-none');
          <?php }  ?>

        }

        <?php if (currentUser::isRentalAdmin()) {  ?>
          if (totTot > 0) aS.removeClass('d-none');
        <?php }  ?>

        _context.append('<hr>');
        _context.append(
          $('<a href="#">clear</a>')
          .on('click', e => {
            e.stopPropagation();
            e.preventDefault();

            $('#<?= $tblID ?>').trigger('update-line-numbers');
            _context.close();

          }));

        _context.open(e);
      });

    let lineidx = 0;
    $('#<?= $tblID ?>')
      .on('download-invoices', function(e) {
        let ids = [];

        $('#<?= $tblID ?> > tbody > tr:not(.d-none)>td[line-number]>i').each((i, el) => {
          let _el = $(el);
          let _tr = _el.closest('tr');
          let _data = _tr.data();

          if (Number(_data.id) > 0 && 'yes' == _data.invoiced) {
            ids.push(_data.id);

          }

        });

        if (ids.length > 0) {
          window.location.href = _.url('<?= $this->route ?>/zipInvoices?ids=' + ids.join(','));

        }

      })
      .on('markpaid-selected', function(e) {
        let ids = [];
        let trs = [];

        $('#<?= $tblID ?> > tbody > tr:not(.d-none)>td[line-number]>i').each((i, el) => {
          let _el = $(el);
          let _tr = _el.closest('tr');
          let _data = _tr.data();

          if (Number(_data.id) > 0 && 'yes' == _data.invoiced) {
            trs.push(_tr);
            ids.push(_data.id);

          }

        });

        if (ids.length > 0) {
          _.post({
            url: _.url('<?= $this->route ?>'),
            data: {
              action: 'job-mark-paid-selected',
              ids: ids.join(',')
            },

          }).then(d => {
            _.growl(d);
            if ('ack' == d.response) {
              $('#<?= $tblID ?>').trigger('update-line-numbers');
              trs.forEach(_tr => _tr.trigger('refresh'));
            }
          });

        } else {
          _.growl('none selected ...');
        }

      })
      .on('select-visible', function(e) {
        let _me = $(this);

        $('> tbody > tr', this)
          .each((i, e) => {
            let _e = $(e);
            let _data = _e.data();

            if (_data.id > 0) {
              $('>td[line-number]', e)
                .html($(e).hasClass('d-none') ? '' : '<i class="bi bi-check"></i>')

            }

          });

        _me.trigger('total-selected');

      })
      .on('total-selected', function(e) {
        let tot = $('> tbody > tr:not(.d-none)>td[line-number]>i', this).length;
        if (tot > 0) {
          $('> thead > tr >td[line-number]', this).html(tot);

        } else {
          let td = $('> thead > tr >td[line-number]', this);
          td.html(td.data('lines'));

        }

      })
      .on('update-line-numbers', function(e) {
        let idx = ++lineidx;
        let tot = 0;
        let stats = {};

        $('> tbody > tr:not(.d-none)', this).each((i, e) => {
          if (idx != lineidx) return false;

          let _e = $(e);
          let _data = _e.data();
          // console.log(_data);
          if (Number(_data.id) > 0) {
            if (!stats[_data.status]) stats[_data.status] = 0;
            stats[_data.status]++;

          } else {
            if (!stats[<?= config::job_status_ghost ?>]) stats[<?= config::job_status_ghost ?>] = 0;
            stats[<?= config::job_status_ghost ?>]++;

          }

          $('>td[line-number]', e)
            .data('line', i + 1)
            .html(i + 1);
          tot++;
        });

        if (idx == lineidx) {
          $('> thead > tr >td[line-number]', this)
            .data('lines', tot)
            .html(tot);

          $('#<?= $stats ?>')
            .html('');

          // console.log(stats);
          $.each(stats, (i, s) => {
            if (Number(s) > 0) {
              let ig = $('<div class="input-group input-group-sm"></div>');
              $('<div class="input-group-prepend"></div>')
                .append(
                  $('<div class="input-group-text"></div>')
                  .html(jobStatuses[i])
                )
                .appendTo(ig);

              $('<div class="form-control"></div>')
                .html(s)
                .appendTo(ig);

              $('<div class="col-auto"></div>')
                .append(ig)
                .appendTo('#<?= $stats ?>');

            }

          });

        }

      });

    let pms = [];
    $('#<?= $tblID ?> > tbody > tr')
      .each((i, tr) => {
        let _tr = $(tr);
        let _data = _tr.data();

        if ('' != String(_data.pm)) {
          if (pms.indexOf(String(_data.pm)) < 0) {
            pms.push(String(_data.pm));

          }

        }

        _tr
          .on('archive', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-archive',
                id: _data.id

              },

            }).then(d => {
              _.growl(d);
              if ('ack' == d.response) {
                if ($('#<?= $_uidArchived ?>').prop('checked')) {
                  _tr
                    .data('archived', 'yes')
                    .addClass('text-muted');
                } else {
                  _tr
                    .remove();
                  $('#<?= $tblID ?>').trigger('update-line-numbers');

                }

              }

            });

          })
          .on('archive-undo', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-archive-undo',
                id: _data.id

              },

            }).then(d => {
              _.growl(d);
              if ('ack' == d.response) {
                _tr
                  .data('archived', 'no')
                  .removeClass('text-muted');


              }

            });

          })
          .on('bump', function(e) {
            e.stopPropagation();

            let _tr = $(this);
            let _data = _tr.data();

            _.get.modal(_.url('<?= $this->route ?>/bump/' + _data.id))
              .then(m => m.on('success', e => _tr.trigger('refresh')))
              .then(m => m.on('hidden.bs.modal', e => _tr.trigger('edit')));

          })
          .on('confirm-recurrence-and-edit', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            if (Number(_data.job_recurrence_parent) > 0) {
              _.post({
                url: _.url('<?= $this->route ?>'),
                data: {
                  action: 'confirm-recurrence',
                  due: _data.due,
                  job_recurrence_parent: _data.job_recurrence_parent,
                },

              }).then(d => {
                if ('ack' == d.response) {
                  console.log('issue reload', d.id, 'view')
                  $(document)
                    .trigger('job-matrix-reload', {
                      view: 'view',
                      idx: d.id
                    });

                } else {
                  _.growl(d);

                }

              });

            }

          })
          .on('disabled-recurrence', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            if (Number(_data.job_recurrence_parent) > 0) {
              _.post({
                url: _.url('<?= $this->route ?>'),
                data: {
                  action: 'disable-recurrence',
                  job_recurrence_parent: _data.job_recurrence_parent,
                },

              }).then(d => {
                if ('ack' == d.response) {
                  console.log('issue reload')
                  $(document)
                    .trigger('job-matrix-reload');

                } else {
                  _.growl(d);

                }

              });

            }

          })
          .on('edit', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _tr.addClass('bg-info');

            _.get.modal(_.url('<?= $this->route ?>/job_edit/' + _data.id))
              .then(d => d.on('bump', e => {
                e.stopPropagation();
                _tr
                  .trigger('bump');

              }))
              .then(d => d.on('complete', e => {
                e.stopPropagation();
                _tr
                  .trigger('refresh');

              }))
              .then(d => d.on('success', () => {
                e.stopPropagation();
                _tr
                  .trigger('refresh');

              }))
              .then(d => d.on('success-and-workorder', () => {
                e.stopPropagation();
                _tr
                  .trigger('refresh')
                  .trigger('create-workorder');

              }))
              .then(m => m.on('add-comment', e => {
                e.stopPropagation();
                _tr
                  .trigger('comment');

              }))
              .then(m => m.on('property-maintenance', e => {
                e.stopPropagation();
                // console.log('modal > property-maintenance');
                _tr
                  .trigger('property-maintenance');

              }))
              .then(m => m.on('edit-workorder', e => {
                e.stopPropagation();
                _tr
                  .trigger('edit');

              }))
              .then(d => d.on('invoice-view', e => {
                e.stopPropagation();
                _tr
                  .trigger('invoice-view');

              }))
              .then(d => d.on('invoice-upload', e => {
                e.stopPropagation();
                _tr
                  .trigger('refresh');

              }))
              .then(d => d.on('quote-view', e => {
                e.stopPropagation();
                _tr
                  .trigger('quote-view');

              }))
              .then(d => d.on('quote-upload', e => {
                e.stopPropagation();
                _tr
                  .trigger('refresh');

              }))
              .then(d => d.on('view-workorder', e => {
                e.stopPropagation();
                _tr
                  .trigger('view-workorder');

              }))
              .then(m => m.on('hidden.bs.modal', d => {
                _tr[0].scrollIntoView({
                  behavior: "smooth",
                  block: "center"
                }); // Object parameter

                setTimeout(() => _tr.removeClass('bg-info'), 1000);

              }));

          })
          .on(_.browser.isMobileDevice ? 'click' : 'contextmenu', function(e) {
            if (e.shiftKey)
              return;

            e.stopPropagation();
            e.preventDefault();

            _.hideContexts();

            let _context = _.context();
            let _tr = $(this);
            let _data = _tr.data();

            let mergeCtrl = $('<a href="#" class="d-none"><i class="bi bi-union"></i>merge</a>')
              .on('activate', function(e) {
                e.stopPropagation();

                $(this).removeClass('d-none')

              })
              .on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();

                _context.close();
                _tr.trigger('merge');

              });

            if (Number(_data.id) > 0) {
              _context.append(
                $('<a href="#"><i class="bi bi-pencil"></i>edit</a>')
                .on('click', e => {
                  e.stopPropagation();

                  _tr.trigger('edit');
                  _context.close();

                })

              );

              if (0 < Number(_data.line_count)) {

                let paidCtrl = $('<a href="#" class="d-none">paid</a>')
                  .on('reconcile', function(e) {
                    let _me = $(this);

                    if ('yes' == _data.paid) {
                      _me
                        .prepend('<i class="bi bi-check"></i>')
                        .on('click', e => {
                          e.stopPropagation();
                          _tr.trigger('mark-paid-undo');
                          _context.close();

                        });

                    } else {
                      _me
                        .on('click', e => {
                          e.stopPropagation();
                          _tr.trigger('mark-paid');
                          _context.close();

                        });

                    }

                    _me
                      .removeClass('d-none');

                  });

                _context.append(paidCtrl);

                _context.append(
                  $('<a href="#" class="d-none"></a>')
                  .on('reconcile', function(e) {
                    let _me = $(this);

                    _.post({
                      url: _.url('<?= $this->route ?>'),
                      data: {
                        action: 'check-has-invoice',
                        id: _data.id
                      },

                    }).then(d => {
                      if ('ack' == d.response) {
                        if ('yes' == d.invoice) {
                          _me
                            .html('view invoice')
                            .removeClass('d-none')
                            .on('click', e => {
                              e.stopPropagation();
                              _tr.trigger('invoice-view');
                              _context.close();

                            });

                          paidCtrl.trigger('reconcile');

                        } else {
                          mergeCtrl.trigger('activate');

                        }
                      } else {
                        _.growl(d);

                      }

                    });

                  })
                  .trigger('reconcile')

                );

                _context.append(
                  $('<a href="#" class="d-none"></a>')
                  .on('reconcile', function(e) {
                    let _me = $(this);

                    // _data is from _tr
                    // console.log( _data);

                    if (Number(_data.contractor) > 0) {
                      _.post({
                        url: _.url('<?= $this->route ?>'),
                        data: {
                          action: 'check-has-workorder',
                          id: _data.id
                        },

                      }).then(d => {
                        if ('ack' == d.response) {
                          if ('yes' == d.workorder) {
                            _me
                              .html('<i class="bi bi-file-pdf text-danger"></i>view workorder')
                              .removeClass('d-none')
                              .on('click', e => {
                                e.stopPropagation();
                                _tr.trigger('view-workorder');
                                _context.close();

                              });

                          } else {
                            _me
                              .html('create workorder')
                              .removeClass('d-none')
                              .on('click', e => {
                                e.stopPropagation();
                                _tr.trigger('create-workorder');
                                _context.close();

                              });

                          }
                        } else {
                          _.growl(d);

                        }

                      });

                    }

                  })
                  .trigger('reconcile')

                );

              }

              // view quote
              _context.append(
                $('<a href="#" class="d-none"></a>')
                .on('reconcile', function(e) {
                  let _me = $(this);

                  // _data is from _tr
                  // console.log( _data);

                  if (Number(_data.contractor) > 0) {
                    _.post({
                      url: _.url('<?= $this->route ?>'),
                      data: {
                        action: 'check-has-quote',
                        id: _data.id
                      },

                    }).then(d => {
                      if ('ack' == d.response) {
                        if ('yes' == d.quote) {
                          _me
                            .html('<i class="bi bi-file-text"></i>view quote')
                            .removeClass('d-none')
                            .on('click', e => {
                              e.stopPropagation();
                              _tr.trigger('quote-view');
                              _context.close();

                            });

                        }
                      } else {
                        _.growl(d);

                      }

                    });

                  }

                })
                .trigger('reconcile')

              );

            } else {
              _context.append(
                $('<a href="#">Confirm recurrence and Edit</a>')
                .on('click', e => {
                  e.stopPropagation();
                  e.preventDefault();

                  _tr.trigger('confirm-recurrence-and-edit');
                  _context.close();

                })

              );

              _context.append(
                $('<a href="#">discontinue recurrence</a>')
                .on('click', e => {
                  e.stopPropagation();
                  e.preventDefault();

                  _tr.trigger('disabled-recurrence');
                  _context.close();

                })

              );

            }

            if (Number(_data.properties_id) > 0) {
              _context.append(
                $('<a href="#" target="_blank"></a>')
                .html('goto ' + _data.address_street)
                .prepend('<i class="bi bi-box-arrow-up-right"></i>')
                .attr('href', _.url('property/view/' + _data.properties_id))
                .on('click', e => _context.close())

              );

            }

            if (Number(_data.contractor) > 0) {
              _context.append(
                $('<a class="d-none" target="_blank"></a>')
                .on('recon', function(e) {
                  if (Number(_data.contractor_primary_contact)) {
                    $(this)
                      .attr('href', _.url('person/view/' + _data.contractor_primary_contact))
                      .html('goto ' + _data.contractor_primary_contact_name)
                      .prepend('<i class="bi bi-box-arrow-up-right"></i>')
                      .removeClass('d-none')
                      .on('click', e => _context.close());

                  }

                })
                .trigger('recon')

              );

            }

            if (Number(_data.id) > 0) {
              _context.append(
                $('<a class="d-none" href="#">email sent</a>')
                .on('click', e => {
                  e.stopPropagation();
                  _tr.trigger('yes' == _data.email_sent ? 'mark-sent-undo' : 'mark-sent');
                  _context.close();
                })
                .on('reconcile', function(e) {
                  if ('yes' == _data.email_sent) {
                    $(this).prepend('<i class="bi bi-check"></i>')
                    <?php if (currentUser::isRentalDelegate()) { ?>
                      $(this).removeClass('d-none');
                    <?php } ?>

                  } else {
                    $(this).removeClass('d-none');

                  }

                })
                .trigger('reconcile')

              );
              if (0 < Number(_data.line_count)) {
                _context.append(
                  $('<a href="#"><i class="bi bi-files"></i>duplicate</a>')
                  .on('click', e => {
                    e.stopPropagation();
                    _tr.trigger('duplicate');
                    _context.close();
                  })

                );

                if (<?= config::job_type_quote ?> == _data.job_type) {
                  // console.log( _data);

                  _context.append(
                    $('<a href="#" title="duplicate, mark as order and archive"><i class="bi bi-ui-checks"></i>invoke order</a>')
                    .on('click', e => {
                      e.stopPropagation();
                      _tr.trigger('invoke-order');
                      _context.close();
                    })

                  );

                }

              }

              _context.append(
                $('<a href="#"><i class="bi bi-arrow-repeat"></i>refresh</a>')
                .on('click', e => {
                  e.stopPropagation();
                  _tr.trigger('refresh');
                  _context.close();
                })

              );
              if (0 == Number(_data.line_count)) {

                _context.append(
                  $('<a href="#"><i class="bi bi-trash"></i>delete</a>')
                  .on('click', e => {
                    e.stopPropagation();
                    _tr.trigger('delete');
                    _context.close();

                  })

                );

              } else {
                if ('yes' == _data.archived) {
                  _context.append(
                    $('<a href="#"><i class="bi bi-archive-fill"></i>archived</a>')
                    .on('click', e => {
                      e.stopPropagation();
                      _tr.trigger('archive-undo');
                      _context.close();

                    })

                  );

                } else {
                  _context.append(
                    $('<a href="#"><i class="bi bi-archive"></i>archive</a>')
                    .on('click', e => {
                      e.stopPropagation();
                      _tr.trigger('archive');
                      _context.close();

                    })

                  );

                }

              }

              _context.append(mergeCtrl);

            }

            _context
              .addClose()
              .open(e);

          })
          .on('comment', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.get
              .modal(_.url('<?= $this->route ?>/comment/?property=' + _data.id))
              .then(m => m.on('hidden.bs.modal', e => _tr.trigger('edit')));

          })
          .on('create-workorder', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.hourglass.on();
            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'create-workorder',
                id: _data.id

              },

            }).then(d => {
              _.hourglass.off();
              _.growl(d);
              if ('ack' == d.response) {
                _tr.trigger('view-workorder');

              } else {
                _.ask.alert({
                  text: d.description

                });
              }

            });

          })
          .on('delete', function(e) {
            let _tr = $(this);

            _.ask.alert({
              title: 'confirm delete',
              text: 'Are you Sure ?',
              buttons: {
                yes: function(e) {
                  _tr.trigger('delete-confirmed');
                  $(this).modal('hide');

                }

              }

            });

          })
          .on('delete-confirmed', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _tr.addClass('text-muted');

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-delete',
                id: _data.id

              },

            }).then(d => {
              _.growl(d);
              if ('ack' == d.response) {
                _tr.remove();

              }

            });

          })
          .on('delete-invoice', function(e) {
            let _tr = $(this);

            _.ask.alert({
              title: 'delete invoice',
              text: 'Are you Sure ?',
              buttons: {
                yes: function(e) {
                  _tr.trigger('delete-invoice-confirmed');
                  $(this).modal('hide');

                }

              }

            });

          })
          .on('delete-invoice-confirmed', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-invoice-delete',
                id: _data.id

              },

            }).then(d => {
              _.growl(d);
              if ('ack' == d.response) {
                _tr
                  .trigger('refresh');

              }

            });

          })
          .on('delete-quote', function(e) {
            let _tr = $(this);

            _.ask.alert({
              title: 'delete quote',
              text: 'Are you Sure ?',
              buttons: {
                yes: function(e) {
                  _tr.trigger('delete-quote-confirmed');
                  $(this).modal('hide');

                }

              }

            });

          })
          .on('delete-quote-confirmed', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-quote-delete',
                id: _data.id

              },

            }).then(d => {
              _.growl(d);
              if ('ack' == d.response) {
                _tr
                  .trigger('refresh');

              }

            });

          })
          .on('duplicate', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.hourglass.on();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-duplicate',
                id: _data.id

              },

            }).then(d => {
              _.growl(d);
              if ('ack' == d.response) {
                _.nav('<?= $this->route ?>/matrix/?v=view&idx=' + d.id);

              } else {
                _.hourglass.off();

              }

            });

          })
          .on('email-invoice', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            if (!!window.EmailClass) {
              let f = o => {
                _.post({
                  url: _.url('<?= $this->route ?>'),
                  data: {
                    action: 'get-invoice-as-attachment',
                    id: _data.id
                  },

                }).then(d => {
                  if ('ack' == d.response) {
                    o.tmpDir = d.tmpdir;
                    if (!!window.EmailClass) {
                      _.email.activate(o);
                    } else {
                      console.log(o);
                      _.ask.alert({
                        text: 'no email program'
                      });

                    }

                  } else {
                    _.growl(d);

                  }

                });

              }

              // console.log(_data);
              let mailer = _.email.mailer({
                subject: _data.address_street + ' invoice - ' + _data.refer,
              });

              f(mailer);

            } else {
              _.ask.alert({
                text: 'no email program'
              });

            }

          })
          .on('email-workorder', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            if (!!window.EmailClass) {
              let f = o => {
                _.post({
                  url: _.url('<?= $this->route ?>'),
                  data: {
                    action: 'get-workorder-and-attachment',
                    id: _data.id
                  },

                }).then(d => {
                  if ('ack' == d.response) {
                    o.tmpDir = d.tmpdir;
                    o.subject = String(d.subject);
                    o.message = String(d.text).toHtml();
                    if (!!window.EmailClass) {
                      _.email.activate(o);
                    } else {
                      console.log(o);
                      _.ask.alert({
                        text: 'no email program'
                      });

                    }

                  } else {
                    _.growl(d);

                  }

                });

              }

              // console.log(_data);
              let mailer = _.email.mailer({
                subject: _data.address_street + ' workorder -' + _data.refer,
                onSend: d => _tr.trigger('mark-sent')
              });

              if (Number(_data.contractor) > 0) {
                _.post({
                  url: _.url('<?= $this->route ?>'),
                  data: {
                    action: 'get-contractor-by-id',
                    id: _data.contractor

                  },

                }).then(d => {
                  if ('ack' == d.response) {
                    if (String(d.data.primary_contact_email).isEmail()) {
                      mailer.to = _.email.rfc922({
                        name: d.data.primary_contact_name,
                        email: d.data.primary_contact_email

                      });

                      if (String(d.data.primary_contact_phone).IsMobilePhone()) {
                        mailer.ccSMSPush({
                          'name': d.data.primary_contact_name,
                          'mobile': d.data.primary_contact_phone
                        });

                      }
                    }

                    f(mailer);

                  } else {
                    _.growl(d);

                  }

                });

              } else {
                f(mailer);

              }

            } else {
              _.ask.alert({
                text: 'no email program'
              });

            }

          })
          .on('invoke-order', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.hourglass.on();
            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-invoke-order',
                id: _data.id

              },

            }).then(d => {
              _.growl(d);
              if ('ack' == d.response) {
                _.nav('<?= $this->route ?>/matrix/?v=view&idx=' + d.id);

              } else {
                _.hourglass.off();

              }

            });

          })
          .on('invoice-view', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.get
              .modal(_.url('<?= $this->route ?>/invoice/' + _data.id))
              .then(m => m.on('delete-invoice', e => {
                e.stopPropagation();
                _tr.trigger('delete-invoice');

              }))
              .then(m => m.on('edit-workorder', e => {
                e.stopPropagation();
                _tr.trigger('edit');

              }))
              .then(m => m.on('email-invoice', e => {
                e.stopPropagation();
                _tr.trigger('email-invoice');

              }))
              .then(m => m.on('job-mark-invoice-senttoowner', e => {
                e.stopPropagation();
                _tr.trigger('mark-senttoowner');

              }))
              .then(m => m.on('job-mark-invoice-senttoowner-undo', e => {
                e.stopPropagation();
                _tr.trigger('mark-senttoowner-undo');

              }))
              .then(m => m.on('tr-refresh', e => {
                e.stopPropagation();
                _tr.trigger('refresh');
              }))
              .then(m => m.on('view-workorder', e => {
                e.stopPropagation();
                _tr.trigger('view-workorder');

              }));

          })
          .on('quote-view', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.get
              .modal(_.url('<?= $this->route ?>/quote/' + _data.id))
              .then(m => m.on('delete-quote', e => {
                e.stopPropagation();
                _tr.trigger('delete-quote');

              }))
              .then(m => m.on('edit-workorder', e => {
                e.stopPropagation();
                _tr.trigger('edit');

              }))
              .then(m => m.on('view-workorder', e => {
                e.stopPropagation();
                _tr.trigger('view-workorder');

              }));

          })
          .on('mark-paid', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-mark-paid',
                id: _data.id
              },

            }).then(d => {
              _.growl(d);
              if ('ack' == d.response) {
                _tr.trigger('refresh');

              }

            });

          })
          .on('mark-paid-undo', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-mark-paid-undo',
                id: _data.id
              },

            }).then(d => {
              _.growl(d);
              if ('ack' == d.response) {
                _tr.trigger('refresh');

              }

            });
          })
          .on('mark-senttoowner', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-mark-invoice-senttoowner',
                id: _data.id
              },

            }).then(d => {
              _.growl(d);
              if ('ack' == d.response) {
                _tr.trigger('refresh');

              }

            });
          })
          .on('mark-senttoowner-undo', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-mark-invoice-senttoowner-undo',
                id: _data.id
              },

            }).then(d => {
              _.growl(d);
              if ('ack' == d.response) {
                _tr.trigger('refresh');

              }

            });
          })
          .on('mark-sent', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'mark-sent',
                id: _data.id
              },

            }).then(d => {
              if ('ack' == d.response) {
                _tr
                  .data('email_sent', 'yes')
                  .trigger('refresh');

              } else {
                _.growl(d);

              }

            });

          })
          .on('mark-sent-undo', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'mark-sent-undo',
                id: _data.id
              },

            }).then(d => {
              if ('ack' == d.response) {
                _tr
                  .data('email_sent', 'no')
                  .trigger('refresh');

              } else {
                _.growl(d);

              }

            });

          })
          .on('merge', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.get
              .modal(_.url('<?= $this->route ?>/merge/' + _data.id))
              .then(m => m.on('success', e => {
                e.stopPropagation();

                $(document)
                  .trigger('job-matrix-reload', {
                    idx: _data.id
                  });

              }));

          })
          .on('property-maintenance', function(e) {
            e.stopPropagation();

            // console.log('tr > property-maintenance');

            let _tr = $(this);
            let _data = _tr.data();

            _.get.modal(_.url('property_maintenance/property/' + _data.properties_id));

          })
          .on('refresh', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'matrix-refresh-row',
                id: _data.id

              },

            }).then(d => {
              if ('ack' == d.response) {
                // console.log('matrix-refresh-row');
                // console.log(d.data);

                let pm = String(d.data.property_manager).initials();
                let archived = false;
                if ('' != d.data.archived) {
                  let da = _.dayjs(d.data.archived);
                  archived = da.isValid() && da.unix() > 0;
                }

                _tr.data({
                  properties_id: d.data.properties_id,
                  address_street: d.data.address_street,
                  line_count: d.data.lines.length,
                  contractor: d.data.contractor_id,
                  due: d.data.due,
                  pm: pm,
                  job_type: d.data.job_type,
                  status: d.data.status,
                  invoice: 1 == Number(d.data.has_invoice) ? 'yes' : 'no',
                  quote: 1 == Number(d.data.has_quote) ? 'yes' : 'no',
                  complete: 1 == Number(d.data.complete) ? 'yes' : 'no',
                  paid: Number(d.data.paid_by) > 0 ? 'yes' : 'no',
                  archived: archived ? 'yes' : 'no',
                });

                archived ? _tr.addClass('text-muted') : _tr.removeClass('text-muted');
                // console.log(d.data);

                $('[address]', _tr).html(d.data.address_street);
                $('[tradingname]', _tr).html(d.data.contractor_trading_name);
                $('[status]', _tr).html(d.data.status_verbatim);
                $('[invoiced]', _tr).html(1 == Number(d.data.has_quote) ? 'q' : (1 == Number(d.data.has_invoice) ? '&check;' : ''));

                let _type = String(d.data.type_verbatim).initials();
                if (<?= config::job_type_recurring ?> == d.data.job_type) {
                  if (Number(d.data.job_recurrence_child) > 0) {
                    $('[type]', _tr).html('<i class="bi bi-arrow-repeat text-success"></i>');
                  } else if (1 == Number(d.data.job_recurrence_disable)) {
                    $('[type]', _tr).html('<i class="bi bi-arrow-repeat text-warning"></i>');
                  } else {
                    $('[type]', _tr).html('<i class="bi bi-arrow-repeat"></i>');
                  }
                } else {
                  $('[type]', _tr).html(_type);
                }

                $('[pm]', _tr).html(pm);

                let due = _.dayjs(d.data.due);
                if (due.isValid() && due.unix() > 0) {
                  $('[due]', _tr).html(due.format('L'));

                } else {
                  $('[due]', _tr).html('&nbsp;');

                }

                if (!!d.data.email_sent) {
                  let dateSent = _.dayjs(d.data.email_sent);
                  if (dateSent.isValid() && dateSent.unix() > 0) {
                    $('[email-sent]', _tr)
                      .html('')
                      .append(
                        $('<i class="bi bi-cursor"></i>')
                        .attr('title', dateSent.format('L'))
                      );

                  } else {
                    $('[email-sent]', _tr).html('&nbsp;');
                  }

                } else {
                  $('[email-sent]', _tr).html('&nbsp;');

                }

                if (d.data.lines.length > 0) {
                  $('[lines]', _tr).html('');
                  $.each(d.data.lines, (i, line) => {
                    let row = $('<div class="form-row mb-1"></div>');

                    $('<div class="col-4 col-md-3 text-truncate"></div>').html(line.item).appendTo(row);
                    $('<div class="col text-truncate"></div>').html(line.description).appendTo(row);

                    $('[lines]', _tr).append(row);

                  });

                } else {
                  $('[lines]', _tr).html(d.data.brief);

                }

              } else {
                _.growl(d);

              }

            });

          })
          .on('view-workorder', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.get
              .modal(_.url('<?= $this->route ?>/workorder/' + _data.id))
              .then(m => m.on('refresh-workorder', e => {
                e.stopPropagation();
                _tr.trigger('create-workorder');

              }))
              .then(m => m.on('email-workorder', e => {
                e.stopPropagation();
                _tr.trigger('email-workorder');

              }))
              .then(m => m.on('edit-workorder', e => {
                e.stopPropagation();
                _tr.trigger('edit');

              }))
              .then(m => m.on('invoice-view', e => {
                e.stopPropagation();
                _tr.trigger('invoice-view');

              }))
              .then(m => m.on('quote-view', e => {
                e.stopPropagation();
                _tr.trigger('quote-view');

              }));

          });

        if (!_.browser.isMobileDevice) {
          if (_data.id > 0) {
            _tr
              .addClass('pointer')
              .on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();

                _.hideContexts();
                $(this).trigger('edit');

              });

            $('td[line-number]', _tr)
              .addClass('pointer')
              .on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();

                let _me = $(this);
                let _data = _me.data();

                if ($('i', this).length > 0) {
                  _me.html(_data.line);

                } else {
                  _me.html('<i class="bi bi-check"></i>');

                }

                $('#<?= $tblID ?>').trigger('total-selected');

              });

          }

        }

      });

    let filterType = '';
    $('#<?= $tblID ?> > thead > tr > td[type]')
      .on(_.browser.isMobileDevice ? 'click' : 'contextmenu', function(e) {
        if (e.shiftKey)
          return;

        e.stopPropagation();
        e.preventDefault();

        _.hideContexts();

        let _context = _.context();
        let _me = $(this);

        $.each(jobTypes, (i, jt) => {
          _context.append(
            $('<a href="#"></a>')
            .html(jt)
            .on('click', function(e) {
              e.stopPropagation();
              e.preventDefault();
              _context.close();

              filterType = i;
              _me
                .html('')
                .append(
                  $('<div class="badge badge-primary"></div>')
                  .html(jt)

                );

              $('#<?= $srch ?>').trigger('search');
              sessionStorage.setItem('job-matrix-filter-type', i);

            })
            .on('reconcile', function() {
              // console.log(i, filterType, String(i) === String(filterType));
              if (String(i) === String(filterType)) $(this).prepend('<i class="bi bi-check"></i>')

            })
            .trigger('reconcile')

          );

        });

        _context.append('<hr>');
        _context.append(
          $('<a href="#">clear</a>').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            _context.close();

            filterType = '';
            _me.html('Type');
            $('#<?= $srch ?>')
              .trigger('search');

            sessionStorage.removeItem('job-matrix-filter-type');

          })
        );

        _context.open(e);

      });

    let filterStatus = '';
    $('#<?= $tblID ?> > thead > tr > td[status]')
      .on(_.browser.isMobileDevice ? 'click' : 'contextmenu', function(e) {
        if (e.shiftKey)
          return;

        e.stopPropagation();
        e.preventDefault();

        _.hideContexts();

        let _context = _.context();
        let _me = $(this);

        $.each(jobStatuses, (i, status) => {
          if (<?= config::job_status_ghost ?> != i) {
            _context.append(
              $('<a href="#"></a>')
              .html(status)
              .on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                _context.close();

                filterStatus = i;
                _me
                  .html('')
                  .append(
                    $('<div class="badge badge-primary"></div>')
                    .html(status)

                  );

                $('#<?= $srch ?>').trigger('search');
                sessionStorage.setItem('job-matrix-filter-status', i);

              })
              .on('reconcile', function() {
                // console.log(i, filterStatus, String(i) === String(filterStatus));
                if (String(i) === String(filterStatus)) $(this).prepend('<i class="bi bi-check"></i>')

              })
              .trigger('reconcile')

            );

          }

        });

        _context.append('<hr>');
        _context.append(
          $('<a href="#">clear</a>').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            _context.close();

            filterStatus = '';
            _me.html('Type');
            $('#<?= $srch ?>')
              .trigger('search');

            sessionStorage.removeItem('job-matrix-filter-status');

          })
        );

        _context.open(e);

      });

    let filterPM = '';
    if (pms.length > 0) {
      $('#<?= $tblID ?> > thead > tr > td[PM]')
        .on(_.browser.isMobileDevice ? 'click' : 'contextmenu', function(e) {
          if (e.shiftKey)
            return;

          e.stopPropagation();
          e.preventDefault();

          _.hideContexts();

          let _context = _.context();
          let _me = $(this);

          $.each(pms, (i, pm) => {
            _context.append(
              $('<a href="#"></a>')
              .html(pm)
              .on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                _context.close();

                filterPM = $(this).html();
                _me
                  .html('')
                  .append($('<div class="badge badge-primary"></div>').html(filterPM));

                $('#<?= $srch ?>').trigger('search');
                localStorage.setItem('job-matrix-filter-pm', filterPM);

              })
              .on('reconcile', function() {
                if (pm === filterPM) $(this).prepend('<i class="bi bi-check"></i>')

              })
              .trigger('reconcile')

            );

          });

          _context.append('<hr>');
          _context.append(
            $('<a href="#">clear</a>').on('click', function(e) {
              e.stopPropagation();
              e.preventDefault();
              _context.close();

              filterPM = '';
              _me.html('User');
              $('#<?= $srch ?>')
                .trigger('search');

              localStorage.removeItem('job-matrix-filter-pm');

            })
          );

          _context.open(e);

        });

    }

    $('#<?= $tblID ?>')
      .on('restore-filter', function(e) {

        let pmFilter = localStorage.getItem('job-matrix-filter-pm');
        let typeFilter = sessionStorage.getItem('job-matrix-filter-type');
        let statusFilter = sessionStorage.getItem('job-matrix-filter-status');
        if (!!pmFilter || !!typeFilter || !!statusFilter) {
          if (!!pmFilter) {
            filterPM = pmFilter;
            $('#<?= $tblID ?> > thead > tr > td[PM]')
              .html('')
              .append($('<div class="badge badge-primary"></div>').html(filterPM));
          }

          if (!!typeFilter) {
            filterType = typeFilter;
            $('#<?= $tblID ?> > thead > tr > td[type]')
              .html('')
              .append($('<div class="badge badge-primary"></div>').html(jobTypes[filterType]));
          }

          if (!!statusFilter) {
            filterStatus = statusFilter;
            $('#<?= $tblID ?> > thead > tr > td[status]')
              .html('')
              .append($('<div class="badge badge-primary"></div>').html(jobStatuses[filterStatus]));
          }

          $('#<?= $srch ?>')
            .trigger('search');
        } else {
          $('#<?= $tblID ?>')
            .trigger('update-line-numbers');

        }

        $('#<?= $tblID ?>')
          .addClass('show');

        // console.log('restore-filter');

      });

    let srchidx = 0;
    $('#<?= $srch ?>')
      .on('search', function(e) {
        let idx = ++srchidx;
        let txt = this.value;

        $('#<?= $tblID ?> > tbody > tr').each((i, tr) => {
          if (idx != srchidx) return false;

          let _tr = $(tr);
          let _data = _tr.data();

          // if (String(filterStatus) !== '') {
          //   console.log(String(_data.status), String(filterStatus), String(_data.status) !== String(filterStatus));
          // }

          if (String(filterType) !== '' && String(_data.job_type) !== String(filterType)) {
            _tr.addClass('d-none');

          } else if (String(filterStatus) !== '' && String(_data.status) !== String(filterStatus)) {
            _tr.addClass('d-none');

          } else if (String(filterPM) !== '' && _data.pm !== filterPM) {
            _tr.addClass('d-none');

          } else if ('' === txt.trim()) {
            _tr.removeClass('d-none');

          } else {
            let str = _tr.text()
            if (str.match(new RegExp(txt, 'gi'))) {
              _tr.removeClass('d-none');

            } else {
              _tr.addClass('d-none');

            }

          }

        });

        $('#<?= $tblID ?>').trigger('update-line-numbers');

      })
      .on('keyup', function(e) {
        $(this).trigger('search')
      });

    $(document)
      .on('job-matrix-reload', (e, opt) => {
        if (!!opt) {
          if (!!opt.idx) {
            if ('maintenance' == opt.view) {
              _.nav('<?= $this->route ?>/matrix?v=maintenance&idx=' + opt.idx);
            } else if ('workorder' == opt.view) {
              _.nav('<?= $this->route ?>/matrix?v=workorder&idx=' + opt.idx);
            } else if ('view' == opt.view) {
              _.nav('<?= $this->route ?>/matrix?v=view&idx=' + opt.idx);
            } else {
              _.nav('<?= $this->route ?>/matrix?idx=' + opt.idx);
            }
          } else {
            window.location.reload();
          }
        } else {
          window.location.reload();
        }

      })
      .ready(() => {
        <?php if ($this->data->idx) {  ?>
          let tr = $('#<?= $tblID ?> > tbody > tr[data-id="<?= $this->data->idx ?>"]');
          if (tr.length > 0) {
            tr[0].scrollIntoView({
              block: "center"
            });

            <?php if ('maintenance' == $this->data->trigger) {  ?>
              tr.trigger('property-maintenance');
            <?php } elseif ('workorder' == $this->data->trigger) {  ?>
              tr.trigger('view-workorder');
            <?php } elseif ('view' == $this->data->trigger) {  ?>
              tr.trigger('edit');
            <?php } else {  ?>
              tr.addClass('bg-light');
              setTimeout(() => tr.removeClass('bg-light'), 3000);
            <?php }  ?>

            history.pushState({}, '', '<?= $this->route ?>/matrix');

          }

        <?php }  ?>

        $('#<?= $tblID ?>')
          .trigger('restore-filter');

        // console.log( 'don\'t leave this active')
        // $('thead>tr>td[data-role="sort-header"]').each((i, el) => {
        //   $(el)
        //     .addClass('pointer')
        //     .on('click', _.table.sort);

        // });

      });

    // console.log('matrix loaded');

  })(_brayworth_);
</script>