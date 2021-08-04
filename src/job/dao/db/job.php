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
$dbc->defineField('job_recurrence_interval', 'int');
$dbc->defineField('job_recurrence_end', 'date');
$dbc->defineField('job_recurrence_week_frequency', 'int');
$dbc->defineField('job_recurrence_month_frequency', 'int');
$dbc->defineField('job_recurrence_year_frequency', 'int');
$dbc->defineField('job_recurrence_day_of_week', 'varchar');
$dbc->defineField('job_recurrence_day_of_month', 'varchar', 100);
$dbc->defineField('job_recurrence_on_business_day', 'tinyint');
$dbc->defineField('job_recurrence_parent', 'bigint');
$dbc->defineField('job_recurrence_child', 'bigint');
$dbc->defineField('job_recurrence_disable', 'tinyint');
$dbc->defineField('properties_id', 'bigint');
$dbc->defineField('contractor_id', 'bigint');
$dbc->defineField('description', 'text');
$dbc->defineField('on_site_contact', 'varchar', 100);
$dbc->defineField('status', 'int');
$dbc->defineField('due', 'date');
$dbc->defineField('job_payment', 'int');
$dbc->defineField('source_job', 'int');
$dbc->defineField('complete', 'tinyint');
$dbc->defineField('email_sent', 'datetime');
$dbc->defineField('email_sent_by', 'bigint');
$dbc->defineField('invoice_reviewed', 'datetime');
$dbc->defineField('invoice_reviewed_by', 'bigint');
$dbc->defineField('paid', 'datetime');
$dbc->defineField('paid_by', 'bigint');
$dbc->defineField('archived', 'datetime');
$dbc->defineField('updated', 'datetime');
$dbc->defineField('updated_by', 'bigint');
$dbc->defineField('created', 'datetime');
$dbc->defineField('created_by', 'bigint');

$dbc->check();
