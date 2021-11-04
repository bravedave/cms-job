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

$categories = $this->data->categories;  ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?>">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <?php
          foreach ($this->data->contractors as $dto) {

            printf(
              '<div class="form-row"><div class="col">%s</div></div>',
              $dto->trading_name
            );

            print '<div class="form-row mb-2">';
            printf('<div class="offset-1 col-5">%s</div>', trim($dto->trading_name) == trim($dto->name) ? $dto->salutation : $dto->name);

            if ($dto->mobile && strings::isPhone($dto->mobile)) {
              printf('<div class="col-3">%s</div>', strings::asMobilePhone($dto->mobile));
            } elseif ($dto->telephone && strings::isPhone($dto->telephone)) {
              printf('<div class="col-3">%s</div>',  strings::asLocalPhone($dto->telephone));
            } elseif ($dto->telephone_business && strings::isPhone($dto->telephone_business)) {
              printf('<div class="col-3">%s</div>', strings::asLocalPhone($dto->telephone_business));
            } else {
              print '<div class="col-3">&nbsp;</div>';
            }

            print '<div class="col-3">';
            if ($dto->services) {
              $services = explode(',', $dto->services);
              foreach ($services as $service) {
                printf(
                  '<div class="text-nowrap">%s</div>',
                  isset($categories[$service]) ?
                    print_r($categories[$service], true) :
                    $service
                );
              }
            }
            print '</div>';

            print '</div>';
          } ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">close</button>
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
          let _modalBody = $('.modal-body', _form);

          // console.table( _data);

          return false;
        });
    }))(_brayworth_);
  </script>
</form>