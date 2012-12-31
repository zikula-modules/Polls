{pageaddvar name="stylesheet" value="modules/Polls/style/style.css"}

{if $allowedtovote eq true and $vars.ajaxvoting}
{ajaxheader modname='Polls' filename='polls.js'}
{/if}

<div id="pollblockcontent">
    <h3>{$item.title|safetext}</h3>

    {if !$allowedtovote}
    <div class="polls-resultstable">
        <ul class="polls-resultschart">
            {section name=options loop=$item.options}
            {if $item.options[options].optiontext neq ""}
            <li>
                <span class="item">{$item.options[options].optiontext|safetext}</span>
                <span class="count">{$item.options[options].percent|safetext}%</span>
                <span class="index" style="width: {$item.options[options].percent|safetext}%"> {gt text="%s vote" plural="%s votes" count=$item.options[options].optioncount tag1=$item.options[options].optioncount}</span>
            </li>
            {/if}
            {/section}
        </ul>
    </div>
    {else}
    <form id="pollvoteform" action="{modurl modname=Polls type=user func=vote}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
			<input type="hidden" name="csrftoken" id="pollstoken" value="{insert name='csrftoken'}" />
            <input type="hidden" name="title" value="{$item.title|safetext}" />
            <input type="hidden" name="pollid" value="{$item.pollid}" />
            <input type="hidden" name="displayresults" value="1" />
            <ul class="poll-options-list">
                {section name=options loop=$item.options}
                {if $item.options[options].optiontext neq ''}
                <li>
                    <input id="block_polls_{$item.options[options].voteid|safetext}" type="radio" name="voteid" value="{$item.options[options].voteid}" />
                    <label for="block_polls_{$item.options[options].voteid|safetext}">{$item.options[options].optiontext|safetext}</label>
                </li>
                {/if}
                {/section}
            </ul>
            {if $usercanvote}
                <div>
                {if $vars.ajaxvoting}
                <input onclick="javascript:pollvote();" name="vote" type="button" value="{gt text='Vote' domain='module_polls'}" />
                {else}
                <input name="submit" type="submit" value="{gt text='Vote' domain='module_polls'}" />
                {/if}
                </div>
                <em id="pollvoteinfo">&nbsp;</em>
            {/if}
        </div>
    </form>
    {/if}

    <p class="z-sub">
        <a href="{modurl modname='Polls' type='user' func='results' pollid=$item.pollid}">{gt text="Results" domain="module_polls"}</a> |
        <a href="{modurl modname='Polls' type='user' func='main'}">{gt text="Other Polls" domain="module_polls"}</a>
    </p>

    {if $vars.ajaxvoting}
    <script type="text/javascript">
        var recordingvote  = '{{gt text="Recording vote" domain="module_polls"}}';
    </script>
    {/if}

</div>
