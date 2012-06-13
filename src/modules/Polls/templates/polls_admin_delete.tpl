{include file="polls_admin_menu.tpl"}
{gt text="Delete Poll" assign=templatetitle}

<div class="z-admincontainer">

	<div class="z-admin-content-pagetitle">
		{icon type="delete" size="small"}
		<h3>{$templatetitle}</h3>
	</div>

    <p class="z-warningmsg">{gt text="Do you really want to delete this poll?"}</p>
    <form class="z-form" action="{modurl modname="Polls" type="admin" func="delete"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
			<input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
            <input type="hidden" name="confirmation" value="1" />
            <input type="hidden" name="pollid" value="{$pollid|safetext}" />
            <fieldset>
                <legend>{gt text="Confirmation prompt"}</legend>
                
                {notifydisplayhooks eventname='polls.ui_hooks.p.form_delete' id=$pollid}
				
			   <div class="z-formbuttons z-buttons">
					{button src='button_ok.png' set='icons/extrasmall' __alt='Delete' __title='Delete' __text="Delete"}
					<a href="{modurl modname=$module type='admin' func='view'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
				</div>

            </fieldset>
        </div>
    </form>
</div>
