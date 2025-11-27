{if $auth.user_type == 'V' && $smarty.request.user_type == 'N'}
    <div class=" btn-bar btn-toolbar nav__actions-bar" id="tools_manage_users_buttons">
        <a class="btn btn-primary cm-tooltip nav__actions-btn-secondary" href="{"profiles.manage&user_type=V"|fn_url}" title="{__("vendors_administrators")}">
            {__("vendor_administrators")}
        </a>

        <a class="btn btn-primary cm-tooltip nav__actions-btn-secondary" href="{"exim.import&section=vendor_customers"|fn_url}" title="{__("vendor_customers_import")}">
            {__("import")}
        </a>

        <a class="btn btn-secondary cm-tooltip nav__actions-btn-primary" href="{"profiles.add?user_type=N"|fn_url}" title="{__("add_user")}">
            {include_ext file="common/icon.tpl" class="icon-plus"}
            {__("add_customer")}
        </a>
    </div>
{/if}
{if $smarty.request.user_type}
    <div class=" btn-bar btn-toolbar nav__actions-bar" id="tools_manage_users_buttons">
    {if $smarty.request.user_type == 'V'}

            <a class="btn btn-primary cm-tooltip nav__actions-btn-secondary" href="{"profiles.manage&user_type=N"|fn_url}" title="{__("vendor_customers")}">
                {__("vendor_customers")}
            </a>

    {/if}
    {if $can_add_user}
        <a class="btn btn-primary cm-tooltip nav__actions-btn-primary" href="{"profiles.add?user_type=`$smarty.request.user_type`"|fn_url}" title="{__("add_user")}">
            {include_ext file="common/icon.tpl" class="icon-plus"}
        {if $auth.user_type == 'V'}
            {__("add_customer")}
        {else}
            {__("add_user")}
        {/if}
        </a>
    {/if}
    </div>
{/if}