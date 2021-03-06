// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

/**
 * create the onload function to enable the respective functions
 */
Event.observe(window, 'load', polls_init_check, false);

function polls_init_check()
{
    if($('polls_multicategory_filter')) {
        polls_filter_init(); 
    }
}

/**
 * Admin panel functions
 */

function polls_filter_init()
{
    Event.observe('polls_property', 'change', polls_property_onchange, false);
    polls_property_onchange();
    $('polls_multicategory_filter').show();
}

function polls_property_onchange()
{
    $$('div#polls_category_selectors select').each(function(select){
        select.hide();
    });
    var id = "polls_"+$('polls_property').value+"_category";
    $(id).show();
}
