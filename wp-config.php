<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ecom' );

/** MySQL database username */
define( 'DB_USER', 'George' );

/** MySQL database password */
define( 'DB_PASSWORD', 'QDOREsafzdu28BF7' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'u3*Xnr4!.2HmMP)[T-c%?Qwev2v$w-*Z(ohrp(>mwh%l KYp7b{;TQUUG-%YmA#P' );
define( 'SECURE_AUTH_KEY',  '8I@9Jx( b9qU2R.tGu9MOS1,tHsqlJr9U-AvA8x(.j>fLR*D)i+sN<(b QLFjp}(' );
define( 'LOGGED_IN_KEY',    'x!+_<4t/eBcQeHPp8F+3BYa$uF-JqHwrTa&-g#VhO1be-o1BpCJiAG:.r7l%:OT@' );
define( 'NONCE_KEY',        'i !8aDdp`!+-Ufv5Ime[JxtBE!u]6j4K93N&r{UU5%?&:N.7fa:wP2fpMZ1,i._f' );
define( 'AUTH_SALT',        '=Llk{)uy>-@ra,vRZ6vMdOPIo8Vh|,S=~%FF)m:M6Yr}kg+`9rSII:i2ET,!+axI' );
define( 'SECURE_AUTH_SALT', '_uq%6.BOR174q<B)W|-KK*]}w_aIm|Uv$OYs8HD~;a^4j[,>b^(o9<1(8AH&d>i ' );
define( 'LOGGED_IN_SALT',   '<KSywaVmz]iH!h(+5ZIdX)v.{3k8thaDyw? IUWGK?HIwknAM6~=pjZK?m5Kt[?5' );
define( 'NONCE_SALT',       'TK&TEwfhGqD/Al 1AL9Ioh%oWP<Rgp`-|eh+`.5euOyb2hQuE`{eJXOxy|zOKV_W' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_ecom';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
