{gt text="Voting booth - %s" tag1=$item.title assign=templatetitle}
{include file="polls_user_menu.tpl"}

<div class="z-menu">
    <span class="z-menuitem-title">
        [
        <a href="{modurl modname='Polls' type='user' func='main'}">{gt text="View polls"}</a> |
        <a href="{modurl modname='Polls' type='user' func='results' pollid=$item.pollid}">{gt text="Results"}</a>
        ]
    </span>
</div>

<h3>{$item.title|safetext}</h3>

<form class="z-form" action="{modurl modname='Polls' type='user' func='vote'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
		<input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="pollid" value="{$item.pollid}" />
        <input type="hidden" name="title" value="{$item.title|safetext}" />
        <fieldset>
            {foreach from=$item.options item=option}
            {if $option.optiontext neq ''}
            <div class="z-formlist">
                <input id="polls_{$option.voteid}" name="voteid" type="radio" value="{$option.voteid}" />
                <label for="polls_{$option.voteid}">{$option.optiontext|safetext}</label>
            </div>
            {/if}
            {/foreach}
        </fieldset>
        
        {if $usercanvote}
        <div class="z-formbuttons">
            <input name="submit" type="submit" value="{gt text='Vote'}" />
        </div>
        {/if}
    </div>
</form>