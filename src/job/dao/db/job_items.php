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

$dbc = \sys::dbCheck( 'job_items' );

$dbc->defineField('job_categories_id', 'bigint');
$dbc->defineField('description', 'varchar', 100);

$dbc->check();
