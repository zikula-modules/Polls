{include file="polls_admin_menu.tpl"}
{gt text="Modify poll" assign="templatetitle"}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='edit.png' set='icons/large' alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    <form id="polls_admin_modifyform" class="z-form" action="{modurl modname=Polls type=admin func=update}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
			<input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
            <input type="hidden" name="poll[pollid]" value="{$item.pollid}" />
            <fieldset>
                <legend>{gt text="Overview"}</legend>
                <div class="z-formrow">
                    <label for="polls_title">{gt text="Title"}</label>
                    <input id="polls_title" name="poll[title]" type="text" size="32" maxlength="100" value="{$item.title|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="pages_urltitle">{gt text="PermaLink URL title"}</label>
                    <input id="pages_urltitle" name="poll[urltitle]" type="text" size="32" maxlength="255" value="{$item.urltitle|safetext}" />
                    <em class="z-sub z-formnote">{gt text="(Blank = auto-generate)"}</em>
                </div>

                {if $modvars.Polls.enablecategorization}
                <div class="z-formrow">
                    <label>{gt text="Category"}</label>
                    {nocache}
                    {foreach from=$catregistry key=property item=category}
                    {array_field_isset array=$item.__CATEGORIES__ field=$property assign="catExists"}
                    {if $catExists}
                    {array_field_isset array=$item.__CATEGORIES__.$property field="id" returnValue=1 assign="selectedValue"}
                    {else}
                    {assign var="selectedValue" value="0"}
                    {/if}
                    <div class="z-formlist">{selector_category category=$category name="poll[__CATEGORIES__][$property]" field="id" selectedValue=$selectedValue defaultValue="0" __defaultText="Choose Category"}</div>
                    {/foreach}
                    {/nocache}
                </div>
                {/if}

                {if $modvars.ZConfig.multilingual}
                <div class="z-formrow">
                    <label for="polls_language">{gt text="Language"}</label>
                    {html_select_languages id=polls_language name=poll[language] all=true installed=true selected=$item.language}
                </div>
                {/if}
            </fieldset>

            <fieldset>
                <legend>{gt text="Voting choices"}</legend>
                <p class="z-formnote z-informationmsg">{gt text="Please enter each available choice into a single field"}</p>
                <div class="z-formrow">
                    <label  for="polls_polloption1">{gt text="Option"} 1</label>
                    <input id="polls_polloption1" type="text" name="poll[options][1]" size="255" maxlength="255" value="{$item.options.0.optiontext|safetext}" />
                </div>
                <div class="z-formrow">
                    <label  for="polls_polloption2">{gt text="Option"} 2</label>
                    <input id="polls_polloption2" type="text" name="poll[options][2]" size="255" maxlength="255" value="{$item.options.1.optiontext|safetext}" />
                </div>
                <div class="z-formrow">
                    <label  for="polls_polloption3">{gt text="Option"} 3</label>
                    <input id="polls_polloption3" type="text" name="poll[options][3]" size="255" maxlength="255" value="{$item.options.2.optiontext|safetext}" />
                </div>
                <div class="z-formrow">
                    <label  for="polls_polloption4">{gt text="Option"} 4</label>
                    <input id="polls_polloption4" type="text" name="poll[options][4]" size="255" maxlength="255" value="{$item.options.3.optiontext|safetext}" />
                </div>
                <div class="z-formrow">
                    <label  for="polls_polloption5">{gt text="Option"} 5</label>
                    <input id="polls_polloption5" type="text" name="poll[options][5]" size="255" maxlength="255" value="{$item.options.4.optiontext|safetext}" />
                </div>
                <div class="z-formrow">
                    <label  for="polls_polloption6">{gt text="Option"} 6</label>
                    <input id="polls_polloption6" type="text" name="poll[options][6]" size="255" maxlength="255" value="{$item.options.5.optiontext|safetext}" />
                </div>
                <div class="z-formrow">
                    <label  for="polls_polloption7">{gt text="Option"} 7</label>
                    <input id="polls_polloption7" type="text" name="poll[options][7]" size="255" maxlength="255" value="{$item.options.6.optiontext|safetext}" />
                </div>
                <div class="z-formrow">
                    <label  for="polls_polloption8">{gt text="Option"} 8</label>
                    <input id="polls_polloption8" type="text" name="poll[options][8]" size="255" maxlength="255" value="{$item.options.7.optiontext|safetext}" />
                </div>
                <div class="z-formrow">
                    <label  for="polls_polloption9">{gt text="Option"} 9</label>
                    <input id="polls_polloption9" type="text" name="poll[options][9]" size="255" maxlength="255" value="{$item.options.8.optiontext|safetext}" />
                </div>
                <div class="z-formrow">
                    <label  for="polls_polloption10">{gt text="Option"} 10</label>
                    <input id="polls_polloption10" type="text" name="poll[options][10]" size="255" maxlength="255" value="{$item.options.9.optiontext|safetext}" />
                </div>
                <div class="z-formrow">
                    <label  for="polls_polloption11">{gt text="Option"} 11</label>
                    <input id="polls_polloption11" type="text" name="poll[options][11]" size="255" maxlength="255" value="{$item.options.10.optiontext|safetext}" />
                </div>
                <div class="z-formrow">
                    <label  for="polls_polloption12">{gt text="Option"} 12</label>
                    <input id="polls_polloption12" type="text" name="poll[options][12]" size="255" maxlength="255" value="{$item.options.11.optiontext|safetext}" />
                </div>
            </fieldset>
            
            {notifydisplayhooks eventname='polls.ui_hooks.p.form_edit' id=$item.pollid}

            <fieldset>
                <legend>{gt text="Meta data"}</legend>
                <ul>
                    {usergetvar name=uname uid=$item.cr_uid assign=username}
                    <li>{gt text="Created by %s" tag1=$username}</li>
                    <li>{gt text="Created on %s" tag1=$item.cr_date|dateformat}</li>
                    {usergetvar name=uname uid=$item.lu_uid assign=username}
                    <li>{gt text="Last updated by %s" tag1=$username}</li>
                    <li>{gt text="Updated on %s" tag1=$item.lu_date|dateformat}</li>
                </ul>
            </fieldset>
            
			<div class="z-formbuttons z-buttons">
				{button src='button_ok.png' set='icons/extrasmall' __alt='Save' __title='Save' __text="Save"}
				<a href="{modurl modname=$module type='admin' func='view'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
			</div>
			
        </div>
    </form>
</div>

