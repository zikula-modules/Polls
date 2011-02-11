{if $usercanvote && $allowedtovote}
    {if $modvars.Polls.enablecategorization and $shorturls and $modvars.Polls.addcategorytitletopermalink}
        <a href="{modurl modname='Polls' func='display' pollid=$poll.pollid cat=$poll.__CATEGORIES__.Main.name}">{$poll.title|safetext}</a>
    {else}
        <a href="{modurl modname='Polls' func='display' pollid=$poll.pollid}">{$poll.title|safetext}</a>
    {/if}
{else}
    {$poll.title|safetext}
{/if}

(<a href="{modurl modname='Polls' func='results' pollid=$poll.pollid}">{gt text="Results"}</a> - {gt text="%s vote" plural="%s votes" count=$poll.votecount tag1=$poll.votecount})
