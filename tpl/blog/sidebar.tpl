<div class="col-sm-4 col-sm-offset-1 blog-sidebar">

    <div class="sidebar-module text-center">
        <span class="rss">
            <a rel="nofollow" target="_blank" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/feed">
                <i class="fa-solid fa-rss"></i>
            </a>
        </span>
    </div>

    {if isset($about)}
    <div class="sidebar-module sidebar-module-inset">
        <h4>About</h4>
        {assign var="about_trunc" value=$about|truncate:500:"...":false:false:true}
        {$about_trunc}
        {if $about|strip_tags|count_characters:true > 500}
            <p><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/about">more</a></p>
        {/if}
    </div>
    {/if}


    {include file="elib:blog/comp_tags.tpl"}


    <div class="sidebar-module">
        <h4>Elsewhere</h4>
        <ol class="list-unstyled">
            {foreach from=$social item=link key=name}
                <li><a rel="nofollow" target="_blank" href="{$link}">{$name}</a></li>
            {/foreach}
        </ol>
    </div>
</div><!-- /.blog-sidebar -->