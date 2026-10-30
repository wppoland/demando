<?php
/**
 * Uninstall cleanup for Enquire.
 *
 * Removes the plugin's own options and the per-user dismissal of the PRO banner
 * when it is deleted from wp-admin. Only what Enquire creates is deleted;
 * WooCommerce data is never touched. No enquiry data is stored.
 *
 * @package Enquire
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

/**
 * Remove this plugin's options from the current site.
 */
function enquire_uninstall_site(): void
{
    delete_option('enquire_settings');
    delete_option('enquire_db_version');
}

// Options are per site, so a network uninstall has to visit every site.
if (is_multisite()) {
    foreach (get_sites(['fields' => 'ids', 'number' => 0]) as $enquire_site_id) {
        switch_to_blog((int) $enquire_site_id);
        enquire_uninstall_site();
        restore_current_blog();
    }
    unset($enquire_site_id);
} else {
    enquire_uninstall_site();
}

// The PRO banner's dismissal is stored per user, so it belongs to the
// plugin rather than to the site content. User meta is global, not
// per-site, which is why this uses delete_metadata's \$delete_all rather
// than a loop over the users of one blog.
delete_metadata('user', 0, 'enquire_pro_banner_dismissed', '', true);
