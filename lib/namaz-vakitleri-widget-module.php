<?php

    if (!class_exists('NamazVakitleriWidgetModule')) {
        class NamazVakitleriWidgetModule {
            private $widget_fields;
            private $widget_instance;

            public function __construct($widget_fields, $widget_instance) {
                $this->widget_fields = $widget_fields;
                $this->widget_instance = $widget_instance;
            }

            public function render_page() {
            }

            public function render_form() {
                echo "<br />";
                $meta_data = $this->widget_instance;
                if (!is_array($meta_data)) {
                    $meta_data = array();
                }
                foreach($this->widget_fields as $field) {
                    $val = $meta_data[$field['text_name']];
                    if (empty($val) || $val == null) {
                        if (!empty($field['default_value'])) {
                            $val = $field['default_value'];
                        }
                        else {
                            $val = '';
                        }
                    }
                    echo $this->render_form_element($field['title'], $field['name'], $field['id'], $field['type'], $val, $field['options'], $field['styles'], $field['description']);
                }
                echo "<br />";
            }

            private function render_form_element($text, $name, $id, $type, $val, $options, $styless, $desc)
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
                    $inner = '<input '.$styles.' type="text" class="form-control" name="'.$name.'" id="'.$id.'" value="' . $val . '" />';
                }
                else if ($type == "number")
                {
                    $inner = '<input '.$styles.' type="number" min="0" max="9999" class="form-control" name="'.$name.'" id="'.$id.'" value="' . $val . '" />';
                }
                else if ($type == "input-color")
                {
                    $inner = '<input '.$styles.' type="text" class="form-control color_field" name="'.$name.'" id="'.$id.'" value="' . $val . '" />';
                }
                else if ($type == "textarea")
                {
                    ob_start();
                    wp_editor($val, $id, $settings = array(
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
                    $inner = '<textarea '.$styles.' class="form-control" name="'.$name.'" id="'.$id.'">'.$val.'</textarea>';
                }
                else if ($type == "select") {
                    $inner = '<select '.$styles.' class="form-control" name="'.$name.'" id="'.$id.'"><option value="">None</option>';
                    foreach($options as $option) {
                        $prefix = $option["key"] == $val ? "selected" : "";
                        $inner .= '<option value="'.$option["key"].'" '.$prefix.'>'.$option["text"].'</option>';
                    }
                    $inner .= '</select>';
                }
                else if ($type == "select-multiple") {
                    if (!is_array($val)) {
                        $val = array();
                    }
                    $inner = '<select '.$styles.' class="form-control" name="'.$name.'[]" id="'.$id.'" multiple>';
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