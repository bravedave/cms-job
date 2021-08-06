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
if ($path = $dao->getInvoicePath($dto)) {
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
          <iframe class="w-100" id="<?= $_modal ?>iframe" src="<?= strings::url(sprintf('%s/invoiceview/%d%s', $this->route, $dto->id, $t)) ?>"></iframe>
        </div>
        <div class="modal-footer">
          <?php if ((int)$dto->paid_by < 1) {  ?>
            <button type="button" class="btn btn-outline-secondary" id="<?= $_delete = strings::rand() ?>"><i class="bi bi-trash"></i> delete</button>
            <script>
              (_ => {
                $('#<?= $_delete ?>')
                  .on('click', function(e) {
                    e.stopPropagation();

                    $('#<?= $_modal ?>')
                      .trigger('delete-invoice')
                      .modal('hide');

                  });

              })(_brayworth_);
            </script>
          <?php } ?>

          <?php if ($this->data->hasWorkorder) { ?>
            <button type="button" class="btn btn-outline-secondary" accesskey="O" id="<?= $_uid = strings::rand() ?>"><i class="bi bi-file-pdf text-danger"></i> <span style="text-decoration: underline;">O</span>rder</button>
            <script>
              $('#<?= $_uid ?>').on('click', e => {
                $('#<?= $_modal ?>').modal('hide');
                $('#<?= $_modal ?>').trigger('view-workorder');
              });
            </script>
          <?php } ?>

          <button type="button" class="btn btn-outline-secondary" id="<?= $_EmailInvoice = strings::rand() ?>"><i class="bi bi-cursor"></i> email invoice</button>
          <script>
            (_ => {
              $('#<?= $_EmailInvoice ?>')
                .on('click', function(e) {
                  e.stopPropagation();

                  $('#<?= $_modal ?>')
                    .trigger('email-invoice')
                    .modal('hide');

                });

            })(_brayworth_);
          </script>

          <button type="button" class="btn btn-outline-secondary" id="<?= $_gotoJob = strings::rand() ?>"><?= config::label_job_view ?></button>

          <div class="form-check">
            <?php
            printf(
              '<input type="checkbox" class="form-check-input" id="%s" %s %s>',
              $_uidReviewed = strings::rand(),
              $dto->invoice_reviewed_by ? 'checked' : '',
              (int)$dto->paid_by > 0 ? 'disabled' : ''

            );

            printf(
              '<label class="form-check-label" for="%s" id="%slabel">%s</label>',
              $_uidReviewed,
              $_uidReviewed,
              $dto->invoice_reviewed_by ?
                sprintf(
                  'Reviewed by %s - %s',
                  $dto->invoice_reviewed_by_name,
                  strings::asShortDate($dto->invoice_reviewed, $time = true)

                )
                :
                'Reviewed'
            );
            ?>

          </div>

          <button type="button" class="btn btn-outline-secondary ml-auto" data-dismiss="modal">close</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (_ => $('#<?= $_modal ?>').on('shown.bs.modal', () => {
      $('#<?= $_uidReviewed ?>')
        .on('change', function(e) {
          let _me = $(this);

          $('#<?= $_modal ?>')
            .trigger(_me.prop('checked') ? 'job-mark-invoice-reviewed' : 'job-mark-invoice-reviewed-undo');

          $('#<?= $_uidReviewed ?>label').html('Reviewed')

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