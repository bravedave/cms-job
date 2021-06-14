<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dao;

$dbc = \sys::dbCheck('job' );

$dbc->defineField('job_type', 'int');
$dbc->defineField('properties_id', 'bigint');
$dbc->defineField('contractor_id', 'text');
$dbc->defineField('description', 'text');
$dbc->defineField('status', 'int');
$dbc->defineField('due', 'date');
$dbc->defineField('job_payment', 'int');
$dbc->defineField('updated', 'datetime');
$dbc->defineField('created', 'datetime');

$dbc->check();
