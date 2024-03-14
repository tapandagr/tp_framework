/**
 * Cornelius - Core PrestaShop module
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2024 © tivuno.com
 * @license   https://tivuno.com/blog/bp/business-news/2-basic-license
 */
$(window).scroll(function () {
    let scroll = $(window).scrollTop();

    if (scroll >= 300) {
        $('.header-bottom').addClass('sticky-header');
    } else {
        $('.header-bottom').removeClass('sticky-header');
    }
});
