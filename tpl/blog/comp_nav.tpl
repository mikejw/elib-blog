
<ul>
    <li class="button">
        {if $module eq 'blog' and $event eq 'default_event'}

        Blog <i class="fa fa-coffee"></i>
        {else}
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/?cat_id={$def_category}">
            Blog <i class="fa fa-coffee"></i>
        </a>
        {/if}
    </li>
    <li  class="button">

        {if $module eq 'about' and $event eq 'default_event'}

        About <i class="fa fa-question"></i>
        {else}
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/about">
            About <i class="fa fa-question"></i>
        </a>
        {/if}
    </li>
    <li class="button">
        {if $module eq 'projects' and $event eq 'default_event'}
        Projects <i class="fa fa-rocket"></i>
        {else}
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/projects">
            Projects <i class="fa fa-rocket"></i>
        </a>
        {/if}
    </li>

{*<li>
<a class="button{if $module eq 'music'} active{/if}" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/music">Music</a>
</li>
*}

    <li class="button">
        {if $module eq 'contact' and $event eq 'default_event'}

        Contact <i class="fa fa-phone"></i>
        {else}
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/contact">
            Contact <i class="fa fa-phone"></i>
        </a>
        {/if}
    </li>
</ul>
<p>&nbsp;</p>

