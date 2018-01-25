<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'tgc1');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'dlbl3jwcveu93qlmskngk1jdpovmfaubuymlqp5d9gdoz6qnj8d14jg3cgsu8xe1');
define('SECURE_AUTH_KEY',  'unuehlahhrgoypednlfd1zetfxsjch1esgkxxctkx2xqjyamsei696gqvdrk0xwu');
define('LOGGED_IN_KEY',    'dr3jqq3903tp4o4xktuchvhfgpgknjbgnuflx9mdew2beeaulm6clzhunq6dr8qw');
define('NONCE_KEY',        'dg9cwfkcqcinsej54tkzoeiyawrjlmufbtvwaetea1zkvdcvx1pn1iuipgptyvkj');
define('AUTH_SALT',        'gdtoyoqwxmiv1op87ftkyxndbbyx0xzmvvujfxz2bawjhk8bwncvscnhmudej016');
define('SECURE_AUTH_SALT', 'hadoa9rmj1lrctaxfh3aee47olyxut84qrlk2oy29rhdukx9vdir9f6wlcfivkk6');
define('LOGGED_IN_SALT',   'c0oj05f5qgnprkjghj6kxchxqefgzj8pftx6zx7lsfxr8ynvnfb6vjusvthpx3rb');
define('NONCE_SALT',       'amk4agt9hvdqiacjdyq1eg4uw3dh3fr9h1yoklofjqryhafqqv24lw9oicr9cvzh');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
