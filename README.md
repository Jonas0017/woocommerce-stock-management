# WooCommerce Stock Management

Sistema para atualização dinâmica de estoque e preços no WooCommerce com interface de administração no WordPress.

## Descrição

Este plugin permite que administradores do WooCommerce atualizem facilmente o estoque e os preços dos produtos através de uma interface simples no painel de administração do WordPress. Além disso, permite adicionar novos produtos e remover produtos existentes.

## Funcionalidades

- Atualização de estoque e preços diretamente no painel de administração.
- Adição de novos produtos.
- Remoção de produtos com confirmação.
- Coleta apenas dos dados mínimos necessários para o checkout.

## Requisitos

- WordPress 5.0 ou superior
- WooCommerce 3.0 ou superior
- Tema [Hello Elementor](https://elementor.com/hello-theme/) ativo
- Plugin [Elementor Pro Elements](https://github.com/ProElements/pro-elements) ativo

## Instalação

### Manualmente

1. Baixe o arquivo `functions.php`.
2. Acesse o diretório do seu tema ativo (geralmente em `wp-content/themes/hello-elementor/`).
3. Substitua o arquivo `functions.php` atual pelo novo arquivo.

### Via GitHub

1. Clone o repositório:
   ```bash
   git clone https://github.com/seu-usuario/woocommerce-stock-management.git

##Alterações Manuais
Se preferir adicionar manualmente apenas as novas funcionalidades ao seu arquivo functions.php, siga os passos abaixo:

1. Baixe o arquivo `functions.php` no diretório do seu tema ativo (geralmente em `wp-content/themes/hello-elementor/functions.php`).
2. Abra ele em seu editor e adicione o seguinte código ao final do arquivo functions.php:

```php
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
```
