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

$dbc = \sys::dbCheck( 'job_categories' );

$dbc->defineField( 'category', 'varchar');

$dbc->check();
