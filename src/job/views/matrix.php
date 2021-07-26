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
  .icon-width {
    width: 1.8em;
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

<h1 class="d-none d-print-block"><?= $this->title ?></h1>
<div class="form-row mb-2 d-print-none">
  <div class="col">
    <input type="search" class="form-control" autofocus id="<?= $srch = strings::rand() ?>">

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
              window.location.reload();

            } else {
              _.hourglass.off();

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

<div class="table-responsive">
  <table class="table table-sm fade" id="<?= $tblID = strings::rand() ?>">
    <thead class="small">
      <tr>
        <td line-number>#</td>
        <td class="constrain">Property</td>
        <td class="d-none d-md-table-cell constrain">Contractor</td>
        <td class="d-none d-md-table-cell">Items</td>
        <td class="text-center" title="Order/Recurring/Quote">Type</td>
        <td class="text-center icon-width"><i class="bi bi-cursor"></i></td>
        <td class="text-center" title="has invoice"><i class="bi bi-info-circle"></i></td>
        <td class="text-center">Status</td>
        <td class="text-center" PM>PM</td>

      </tr>

    </thead>

    <tbody>
      <?php while ($dto = $this->data->res->dto()) {
        $lines = json_decode($dto->lines) ?? [];
        $pm = strings::initials($dto->pm);

        printf(
          '<tr
            class="%s"
            data-id="%s"
            data-properties_id="%s"
            data-address_street="%s"
            data-line_count="%s"
            data-contractor="%s"
            data-pm="%s"
            data-email_sent="%s"
            data-job_type="%s"
            data-archived="%s",
            data-invoiced="%s"
            data-paid="%s">',
          strtotime($dto->archived) > 0 ? 'text-muted' : '',
          $dto->id,
          $dto->properties_id,
          htmlentities($dto->address_street),
          count($lines),
          $dto->contractor_id,
          $pm,
          strtotime($dto->email_sent) > 0 ? 'yes' : 'no',
          $dto->job_type,
          strtotime($dto->archived) > 0 ? 'yes' : 'no',
          1 == (int)$dto->has_invoice ? 'yes' : 'no',
          (int)$dto->paid_by > 0 ? 'yes' : 'no'

        );
      ?>
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

        <td class="text-center" type><?= strings::initials(config::cms_job_type_verbatim($dto->job_type)) ?></td>

        <td class="text-center icon-width" email-sent>
          <?php
          if (strtotime($dto->email_sent) > 0) {
            printf(
              '<i class="bi bi-cursor" title="%s"></i>',
              strings::asLocalDate($dto->email_sent)
            );

            if ($dto->status < config::job_status_sent) {
              $dto->status = config::job_status_sent; // auto advance status
            }
          } else {
            print '&nbsp;';
          }
          ?>
        </td>
        <td class="text-center" invoiced><?= $dto->has_invoice ? '&check;' : '' ?></td>
        <td class="text-center" status><?= config::cms_job_status_verbatim($dto->status) ?></td>

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
    $('#<?= $tblID ?>')
      .on('update-line-numbers', function(e) {
        let tot = 0;
        $('> tbody > tr:not(.d-none) >td[line-number]', this).each((i, e) => {
          $(e).data('line', i + 1).html(i + 1);
          tot++;
        });

        $('> thead > tr >td[line-number]', this).html(tot);

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
          .on('edit', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _tr.addClass('bg-info');

            _.get.modal(_.url('<?= $this->route ?>/job_edit/' + _data.id))
              .then(d => d.on('complete', e => {
                e.stopPropagation();
                _tr
                  .trigger('refresh');

              }))
              .then(d => d.on('success', () => {
                _tr
                  .trigger('refresh');

              }))
              .then(d => d.on('success-and-workorder', () => {
                _tr
                  .trigger('refresh')
                  .trigger('create-workorder');

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

            if (Number(_data.properties_id) > 0) {
              _context.append(
                $('<a href="#" target="_blank"></a>')
                .html('goto ' + _data.address_street)
                .prepend('<i class="bi bi-box-arrow-up-right"></i>')
                .attr('href', _.url('property/view/' + _data.properties_id))
                .on('click', e => _context.close())

              );

            }

            _context.append(
              $('<a href="#">email sent</a>')
              .on('click', e => {
                e.stopPropagation();
                _tr.trigger('yes' == _data.email_sent ? 'mark-sent-undo' : 'mark-sent');
                _context.close();
              })
              .on('reconcile', function(e) {
                if ('yes' == _data.email_sent) {
                  $(this).prepend('<i class="bi bi-check"></i>')

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

            } else if ('yes' == _data.archived) {
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
                subject: _data.address_street + ' workorder',
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
              console.log(o);
              console.log('no email program');

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
              .then(m => m.on('job-mark-invoice-reviewed', e => {
                e.stopPropagation();
                _tr.trigger('mark-reviewed');

              }))
              .then(m => m.on('job-mark-invoice-reviewed-undo', e => {
                e.stopPropagation();
                _tr.trigger('mark-reviewed-undo');

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
          .on('mark-reviewed', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-mark-invoice-reviewed',
                id: _data.id
              },

            }).then(d => {
              _.growl(d);
              if ('ack' == d.response) {
                _tr.trigger('refresh');

              }

            });

          })
          .on('mark-reviewed-undo', function(e) {
            let _tr = $(this);
            let _data = _tr.data();

            _.post({
              url: _.url('<?= $this->route ?>'),
              data: {
                action: 'job-mark-invoice-reviewed-undo',
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
                  pm: pm,
                  job_type: d.data.job_type,
                  invoice: 1 == Number(d.data.has_invoice) ? 'yes' : 'no',
                  complete: 1 == Number(d.data.complete) ? 'yes' : 'no',
                  paid: Number(d.data.paid_by) > 0 ? 'yes' : 'no',
                  archived: archived ? 'yes' : 'no',

                });

                archived ? _tr.addClass('text-muted') : _tr.removeClass('text-muted');

                // console.log(d.data);

                $('[address]', _tr).html(d.data.address_street);
                $('[tradingname]', _tr).html(d.data.contractor_trading_name);
                $('[status]', _tr).html(d.data.status_verbatim);
                $('[invoiced]', _tr).html(1 == Number(d.data.has_invoice) ? '&check;' : '');
                $('[type]', _tr).html(String(d.data.type_verbatim).initials());
                $('[pm]', _tr).html(pm);

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
                if (pm == filterPM) $(this).prepend('<i class="bi bi-check"></i>')

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
              _me.html('PM');
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
        if (!!pmFilter) {
          filterPM = pmFilter;
          $('#<?= $tblID ?> > thead > tr > td[PM]')
            .html('')
            .append($('<div class="badge badge-primary"></div>').html(filterPM));

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

          if (filterPM != '' && _data.pm != filterPM) {
            _tr.addClass('d-none');

          } else if ('' == txt.trim()) {
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
      .ready(() => {
        <?php if ($this->data->idx) {  ?>
          let tr = $('#<?= $tblID ?> > tbody > tr[data-id="<?= $this->data->idx ?>"]');
          if (tr.length > 0) {
            tr[0].scrollIntoView({
              block: "center"
            });

            <?php if ('workorder' == $this->data->trigger) {  ?>
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

      });

  })(_brayworth_);
</script>