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

use currentUser, strings;

$categories = $this->data->categories;  ?>

<h1 class="d-none d-print-block"><?= $this->title ?></h1>
<div class="form-row mb-2 fade d-print-none" id="<?= $srch = strings::rand() ?>row">
  <div class="col">
    <input type="search" class="form-control" autofocus id="<?= $srch ?>">

  </div>

</div>

<div class="table-responsive">
  <table class="table table-sm fade" id="<?= $tblID = strings::rand() ?>">
    <thead class="small">
      <tr>
        <td line-number>#</td>
        <td data-role="sort-header" data-key="trading">Trading As</td>
        <td data-role="sort-header" data-key="contact">Contact</td>
        <td>Tel</td>
        <td>Services</td>
        <td data-role="sort-header" data-key="jobs" data-sorttype="numeric" class="text-center">Jobs</td>
        <td class="text-center"><i class="bi bi-shield-check"></i></td>

      </tr>

    </thead>

    <tbody>
      <?php foreach ($this->data->dtoSet as $dto) {
        $contactName = trim($dto->trading_name) == trim($dto->name) ?
          $dto->salutation : $dto->name;

        printf(
          '<tr
            data-id="%s"
            data-trading="%s"
            data-contact="%s"
            data-jobs="%s">',
          $dto->id,
          htmlspecialchars($dto->trading_name),
          htmlspecialchars($contactName),
          $dto->jobs

        );
      ?>
        <td line-number class="small"></td>
        <td data-role="trading_name"><?= $dto->trading_name ?></td>
        <td data-role="primary_contact"><?= $contactName ?></td>
        <td class="text-nowrap" data-role="phone">
          <?php
          if ($dto->mobile && strings::isPhone($dto->mobile)) {
            print strings::asMobilePhone($dto->mobile);
          } elseif ($dto->telephone && strings::isPhone($dto->telephone)) {
            print strings::asLocalPhone($dto->telephone);
          } elseif ($dto->telephone_business && strings::isPhone($dto->telephone_business)) {
            print strings::asLocalPhone($dto->telephone_business);
          } else {
            print '&nbsp;';
          } ?>
        </td>

        <td>
          <?php
          if ($dto->services) {
            $services = explode(',', $dto->services);
            foreach ($services as $service) {
              if (isset($categories[$service])) {
                printf('<div class="text-nowrap">%s</div>', print_r($categories[$service], true));
              } else {
                printf('<div>%s</div>', $service);
              }
            }
          } else {
            print '&nbsp;';
          }
          ?>
        </td>
        <td class="text-center"><?= $dto->jobs ?></td>
        <td class="text-center"><?= $dto->insurance ? '<i class="bi bi-shield-check"></i>' : '<i class="bi bi-shield-fill-x text-danger"></i>' ?></td>


      <?php
        print '</tr>';
      } ?>

    </tbody>

  </table>

</div>
<script>
  (_ => {
    $('#<?= $tblID ?>')
      .on('update-line-numbers', function(e) {
        let t = 0;
        $('> tbody > tr:not(.d-none) >td[line-number]', this).each((i, e) => {
          $(e).data('line', i + 1).html(i + 1);
          t++;
        });
        $('> thead > tr > td[line-number]', this).data('count', t).html(t);
      });

    const rowClick = function(e) {
      e.stopPropagation();
      e.preventDefault();

      _.hideContexts();
      $(this).trigger('edit');

    };

    const rowContext = function(e) {
      if (e.shiftKey)
        return;

      e.stopPropagation();
      e.preventDefault();

      _.hideContexts();

      let _tr = $(this);
      let _context = _.context();

      _context.append(
        $('<a href="#"><i class="bi bi-pencil"></i><strong>edit</strong></a>')
        .on('click', e => {
          e.stopPropagation();
          e.preventDefault();

          _context.close();
          $(this).trigger('edit');

        })

      );

      <?php if (currentUser::isadmin()) { ?>

        _context.append(
          $('<a href="#"><i class="bi bi-arrows-angle-contract"></i>merge</a>')
          .on('click', e => {
            e.stopPropagation();
            e.preventDefault();

            _context.close();
            _tr.trigger('merge');

          })

        );

        _context.append(
          $('<a href="#"><i class="bi bi-trash"></i>delete</a>')
          .on('click', e => {
            e.stopPropagation();
            e.preventDefault();

            _context.close();
            _tr.trigger('delete');

          })

        );

      <?php } ?>

      _context.open(e);
    };

    const rowDelete = function(e) {
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

    };

    const rowDeleteConfirmed = function(e) {
      let _tr = $(this);
      let _data = _tr.data();

      _.post({
        url: _.url('<?= $this->route ?>'),
        data: {
          action: 'contractor-delete',
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

    };

    const rowDocumentView = function(e, document) {
      let _tr = $(this);
      let _data = _tr.data();

      // console.log(document);

      _.get.modal(_.url(`<?= $this->route ?>/contractor_document_view/${_data.id}?d=${encodeURIComponent(document)}`))
        .then(m => m.on('hidden.bs.modal', e => _tr.trigger('edit')));

    };

    const rowEdit = function(e) {
      let _me = $(this);
      let _data = _me.data();

      _.get.modal(_.url('<?= $this->route ?>/contractor_edit/' + _data.id))
        .then(d => d.on('document-view', (e, doc) => {
          e.stopPropagation();
          _me.trigger('document-view', doc);

        }))
        .then(d => d.on('edit-primary-contact', e => {
          e.stopPropagation();
          _me.trigger('edit-primary-contact');

        }))
        .then(d => d.on('success', (e, d) => {
          _.nav('<?= $this->route ?>/contractors?idx=' + d.id);

        }))
        .then(d => d.on('send-sms', () => _.ask.warning({
          'title': 'not implemented',
          'text': 'Feature not implented'
        })));

    };

    const rowEditPrimaryContact = function(e) {
      let _me = $(this);
      let _data = _me.data();

      _.get.modal(_.url('people/getPerson'))
        .then(m => m.on('success', (e, person) => {
          _.post({
            url: _.url('<?= $this->route ?>'),
            data: {
              action: 'set-primary-contact',
              id: _data.id,
              people_id: person.id

            },

          }).then(d => {
            if ('ack' == d.response) {
              $('td[data-role="primary_contact"]', _me).html(person.name)
              if (String(person.mobile).IsMobilePhone()) {
                $('td[data-role="phone"]', _me).html(String(person.mobile).AsMobilePhone())

              } else if (String(person.mobile).IsPhone()) {
                $('td[data-role="phone"]', _me).html(String(person.mobile).AsLocalPhone())

              } else {
                $('td[data-role="phone"]', _me).html('&nbsp;')

              }

            } else {
              _.growl(d);

            }

          });

        }))
        .then(m => m.on('hidden.bs.modal', (e) => _me.trigger('edit')));

    };

    const rowMerge = function(e) {
      let _tr = $(this);
      let _data = _tr.data();

      _.get.modal(_.url(`<?= $this->route ?>/contractor_merge/${_data.id}`))
        .then(m => m.on('success', e => _tr.remove()));

    };

    $('#<?= $tblID ?> > tbody > tr')
      .each((i, tr) => {
        $(tr)
          .addClass('pointer')
          .on('click', rowClick)
          .on('edit', rowEdit)
          .on('edit-primary-contact', rowEditPrimaryContact)
          .on('contextmenu', rowContext)
          .on('delete', rowDelete)
          .on('delete-confirmed', rowDeleteConfirmed)
          .on('document-view', rowDocumentView)
          .on('merge', rowMerge);

      });

    let srchidx = 0;
    $('#<?= $srch ?>')
      .on('keyup', function(e) {
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

    $(document)
      .ready(() => {
        <?php if ($this->data->idx) {  ?>
          let tr = $('#<?= $tblID ?> > tbody > tr[data-id="<?= $this->data->idx ?>"]');
          if (tr.length > 0) {
            tr[0].scrollIntoView({
              block: "center"
            });

            tr.addClass('bg-light');
            setTimeout(() => tr.removeClass('bg-light'), 3000);

            history.pushState({}, '', '<?= $this->route ?>/contractor');

          }

        <?php }  ?>

        $('#<?= $tblID ?>')
          .addClass('show')
          .trigger('update-line-numbers');

        $('#<?= $srch ?>row')
          .addClass('show');

      });

  })(_brayworth_);
</script>