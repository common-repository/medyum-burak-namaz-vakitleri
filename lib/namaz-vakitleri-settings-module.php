<?php

    if (!class_exists('NamazVakitleriSettingsModule')) {
        class NamazVakitleriSettingsModule {
            private $menu_name;
            private $menu_key;
            private $menu_role;
            private $settings_key;
            private $settings_fields;
            private $auto_load;

            public function __construct($auto_load, $menu_name, $menu_key, $menu_role, $settings_key, $settings_fields) {
                $this->menu_name = $menu_name;
                $this->menu_role = $menu_role;
                $this->menu_key = $menu_key;
                $this->auto_load = $auto_load;
                $this->settings_key = $settings_key;
                $this->settings_fields = $settings_fields;
                add_action( 'admin_menu', array( $this, 'render_page' ) );
            }

            public function load() {
                return get_option($this->settings_key, true);
            }

            public function render_page() {
                $page = add_menu_page($this->menu_name, $this->menu_name, $this->menu_role, $this->menu_key, array( $this, "render_form" ));
                add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets' ) );
            }

            public function save($vars) {
                if ( array_key_exists($this->settings_key, $vars ) ) {
                    $data = $vars[$this->settings_key];
                    if (is_array($data)) {
                        foreach($data as $key => $val) {
                            if (is_array($data[$key])) {
                                foreach($data[$key] as $subkey => $subval) {
                                    $data[$key][$subkey] = stripslashes($data[$key][$subkey]);
                                }
                            }
                            else {
                                $data[$key] = stripslashes($data[$key]);
                            }
                        }
                    }
                    else {
                        $data = stripslashes($data);
                    }
                    update_option($this->settings_key, $data);
                    delete_transient($this->menu_key);
                }
            }

            public function render_form() {
                if ($_POST) {
                    $this->save($_POST);
                }
                ?>
                <div class="wrap">
                <h2><?=$this->menu_name?></h2>
                <hr />
                <br />
                <form method="POST" action="<?=admin_url('admin.php?page='.$this->menu_key)?>">
                    <?php
                    $meta_data = get_option($this->settings_key, true);
                    if (!is_array($meta_data)) {
                        $meta_data = array();
                    }
                    foreach($this->settings_fields as $field) {
                        $val = $meta_data[$field['name']];
                        if (empty($val) || $val == null) {
                            if (!empty($field['default_value'])) {
                                $val = $field['default_value'];
                            }
                            else {
                                $val = '';
                            }
                        }
                        echo $this->render_form_element($field['title'], $this->settings_key.'['.$field['name'].']', $field['type'], $val, $field['options'], $field['styles'], $field['description']);
                    }
                    ?>
                    <?php submit_button(); ?>
                </form>
                </div>
                <?php
            }

            public function load_admin_assets() {
                wp_register_style( 'burc-bot-settings-style', plugin_dir_url(dirname(__FILE__)).'assets/admin/css/style.css', false, '1.0.0' );
                wp_enqueue_style( 'burc-bot-settings-style' );
                wp_register_script('burc-bot-settings-script', plugin_dir_url(dirname(__FILE__)).'assets/admin/js/script.js', array( 'jquery' ), '1.0.0', true);
                wp_enqueue_script('burc-bot-settings-script');
            }

            private function render_form_element($text, $name, $type, $val, $options, $styless, $desc)
            {
                $styles = '';
                if ($styless != null) {
                    foreach($styless as $style) {
                        $styles .= $style['key'].':'.$style['value'].'; ';
                    }
                }
                if (!empty($styles)) {
                    $styles = ' style="'.$styles.'" ';
                }

                if (!empty($desc)) {
                    $desc = '<div class="alert alert-info">'.$desc.'</div>';
                }

                if ($type == "input")
                {
                    $inner = '<input '.$styles.' type="text" class="form-control" name="'.$name.'" value="' . $val . '" />';
                }
                else if ($type == "number")
                {
                    $inner = '<input '.$styles.' type="number" min="0" max="9999" class="form-control" name="'.$name.'" value="' . $val . '" />';
                }
                else if ($type == "input-color")
                {
                    $inner = '<input '.$styles.' type="text" class="form-control color_field" name="'.$name.'" value="' . $val . '" />';
                }
                else if ($type == "textarea")
                {
                    ob_start();
                    wp_editor($val, $name, $settings = array(
                        'textarea_rows' => '5',
                        'textarea_name' => $name,
                        'media_buttons' => false,
                        'wpautop' => false
                    ));
                    $inner = ob_get_clean();
                }
                else if ($type == "editor")
                {
                    echo "<div class='row'>
                        <div class='col-md-12'>
                            <div class='form-group'>
                                <label class='form-label'>$text (Live)</label>$desc";

                    wp_editor($val, $name, array(
                        'media_buttons' => true
                    ));

                    echo "</div>
                        </div>
                    </div>";
                    return "";
                }
                else if ($type == "textarea-only")
                {
                    $inner = '<textarea '.$styles.' class="form-control" name="'.$name.'">'.$val.'</textarea>';
                }
                else if ($type == "select") {
                    $inner = '<select '.$styles.' class="form-control" name="'.$name.'"><option value="">None</option>';
                    foreach($options as $option) {
                        $prefix = $option["key"] == $val ? "selected" : "";
                        $inner .= '<option value="'.$option["key"].'" '.$prefix.'>'.$option["text"].'</option>';
                    }
                    $inner .= '</select>';
                }
                else if ($type == "select-multiple") {
                    $inner = '<select '.$styles.' class="form-control" name="'.$name.'[]" multiple>';
                    foreach($options as $option) {
                        $prefix = in_array($option["key"], $val) ? "selected" : "";
                        $inner .= '<option value="'.$option["key"].'" '.$prefix.'>'.$option["text"].'</option>';
                    }
                    $inner .= '</select>';
                }
                return "
                    <div class='row'>
                        <div class='col-md-12'>
                            <div class='form-group'>
                                <label class='form-label'>$text</label>
                                $desc
                                $inner
                            </div>
                        </div>
                    </div>
                    ";
            }
        }
    }