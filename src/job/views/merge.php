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

$address = [];
if ($dto->address_street) $address[] = $dto->address_street;
if ($dto->address_suburb) $address[] = $dto->address_suburb;
if ($dto->address_postcode) $address[] = $dto->address_postcode;

?>
<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="action" value="merge-jobs">
  <input type="hidden" name="src" value="<?= $dto->id ?>">

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
          <div class="form-row mb-2">
            <?php
            printf(
              '<div class="col">
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend">
                    <div class="input-group-text">refer</div>
                  </div>
                  <div class="form-control">%s</div>
                </div>
              </div>',
              workorder::reference($dto->id)
            );

            // status
            printf(
              '<div class="col-auto">
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend">
                    <div class="input-group-text">status</div>
                  </div>
                  <div class="form-control">%s</div>
                </div>
              </div>',
              config::job_status[$dto->status]

            );

            $_u = [strings::asShortDate($dto->created)];
            $_title = '';
            if ($dto->created_by_name) {
              $_title = 'created by ' . $dto->created_by_name;
              $_u[] = strings::initials($dto->created_by_name);
            }

            printf(
              '<div class="col-auto" title="%s">
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend">
                    <div class="input-group-text">create</div>
                  </div>
                  <div class="form-control">%s</div>
                </div>
              </div>',
              $_title,
              implode(' / ', $_u)
            );

            if (strtotime($dto->updated) > strtotime($dto->created)) {
              if ($dto->updated_by_name) {
                $_title = 'updated by ' . $dto->updated_by_name;
                $_u[] = strings::initials($dto->updated_by_name);
              }
              printf(
                '<div class="col-auto" title="%s">
                  <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                      <div class="input-group-text">update</div>
                    </div>
                    <div class="form-control">%s</div>
                  </div>
                </div>',
                $_title,
                implode(' / ', $_u)

              );
            }  ?>

          </div>

          <!-- --[address/due]-- -->
          <div class="form-row mb-2">

            <!-- --[address]-- -->
            <div class="col">
              <div class="form-control"><?= implode(' ', $address) ?></div>

            </div>

            <!-- --[due]-- -->
            <div class="col-auto">
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">due</div>
                </div>
                <div class="form-control">
                  <?= strings::asLocalDate($dto->due) ?>

                </div>

              </div>

            </div>

          </div>

          <div class="form-row mb-2">
            <div class="col">
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">Target <?= config::label_job ?></div>
                </div>

                <select name="target" class="form-control">
                  <option value="" selected>select target</option>
                  <?php
                  foreach ($this->data->otherjobs as $job) {
                    printf(
                      '<option value="%s">%s - %s</option>',
                      $job->id,
                      $job->refer,
                      $job->address_street

                    );
                  }
                  ?>
                </select>

              </div>

            </div>

          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" disabled>merge</button>

          </div>

        </div>

      </div>

    </div>
    <script>
      (_ => {
        $('select[name="target"]', '#<?= $_form ?>')
          .on('change', function(e) {
            let _me = $(this);

            $('button[type="submit"]', '#<?= $_form ?>')
              .prop('disabled', Number(_me.val()) < 1);

          });

        $('#<?= $_modal ?>').on('shown.bs.modal', () => {
          $('#<?= $_form ?>')
            .on('submit', function(e) {
              let _form = $(this);
              let _data = _form.serializeFormJSON();

              // console.log( _data);
              // return false;

              _.post({
                url: _.url('<?= $this->route ?>'),
                data: _data,

              }).then(d => {
                if ('ack' == d.response) {
                  $('#<?= $_modal ?>')
                    .trigger('success')
                    .modal('hide');
                } else {
                  _.growl(d);

                }

              });

              return false;
            });
        });
      })(_brayworth_);
    </script>
</form>