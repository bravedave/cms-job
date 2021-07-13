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
$t = '';

$dao = new dao\job;
if ($path = $dao->getWorkOrderPath($dto)) {
  if (file_exists($path)) {
    $t = '?t=' . filemtime($path);
  }
}

$_modal = strings::rand();

?>
<style>
  @media (min-width: 768px) {
    #<?= $_modal ?>iframe {
      min-height: calc(100vh - 230px) !important;
    }

  }
</style>
<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <iframe class="w-100" id="<?= $_modal ?>iframe" src="<?= strings::url(sprintf('%s/workorderpdf/%d%s', $this->route, $dto->id, $t)) ?>"></iframe>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" id="<?= $_RefreshWorkOrder = strings::rand() ?>">refresh order</button>
          <button type="button" class="btn btn-outline-secondary" id="<?= $_EmailOrder = strings::rand() ?>"><i class="bi bi-cursor"></i> email order</button>
          <button type="button" class="btn btn-outline-secondary" id="<?= $_gotoJob = strings::rand() ?>"><?= config::label_job_view ?></button>
          <button type="button" class="btn btn-outline-secondary ml-auto" data-dismiss="modal">close</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
      $('#<?= $_RefreshWorkOrder ?>')
        .on('click', function(e) {
          e.stopPropagation();

          $('#<?= $_modal ?>')
            .trigger('refresh-workorder')
            .modal('hide');

        });

      $('#<?= $_EmailOrder ?>')
        .on('click', function(e) {
          e.stopPropagation();

          $('#<?= $_modal ?>')
            .trigger('email-workorder')
            .modal('hide');

        });

      $('#<?= $_gotoJob ?>')
        .on('click', function(e) {
          e.stopPropagation();

          $('#<?= $_modal ?>')
            .trigger('edit-workorder')
            .modal('hide');

        });

      $('#<?= $_form ?>')
        .on('submit', function(e) {
          let _form = $(this);
          let _data = _form.serializeFormJSON();
          let _modalBody = $('.modal-body', _form);

          // console.table( _data);

          return false;
        });
    }))(_brayworth_);
  </script>
</form>