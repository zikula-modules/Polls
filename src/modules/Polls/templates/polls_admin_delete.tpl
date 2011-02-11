{include file="polls_admin_menu.tpl"}
{gt text="Delete Poll" assign=templatetitle}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='editdelete.gif' set='icons/large' alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    <p class="z-warningmsg">{gt text="Do you really want to delete this poll?"}</p>
    <form class="z-form" action="{modurl modname="Polls" type="admin" func="delete"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Polls'}" />
            <input type="hidden" name="confirmation" value="1" />
            <input type="hidden" name="pollid" value="{$pollid|safetext}" />
            <fieldset>
                <legend>{gt text="Confirmation prompt"}</legend>
                <div class="z-formbuttons">
                    {button src='button_ok.gif' set='icons/small' __alt="Delete" __title="Delete"}
                    <a href="{modurl modname=Polls type=admin func=view}">{img modname='core' src='button_cancel.gif' set='icons/small'   __alt="Cancel" __title="Cancel"}</a>
                </div>
            </fieldset>
        </div>
    </form>
</div>
