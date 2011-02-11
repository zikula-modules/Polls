{include file="polls_admin_menu.tpl"}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='configure.gif' set='icons/large' __alt='Settings' }</div>
    <h2>{gt text="Settings"}</h2>
    <form class="z-form" action="{modurl modname="Polls" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Polls'}" />
            <fieldset>
                <legend>{gt text="General settings"}</legend>
                <div class="z-formrow">
                    <label for="polls_enablecategorization">{gt text="Enable categorization"}</label>
                    <input id="polls_enablecategorization" type="checkbox" name="enablecategorization"{if $modvars.Polls.enablecategorization} checked="checked"{/if} />
                </div>
                <div class="z-formrow">
                    <label for="polls_itemsperpage">{gt text="Polls per page"}</label>
                    <input id="polls_itemsperpage" type="text" name="itemsperpage" size="3" value="{$modvars.Polls.itemsperpage|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="polls_scale">{gt text="Scale of results bar"}</label>
                    <input id="polls_scale" type="text" name="scale" size="3" value="{$modvars.Polls.scale|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="polls_recurrence">{gt text="How soon can a user vote for each poll?"}</label>
                    {html_options name="recurrence" options=$recurrences selected=$modvars.Polls.recurrence}
                </div>
                <div class="z-formrow">
                    <label for="polls_sortorder">{gt text="Order polls by"}</label>
                    <select id="polls_sortorder" name="sortorder" size="1">
                        <option value="0"{if $modvars.Polls.sortorder eq 0} selected="selected"{/if}>{gt text="Internal ID"}</option>
                        <option value="1"{if $modvars.Polls.sortorder eq 1} selected="selected"{/if}>{gt text="Date/Time"}</option>
                    </select>
                </div>
            </fieldset>
            <fieldset>
                <legend>{gt text="Permalinks"}</legend>
                <div class="z-formrow">
                    <label for="pages_addcategorytitletopermalink">{gt text="Add category title to permalink"}</label>
                    <input id="pages_addcategorytitletopermalink" type="checkbox" name="addcategorytitletopermalink"{if $modvars.Polls.addcategorytitletopermalink} checked="checked"{/if} />
                </div>
            </fieldset>
            
            <div class="z-formbuttons">
                {button src='button_ok.gif' set='icons/small' __alt="Update Configuration" __title="Update Configuration"}
                <a href="{modurl modname=Polls type=admin func=view}">{img modname='core' src='button_cancel.gif' set='icons/small' __alt="Cancel" __title="Cancel"}</a>
            </div>
        </div>
    </form>
</div>
