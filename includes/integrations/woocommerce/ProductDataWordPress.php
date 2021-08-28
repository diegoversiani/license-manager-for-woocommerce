<?php

namespace LicenseManagerForWooCommerce\Integrations\WooCommerce;

use LicenseManagerForWooCommerce\Enums\LicenseStatus;
use LicenseManagerForWooCommerce\Models\Resources\Generator as GeneratorResourceModel;
use LicenseManagerForWooCommerce\Repositories\Resources\Generator as GeneratorResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use LicenseManagerForWooCommerce\Settings;
use WP_Error;
use WP_Post;

defined('ABSPATH') || exit;

class ProductDataWordPress
{
    /**
     * @var string
     */
    const ADMIN_TAB_NAME = 'license_manager_wp_product_tab';

    /**
     * @var string
     */
    const ADMIN_TAB_TARGET = 'license_manager_wp_product_data';

    /**
     * ProductData constructor.
     */
    public function __construct()
    {
        add_action( 'lmfwc_simple_product_data_panel',               array( $this, 'simpleProductDataPanel'),   20, 1 );
        add_action( 'lmfwc_simple_product_save',                     array( $this, 'simpleProductSave'),        20, 1 );
        add_action( 'lmfwc_variable_product_data_panel',             array( $this, 'variableProductDataPanel'), 20, 3 );
        add_action( 'lmfwc_variable_product_save',                   array( $this, 'variableProductSave' ),     20, 2 );
    }

    /**
     * Adds a product data tab for simple WooCommerce products.
     *
     * @param array $tabs
     * @return array
     */
    public function simpleProductTab( $tabs )
    {
        $tabs[ self::ADMIN_TAB_NAME ] = array(
            'label' => __( 'WordPress Product', 'license-manager-for-woocommerce' ),
            'target' => self::ADMIN_TAB_TARGET,
            'priority' => 22
        );

        return $tabs;
    }

    /**
     * Displays the new fields inside the new product data tab.
     */
    public function simpleProductDataPanel()
    {
        global $post;

        $productVersion       = get_post_meta( $post->ID, 'lmfwc_licensed_product_version', true );
        $productTested        = get_post_meta( $post->ID, 'lmfwc_licensed_product_tested', true );
        $productRequires      = get_post_meta( $post->ID, 'lmfwc_licensed_product_requires', true );
        $productRequiresPhp   = get_post_meta( $post->ID, 'lmfwc_licensed_product_requires_php', true );
        $productChangelog     = get_post_meta( $post->ID, 'lmfwc_licensed_product_changelog', true );
        $productIconUrl       = get_post_meta( $post->ID, 'lmfwc_licensed_product_icon_url', true );
        $productBannerLowUrl  = get_post_meta( $post->ID, 'lmfwc_licensed_product_banner_low_url', true );
        $productBannerHighUrl = get_post_meta( $post->ID, 'lmfwc_licensed_product_banner_high_url', true );

        echo '</div><div class="options_group">';

        woocommerce_wp_text_input(
            array(
                'id'          => 'lmfwc_licensed_product_version',
                'label'       => esc_html__( 'Product version', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'Defines current version of the product.', 'license-manager-for-woocommerce' ),
                'value'       => $productVersion,
                'desc_tip'    => true
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => 'lmfwc_licensed_product_tested',
                'label'       => esc_html__( 'Product tested', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'The version of WordPress where the product has been tested up to.', 'license-manager-for-woocommerce' ),
                'value'       => $productTested,
                'desc_tip'    => true
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => 'lmfwc_licensed_product_requires',
                'label'       => esc_html__( 'Product requires', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'The version of WordPress that the product requires.', 'license-manager-for-woocommerce' ),
                'value'       => $productRequires,
                'desc_tip'    => true
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => 'lmfwc_licensed_product_requires_php',
                'label'       => esc_html__( 'Product requires PHP', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'The version of PHP that the product requires.', 'license-manager-for-woocommerce' ),
                'value'       => $productRequiresPhp,
                'desc_tip'    => true
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => 'lmfwc_licensed_product_icon_url',
                'label'       => esc_html__( 'Product icon', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'URL to the image used as the product icon within WordPress Admin.', 'license-manager-for-woocommerce' ),
                'value'       => $productIconUrl,
                'desc_tip'    => true
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => 'lmfwc_licensed_product_banner_low_url',
                'label'       => esc_html__( 'Product banner (low resolution)', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'URL to the image used as the product banner for low resolution screens within WordPress Admin.', 'license-manager-for-woocommerce' ),
                'value'       => $productBannerLowUrl,
                'desc_tip'    => true
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => 'lmfwc_licensed_product_banner_high_url',
                'label'       => esc_html__( 'Product banner (high resolution)', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'URL to the image used as the product banner for high resolution screens within WordPress Admin.', 'license-manager-for-woocommerce' ),
                'value'       => $productBannerHighUrl,
                'desc_tip'    => true
            )
        );

        ?>
        <div class="form-field lmfwc_licensed_product_changelog">
            <label><?php esc_html_e( 'Product changelog', 'license-manager-for-woocommerce' ) ?></label>
            <?php wp_editor( $productChangelog, 'lmfwc_licensed_product_changelog', array( 'media_buttons' => false ) ); ?>
        </div>
        <?php
    }

    /**
     * Hook which triggers when the WooCommerce Product is being saved or updated.
     *
     * @param int $postId
     */
    public function simpleProductSave($postId)
    {
        // Edit flag isn't set
        if ( ! isset( $_POST['lmfwc_edit_flag'] ) ) {
            return;
        }

        // Update the product version according to the field.
        $productVersion = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_version'] ) );

        update_post_meta( $postId, 'lmfwc_licensed_product_version', $productVersion );

        // Update the product WordPress version tested up to according to the field.
        $productTested = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_tested'] ) );

        update_post_meta( $postId, 'lmfwc_licensed_product_tested', $productTested );

        // Update the product required WordPress version according to the field.
        $productRequires = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_requires'] ) );

        update_post_meta( $postId, 'lmfwc_licensed_product_requires', $productRequires );

        // Update the product required PHP version according to the field.
        $productRequiresPhp = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_requires_php'] ) );

        update_post_meta( $postId, 'lmfwc_licensed_product_requires_php', $productRequiresPhp );

        // Update the product icon url according to the field.
        $productIconUrl = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_icon_url'] ) );

        update_post_meta( $postId, 'lmfwc_licensed_product_icon_url', $productIconUrl );

        // Update the product banner_low url according to the field.
        $productBannerLowUrl = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_banner_low_url'] ) );

        update_post_meta( $postId, 'lmfwc_licensed_product_banner_low_url', $productBannerLowUrl );

        // Update the product banner url according to the field.
        $productBannerHighUrl = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_banner_high_url'] ) );

        update_post_meta( $postId, 'lmfwc_licensed_product_banner_high_url', $productBannerHighUrl );

        // Update the product changelog according to the field.
        $productChangelog = wp_unslash( $_POST['lmfwc_licensed_product_changelog'] );

        update_post_meta( $postId, 'lmfwc_licensed_product_changelog', $productChangelog );
    }

    /**
     * Adds the new product data fields to variable WooCommerce Products.
     *
     * @param int     $loop
     * @param array   $variationData
     * @param WP_Post $variation
     */
    public function variableProductDataPanel($loop, $variationData, $variation)
    {
        global $post;

        $productId            = $variation->ID;
        $productVersion       = get_post_meta( $productId, 'lmfwc_licensed_product_version', true );
        $productTested        = get_post_meta( $productId, 'lmfwc_licensed_product_tested', true );
        $productRequires      = get_post_meta( $productId, 'lmfwc_licensed_product_requires', true );
        $productRequiresPhp   = get_post_meta( $productId, 'lmfwc_licensed_product_requires_php', true );
        $productChangelog     = get_post_meta( $productId, 'lmfwc_licensed_product_changelog', true );
        $productIconUrl       = get_post_meta( $productId, 'lmfwc_licensed_product_icon_url', true );
        $productBannerLowUrl  = get_post_meta( $productId, 'lmfwc_licensed_product_banner_low_url', true );
        $productBannerHighUrl = get_post_meta( $productId, 'lmfwc_licensed_product_banner_high_url', true );

        woocommerce_wp_text_input(
            array(
                'id'          => "lmfwc_licensed_product_version_[{$productId}]",
                'name'        => "lmfwc_licensed_product_version[{$productId}]",
                'label'       => esc_html__( 'Product version', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'Defines current version of the product.', 'license-manager-for-woocommerce' ),
                'value'       => $productVersion,
                'desc_tip'    => true
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => "lmfwc_licensed_product_tested_[{$productId}]",
                'name'        => "lmfwc_licensed_product_tested[{$productId}]",
                'label'       => esc_html__( 'Product tested', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'The version of WordPress where the product has been tested up to.', 'license-manager-for-woocommerce' ),
                'value'       => $productTested,
                'desc_tip'    => true
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => "lmfwc_licensed_product_requires_[{$productId}]",
                'name'        => "lmfwc_licensed_product_requires[{$productId}]",
                'label'       => esc_html__( 'Product requires', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'The version of WordPress that the product requires.', 'license-manager-for-woocommerce' ),
                'value'       => $productRequires,
                'desc_tip'    => true
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => "lmfwc_licensed_product_requires_php_[{$productId}]",
                'name'        => "lmfwc_licensed_product_requires_php[{$productId}]",
                'label'       => esc_html__( 'Product requires PHP', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'The version of PHP that the product requires.', 'license-manager-for-woocommerce' ),
                'value'       => $productRequiresPhp,
                'desc_tip'    => true
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => "lmfwc_licensed_product_icon_url_[{$productId}]",
                'name'        => "lmfwc_licensed_product_icon_url[{$productId}]",
                'label'       => esc_html__( 'Product icon', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'URL to the image used as the product icon within WordPress Admin.', 'license-manager-for-woocommerce' ),
                'value'       => $productIconUrl,
                'desc_tip'    => true
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => "lmfwc_licensed_product_banner_low_url_[{$productId}]",
                'name'        => "lmfwc_licensed_product_banner_low_url[{$productId}]",
                'label'       => esc_html__( 'Product banner (low resolution)', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'URL to the image used as the product banner for low resolution screens within WordPress Admin.', 'license-manager-for-woocommerce' ),
                'value'       => $productBannerLowUrl,
                'desc_tip'    => true
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => "lmfwc_licensed_product_banner_high_url_[{$productId}]",
                'name'        => "lmfwc_licensed_product_banner_high_url[{$productId}]",
                'label'       => esc_html__( 'Product banner (high resolution)', 'license-manager-for-woocommerce' ),
                'description' => esc_html__( 'URL to the image used as the product banner for high resolution screens within WordPress Admin.', 'license-manager-for-woocommerce' ),
                'value'       => $productBannerHighUrl,
                'desc_tip'    => true
            )
        );

        ?>
        <div class="form-field lmfwc_licensed_product_changelog">
            <label><?php esc_html_e( 'Product changelog', 'license-manager-for-woocommerce' ) ?></label>
            <?php wp_editor( $productChangelog, "lmfwc_licensed_product_changelog[{$productId}]", array( 'media_buttons' => false ) ); ?>
        </div>
        <?php
    }

    /**
     * Saves the data from the product variation fields.
     *
     * @param int $variationId
     * @param int $i
     */
    public function variableProductSave($variationId, $i)
    {
        // Update the product version according to the field.
        $productVersion = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_version'][$variationId] ) );

        update_post_meta( $variationId, 'lmfwc_licensed_product_version', $productVersion );

        // Update the product WordPress version tested up to according to the field.
        $productTested = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_tested'][$variationId] ) );

        update_post_meta( $variationId, 'lmfwc_licensed_product_tested', $productTested );

        // Update the product required WordPress version according to the field.
        $productRequires = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_requires'][$variationId] ) );

        update_post_meta( $variationId, 'lmfwc_licensed_product_requires', $productRequires );

        // Update the product required PHP version according to the field.
        $productRequiresPhp = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_requires_php'][$variationId] ) );

        update_post_meta( $variationId, 'lmfwc_licensed_product_requires_php', $productRequiresPhp );

        // Update the product icon url according to the field.
        $productIconUrl = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_icon_url'][$variationId] ) );

        update_post_meta( $variationId, 'lmfwc_licensed_product_icon_url', $productIconUrl );

        // Update the product banner_low url according to the field.
        $productBannerLowUrl = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_banner_low_url'][$variationId] ) );

        update_post_meta( $variationId, 'lmfwc_licensed_product_banner_low_url', $productBannerLowUrl );

        // Update the product banner url according to the field.
        $productBannerHighUrl = sanitize_text_field( wp_unslash( $_POST['lmfwc_licensed_product_banner_high_url'][$variationId] ) );

        update_post_meta( $variationId, 'lmfwc_licensed_product_banner_high_url', $productBannerHighUrl );

        // Update the product changelog according to the field.
        $productChangelog = wp_unslash( $_POST['lmfwc_licensed_product_changelog'][$variationId] );

        update_post_meta( $variationId, 'lmfwc_licensed_product_changelog', $productChangelog );
    }

}
