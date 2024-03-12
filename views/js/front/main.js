$(window).scroll(function () {
    let scroll = $(window).scrollTop();

    if (scroll >= 300) {
        $('.header-bottom').addClass('sticky-header');
    } else {
        $('.header-bottom').removeClass('sticky-header');
    }
});
