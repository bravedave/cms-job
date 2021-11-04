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

use currentUser, strings;  ?>

<h1 class="d-none d-print-block"><?= $this->title ?></h1>
<div class="form-row mb-2 d-print-none">
  <div class="col">
    <input type="search" class="form-control" autofocus id="<?= $srch = strings::rand() ?>">


  </div>

</div>

<div class="table-responsive">
  <table class="table table-sm" id="<?= $tblID = strings::rand() ?>">
    <thead class="small">
      <tr>
        <td>#</td>
        <td>category</td>
        <td>item</td>
        <td>description</td>
        <td class="text-center">active</td>

      </tr>

    </thead>

    <tbody>
      <?php
      while ($dto = $this->data->res->dto()) {
        printf(
          '<tr data-id="%s" data-active="%s">',
          $dto->id,
          $dto->inactive ? 'no' : 'yes'

        );

        print '<td line-number class="small"></td>';
        printf('<td>%s</td>', $dto->category);
        printf('<td>%s</td>', $dto->item);
        printf('<td>%s</td>', $dto->description);
        printf(
          '<td class="text-center" active>%s</td>',
          $dto->inactive ? '&times' : '&check;'
        );

        print '</tr>';
      } ?>
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
      });

    $('#<?= $tblID ?> > tbody > tr').each((i, tr) => {
      let _tr = $(tr);

      _tr
        .on('edit', function(e) {
          let _me = $(this);
          let _data = _me.data();

          _.get.modal(_.url('<?= $this->route ?>/item_edit/' + _data.id))
            .then(d => d.on('success', () => window.location.reload()));

        })
        .on('delete', function(e) {
          let _tr = $(this);

          _.ask.alert({
            text: 'Are you sure ?',
            title: 'Confirm Delete',
            buttons: {
              yes: function(e) {

                $(this).modal('hide');
                _tr.trigger('delete-confirmed');

              }

            }

          });

        })
        .on('delete-confirmed', function(e) {
          let _tr = $(this);
          let _data = _tr.data();

          _.post({
            url: _.url('<?= $this->route ?>'),
            data: {
              action: 'item-delete',
              id: _data.id

            },

          }).then(d => {
            if ('ack' == d.response) {
              _tr.remove();
              $('#<?= $tblID ?>').trigger('update-line-numbers');

            } else {
              _.growl(d);

            }

          });

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

              _context.close();
              _tr.trigger('edit');

            })

          );

          <?php if (currentUser::restriction('can-add-job-items')) { ?>

            _context.append(
              $('<a href="#"><i class="bi bi-trash"></i>delete</a>')
              .on('click', e => {
                e.stopPropagation();

                _context.close();
                _tr.trigger('delete');

              })

            );

          <?php } ?>

          _context.append(
            $('<a href="#">active</a>')
            .on('click', e => {
              e.stopPropagation();

              _context.close();
              _tr.trigger('yes' == _data.active ? 'mark-inactive' : 'mark-active');

            })
            .on('reconcile', function(e) {
              let _me = $(this);
              if ('yes' == _data.active) {
                _me.prepend('<i class="bi bi-check"></i>');

              }

            })
            .trigger('reconcile')

          );

          _context.append(
            $('<a href="#">show contractors</a>')
            .on('click', e => {
              e.stopPropagation();

              _context.close();
              _tr.trigger('show-contractors');

            })

          );

          _context
            .addClose()
            .open(e);

        })
        .on('mark-active', function(e) {
          let _tr = $(this);
          let _data = _tr.data();

          _.post({
            url: _.url('<?= $this->route ?>'),
            data: {
              action: 'item-mark-active',
              id: _data.id

            },

          }).then(d => {
            if ('ack' == d.response) {
              _tr.data('active', 'yes');
              $('td[active]', _tr).html('&check;');

            } else {
              _.growl(d);

            }

          });

        })
        .on('mark-inactive', function(e) {
          let _tr = $(this);
          let _data = _tr.data();

          _.post({
            url: _.url('<?= $this->route ?>'),
            data: {
              action: 'item-mark-inactive',
              id: _data.id

            },

          }).then(d => {
            if ('ack' == d.response) {
              _tr.data('active', 'no');
              $('td[active]', _tr).html('&times;');

            } else {
              _.growl(d);

            }

          });

        })
        .on('show-contractors', function(e) {
          let _tr = $(this);
          let _data = _tr.data();

          _.get.modal(_.url('<?= $this->route ?>/contractorsfor/' + _data.id));

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

    let srchidx = 0;
    $('#<?= $srch ?>').on('keyup', function(e) {
      let idx = ++srchidx;
      let txt = this.value;

      $('#<?= $tblID ?> > tbody > tr').each((i, tr) => {
        if (idx != srchidx) return false;

        let _tr = $(tr);
        if ('' == txt.trim()) {
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

    });

    $(document).ready(() => $('#<?= $tblID ?>').trigger('update-line-numbers'));

  })(_brayworth_);
</script>