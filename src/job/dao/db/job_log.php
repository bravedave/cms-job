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

$dbc = \sys::dbCheck('job_log');

$dbc->defineField('job_id', 'bigint');
$dbc->defineField('comment', 'text');
$dbc->defineField('user_id', 'bigint');
$dbc->defineField('created', 'datetime');
$dbc->defineField('updated', 'datetime');

$dbc->check();
