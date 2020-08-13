 $(document).ready(function(){

  $('#itemTabs a').click(function (e) {
    e.preventDefault()
    $(this).tab('show')
  })



  $(function () {
    $('[data-toggle="tooltip"]').tooltip()
  })


  $('.slider-wrap').each(function(){
    var parent = $(this);
    $(".slider-prev", parent).click(function(){
        $('.lSPrev', parent).click();
    });
    $(".slider-next", parent).click(function(){
        $('.lSNext', parent).click();
    });
  });  


  $('.preview-wrap').each(function(){
    var parent = $(this);
    $('.preview-thumbs', parent).click(function(){
        $('.preview-thumbs',parent).removeClass("is_active" );
        $(this).addClass( "is_active" );
      $('.preview-mainimg',parent).removeClass("is_active");
      $('.preview-mainimg',parent).eq($(this).index()).addClass("is_active" )
    });
  });

  $('.more-colors a').click(function (e) {
      e.preventDefault()
      $('.more-colors-change').toggle()
  })




   $('#myTab a').click(function (e) {
    e.preventDefault()
    $(this).tab('show')
  })
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
    $(e.target).find('.tabs-slider').each(function() {
      $(this).get(0).slick.setPosition();
      $(this).resize();
    });
  });
  
  
$('[data-countdown]').each(function() {
    var $this = $(this), finalDate = $(this).data('countdown');
    $this.countdown(finalDate, function(event) {
      $this.html(event.strftime("<ul class='count-list'>" +
      "<li class='count-list-bg'>" + "<span class='range-number'>%D</span>" + "<div class='range-time'>дня</div></li>" +
      "<li class='count-list-bg  count-list-bg-gr'>" + "<span class='range-number'>%H</span>" + "<div class='range-time'>часов</div></li>" +
      "<li class='count-list-bg'>" + "<span class='range-number'>%M</span>" + "<div class='range-time'>минут</div></li>" + 
      "<li class='count-list-bg'>" +"<span class='range-number'>%S</span>" + "<div class='range-time'>секунд</div></li>"+
      "</ul>"));
    });
  });



});


jQuery(document).ready(function() {
  $(".videoplay").click(function() {
    $.fancybox({
        'padding'   : 0,
        'autoScale'   : false,
        'transitionIn'  : 'none',
        'transitionOut' : 'none',
        'title'     : this.title,
        'width'     : 640,
        'height'    : 385,
        'href'      : this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
        'type'      : 'swf',
        'swf'     : {
            'wmode'       : 'transparent',
            'allowfullscreen' : 'true'
        }
    });
      return false;
  });
});

 $(document).ready(function() {
    $('#specialSlider').lightSlider({
      item: 4,
      auto:true,
      loop: true,
      // slideEndAnimation: true,
      speed: 1000,
      pause: 7000,
      slideMargin: 10,
      pager: false,
      responsive: [
      {
        breakpoint: 800,
        settings: {
          item: 2,
        },
      }, 
       {
        breakpoint: 500,
        settings: {
          item: 1,
        },
      }, 
      ],
  });


});


$(document).ready(function() {


function toggleClassMenu() {
  myMenu.classList.add("menu--animatable");
  if(!myMenu.classList.contains("menu--visible")) {
  myMenu.classList.add("menu--visible");
  } else {
  myMenu.classList.remove('menu--visible');
  }
}


function toggleClassSide() {
  sidebar.classList.add("side--animatable");
  if(!jQuery(event.target).closest('.catalog-panel').length) {

       
    if(!sidebar.classList.contains("side--visible")) {
    sidebar.classList.add("side--visible");
    } else {
    sidebar.classList.remove('side--visible');
    }
   } 

}
function OnTransitionEnd() {
  myMenu.classList.remove("menu--animatable");
  sidebar.classList.remove("side--animatable");

}


var myMenu = document.querySelector(".menu");
var oppMenu = document.querySelector(".menu-icon");
if(myMenu)
{
  myMenu.addEventListener("transitionend", OnTransitionEnd, false); 
  myMenu.addEventListener("click", toggleClassMenu, false); 
}
if(oppMenu)
  oppMenu.addEventListener("click", toggleClassMenu, false);




var sidebar = document.querySelector(".side");
var appSide = document.querySelector(".open_side");

if(sidebar)
{
  sidebar.addEventListener("transitionend", OnTransitionEnd, false);
  sidebar.addEventListener("click", toggleClassSide, false);
}
if(appSide)
  appSide.addEventListener("click", toggleClassSide, false);


}); 



