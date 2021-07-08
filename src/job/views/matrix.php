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

use strings;  ?>
<style>
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

  @media (min-width: 1024px) and (max-width: 1199px) {
    .constrain {
      width: 188px;
    }

    .constrained {
      max-width: 180px;
    }

  }

  @media (min-width: 1200px) and (max-width: 1439px) {
    .constrain {
      width: 208px;
    }

    .constrained {
      max-width: 200px;
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

<div class="table-responsive">
  <table class="table table-sm fade" id="<?= $tblID = strings::rand() ?>">
    <thead class="small">
      <tr>
        <td>#</td>
        <td class="constrain">Property</td>
        <td class="d-none d-md-table-cell constrain">Contractor</td>
        <td class="d-none d-md-table-cell">Items</td>
        <td class="text-center" title="Order/Recurring/Quote">Type</td>
        <td class="text-center">Status</td>
        <td class="text-center">PM</td>

      </tr>

    </thead>

    <tbody>
      <?php while ($dto = $this->data->res->dto()) {
        $lines = json_decode($dto->lines) ?? [];
        $pm = strings::initials($dto->pm);
      ?>
        <tr <?php
            printf(
              'data-id="%s" data-properties_id="%s" data-address_street="%s" data-line_count="%s" data-contractor="%s" data-pm="%s"',
              $dto->id,
              $dto->properties_id,
              htmlentities($dto->address_street),
              count($lines),
              $dto->contractor_id,
              $pm

            );
            ?>>
          <td class="small" line-number></td>
          <td class="constrain">
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

          <td class="text-center" type><?= strings::initials(config::cms_job_type_verbatim($dto->status)) ?></td>

          <td class="text-center" status><?= config::cms_job_status_verbatim($dto->status) ?></td>

          <td class="text-center" pm><?= $pm ?></td>

        </tr>

      <?php } ?>

    </tbody>

  </table>

</div>

<script>
  (_ => {
    $('#<?= $tblID ?>')
      .on('update-line-numbers', function(e) {
        $('> tbody > tr:not(.d-none) >td[line-number]', this).each((i, e) => {
          $(e).data('line', i + 1).html(i + 1);
        });
      })

    $('#<?= $tblID ?> > tbody > tr').each((i, tr) => {
      let _tr = $(tr);
      _tr
        .on('edit', function(e) {
          let _tr = $(this);
          let _data = _tr.data();

          _tr.addClass('bg-info');

          _.get.modal(_.url('<?= $this->route ?>/job_edit/' + _data.id))
            .then(d => d.on('success', () => {
              _tr
                .trigger('refresh');

            }))
            .then(d => d.on('success-and-workorder', () => {
              _tr
                .trigger('refresh')
                .trigger('create-workorder');

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

          _context
            .append.a()
            .html('<i class="bi bi-pencil"></i>edit')
            .on('click', e => {
              e.stopPropagation();
              _tr.trigger('edit');
              _context.close();

            });

          if (0 == Number(_data.line_count)) {

            _context
              .append.a()
              .html('<i class="bi bi-trash"></i>delete')
              .on('click', e => {
                e.stopPropagation();
                _tr.trigger('delete');
                _context.close();

              });

          } else {

            _context
              .append.a()
              .html('<div class="text-muted">workorder ...</div>')
              .on('reconcile', function(e) {
                let _me = $(this);

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
                        .on('click', e => {
                          e.stopPropagation();
                          _tr.trigger('view-workorder');
                          _context.close();

                        });

                    } else {
                      _me
                        .html('create workorder')
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

              })
              .trigger('reconcile');

          }

          if (Number(_data.properties_id) > 0) {
            _context.append.a()
              .attr('target', '_blank')
              .html('goto ' + _data.address_street)
              .prepend('<i class="bi bi-box-arrow-up-right"></i>')
              .attr('href', _.url('property/view/' + _data.properties_id))
              .on('click', e => _context.close());

          }

          _context
            .addClose()
            .open(e);

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
                    console.log('no email program');

                  }

                } else {
                  _.growl(d);

                }

              });

            }

            // console.log('email-workorder');
            // console.log(_data);
            let mailer = _.email.mailer({
              subject: _data.address_street + ' workorder'
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
            console.log(o);
            console.log('no email program');

          }

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
              _tr.data({
                properties_id: d.data.properties_id,
                address_street: d.data.address_street,
                line_count: d.data.lines.length,
                contractor: d.data.contractor_id,
                pm: pm

              });

              console.log(d.data);

              $('[address]', _tr).html(d.data.address_street);
              $('[tradingname]', _tr).html(d.data.contractor_trading_name);
              $('[status]', _tr).html(d.data.status_verbatim);
              $('[type]', _tr).html(String(d.data.type_verbatim).initials());
              $('[pm]', _tr).html(pm);

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
            .then(m => m.on('refresh-workorder', e => _tr.trigger('create-workorder')))
            .then(m => m.on('email-workorder', e => _tr.trigger('email-workorder')))
            .then(m => m.on('edit-workorder', e => _tr.trigger('edit')));

        });

      if (!_.browser.isMobileDevice) {
        _tr
          .addClass('pointer')
          .on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            _.hideContexts();
            $(this).trigger('edit');

          });

      }

    });

    $(document)
      .ready(() => {
        <?php if ($this->data->idx) {  ?>
          let tr = $('#<?= $tblID ?> > tbody > tr[data-id="<?= $this->data->idx ?>"]');
          if (tr.length > 0) {
            tr[0].scrollIntoView({
              block: "center"
            });

            <?php if ('workorder' == $this->data->trigger) {  ?>
              tr.trigger('view-workorder');
            <?php } else {  ?>
              tr.addClass('bg-light');
              setTimeout(() => tr.removeClass('bg-light'), 3000);
            <?php }  ?>

            history.pushState({}, '', '<?= $this->route ?>/matrix');

          }

        <?php }  ?>

        $('#<?= $tblID ?>')
          .addClass('show')
          .trigger('update-line-numbers');

      });

  })(_brayworth_);
</script>