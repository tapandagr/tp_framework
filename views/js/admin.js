/**
 *
*/
$('.new-category-ajax').on('click', function(e)
{
    e.preventDefault();
    e.stopPropagation();
    $('.category-ajax-form').slideToggle();
});
