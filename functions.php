<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '3.1.0' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);

			/*
			 * Editor Style.
			 */
			add_editor_style( 'classic-editor.css' );

			/*
			 * Gutenberg wide images.
			 */
			add_theme_support( 'align-wide' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	/**
	 * Check whether to display header footer.
	 *
	 * @return bool
	 */
	function hello_elementor_display_header_footer() {
		$hello_elementor_header_footer = true;

		return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		$min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				get_template_directory_uri() . '/style' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				get_template_directory_uri() . '/theme' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( hello_elementor_display_header_footer() ) {
			wp_enqueue_style(
				'hello-elementor-header-footer',
				get_template_directory_uri() . '/header-footer' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function hello_elementor_add_description_meta_tag() {
		if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

// Admin notice
if ( is_admin() ) {
	require get_template_directory() . '/includes/admin-functions.php';
}

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
	// Customizer controls
	function hello_elementor_customizer() {
		if ( ! is_customize_preview() ) {
			return;
		}

		if ( ! hello_elementor_display_header_footer() ) {
			return;
		}

		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
add_action( 'init', 'hello_elementor_customizer' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		wp_body_open();
	}
}


function custom_search_script() {
    ?>
    <script>
        jQuery(document).ready(function($) {
            function normalize(str) {
                return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            }

            $('#search-products').on('input', function() {
                var searchQuery = normalize($(this).val());
                $('.woocommerce-loop-product__title').each(function() {
                    var productName = normalize($(this).text());
                    if (productName.includes(searchQuery)) {
                        $(this).closest('.product').show();
                    } else {
                        $(this).closest('.product').hide();
                    }
                });
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'custom_search_script');

function custom_add_to_cart_script() {
    ?>
    <script>
        jQuery(document).ready(function($) {
            $('.woocommerce ul.products li.product a img').on('click', function(e) {
                e.preventDefault();
                var productLink = $(this).closest('a').attr('href');
                var productId = $(this).closest('.product').find('a.add_to_cart_button').data('product_id');

                $.ajax({
                    type: 'POST',
                    url: '/?wc-ajax=add_to_cart',
                    data: {
                        product_sku: '',
                        product_id: productId,
                        quantity: 1
                    },
                    success: function(response) {
                        if (response.error && response.product_url) {
                            window.location.href = response.product_url;
                            return;
                        }
                        window.location.href = '/carrinho/';
                    }
                });
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'custom_add_to_cart_script');

// Remover campos desnecessários do checkout
add_filter( 'woocommerce_checkout_fields', 'custom_override_checkout_fields' );
function custom_override_checkout_fields( $fields ) {
    // Remover campos desnecessários
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_phone']);
    
    // Ajustar o campo de nome
    $fields['billing']['billing_first_name']['placeholder'] = 'Nome Completo';
    $fields['billing']['billing_first_name']['label'] = false;
    $fields['billing']['billing_first_name']['class'] = array('form-row-first');
    
    // Ajustar o campo de email
    $fields['billing']['billing_email']['placeholder'] = 'Email';
    $fields['billing']['billing_email']['label'] = false;
    $fields['billing']['billing_email']['class'] = array('form-row-last');
    
    return $fields;
}

// Adicionar Menu no Painel de Administração
function custom_stock_menu() {
    add_menu_page(
        'Atualizar Estoque', // Título da página
        'Atualizar Estoque', // Título do menu
        'manage_options',    // Capacidade
        'custom-stock-menu', // Slug do menu
        'custom_stock_page', // Função callback
        'dashicons-update',  // Ícone do menu
        6                    // Posição
    );
}
add_action('admin_menu', 'custom_stock_menu');

// Criar Página de Administração
function custom_stock_page() {
    ?>
    <div class="wrap">
        <h1>Atualizar Estoque</h1>
        <form method="post" action="">
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th id="product_id" class="manage-column column-columnname" scope="col">ID do Produto</th>
                        <th id="product_name" class="manage-column column-columnname" scope="col">Nome do Produto</th>
                        <th id="current_price" class="manage-column column-columnname" scope="col">Valor Atual</th>
                        <th id="new_stock" class="manage-column column-columnname" scope="col">Quantidade em Estoque</th>
                        <th id="actions" class="manage-column column-columnname" scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $args = array(
                        'post_type' => 'product',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC',
                    );
                    $products = get_posts($args);
                    foreach ($products as $product) {
                        $product_id = $product->ID;
                        $product_name = $product->post_title;
                        $current_price = get_post_meta($product_id, '_price', true);
                        $new_stock = get_post_meta($product_id, '_stock', true);
                        echo '<tr>';
                        echo '<td>' . $product_id . '</td>';
                        echo '<td>' . $product_name . '</td>';
                        echo '<td><input type="number" step="0.01" name="price_' . $product_id . '" value="' . $current_price . '" /></td>';
                        echo '<td><input type="number" name="stock_' . $product_id . '" value="' . $new_stock . '" /></td>';
                        echo '<td><button type="button" class="button-secondary remove-row" data-product-id="' . $product_id . '">Remover</button></td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="update_stock" class="button-primary" value="Atualizar Estoque" />
            </p>
        </form>

        <h2>Adicionar Novo Produto</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Nome do Produto</th>
                    <td><input type="text" name="new_product_name" value="" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Valor do Produto</th>
                    <td><input type="number" step="0.01" name="new_product_price" value="" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Quantidade em Estoque</th>
                    <td><input type="number" name="new_product_stock" value="" /></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="add_product" class="button-primary" value="Adicionar Produto" />
            </p>
        </form>
    </div>
    <?php

    if (isset($_POST['update_stock'])) {
        foreach ($products as $product) {
            $product_id = $product->ID;
            $new_price = $_POST['price_' . $product_id];
            $new_stock = $_POST['stock_' . $product_id];
            custom_update_stock($product_id, $new_stock, $new_price);
        }
    }

    if (isset($_POST['add_product'])) {
        $new_product_name = $_POST['new_product_name'];
        $new_product_price = $_POST['new_product_price'];
        $new_product_stock = $_POST['new_product_stock'];
        custom_add_product($new_product_name, $new_product_price, $new_product_stock);
    }
}

// Função de Atualização de Estoque e Preço
function custom_update_stock($product_id, $new_stock, $new_price) {
    global $wpdb;

    // Atualizar o estoque no banco de dados
    $stock_result = $wpdb->update(
        $wpdb->prefix . 'postmeta',
        array('meta_value' => $new_stock),
        array(
            'post_id' => $product_id,
            'meta_key' => '_stock'
        )
    );

    // Atualizar o preço no banco de dados
    $price_result = $wpdb->update(
        $wpdb->prefix . 'postmeta',
        array('meta_value' => $new_price),
        array(
            'post_id' => $product_id,
            'meta_key' => '_price'
        )
    );

    if ($stock_result !== false && $price_result !== false) {
        echo '<div id="message" class="updated notice is-dismissible"><p>Estoque e preço atualizados com sucesso!</p></div>';
    } else {
        echo '<div id="message" class="error notice is-dismissible"><p>Erro ao atualizar estoque ou preço.</p></div>';
    }
}

// Função para adicionar um novo produto
function custom_add_product($product_name, $product_price, $product_stock) {
    $new_product = array(
        'post_title' => wp_strip_all_tags($product_name),
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'product'
    );

    $product_id = wp_insert_post($new_product);

    if (!is_wp_error($product_id)) {
        update_post_meta($product_id, '_price', $product_price);
        update_post_meta($product_id, '_stock', $product_stock);
        echo '<div id="message" class="updated notice is-dismissible"><p>Produto adicionado com sucesso!</p></div>';
    } else {
        echo '<div id="message" class="error notice is-dismissible"><p>Erro ao adicionar produto.</p></div>';
    }
}

// Função para remover um produto
function custom_remove_product() {
    if (isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        wp_delete_post($product_id);
        echo 'Produto removido com sucesso!';
    }
    wp_die();
}
add_action('wp_ajax_custom_remove_product', 'custom_remove_product');

// Script para confirmação de remoção de produto
function custom_stock_scripts() {
    ?>
    <script>
        jQuery(document).ready(function($) {
            $('.remove-row').on('click', function() {
                if (confirm('Você realmente quer remover este produto?')) {
                    var productId = $(this).data('product-id');
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'custom_remove_product',
                            product_id: productId
                        },
                        success: function(response) {
                            location.reload();
                        }
                    });
                }
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'custom_stock_scripts');
