<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'ads');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

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
define('AUTH_KEY',         ' poQe]2!]U~u_8e=,+8>Dv*JAQ]5#b1Wc,4ipQ)ZWJz&B2ylU^-VBF<<g?dCFS#H');
define('SECURE_AUTH_KEY',  '5xSwRN=5Y=,Xqh|aZ|+MWsajYUGy[~)D,y|_|5c6|>+,8!E.!@Z.O[OxD+8-oa%-');
define('LOGGED_IN_KEY',    'O{zd};0SP=$PR3SD3I#]A_b!|trW|D+L>Lop/eM_2g O},5X=-xQyZV/!Sh),++W');
define('NONCE_KEY',        '1>JZE,E1D&Ct$R7Rr+-[iG/}H5F1?c#&)k-fvf)|-H=%`/6AZ-rxcgI&Wl)>*.jd');
define('AUTH_SALT',        '!26Lrfc!6*h+cNCFV0p^kV|9;#)`-l->U*$+?{(?m|1$gpy1zr7Z#CD|lQJrp[6(');
define('SECURE_AUTH_SALT', '+*u-+[&m+}0J*y||C&mdf;+}]dNQtkYr_a*IO=>Vg([QQ.,93r6~8JE@w]=[nx#C');
define('LOGGED_IN_SALT',   'NYqU/wOw.NnL7h@z&+u+(A!IvVb/Nw+ ON9>Q_cb,Xe-)y$+~5$X![}A3N`dXKLN');
define('NONCE_SALT',       'xb!JRY:h5),%KU72!<;VqV(,p[ *R]^69n7k|:WCT,+vBxq.3q):{KJXmm$Tyb^-');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

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
