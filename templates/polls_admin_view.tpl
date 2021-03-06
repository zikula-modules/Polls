{ajaxheader modname="Polls" filename="polls_admin.js"}
{include file="polls_admin_menu.tpl"}
{gt text="View polls" assign="templatetitle"}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname="core" src="windowlist.png" set="icons/large" alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    
    {if $modvars.Polls.enablecategorization}
    <form class="z-form" action="{modurl modname='Polls' type='admin' func='view'}" method="post" enctype="application/x-www-form-urlencoded">
        <fieldset>
            <div id="polls_multicategory_filter">
                <label for="polls_property">{gt text="Category"}</label>
                {nocache}
                {if $numproperties gt 1}
                {html_options id="polls_property" name="polls_property" options=$properties selected=$property}
                {else}
                <input type="hidden" id="polls_property" name="polls_property" value="{$property}" />
                {/if}
                <div id="polls_category_selectors">
                    {foreach from=$catregistry key=prop item=cat}
                    {assign var=propref value=$prop|string_format:'polls_%s_category'}
                    {if $property eq $prop}
                    {assign var="selectedValue" value=$category}
                    {else}
                    {assign var="selectedValue" value=0}
                    {/if}
                    <noscript>
                        <div class="property_selector_noscript"><label for="{$propref}">{$prop}</label>:</div>
                    </noscript>
                    {selector_category category=$cat name=$propref selectedValue=$selectedValue allValue=0 __allText="Choose One" editLink=false}
                    {/foreach}
                </div>
                {/nocache}
                <input name="submit" type="submit" value="{gt text='Filter'}" />
                <input name="clear" type="submit" value="{gt text='Reset'}" />
            </div>
        </fieldset>
    </form>
    {/if}
    <table class="z-admintable">
        <thead>
            <tr>
                <th>{gt text="Title"}</th>
                <th>{gt text="Internal ID"}</th>
                {if $modvars.Polls.enablecategorization}
                <th>{gt text="Category"}</th>
                {/if}
                <th>{gt text="Options"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$polls item=poll}
            <tr class="{cycle values='z-odd,z-even'}">
                <td>{$poll.title|safetext}</td>
                <td>{$poll.pollid|safetext}</td>
                {if $modvars.Polls.enablecategorization}
                <td>
                    {if isset($poll.__CATEGORIES__)}
                    <ul>
                        {foreach from=$poll.__CATEGORIES__ item=cat}
                        {array_field_isset assign="catname" array=$cat.display_name field=$lang returnValue=1}
                        {if $catname eq ''}{assign var="catname" value=$cat.name}{/if}
                        <li>{$catname}</li>
                        {/foreach}
                    </ul>
                    {/if}
                </td>
                {/if}
                <td>
                    {assign var="options" value=$poll.options}
                    {section name=options loop=$options}
                    <a href="{$options[options].url|safetext}">{img modname='core' set='icons/extrasmall' src=$options[options].image alt=$options[options].title title=$options[options].title}</a>
                    {/section}
                </td>
            </tr>
            {foreachelse}
            <tr class="z-admintableempty"><td {if $modvars.Polls.enablecategorization}colspan="4"{else}colspan="3"{/if}>{gt text="No polls found."}</td></tr>
            {/foreach}
        </tbody>
    </table>
    
    {pager show="page" rowcount=$pager.numitems limit=$pager.itemsperpage posvar="startnum"}
</div>
