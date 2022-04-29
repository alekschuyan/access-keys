<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://vadyus.com
 * @since      1.0.0
 *
 * @package    Access_Keys
 * @subpackage Access_Keys/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<form method="post" name="my_options" action="options.php">

    <?php

    $options = get_option($this->plugin_name);
    $licence_key_count = $options['licence_key_count'];

    global $wpdb;
    $table_name = $wpdb->prefix . "access_keys";
    $access_keys = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC" );

    settings_fields( $this->plugin_name );
    do_settings_sections( $this->plugin_name );

    ?>

    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <fieldset>
        <legend class="screen-reader-text"><span><?php _e('Генерация новых ключей. Укажите количество: ', $this->plugin_name);?></span></legend>
        <h3><?php esc_attr_e('Генерация новых ключей.', $this->plugin_name);?></h3>
        <label for="<?php echo $this->plugin_name;?>-licence_key_count">
            <span><?php esc_attr_e('Укажите количество новых ключей для генерации: ', $this->plugin_name);?></span>
        </label>
        <input type="number" min="0" max="100" style="text-align: center;"
               class="small-text" id="<?php echo $this->plugin_name;?>-licence_key_count"
               name="<?php echo $this->plugin_name;?>[licence_key_count]"
               value="0"
        />
        <?php submit_button(__('Применить изменения', $this->plugin_name), 'primary','submit', TRUE); ?>
    </fieldset>

    <br class="clear" />
    <h3>Список ключей:</h3>
    <table class="access_keys_list widefat">
        <thead>
        <tr>
            <th style="min-width: 22px;"><?php esc_attr_e( '№ п/п', 'access-keys' ); ?></th>
            <th><?php esc_attr_e( 'Ключ', 'access-keys' ); ?></th>
            <th style="width: 130px;"><?php esc_attr_e( 'Данные привязки к ПК клиента (BIOS)', 'access-keys' ); ?></th>
            <th style="width: 150px;"><?php esc_attr_e( 'Комментарий', 'access-keys' ); ?></th>
            <th><?php esc_attr_e( 'Статус ключа', 'access-keys' ); ?></th>
            <th style="width: 110px;"><?php esc_attr_e( 'Дата генерации (сначала новые)', 'access-keys' ); ?></th>
            <th style="width: 110px;"><?php esc_attr_e( 'Дата активации', 'access-keys' ); ?></th>
            <th><?php esc_attr_e( 'Действия', 'access-keys' ); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        if(isset($access_keys) && !empty($access_keys)) {
            foreach ( $access_keys as $kk => $key ) {
                $num = $kk + 1;
                if($num % 2 == 0) $alt = "alternate";
                else $alt = "";
                ?>
                <tr class="<?=$alt;?>">
                    <td class="row-title"><?=$num;?>.</td>
                    <td>
                        <input type="text" <?php if(stripos($key->licence_key, 'demo-') !== false) { ?> style="background: #00A0D2; color: #fff;" <?php } ?> id="key_<?=$key->id;?>" value="<?=$key->licence_key;?>" readonly>
                        <a class="copy_btn" onclick="CopyText('key_<?=$key->id;?>')">Скопировать ключ</a>
                    </td>
                    <td>
                        <?php
                        if($key->bios) {
                            if(stripos($key->licence_key, 'demo-') !== false)
                                echo "<div class='notice notice-info inline'>$key->bios</div>";
                            else
                                echo "<div class='notice notice-success inline'>$key->bios</div>";
                        } else {
                            echo "<div class='notice notice-error inline'>Нет привязки.</div>";
                        }
                        ?>
                    </td>
                    <td><textarea name="<?php echo $this->plugin_name;?>[comment][<?=$key->id;?>]" id="<?php echo $this->plugin_name;?>-comment-<?=$key->id;?>" cols="20" rows="3" style="resize: none;"><?=$key->comment;?></textarea></td>
                    <td>
                        <?php
                            if($key->status) { ?>
                                <label title='Активный'>
                                    <input type="radio" name="<?php echo $this->plugin_name;?>[status][<?=$key->id;?>]" value="1" checked />
                                    <span><?php esc_attr_e( 'Вкл.', 'access-keys' ); ?></span>
                                </label><br>
                                <label title='Неактивный'>
                                    <input type="radio" name="<?php echo $this->plugin_name;?>[status][<?=$key->id;?>]" value="0" />
                                    <span><?php esc_attr_e( 'Выкл.', 'access-keys' ); ?></span>
                                </label>
                            <?php } else { ?>
                                <label title='Активный'>
                                    <input type="radio" name="<?php echo $this->plugin_name;?>[status][<?=$key->id;?>]" value="1" />
                                    <span><?php esc_attr_e( 'Вкл.', 'access-keys' ); ?></span>
                                </label><br>
                                <label title='Неактивный'>
                                    <input type="radio" name="<?php echo $this->plugin_name;?>[status][<?=$key->id;?>]" value="0" checked />
                                    <span><?php esc_attr_e( 'Выкл.', 'access-keys' ); ?></span>
                                </label>
                            <?php }
                        ?>
                    </td>
                    <td><?=$key->date_added;?></td>
                    <td>
                        <?php
                        if($key->date_activate) {
                            if(stripos($key->licence_key, 'demo-') !== false)
                                echo "<div class='notice notice-info inline'>Активирован:<br/>$key->date_activate</div>";
                            else
                                echo "<div class='notice notice-success inline'>Активирован:<br/>$key->date_activate</div>";
                        } else {
                            echo "<div class='notice notice-error inline'>Не активирован.</div>";
                        }
                        ?>
                    </td>
                    <td>
                        <label for="<?php echo $this->plugin_name;?>-delete-<?=$key->id;?>">
                            <input name="<?php echo $this->plugin_name;?>[delete][<?=$key->id;?>]" type="checkbox" id="<?php echo $this->plugin_name;?>-delete-<?=$key->id;?>" value="1" />
                            <span><?php esc_attr_e( 'Удалить ключ', 'access-keys' ); ?></span>
                        </label>
                    </td>
                </tr>
            <?php }
        } else { ?>
            <tr>
                <td colspan="8"><h4>Ключи отсутствуют. Сгенерируйте новые.</h4></td>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
            <th class="row-title"><?php esc_attr_e( '№ п/п', 'access-keys' ); ?></th>
            <th><?php esc_attr_e( 'Ключ', 'access-keys' ); ?></th>
            <th><?php esc_attr_e( 'Данные привязки к ПК клиента (BIOS)', 'access-keys' ); ?></th>
            <th><?php esc_attr_e( 'Комментарий', 'access-keys' ); ?></th>
            <th><?php esc_attr_e( 'Статус ключа', 'access-keys' ); ?></th>
            <th><?php esc_attr_e( 'Дата генерации (сначала новые)', 'access-keys' ); ?></th>
            <th><?php esc_attr_e( 'Дата активации', 'access-keys' ); ?></th>
            <th><?php esc_attr_e( 'Действия', 'access-keys' ); ?></th>
        </tfoot>
    </table>

    <?php submit_button(__('Применить изменения', $this->plugin_name), 'primary','submit', TRUE); ?>

</form>
