<?php

if (!class_exists('NamazVakitleriCore')) {
    class NamazVakitleriCore
    {
        public $plugin_path = '';
        public $plugin_url = '';
        public $namaz_core = false;
        function __construct($_plugin_path, $_plugin_url)
        {
            $this->plugin_path = $_plugin_path;
            $this->plugin_url = $_plugin_url;
        }
        public function load()
        {
            add_action('init', function () {
                foreach (glob($this->plugin_path . "/inc/*.php") as $filename) {
                    include($filename);
                } 
            });
            add_action('wp_ajax_namaz_vakitleri_ulkeler', array($this, 'get_ulkeler_ajax'));
            add_action('wp_ajax_norpriv_add_namaz_vakitleri_ulkeler', array($this, 'get_sehirler_ajax'));
            add_action('wp_ajax_namaz_vakitleri_sehirler', array($this, 'get_sehirler_ajax'));
            add_action('wp_ajax_norpriv_add_namaz_vakitleri_sehirler', array($this, 'get_sehirler_ajax'));
            add_action('wp_ajax_namaz_vakitleri_ilceler', array($this, 'get_ilceler_ajax'));
            add_action('wp_ajax_norpriv_add_namaz_vakitleri_ilceler', array($this, 'get_ilceler_ajax'));
            add_action('wp_ajax_namaz_vakitleri_vakitler', array($this, 'get_vakitler_ajax'));
            add_action('wp_ajax_norpriv_add_namaz_vakitleri_vakitler', array($this, 'get_vakitler_ajax'));
            add_action('widgets_init', function () {
                foreach (glob($this->plugin_path . "/lib/*.php") as $filename) {
                    include($filename);
                }
                foreach (glob($this->plugin_path . "/lazy-lib/*.php") as $filename) {
                    include($filename);
                }
                foreach (glob($this->plugin_path . "/widgets/*.php") as $filename) {
                    include($filename);
                    register_widget(str_replace(".php", "", str_replace($this->plugin_path . "/widgets/", "", $filename)));
                }
                $this->namaz_core = new Namaz();
            });
            add_action('init', function () {
            });
            add_action('init', function () {
                $pre_options = get_option('medyum_burak_namaz_vakitleri_settings', true);
                $namazVakitleriFields = array(
                    array(
                        'title' => 'Give credits to author',
                        'name' => 'give_credits',
                        'type' => 'select',
                        'default_value' => '',
                        'options' => array(
                            array("key" => "", "text" => "Yes"),
                            array("key" => "-1", "text" => "No"),
                        )
                    ),
                    array(
                        'title' => 'Short Code',
                        'name' => 'short_code',
                        'type' => 'input',
                        'default_value' => 'gunluk_burc',
                    ),
                    array(
                        'title' => 'Default Country',
                        'name' => 'default_country',
                        'type' => 'select',
                        'description' => 'Save the settings for load cities',
                        'default_value' => '',
                        'options' => $this->get_ulkeler()
                    ),
                    array(
                        'title' => 'Default City',
                        'name' => 'default_city',
                        'type' => 'select',
                        'description' => 'Save the settings for load districts',
                        'default_value' => '',
                        'options' => $this->get_sehirler(intval($pre_options['default_country']))
                    ),
                    array(
                        'title' => 'Default District',
                        'name' => 'default_district',
                        'type' => 'select',
                        'default_value' => '',
                        'options' => $this->get_ilceler(intval($pre_options['default_country']), intval($pre_options['default_city']))
                    ),
                );
                $namazVakitleriSettingsInstance = new NamazVakitleriSettingsModule(
                    true,
                    "Medyum Burak Namaz Vakitleri",
                    "medyum_burak_namaz_vakitleri",
                    "administrator",
                    "medyum_burak_namaz_vakitleri_settings",
                    $namazVakitleriFields
                );
                $GLOBALS["namazVakitleriSettings"] = false;
                global $namazVakitleriSettings;
                $namazVakitleriSettings = $namazVakitleriSettingsInstance->load();
                add_shortcode($namazVakitleriSettings["short_code"], array($this, 'render_shortcode'));
            });
        }
        public function copyright()
        {
            echo '<div class="namaz_copyright">' .
                '<a href="https://www.medyumburak.com/" title="Medyum Burak Namaz Vakitleri">Medyum Burak Namaz Vakitleri</a>' .
                '</div>';
        }
        public function render_shortcode()
        {
            global $namazVakitleriSettings;
            ob_start();
            $this->render($namazVakitleriSettings);
            $output = ob_get_clean();
            return $output;
        }
        public function render($options)
        {
            global $namazVakitleriSettings;
            $selected_country = !empty($_COOKIE["default_country"]) ? $_COOKIE["default_country"] : $namazVakitleriSettings['default_country'];
            $selected_city = !empty($_COOKIE["default_city"]) ? $_COOKIE["default_city"] : $namazVakitleriSettings['default_city'];
            $selected_district = !empty($_COOKIE["default_district"]) ? $_COOKIE["default_district"] : $namazVakitleriSettings['default_district'];
            $selected_district_name = $this->find_value($this->get_ilceler($selected_country, $selected_city), 'key', $selected_district)["text"];
            echo '<div class="namaz_container">' . "\n";

            echo '<div class="panel panel-default">' . "\n";
            echo '<div class="panel-heading"><a href="#" data-toggle-panel="true">'.$selected_district_name.'</a></div>' . "\n";
            echo '<div class="panel-body" style="display: none;">' . "\n";
            echo '<div class="form-controls">' . "\n";
            echo '<label>Ülke</label>' . "\n";
            echo '<select name="ulke_id" class="form-control">';
            foreach($this->get_ulkeler() as $ulke) {
                echo '<option '.($ulke["key"] == $selected_country ? 'selected' : '').' value="'.$ulke["key"].'">'.$ulke["text"].'</option>';
            }
            echo '</select>';
            echo '<label>Şehir</label>' . "\n";
            echo '<select name="sehir_id" class="form-control">';
            foreach($this->get_sehirler($selected_country) as $sehir) {
                echo '<option '.($sehir["key"] == $selected_city ? 'selected' : '').' value="'.$sehir["key"].'">'.$sehir["text"].'</option>';
            }
            echo '</select>';
            echo '<label>İlçe</label>' . "\n";
            echo '<select name="ilce_id" class="form-control">';
            foreach($this->get_ilceler($selected_country, $selected_city) as $ilce) {
                echo '<option '.($ilce["key"] == $selected_district ? 'selected' : '').' value="'.$ilce["key"].'">'.$ilce["text"].'</option>';
            }
            echo '</select>';
            echo "</div>\n";
            echo "</div>\n";
            echo "</div>\n";
            echo "<hr />\n";
            echo '<div class="namaz_vakitler_container">' . "\n";
            echo $this->get_vakitler($selected_district);
            echo "</div>\n";
            echo "</div>\n";
        }
        public function find_value($arr, $col, $val)
        {
            foreach ($arr as $r) {
                if ($r[$col] == $val) {
                    return $r;
                }
            }
            return false;
        }
        public function get_ulkeler_ajax() {
            echo json_encode($this->get_ulkeler());
            wp_die();
        }
        public function get_ulkeler() {
            $ulkeler_data = $this->namaz_core->ulkeler();
            if ($ulkeler_data["durum"] == "basarili") {
                $ulkeler_data = $ulkeler_data["veri"];
                $return_data = array();
                foreach($ulkeler_data as $key => $value) {
                    $return_data[] = array("key" => $key, "text" => mb_ucfirst(mb_strtolower($value, 'utf-8'), 'utf-8'));
                }
                return $return_data;
            }
            return false;
        }
        public function get_sehirler_ajax() {
            echo json_encode($this->get_sehirler($_REQUEST["ulke_id"]));
            wp_die();
        }
        public function get_sehirler($ulke_id) {
            if ($ulke_id == 0) {
                return array();
            }
            $sehirler_data = $this->namaz_core->sehirler($ulke_id);
            if ($sehirler_data["durum"] == "basarili") {
                $sehirler_data = $sehirler_data["veri"];
                $return_data = array();
                foreach($sehirler_data as $key => $value) {
                    $return_data[] = array("key" => $key, "text" => mb_ucfirst(mb_strtolower($value, 'utf-8'), 'utf-8'));
                }
                return $return_data;
            }
            return false;
        }
        public function get_ilceler_ajax() {
            echo json_encode($this->get_ilceler($_REQUEST["ulke_id"], $_REQUEST["sehir_id"]));
            wp_die();
        }
        public function get_ilceler($ulke_id, $sehir_id) {
            if ($ulke_id == 0 || $sehir_id == 0) {
                return array();
            }
            $ilceler_data = $this->namaz_core->ilceler($ulke_id, $sehir_id);
            if ($ilceler_data["durum"] == "basarili") {
                $ilceler_data = $ilceler_data["veri"];
                $return_data = array();
                foreach($ilceler_data as $key => $value) {
                    $return_data[] = array("key" => $key, "text" => mb_ucfirst(mb_strtolower($value, 'utf-8'), 'utf-8'));
                }
                return $return_data;
            }
            return false;
        }
        public function get_vakitler_ajax(){
            $default_country = $_REQUEST["ulke_id"];
            $default_city = $_REQUEST["sehir_id"];
            $default_district = $_REQUEST["ilce_id"];
            if ($default_country > 0 && $default_city > 0 && $default_district > 0) {
                setcookie("default_country", $default_country, time()+3600*24*1000, "/");
                setcookie("default_city", $default_city, time()+3600*24*1000, "/");
                setcookie("default_district", $default_district, time()+3600*24*1000, "/");
            }
            echo $this->get_vakitler($_REQUEST["ilce_id"]);
            wp_die();
        }
        public function get_vakitler($ilce_id) {
            $vakitler_data = $this->namaz_core->vakitler($ilce_id);
            if ($vakitler_data['durum'] == 'basarili') {
                $vakitler_data['veri']['vakitler'] = $this->find_value($vakitler_data['veri']['vakitler'], 'tarih', date('d.m.Y'));
            }
            echo '<table><tbody>';
            echo '<tr><td colspan="2" class="header">'.$vakitler_data['veri']['yer_adi'].'</td></tr>';
            echo '<tr><td>İmsak</td><td>'.$vakitler_data['veri']['vakitler']['imsak'].'</td></tr>';
            echo '<tr><td>Güneş</td><td>'.$vakitler_data['veri']['vakitler']['gunes'].'</td></tr>';
            echo '<tr><td>Öğle</td><td>'.$vakitler_data['veri']['vakitler']['ogle'].'</td></tr>';
            echo '<tr><td>İkindi</td><td>'.$vakitler_data['veri']['vakitler']['ikindi'].'</td></tr>';
            echo '<tr><td>Akşam</td><td>'.$vakitler_data['veri']['vakitler']['aksam'].'</td></tr>';
            echo '<tr><td>Yatsı</td><td>'.$vakitler_data['veri']['vakitler']['yatsi'].'</td></tr>';
            echo '</tbody></table>';
        }
        public function initAssets()
        {
            add_action('wp_enqueue_scripts', function () {
                wp_register_style('medyum_burak_namaz_vakitleri_style', $this->plugin_url . 'assets/css/medyum-burak-namaz-vakitleri.css');
                wp_enqueue_style('medyum_burak_namaz_vakitleri_style');
            });
            add_action('wp_enqueue_scripts', function () {
                wp_register_script('medyum_burak_namaz_vakitleri_script', $this->plugin_url . 'assets/js/medyum-burak-namaz-vakitleri.js');
                wp_enqueue_script('medyum_burak_namaz_vakitleri_script');
                wp_localize_script('medyum_burak_namaz_vakitleri_script', 'medyum_burak_namaz_vakitleri_script', array('ajax_url' => admin_url('admin-ajax.php')));
            }, PHP_INT_MAX, 2);
        }
    }
}