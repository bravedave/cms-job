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
  @media (min-width: 768px) and (max-width: 1023px) {
    .constrain { width: 168px; }
    .constrained { max-width: 160px; }

  }

  @media (min-width: 1024px) and (max-width: 1439px) {
    .constrain { width: 228px; }
    .constrained { max-width: 220px; }

  }

  @media (min-width: 1440px) {
    .constrain { width: 308px; }
    .constrained { max-width: 300px; }

  }
</style>

<div class="table-responsive">
  <table class="table table-sm" id="<?= $tblID = strings::rand() ?>">
    <thead class="small">
      <tr>
        <td>#</td>
        <td>Property</td>
        <td>Contractor</td>
        <td>Items</td>
        <td class="text-center">Status</td>

      </tr>

    </thead>

    <tbody>
      <?php while ($dto = $this->data->res->dto()) {
        $lines = json_decode($dto->lines) ?? [];
        ?>
        <tr
          data-id="<?= $dto->id ?>"
          data-line_count="<?= count($lines) ?>"
          >
          <td class="small" line-number></td>
          <td class="constrain">
            <div class="constrained text-truncate">
              <?= $dto->address_street ?>

            </div>

          </td>

          <td class="constrain">
            <div class="constrained text-truncate">
              <?= $dto->contractor_trading_name ?>

            </div>

          </td>

          <td>
            <?php
              if ( $lines) {
                foreach ($lines as $line) {
                  // \sys::logger( sprintf('<%s> %s', print_r( $line, true), __METHOD__));
                  printf(
                    '<div class="form-row mb-1"><div class="col-4">%s</div><div class="col">%s</div></div>',
                    $line->item,
                    $line->description

                  );
                }

              }
              else {
                print strings::brief( $dto->description);

              }

              ?>

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

          _context.append($('<a href="#" class="font-weight-bold"><i class="bi bi-pencil"></i>edit</a>').on('click', function(e) {
            e.stopPropagation();
            // e.preventDefault();

            _tr.trigger('edit');
            _context.close();

          }));

          if ( 0 == Number(_data.line_count)) {
            _context.append($('<a href="#"><i class="bi bi-trash"></i>delete</a>').on('click', function(e) {
              e.stopPropagation();
              // e.preventDefault();

              _tr.trigger('delete');
              _context.close();

            }));

          }

          _context.append('<hr>');
          _context.append($('<a href="#">close menu</a>').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            _context.close();

          }));

          _context.open(e);

        })
        .on('delete', function( e) {
          let _tr = $(this);

          _.ask.alert({
            title : 'confirm delete',
            text : 'Are you Sure ?',
            buttons : {
              yes : function(e) {
                _tr.trigger('delete-confirmed');
                $(this).modal('hide');

              }

            }

          });

        })
        .on('delete-confirmed', function( e) {
          let _tr = $(this);
          let _data = _tr.data();

          _tr.addClass('text-muted');

          _.post({
            url : _.url('<?= $this->route ?>'),
            data : {
              action : 'job-delete',
              id : _data.id

            },

          }).then( d => {
            _.growl( d);
            if ( 'ack' == d.response) {
              _tr.remove();

            }

          });

        });

    });

    $(document).ready(() => $('#<?= $tblID ?>').trigger('update-line-numbers'));

  })(_brayworth_);
</script>