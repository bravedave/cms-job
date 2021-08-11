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
        <div class="modal-body p-2">
          <iframe class="w-100" id="<?= $_modal ?>iframe" src="<?= strings::url(sprintf('%s/invoiceview/%d%s', $this->route, $dto->id, $t)) ?>"></iframe>
          <?php if ($dto->maintenance_directly_to_owner) {  ?>
            <div class="alert alert-warning my-0">send maintenance invoices directly to owner for payment</div>
          <?php }  ?>
        </div>
        <div class="modal-footer px-2 justify-content-start">
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

          <div class="form-check form-check-inline">
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
                  'reviewed by %s - %s',
                  $dto->invoice_reviewed_by_name,
                  strings::asShortDate($dto->invoice_reviewed, $time = true)

                )
                :
                'reviewed'
            );
            ?>

          </div>

          <div class="form-check form-check-inline">
            <?php
            printf(
              '<input type="checkbox" class="form-check-input" id="%s" %s %s>',
              $_uidSentToOwner = strings::rand(),
              $dto->invoice_senttoowner_by ? 'checked' : '',
              (int)$dto->paid_by > 0 ? 'disabled' : ''

            );

            printf(
              '<label class="form-check-label" for="%s" id="%slabel">%s</label>',
              $_uidSentToOwner,
              $_uidSentToOwner,
              $dto->invoice_senttoowner_by ?
                sprintf(
                  'sent to owner for direct payment by %s - %s',
                  $dto->invoice_senttoowner_by_name,
                  strings::asShortDate($dto->invoice_senttoowner, $time = true)

                )
                :
                'sent to owner for direct payment'
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
        .on('mark-reviewed', function(e) {
          _.post({
            url: _.url('<?= $this->route ?>'),
            data: {
              action: 'job-mark-invoice-reviewed',
              id: <?= $dto->id ?>
            },

          }).then(d => {
            _.growl(d);
            if ('ack' == d.response) {
              $('#<?= $_modal ?>')
                .trigger('tr-refresh');

            }

          });

        })
        .on('mark-reviewed-undo', function(e) {
          let _tr = $(this);
          let _data = _tr.data();

          _.post({
            url: _.url('<?= $this->route ?>'),
            data: {
              action: 'job-mark-invoice-reviewed-undo',
              id: <?= $dto->id ?>
            },

          }).then(d => {
            _.growl(d);
            if ('ack' == d.response) {
              $('#<?= $_modal ?>')
                .trigger('tr-refresh');

            }

          });
        })
        .on('change', function(e) {
          let _me = $(this);
          _me.trigger(_me.prop('checked') ? 'mark-reviewed' : 'mark-reviewed-undo')

          $('#<?= $_uidReviewed ?>label').html('reviewed')

        });

      $('#<?= $_uidSentToOwner ?>')
        .on('change', function(e) {
          let _me = $(this);

          $('#<?= $_modal ?>')
            .trigger(_me.prop('checked') ? 'job-mark-invoice-senttoowner' : 'job-mark-invoice-senttoowner-undo');

          $('#<?= $_uidSentToOwner ?>label').html('sent to owner for direct payment')

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