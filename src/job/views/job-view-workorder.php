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
  @media (max-width: 767px) {
    .modal-fullscreen-sm.modal-dialog {
      max-width: 100%;
      margin: 0;
      top: 0;
      bottom: 0;
      left: 0;
      right: 0;
      height: 100vh;
      display: flex;
    }

    .modal-fullscreen-sm>.modal-content {
      height: 100%;
      border: 0;
      border-radius: 0;
    }
  }

  @media (min-width: 768px) {
    <?php
    printf(
      '#%s iframe { min-height: calc(100vh - 230px) !important; }',
      $_modal
    );  ?>
  }
</style>
<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-sm modal-xl modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-2">
          <iframe class="w-100 h-100" id="<?= $_modal ?>iframe" src="<?= strings::url(sprintf('%s/workorderpdf/%d%s', $this->route, $dto->id, $t)) ?>"></iframe>
        </div>
        <div class="modal-footer px-2">
          <?php if ((int)$dto->paid_by < 1) {  ?>
            <button type="button" class="btn btn-outline-secondary" id="<?= $_RefreshWorkOrder = strings::rand() ?>"><i class="bi bi-recycle"></i><span class="d-none d-md-inline">refresh order</span></button>
            <button type="button" class="btn btn-outline-secondary" id="<?= $_EmailOrder = strings::rand() ?>"><i class="bi bi-cursor"></i> email<span class="d-none d-md-inline"> order</span></button>
            <script>
              (_ => {
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

              })(_brayworth_);
            </script>
          <?php }  ?>

          <button type="button" class="btn btn-outline-secondary" id="<?= $_gotoJob = strings::rand() ?>"><?= config::label_job_view ?></button>
          <?php if ($this->data->hasInvoice) { ?>
            <button type="button" class="btn btn-outline-secondary" id="<?= $_uid = strings::rand() ?>"><span class="d-none d-md-inline">View </span>Invoice</button>
            <script>
              $('#<?= $_uid ?>').on('click', e => {
                $('#<?= $_modal ?>')
                  .trigger('invoice-view')
                  .modal('hide');
              });
            </script>
          <?php } ?>
          <?php if ($this->data->hasQuote) { ?>
            <button type="button" class="btn btn-outline-secondary" id="<?= $_uid = strings::rand() ?>"><span class="d-none d-md-inline">View </span>Quote</button>
            <script>
              $('#<?= $_uid ?>').on('click', e => {
                $('#<?= $_modal ?>')
                  .trigger('quote-view')
                  .modal('hide');
              });
            </script>
          <?php } ?>
          <button type="button" class="btn btn-outline-secondary ml-auto" data-dismiss="modal">close</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
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