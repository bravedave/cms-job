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
    .constrain {
      max-width: 160px;
    }

  }

  @media (min-width: 1024px) and (max-width: 1439px) {
    .constrain {
      max-width: 250px;
    }

  }

  @media (min-width: 1440px) {
    .constrain {
      max-width: 300px;
    }

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
      <?php while ($dto = $this->data->res->dto()) { ?>
        <tr data-id="<?= $dto->id ?>">
          <td class="small" line-number></td>
          <td>
            <div class="constrain text-truncate">
              <?= $dto->address_street ?>

            </div>

          </td>

          <td>
            <div class="constrain text-truncate">
              <?= $dto->contractor_trading_name ?>

            </div>

          </td>

          <td><?php
              $lines = json_decode($dto->lines);
              foreach ($lines as $line) {
                // \sys::logger( sprintf('<%s> %s', print_r( $line, true), __METHOD__));
                printf(
                  '<div class="form-row mb-1"><div class="col-4">%s</div><div class="col">%s</div></div>',
                  $line->item,
                  $line->description

                );
              }

              ?></td>

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

          _context.append('<hr>');
          _context.append($('<a href="#">close menu</a>').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            _context.close();


          }));

          _context.open(e);

        });;

    });

    $(document).ready(() => $('#<?= $tblID ?>').trigger('update-line-numbers'));

  })(_brayworth_);
</script>