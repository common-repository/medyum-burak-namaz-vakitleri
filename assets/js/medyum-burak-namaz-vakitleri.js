jQuery(function($) {
    $("[data-toggle-panel=true]").click(function(e) {
        e.preventDefault();
        $parent = $(this).parents(".panel:first");
        $parent.find(".panel-body").toggle();
        return false;
    });
    $(".namaz_container").each(function(e) {
        var $this = $(this);
        var $ajax_url = $this.attr("data-url");
        $this.find("[name=ulke_id]").change(function(e) {
            var val = $(this).val() || 0;
            $this.find("[name=sehir_id]").html('');
            $this.find("[name=ilce_id]").html('');
            var data = {
                'action': 'namaz_vakitleri_sehirler',
                'ulke_id': val
            };
            jQuery.post(medyum_burak_namaz_vakitleri_script.ajax_url, data, function(response) {
                response = JSON.parse(response);
                $.each(response, function(index, row) {
                    $this.find("[name=sehir_id]").append('<option value="' + row.key + '">' + row.text + '</option>');
                });
            });
        });
        $this.find("[name=sehir_id]").change(function(e) {
            var val = $(this).val() || 0;
            $this.find("[name=ilce_id]").html('');
            var data = {
                'action': 'namaz_vakitleri_ilceler',
                'ulke_id': $this.find("[name=ulke_id]").val(),
                'sehir_id': val
            };
            jQuery.post(medyum_burak_namaz_vakitleri_script.ajax_url, data, function(response) {
                response = JSON.parse(response);
                $.each(response, function(index, row) {
                    $this.find("[name=ilce_id]").append('<option value="' + row.key + '">' + row.text + '</option>');
                });
            });
        });
        $this.find("[name=ilce_id]").change(function(e) {
            var val = $(this).val() || 0;
            $this.find("[data-toggle-panel=true]").text($(this).find("option:selected").text());
            var data = {
                'action': 'namaz_vakitleri_vakitler',
                'ulke_id': $this.find("[name=ulke_id]").val(),
                'sehir_id': $this.find("[name=sehir_id]").val(),
                'ilce_id': val
            };
            jQuery.post(medyum_burak_namaz_vakitleri_script.ajax_url, data, function(response) {
                $this.find(".namaz_vakitler_container").html(response);
                $this.find("[data-toggle-panel=true]").trigger("click");
            });
        });
    });
});