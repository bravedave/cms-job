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

use Dompdf\Dompdf;
use strings;

abstract class workorder {
  protected static function getImagePath($path) {
    /**
     * Dompdf won't read in \ path's
     * convert \ to /
     */
    if (\sys::isWindows()) {
      return str_replace('\\', '/', $path);
    }

    return $path;
  }

  static function create(dao\dto\job $dto): bool {

    $t = new template(__DIR__ . '/templates/workorder.html');
    $t->css(__DIR__ . '/templates/css.css');


    if (config::job_type_recurring == $dto->job_type) {
      $t->replace('title', 'Recurring Workorder');
    } elseif (config::job_type_quote == $dto->job_type) {
      $t->replace('title', 'Quote Request');
    } else {
      $t->replace('title', 'Workorder');
    }

    $t->replace(
      'logo',
      sprintf(
        '<img src="%s" width="120" alt="logo">',
        self::getImagePath(__DIR__ . '/images/dealogo.svg')

      )
    );

    $t->replace('updated', strings::asLocalDate($dto->updated));
    $t->replace('due', strings::asLocalDate($dto->due));

    $contractor = [
      'To:',
      $dto->contractor_trading_name

    ];

    $t->replace('contractor', implode('<br>', $contractor));

    $address = [
      'Address:',
      sprintf( '<strong>%s</strong>', $dto->address_street),
      sprintf('%s %s', $dto->address_suburb, $dto->address_postcode)

    ];
    $t->replace('address', implode('<br>', $address));

    $invoice_to = [
      'Invoice To:',
      $dto->owner_name

    ];

    if (config::cms_job_invoiceto()) {
      $invoice_to[] = strings::text2html(config::cms_job_invoiceto());
    }

    $t->replace('invoice_to', implode('<br>', $invoice_to));

    $cells = [
      '<td>category</td>',
      '<td>item</td>',
      '<td>description</td>'

    ];

    $thead = [
      sprintf('<tr>%s</tr>', implode($cells))

    ];

    $tr = [];
    foreach ($dto->lines as $lines) {
      $cells = [
        sprintf('<td>%s</td>', $lines->category),
        sprintf('<td>%s</td>', $lines->item),
        sprintf('<td>%s</td>', $lines->description)

      ];

      $tr[] = sprintf('<tr>%s</tr>', implode($cells));
    }

    $content = [];

    $access = [];

    if ($dto->on_site_contact || $dto->tenants) {
      $tenants = [];
      if ($dto->on_site_contact) {
        $tenants[] = sprintf(
          '<div class="mb-1"><strong>Site Contact</strong> : %s</div>',
          $dto->on_site_contact
        );
      }

      if ($dto->tenants) {
        $tenants[] = '<h3 class="mb-1 mt-0">Tenants</h3>';
        foreach ($dto->tenants as $tenant) {
          $_tenant = [$tenant->name];
          if ($tenant->phone) $_tenant[] = $tenant->phone;
          if ($tenant->email) $_tenant[] = $tenant->email;

          $tenants[] = sprintf('<div class="mb-1">%s</div>', implode(', ', $_tenant));
        }
      }

      if ($tenants) $access[] = sprintf('<td class="noborder">%s</td>', implode($tenants));
    }

    if ($dto->keys) {
      $keys = [];
      foreach ($dto->keys as $key) {
        $keys[] = sprintf(
          '<div class="mb-1 text-right"><strong>Key</strong> : %s</div>',
          $key->keyset
        );
      }

      if ($keys) $access[] = sprintf('<td class="noborder">%s</td>', implode($keys));
    }

    if ($access) {
      $content[] = sprintf(
        '<table class="table mt-1"><tbody><tr>%s</tr></tbody></table>',
        implode($access)

      );
    }

    $content[] = sprintf(
      '<h3 class="mb-0 pl-1">Description</h3>
      <div class="p-1">%s</div>',
      strings::text2html($dto->description)

    );

    $content[] = sprintf(
      '<h3 class="mb-0 pl-1">Items</h3>
      <table class="table"><thead>%s</thead><tbody>%s</tbody></table>',
      implode(PHP_EOL, $thead),
      implode(PHP_EOL, $tr)

    );

    $pm = [
      $dto->property_manager,
      '<strong>Property Manager</strong>'

    ];

    if ( $dto->property_manager_mobile) {
      $dao = new dao\users;
      $options = $dao->options((object)[
        'id' => $dto->property_manager_id
      ]);

      if ( $includeMobile = 'yes' != $options->get('mobile-exclude-from-footer')) {
        $pm[] = sprintf(
          'm. %s',
          strings::asMobilePhone($dto->property_manager_mobile)

        );

      }

    }

    if ( $dto->property_manager_email) {
      $pm[] = sprintf(
        'e. %s',
        $dto->property_manager_email

      );

    }

    $content[] = sprintf(
      '<table class="table mt-2"><tbody><tr><td class="noborder">%s</td></tr></tbody></table>',
      implode('<br>', $pm)

    );

    $t->replace('content', implode($content));

    $dompdf = new Dompdf([
      'chroot' => strings::getCommonPath([
        __DIR__,
        config::dataPath()

      ])
    ]);
    $dompdf->setPaper('A4', 'portrait');  // (Optional) Setup the paper size and orientation
    $dompdf->loadHtml($_rendered = $t->render());
    $dompdf->render();  // Render the HTML as PDF

    $pdf = $dompdf->output();  // trap generated PDF

    $dao = new dao\job;
    $pathPDF = implode(DIRECTORY_SEPARATOR, [
      $dao->store($dto),
      'workorder.pdf'

    ]);

    $pathRendered = implode(DIRECTORY_SEPARATOR, [
      $dao->store($dto),
      'workorder.html'

    ]);


    if (file_exists($pathPDF))
      unlink($pathPDF);
    if (file_exists($pathRendered))
      unlink($pathRendered);

    file_put_contents($pathPDF, $pdf);
    file_put_contents($pathRendered, $_rendered);

    unset($dompdf);

    return true;
  }
}
