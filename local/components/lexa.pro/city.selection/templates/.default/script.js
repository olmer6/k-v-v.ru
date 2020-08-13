// лучше этот файлы вынести выше в логику компонента


function showCitySelection(event)
{
    // что передаем из шаблона
    var params = event.data.params;

    $.post(params.CURRENT_PATH_AJAX, {CACHE_TIME: params.CACHE_TIME}).done(function(ajaxHtml){

        $(".city-selection__dialog").html(ajaxHtml);
        $(".city-selection__dialog").show();
        $(".city-selection__shadow").show();

    });

    return false;
}


$(document).ready(function(){

    $(".city-selection").click({params:citySelectionJs}, showCitySelection);

    // нажатие на ссылку региона
    $(".city-selection__dialog").on("click",".ajax-city-selection__regions_box a", function(){

        var curRegion = $(this).attr("region");

        $(".ajax-city-selection__cities_box div").hide();

        $(".cities-region-"+curRegion).show(100);

        return false;

    });

    // нажатие на ссылку города
    $(".city-selection__dialog").on("click",".ajax-city-selection__cities_box a", function(){

        var cityZip = $(this).attr("city-zip");
        var cityName = $(this).attr("city-name");
        var cityCode = $(this).attr("city-code");

        $.get(citySelectionJs.CURRENT_PATH_AJAX,{
            CITY_ZIP: cityZip,
            CITY_NAME: cityName,
            CITY_CODE: cityCode
        }).done(function(){

            $(".city-selection").text(cityName);
            $(".city-selection__dialog").hide();
            $(".city-selection__shadow").hide();

        });


        return false;

    });

    $(".city-selection__dialog").on("keyup", ".ajax-city-selection__search", function(val) {

        var textInput = val.target.value;//введеные символы в инпут

        var isShowSearch = false; // признак найденых городов

        $(".cities-search ul").html(""); // очистим содержимое

        $(".cities-region-list").hide();

        if(textInput.length > 1)
        {
            $(".cities-region-list .li-city").each(function (index, elem) {

                var a_text = $(this).children("a").text();

                if (a_text.toLowerCase().indexOf(textInput.toLowerCase()) > -1) {

                    isShowSearch = true;

                    $(this).clone().appendTo(".cities-search ul");
                }
            });

            if (isShowSearch)
            {
                $(".cities-search").show();
            }
        }
    });

    $(".city-selection__shadow").click(function () {
        $(".city-selection__dialog").hide();
        $(".city-selection__shadow").hide();
    });

});