<?php
namespace Jet_FB_Paypal\Shared;

defined( 'ABSPATH' ) || exit;

/**
 * Orchestrates multiple copies of the library and boots the newest one.
 */
if ( ! class_exists( __NAMESPACE__ . '\\Loader', false ) ) {

	final class Loader {

		/**
		 * @var array<string, array{dir:string, loader:callable}>
		 */
		private static $candidates = [];

		/** @var bool */
		private static $bootstrapped = false;

		/** @var string|null */
		private static $picked_version = null;

		/**
		 * Register this on-disk copy as a candidate.
		 *
		 * @param string   $version  Semantic version, e.g. "1.4.0".
		 * @param string   $dir      Absolute path to the library folder (this file's dir).
		 * @param callable $loader   Callback that sets up autoloading and boots this version.
		 */
		public static function register( string $version, string $dir, callable $loader ): void {

			// Last write wins for identical version keys (safe in practice).
			self::$candidates[ $version ] = [
				'dir'    => $dir,
				'loader' => $loader,
			];

			// Ensure bootstrap runs exactly once.
			if ( did_action( 'after_setup_theme' ) ) {
				// If after_setup_theme already fired, bootstrap immediately.
				self::bootstrap();
			} elseif ( ! has_action( 'after_setup_theme', [ __CLASS__, 'bootstrap' ], -10000 ) ) {
				add_action( 'after_setup_theme', [ __CLASS__, 'bootstrap' ], -10000 );
			}
		}

		/**
		 * Pick the highest version and initialize only that one.
		 */
		public static function bootstrap(): void {
			if ( self::$bootstrapped || empty( self::$candidates ) ) {
				return;
			}

			// Sort versions descending using PHP's version_compare.
			uksort(
				self::$candidates,
				static function ( $a, $b ) {
					return version_compare( (string) $b, (string) $a );
				}
			);

			$selected_version = array_key_first( self::$candidates );
			$selected         = self::$candidates[ $selected_version ];

			self::$bootstrapped   = true;
			self::$picked_version = (string) $selected_version;

			// Initialize the chosen copy (registers autoloader, loads functions, etc).
			$loader = $selected['loader'];
			$loader( $selected['dir'] );

			if ( ! defined( 'JET_FB_SUBSCRIPTIONS_SHARED_URL' ) ) {
				define(
					'JET_FB_SUBSCRIPTIONS_SHARED_URL',
					plugin_dir_url( trailingslashit( $selected['dir'] ) . '/Loader.php' )
				);
			}

			if ( ! defined( 'JET_FB_SUBSCRIPTIONS_SHARED_PATH' ) ) {
				define(
					'JET_FB_SUBSCRIPTIONS_SHARED_PATH',
					trailingslashit( $selected['dir'] )
				);
			}
		}

		/**
		 * The version that won the selection (or "0.0.0" if not yet picked).
		 */
		public static function picked_version(): string {
			return self::$picked_version ?? '0.0.0';
		}
	}
}

/**
 * Register *this* on-disk copy.
 * Bump the version below with each release of your shared library.
 */
Loader::register(
	'1.0.0',
	__DIR__,
	static function ( string $dir ): void {
		// --- Tiny PSR-4 autoloader for the *selected* version only. ---
		spl_autoload_register(
			static function ( string $class ) use ( $dir ): void {

				$prefix  = 'Jet_FB_Paypal\\';
				$len     = strlen( $prefix );

				if ( strncmp( $class, $prefix, $len ) !== 0 ) {
					return; // not our namespace
				}

				$relative = substr( $class, $len );
				$file     = trailingslashit( $dir ) . str_replace( '\\', '/', $relative ) . '.php';

				if ( is_readable( $file ) ) {
					require $file;
				}
			},
			true, // throw
			true  // prepend to ensure our chosen loader wins
		);
	}
);
