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

<h1 class="d-none d-print-block"><?= $this->title ?></h1>
<div class="form-row mb-2 d-print-none">
  <div class="col">
    <input type="search" class="form-control" autofocus id="<?= $srch = strings::rand() ?>" />


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

      </tr>

    </thead>

    <tbody>
      <?php while ($dto = $this->data->res->dto()) {  ?>
        <tr data-id="<?= $dto->id ?>">
          <td line-number></td>
          <td><?= $dto->category ?></td>
          <td><?= $dto->item ?></td>
          <td><?= $dto->description ?></td>

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
      });

    $('#<?= $tblID ?> > tbody > tr').each((i, tr) => {
      $(tr)
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

          _context.append($('<a href="#"><i class="bi bi-trash"></i>delete</a>').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            _context.close();
            _tr.trigger('delete');

          }));

          _context.append('<hr>');
          _context.append($('<a href="#">close menu</a>').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            _context.close();


          }));

          _context.open(e);

        });;

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