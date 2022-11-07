<?php
/**
 * Settings page view.
 *
 * @package Extra_Checkout_Fields_For_Brazil/Admin/View
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

    <div class="wrap">
        <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

        <?php settings_errors(); ?>

        <form method="post" action="options.php">
            <?php
            settings_fields( 'wcmp_pagarme_settings' );
            do_settings_sections( 'wcmp_pagarme_settings' );
            submit_button();
            ?>
        </form>
    </div>

<?php
