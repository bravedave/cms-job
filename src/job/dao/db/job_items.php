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

$dbc = \sys::dbCheck( 'job_items' );

$dbc->defineField('job_categories_id', 'bigint');
$dbc->defineField('item', 'varchar');
$dbc->defineField('description', 'varchar', 100);

$dbc->check();
