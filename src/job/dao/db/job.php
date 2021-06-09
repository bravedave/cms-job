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
$dbc->defineField('description', 'text');
$dbc->defineField('updated', 'datetime');
$dbc->defineField('created', 'datetime');

$dbc->check();
