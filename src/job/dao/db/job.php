<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\job\dao\db;

$dbc = \sys::dbCheck('job' );

$dbc->defineField('job_type', 'int');
$dbc->defineField('properties_id', 'bigint');
$dbc->defineField('contractor_id', 'bigint');
$dbc->defineField('description', 'text');
$dbc->defineField('on_site_contact', 'varchar', 100);
$dbc->defineField('status', 'int');
$dbc->defineField('due', 'date');
$dbc->defineField('job_payment', 'int');
$dbc->defineField('email_sent', 'datetime');
$dbc->defineField('email_sent_by', 'bigint');
$dbc->defineField('updated', 'datetime');
$dbc->defineField('created', 'datetime');

$dbc->check();
