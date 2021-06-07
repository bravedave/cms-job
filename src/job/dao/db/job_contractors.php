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

$dbc = \sys::dbCheck( 'job_contractors' );

$dbc->defineField( 'trading_name', 'varchar', 100);
$dbc->defineField( 'company_name', 'varchar', 100);
$dbc->defineField( 'abn', 'varchar');
$dbc->defineField( 'services', 'varchar');
$dbc->defineField( 'primary_contact', 'bigint');
$dbc->defineField( 'primary_contact_role', 'varchar');
$dbc->defineField( 'console_contact_id', 'varchar');

$dbc->check();
