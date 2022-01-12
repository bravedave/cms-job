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
use currentUser, strings;

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

  static function reference( int $id) : string {
    return 'DEA' . str_pad($id, 6, '0', STR_PAD_LEFT);

  }

  static function create(dao\dto\job $dto): bool {

    $t = new template(__DIR__ . '/templates/workorder.html');
    $t->css(__DIR__ . '/templates/css.css');


    $t->replace('title', config::cms_job_PDF_title($dto->job_type));
    $t->replace('reference', self::reference($dto->id));

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

    if ( $dto->contractor_primary_contact_name) {
      $contractor[] = $dto->contractor_primary_contact_name;

    }

    $t->replace('contractor', implode('<br>', $contractor));

    $address = [
      'Address:',
      sprintf('<strong>%s</strong>', $dto->address_street),
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

    if ( config::job_type_recurring == $dto->job_type) {

      $LeftWidth = '120';
      $recurrence = [];
      if ( config::job_recurrence_interval_week == $dto->job_recurrence_interval) {

        $frequency = 'week';
        if ( in_array( (int)$dto->job_recurrence_week_frequency, [2])) {
          $frequency = sprintf( '%dnd week', (int)$dto->job_recurrence_week_frequency);

        }
        elseif ( in_array( (int)$dto->job_recurrence_week_frequency, [3])) {
          $frequency = sprintf( '%drd week', (int)$dto->job_recurrence_week_frequency);
        }
        elseif ( in_array( (int)$dto->job_recurrence_week_frequency, [4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20])) {
          $frequency = sprintf( '%dth week', (int)$dto->job_recurrence_week_frequency);
        }
        else {
          $frequency = sprintf( '%d week/s', (int)$dto->job_recurrence_week_frequency);
        }

        $recurrence[] = sprintf('<tr><td style="width: %dpx">Job repeats every</td><td>%s</td></tr>', $LeftWidth, $frequency);
        $recurrence[] = sprintf('<tr><td style="width: %dpx">commencing</td><td>%s</td></tr>', $LeftWidth, strings::asLocalDate($dto->due));

        $days = [];
        $daysOfWeek = explode(',', $dto->job_recurrence_day_of_week);
        if ( in_array(1, $daysOfWeek)) $days[] = 'Monday';
        if ( in_array(2, $daysOfWeek)) $days[] = 'Tuesday';
        if ( in_array(3, $daysOfWeek)) $days[] = 'Wednesday';
        if ( in_array(4, $daysOfWeek)) $days[] = 'Thursday';
        if ( in_array(5, $daysOfWeek)) $days[] = 'Friday';
        if ( in_array(6, $daysOfWeek)) $days[] = 'Saturday';
        if ( in_array(7, $daysOfWeek)) $days[] = 'Sunday';
        if ( $days) {
          $recurrence[] = sprintf('<tr><td style="width: %dpx">on</td><td>%s</td></tr>', $LeftWidth, implode( ',', $days));

        }

        if (strtotime($dto->job_recurrence_end) > 0) {
          $recurrence[] = sprintf('<tr><td style="width: %dpx">until</td><td>%s</td></tr>', $LeftWidth, strings::asLocalDate($dto->job_recurrence_end));

        }

      } else if ( config::job_recurrence_interval_month == $dto->job_recurrence_interval) {

        $frequency = 'month';
        if (in_array((int)$dto->job_recurrence_month_frequency, [2])) {
          $frequency = sprintf('%dnd month', (int)$dto->job_recurrence_month_frequency);
        } elseif (in_array((int)$dto->job_recurrence_month_frequency, [3])) {
          $frequency = sprintf('%drd month', (int)$dto->job_recurrence_month_frequency);
        } elseif (in_array((int)$dto->job_recurrence_month_frequency, [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20])) {
          $frequency = sprintf('%dth month', (int)$dto->job_recurrence_month_frequency);
        } else {
          $frequency = sprintf('%d month/s', (int)$dto->job_recurrence_month_frequency);
        }

        $businessDay = '';
        if ($dto->job_recurrence_on_business_day) {
          $businessDay = ', on the next business day';
        }

        $recurrence[] = sprintf('<tr><td style="width: %dpx">Job repeats every</td><td>%s</td></tr>', $LeftWidth, $frequency . $businessDay);
        $recurrence[] = sprintf('<tr><td style="width: %dpx">commencing</td><td>%s</td></tr>', $LeftWidth, strings::asLocalDate($dto->due));

        if (strtotime($dto->job_recurrence_end) > 0) {
          $recurrence[] = sprintf('<tr><td style="width: %dpx">until</td><td>%s</td></tr>', $LeftWidth, strings::asLocalDate($dto->job_recurrence_end));
        }

      } else if ( config::job_recurrence_interval_year == $dto->job_recurrence_interval) {

        $frequency = 'year';
        if (in_array((int)$dto->job_recurrence_year_frequency, [2])) {
          $frequency = sprintf('%dnd year', (int)$dto->job_recurrence_year_frequency);
        } elseif (in_array((int)$dto->job_recurrence_year_frequency, [3])) {
          $frequency = sprintf('%drd year', (int)$dto->job_recurrence_year_frequency);
        } elseif (in_array((int)$dto->job_recurrence_year_frequency, [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20])) {
          $frequency = sprintf('%dth year', (int)$dto->job_recurrence_year_frequency);
        } else {
          $frequency = sprintf('%d year/s', (int)$dto->job_recurrence_year_frequency);
        }

        $businessDay = '';
        if ( $dto->job_recurrence_on_business_day) {
          $businessDay = ', on the next business day';
        }

        $recurrence[] = sprintf('<tr><td style="width: %dpx">Job repeats every</td><td>%s</td></tr>', $LeftWidth, $frequency . $businessDay);
        $recurrence[] = sprintf('<tr><td style="width: %dpx">commencing</td><td>%s</td></tr>', $LeftWidth, strings::asLocalDate($dto->due));

        if (strtotime($dto->job_recurrence_end) > 0) {
          $recurrence[] = sprintf('<tr><td style="width: %dpx">until</td><td>%s</td></tr>', $LeftWidth, strings::asLocalDate($dto->job_recurrence_end));
        }

      }

      $content[] = sprintf(
        '<h3 class="mb-0 pl-1">Recurrence Schedule</h3>
        <table class="table mt-1"><tbody>%s</tbody></table>',
        implode($recurrence)

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
      '<strong>For any information relating to this job, please contact:</strong>',
      $dto->property_manager
    ];

    if ($dto->property_manager_mobile) {

      $includeMobile = true;
      // if ($dto->property_manager_id) {
      //   $dao = new dao\users;
      //   if ($uDto = $dao->getByID($dto->property_manager_id)) {
      //     $options = $dao->options($uDto);
      //     $includeMobile = 'yes' != $options->get('mobile-exclude-from-footer');
      //   }
      // }

      if ($includeMobile) {
        $pm[] = sprintf(
          'm. %s',
          strings::asMobilePhone($dto->property_manager_mobile)

        );
      }
    }

    if ($dto->property_manager_email) {
      $pm[] = sprintf(
        'e. %s',
        $dto->property_manager_email

      );
    }

    // $pm[] = '<strong>Property Manager</strong>';

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

  static function expand_template( dao\dto\job $dto, string $text) : string {
    $search = [];
    $replace = [];

    $search[] = '@{myname}@';
    $replace[] = currentUser::name();

    $search[] = '@{myfirstname}@';
    $replace[] = strings::FirstWord( currentUser::name());

    $search[] = '@{address}@';
    $replace[] = $dto->address_street;

    $search[] = '@{type}@';
    $replace[] = config::cms_job_type_verbatim($dto->job_type);

    $search[] = '@{contractor_contact}@';
    $replace[] = $dto->contractor_primary_contact_name;

    $search[] = '@{contractor_contact_firstname}@';
    $replace[] = strings::FirstWord( $dto->contractor_primary_contact_name);

    $search[] = '@{contact}@';
    $replace[] = strings::asLocalDate($dto->on_site_contact);

    $search[] = '@{duedate}@';
    $replace[] = strings::asLocalDate($dto->due);

    $search[] = '@{PMname}@';
    $replace[] = $dto->property_manager;

    $search[] = '@{PMfirstname}@';
    $replace[] = strings::FirstWord( $dto->property_manager);

    $search[] = '@{PMemail}@';
    $replace[] = strings::FirstWord( $dto->property_manager_email);

    $search[] = '@{PMmobile}@';
    $replace[] = strings::FirstWord( $dto->property_manager_mobile);

    $search[] = '@{PMphone}@';
    $replace[] = strings::FirstWord( $dto->property_manager_telephone);

    $_replace = array_map(function ($s) {
      return str_replace('$', '\$', $s);
    }, $replace);
    return preg_replace($search, $_replace, $text);

  }
}
