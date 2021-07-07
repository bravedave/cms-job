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

$dbc = \sys::dbCheck('job_lines' );

$dbc->defineField('job_id', 'bigint');
$dbc->defineField('item_id', 'bigint');
$dbc->defineField('updated', 'datetime');
$dbc->defineField('created', 'datetime');

$dbc->check();
