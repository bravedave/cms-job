<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\console;

use strings;  ?>

<div class="table-responsive">
  <table class="table table-sm" id="<?= $tblID = strings::rand() ?>">
    <thead class="small">
      <tr>
        <td>#</td>
        <td>Category</td>

      </tr>

    </thead>

    <tbody>
      <?php while ($dto = $this->data->res->dto()) { ?>
        <tr data-id="<?= $dto->id ?>">
          <td line-number class="small"></td>
          <td><?= $dto->category ?></td>

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
      .trigger('update-line-numbers');

    $('#<?= $tblID ?> > tbody > tr').each((i, tr) => {
      $(tr)
        .on('edit', function(e) {
          let _me = $(this);
          let _data = _me.data();

          _.get.modal(_.url('<?= $this->route ?>/category_edit/' + _data.id))
            .then(d => d.on('success', () => window.location.reload()));

        })
        .addClass('pointer')
        .on('click', function(e) {
          e.stopPropagation();
          e.preventDefault();

          _.hideContexts();
          $(this).trigger('edit');

        });

    });

    $(document).ready(() => {});

  })(_brayworth_);
</script>