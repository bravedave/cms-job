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

use strings, theme;  ?>

<form id="<?= $_form = strings::rand() ?>">
  <input type="hidden" name="action" value="template-save">
  <input type="hidden" name="template" value="<?= $this->data->template ?>">

  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label">Template <?= $this->data->template ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>

        </div>

        <div class="modal-body">
          <textarea name="text" rows="8" class="form-control"><?= $this->data->text ?></textarea>
          <div class="form-text">
            macros:<br>
            <strong class="user-select-all">{address}</strong> for the property address<br>
            <strong class="user-select-all">{type}</strong> for <?= config::label ?> type (order, recurring, quote)<br>
            <strong class="user-select-all">{contact}</strong> for the site contact<br>
            <strong class="user-select-all">{duedate}</strong><br>
            <strong class="user-select-all">{PMname}</strong>,
              <strong class="user-select-all">{PMfirstname}</strong>,
              <strong class="user-select-all">{PMemail}</strong>,
              <strong class="user-select-all">{PMmobile}</strong> &amp; <strong class="user-select-all">{PMphone}</strong><br>
            <strong class="user-select-all">{myfirstname}</strong>, <strong class="user-select-all">{myname}</strong>

          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-outline-primary">Save</button>

        </div>

      </div>

    </div>

  </div>
  <script>
    (_ => $(document).ready(() => {
      $('#<?= $_form ?>')
        .on('submit', function(e) {
          let _form = $(this);
          let _data = _form.serializeFormJSON();

          // console.table( _data);

          _.post({
            url: _.url('<?= $this->route ?>'),
            data: _data,

          }).then(d => {
            $('#<?= $_modal ?>').modal('hide');
            if ('ack' == d.response) {
              $('#<?= $_modal ?>').trigger('success');
            };
            _.growl(d);

          });


          return false;

        });

    }))(_brayworth_);
  </script>

</form>