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
  <table class="table table-sm" id="<?= $tblID = strings::rand() ?>">
    <thead class="small">
      <tr>
        <td>#</td>
        <td class="constrain">Property</td>
        <td class="d-none d-md-table-cell constrain">Contractor</td>
        <td class="d-none d-md-table-cell">Items</td>
        <td class="text-center">Status</td>
        <td class="text-center">PM</td>

      </tr>

    </thead>

    <tbody>
      <?php while ($dto = $this->data->res->dto()) {
        $lines = json_decode($dto->lines) ?? [];
        $pm = strings::initials($dto->pm);
      ?>
        <tr data-id="<?= $dto->id ?>" data-line_count="<?= count($lines) ?>" data-pm="<?= $pm ?>">
          <td class="small" line-number></td>
          <td class="constrain">
            <div class="constrained text-truncate">
              <?= $dto->address_street ?>

            </div>
            <div class="d-md-none constrained text-truncate">
              <?= $dto->contractor_trading_name ?>

            </div>

          </td>

          <td class="d-none d-md-table-cell constrain">
            <div class="constrained text-truncate">
              <?= $dto->contractor_trading_name ?>

            </div>

          </td>

          <td class="d-none d-md-table-cell">
            <?php
            if ($lines) {
              foreach ($lines as $line) {
                // \sys::logger( sprintf('<%s> %s', print_r( $line, true), __METHOD__));
                printf(
                  '<div class="form-row mb-1"><div class="col-4 col-md-3">%s</div><div class="col">%s</div></div>',
                  $line->item,
                  $line->description

                );
              }
            } else {
              print strings::brief($dto->description);
            } ?>

          </td>

          <td class="text-center">
            <?php
            if (config::job_status_new == $dto->status) {
              print 'new';
            } elseif (config::job_status_quote == $dto->status) {
              print 'quote';
            } elseif (config::job_status_assigned == $dto->status) {
              print 'assigned';
            } else {
              print $dto->status;
            } ?>

          </td>

          <td class="text-center"><?= $pm ?></td>

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
      $(tr)
        .on('edit', function(e) {
          let _me = $(this);
          let _data = _me.data();

          _.get.modal(_.url('<?= $this->route ?>/job_edit/' + _data.id))
            .then(d => d.on('success', () => window.location.reload()));

        })
        .addClass('pointer')
        .on('click', function(e) {
          e.stopPropagation();
          e.preventDefault();

          _.hideContexts();
          $(this).trigger('edit');

        })
        .on('contextmenu', function(e) {
          if (e.shiftKey)
            return;

          e.stopPropagation();
          e.preventDefault();

          _.hideContexts();

          let _context = _.context();
          let _tr = $(this);
          let _data = _tr.data();

          _context
            .append('<div class="pointer font-weight-bold"><i class="bi bi-pencil"></i>edit</div>')
            .on('click', e => {
              e.stopPropagation();
              _tr.trigger('edit');
              _context.close();

            });

          if (0 == Number(_data.line_count)) {

            _context
              .append('<div class="pointer"><i class="bi bi-trash"></i>delete</div>')
              .on('click', e => {
                e.stopPropagation();
                _tr.trigger('delete');
                _context.close();

              });

          } else {

            _context
              .append('<div class="text-muted">workorder ...</div>')
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
                        .html('<div class="pointer">view workorder</div>')
                        .on('click', e => {
                          e.stopPropagation();
                          _tr.trigger('view-workorder');
                          _context.close();

                        });

                    } else {
                      _me
                        .html('<div class="pointer">create workorder</div>')
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

          _context
            .addClose()
            .open(e);

        })
        .on('create-workorder', function(e) {
          let _tr = $(this);
          let _data = _tr.data();

          _.post({
            url: _.url('<?= $this->route ?>'),
            data: {
              action: 'create-workorder',
              id: _data.id

            },

          }).then(d => {
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
        .on('view-workorder', function(e) {
          let _tr = $(this);
          let _data = _tr.data();

          _.get
            .modal(_.url('<?= $this->route ?>/workorder/' + _data.id))
            .then(m => m.on('refresh-workorder', e => _tr.trigger('create-workorder')));

        });

    });

    $(document).ready(() => $('#<?= $tblID ?>').trigger('update-line-numbers'));

  })(_brayworth_);
</script>