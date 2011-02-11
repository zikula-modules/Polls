{gt text="Polls" assign=templatetitle}
{include file="polls_user_menu.tpl"}
<div class="z-menu">
    <span class="z-menuitem-title">
        [ <a href="{modurl modname="Polls" type="user" func="main"}">{gt text="View polls"}</a> ]
    </span>
</div>

{if $modvars.Polls.enablecategorization && isset($categories)}
<h2>{gt text="Categories"}</h2>
{foreach from=$categories key=property item=category}
<ul>
    {foreach from=$category.subcategories item=subcategory}
    
    {array_field_isset assign="categoryname" array=$subcategory.display_name field=$lang returnValue=1}
    {if $categoryname eq ''}{assign var="categoryname" value=$subcategory.name}{/if}
    {array_field_isset assign="categorydesc" array=$subcategory.display_desc field=$lang returnValue=1}

    {if $shorturls}
    <li><a href="{modurl modname='Polls' func='view' prop=$property cat=$subcategory.path|replace:$category.path:''}" title="{$categorydesc}">{$categoryname}</a></li>
    {else}
    <li><a href="{modurl modname=Polls func=view prop=$property cat=$subcategory.id}" title="{$categorydesc}">{$categoryname}</a></li>
    {/if}
    {/foreach}
</ul>
{/foreach}
{/if}

{modfunc modname='Polls' func='view'}
