<?php
/**
 * Affiliate Click Tracker By Heroframe
 *
 * @package       HEROFRAME
 * @author        Heroframe AI Team
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Affiliate Click Tracker By Heroframe
 * Plugin URI:    https://heroframe.ai
 * Description:   The best free affiliate link tracker for WordPress. Discover which links are not being clicked, so you can fix them and earn more.
 * Version:       1.0.0
 * Author:        Heroframe AI Team
 * Text Domain:   heroframe-affiliate-click-tracker
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Affiliate Click Tracker By Heroframe. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Plugin Root File
define( 'HEROFRAME_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'HEROFRAME_PLUGIN_BASE',	plugin_basename( HEROFRAME_PLUGIN_FILE ) );
include_once(plugin_dir_path( __FILE__ ) . '/admin/main.php' );
