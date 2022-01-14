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

use strings, theme;

$dto = $this->data->dto;
$document = $this->data->document;
$_modal = strings::rand();
?>
<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <style>
    @media (min-width: 768px) {
      <?php
      printf(
        '#%s iframe { min-height: calc(100vh - 230px) !important; }',
        $_modal
      );  ?>
    }
  </style>
  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-2">
          <iframe src="<?= strings::url(
                          sprintf(
                            '%s/contractor_document/%d?d=%s&%s',
                            $this->route,
                            $dto->id,
                            $document,
                            time()
                          )
                        ) ?>" class="w-100" id="<?= $_modal ?>iframe"></iframe>

        </div>
      </div>
    </div>
  </div>
  <script>
    (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
      $('#<?= $_form ?>')
        .on('submit', function(e) {
          let _form = $(this);
          let _data = _form.serializeFormJSON();

          // console.table( _data);

          return false;
        });
    }))(_brayworth_);
  </script>
</form>