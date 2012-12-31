{gt text="Results - %s" tag1=$item.title assign=templatetitle}
{include file="polls_user_menu.tpl"}

<div class="z-menu">
    <span class="z-menuitem-title">
        [
        <a href="{modurl modname='Polls' type='user' func='main'}">{gt text="View polls"}</a>
        {if $usercanvote && $allowedtovote} | <a href="{modurl modname='Polls' type='user' func='display' pollid=$item.pollid}">{gt text="Voting booth"}</a>{/if}
        ]
    </span>
</div>

<h3>{$item.title|safetext|notifyfilters:'polls.filter_hooks.p.filter'}</h3>

<div class="polls-resultstable">
    <ul class="polls-resultschart">
        {section name=options loop=$item.options}
        {if $item.options[options].optiontext neq ""}
        <li>
            <span class="item">{$item.options[options].optiontext|safetext}</span>
            <span class="count">{$item.options[options].percent|safetext}%</span>
            <span class="index" style="width: {$item.options[options].percent|safetext}%"> {$item.options[options].optioncount|safetext} {gt text="Votes"}</span>
        </li>
        {/if}
        {/section}
    </ul>
</div>

<p class="polls-resultlinks">
    <strong>{gt text="%s vote" plural="%s votes" count=$votecount tag1=$votecount}</strong>
</p>

{if $modvars.Polls.recurrence gt 0}
    <p class="polls-resultlinks">
    {if $modvars.Polls.recurrence eq 1}
        {gt text="We only allow one vote per day"}
    {elseif $modvars.Polls.recurrence eq 7}
        {gt text="We only allow one vote per week"}
    {elseif $modvars.Polls.recurrence eq 31}
        {gt text="We only allow one vote per month"}
    {/if}
    </p>
{/if}

{if !$allowedtovote}
    <p class="polls-resultlinks">{gt text="You have already voted!"}</p>
{/if}

{notifydisplayhooks eventname='polls.ui_hooks.p.display_view' id=$item.pollid}
