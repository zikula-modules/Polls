{include file="polls_admin_menu.tpl"}
{gt text="Create new poll" assign=templatetitle}

<div class="z-admincontainer">
	<div class="z-adminpageicon">{img modname='core' src='edit_add.png' set='icons/large' alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    <form id="polls_admin_newform" class="z-form" action="{modurl modname="Polls" type="admin" func="create"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
			<input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
            <fieldset>
                <legend>{gt text="Overview"}</legend>
                <div class="z-formrow">
                    <label for="polls_title">{gt text="Title"}</label>
                    <input id="polls_title" type="text" name="poll[title]" size="32" maxlength="100" />
                </div>
                <div class="z-formrow">
                    <label for="pages_urltitle">{gt text="PermaLink URL title"}</label>
                    <input id="pages_urltitle" name="poll[urltitle]" type="text" size="32" maxlength="255" />
                    <em class="z-sub z-formnote">{gt text="(Blank = auto-generate)"}</em>
                </div>
                
                {if $modvars.Polls.enablecategorization}
                <div class="z-formrow">
                    <label>{gt text="Category"}</label>
                    {nocache}
                        {foreach from=$catregistry key=property item=category}
                        <div class="z-formlist">{selector_category category=$category name="poll[__CATEGORIES__][$property]" field="id" selectedValue="0" defaultValue="0" __defaultText="Choose Category"}</div>
                        {/foreach}
                    {/nocache}
                </div>
                {/if}
                
                {if $modvars.ZConfig.multilingual}
                <div class="z-formrow">
                    <label for="polls_language">{gt text="Language"}</label>
                    {html_select_languages id=polls_language name=poll[language] all=true installed=true selected=$lang}
                </div>
                {/if}
            </fieldset>
            
            <fieldset>
                <legend>{gt text="Voting choices"}</legend>
                <p class="z-formnote z-informationmsg">{gt text="Please enter each available choice into a single field"}</p>
                <div class="z-formrow">
                    <label for="polls_polloption1">{gt text="Option"} 1</label>
                    <input id="polls_polloption1" type="text" name="poll[options][1]" size="255" maxlength="255" />
                </div>
                <div class="z-formrow">
                    <label for="polls_polloption2">{gt text="Option"} 2</label>
                    <input id="polls_polloption2" type="text" name="poll[options][2]" size="255" maxlength="255" />
                </div>
                <div class="z-formrow">
                    <label for="polls_polloption3">{gt text="Option"} 3</label>
                    <input id="polls_polloption3" type="text" name="poll[options][3]" size="255" maxlength="255" />
                </div>
                <div class="z-formrow">
                    <label for="polls_polloption4">{gt text="Option"} 4</label>
                    <input id="polls_polloption4" type="text" name="poll[options][4]" size="255" maxlength="255" />
                </div>
                <div class="z-formrow">
                    <label for="polls_polloption5">{gt text="Option"} 5</label>
                    <input id="polls_polloption5" type="text" name="poll[options][5]" size="255" maxlength="255" />
                </div>
                <div class="z-formrow">
                    <label for="polls_polloption6">{gt text="Option"} 6</label>
                    <input id="polls_polloption6" type="text" name="poll[options][6]" size="255" maxlength="255" />
                </div>
                <div class="z-formrow">
                    <label for="polls_polloption7">{gt text="Option"} 7</label>
                    <input id="polls_polloption7" type="text" name="poll[options][7]" size="255" maxlength="255" />
                </div>
                <div class="z-formrow">
                    <label for="polls_polloption8">{gt text="Option"} 8</label>
                    <input id="polls_polloption8" type="text" name="poll[options][8]" size="255" maxlength="255" />
                </div>
                <div class="z-formrow">
                    <label for="polls_polloption9">{gt text="Option"} 9</label>
                    <input id="polls_polloption9" type="text" name="poll[options][9]" size="255" maxlength="255" />
                </div>
                <div class="z-formrow">
                    <label for="polls_polloption10">{gt text="Option"} 10</label>
                    <input id="polls_polloption10" type="text" name="poll[options][10]" size="255" maxlength="255" />
                </div>
                <div class="z-formrow">
                    <label for="polls_polloption11">{gt text="Option"} 11</label>
                    <input id="polls_polloption11" type="text" name="poll[options][11]" size="255" maxlength="255" />
                </div>
                <div class="z-formrow">
                    <label for="polls_polloption12">{gt text="Option"} 12</label>
                    <input id="polls_polloption12" type="text" name="poll[options][12]" size="255" maxlength="255" />
                </div>
            </fieldset>
            
			<div class="z-formbuttons z-buttons">
				{button src='button_ok.png' set='icons/extrasmall' __alt='Save' __title='Save' __text="Save"}
				<a href="{modurl modname=$module type='admin' func='view'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
			</div>
			
        </div>
    </form>
</div>
