{if $func eq 'view'}
    {gt text="Polls" assign=templatetitle}
    {include file="polls_user_menu.tpl"}
    <div class="z-menu">
        <span class="z-menuitem-title">
            [ <a href="{modurl modname="Polls" type="user" func="main"}">{gt text="View polls"}</a> ]
        </span>
    </div>
{/if}

{if $category}
    {array_field_isset assign="categoryname" array=$category.display_name field=$lang returnValue=1}
    {if $categoryname eq ''}
        {assign var="categoryname" value=$category.name}
    {/if}
    {array_field_isset assign="categorydesc" array=$category.display_desc field=$lang returnValue=1}

    <h3>{$categoryname}</h3>
    {gt text="Polls - %s" tag1=$categoryname assign=templatetitle}
    {pagesetvar name="title" value=$templatetitle}
    {if $categorydesc neq ''}
        <p>{$categorydesc}</p>
    {/if}
{else}
    <h3>{gt text="Past surveys"}</h3>
{/if}

{if $polls}
    <ul>
        {foreach item=poll from=$polls}
        <li>{$poll}</li>
        {/foreach}
    </ul>
{else}
    <p>{gt text="No polls found."}</p>
{/if}

{pager show=page rowcount=$pager.numitems limit=$pager.itemsperpage posvar=startnum}
