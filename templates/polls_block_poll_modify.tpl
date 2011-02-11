<div class="z-formrow">
    <label for="polls_pollid">{gt text="Poll to display" domain="module_polls"}</label>
    <select id="polls_pollid" name="pollid">
        {html_options options=$polls selected=$vars.pollid}
    </select>
</div>

<div class="z-formrow">
    <label for="polls_ajaxvoting">{gt text="Use ajax for voting" domain="module_polls"}</label>
    <input id="polls_ajaxvoting" type="checkbox" name="ajaxvoting" value="1"{if $vars.ajaxvoting} checked="checked"{/if} />
</div>
