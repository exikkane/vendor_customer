{if $auth.user_type == 'V'}
    <div class=" btn-bar btn-toolbar nav__actions-bar" id="tools_manage_users_buttons">
        <a class="btn btn-primary cm-tooltip nav__actions-btn-secondary" href="{"exim.import&section=vendor_customers"|fn_url}" title="{__("vendor_customers_import")}">
            {__("import")}
        </a>

        <a class="btn btn-secondary cm-tooltip nav__actions-btn-primary" href="{"profiles.add?user_type=N"|fn_url}" title="{__("add_user")}">
            {include_ext file="common/icon.tpl" class="icon-plus"}
            {__("add_user")}
        </a>
    </div>
{/if}