{include file="header.tpl"}

<div class="pagination">&nbsp;</div>

<div class="row d-flex justify-content-between">
    <div class="col-sm-7 blog-main">

        {include file="elib://blog/comp_blog_heading.tpl"}

        {foreach from=$blogs item=blog_item}
            <div class="blog-post">
                <h2 class="blog-post-title">
                    {if $blog_item.slug neq ''}
                        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$blog_item.stamp|date_format:"%Y"}/{$blog_item.month_slug}/{$blog_item.stamp|date_format:"%d"}/{$blog_item.slug}">
                            {$blog_item.heading}
                        </a>
                    {else}
                        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/item/{$blog_item.blog_id}">
                            {$blog_item.heading}
                        </a>
                    {/if}
                </h2>

                <p class="blog-post-meta">
                    {$blog_item.stamp|sdate:$def_date_format}
                </p>

                {*<h3>{$blog_item.stamp|date_format:"%A %e %B, %Y"}</h3>*}
                <div class="content">
                    {$blog_item.body|blog_images:$WEB_ROOT:$PUBLIC_DIR}
                    {if $blog_item.truncated eq 1}
                        <p>
                            {if $blog_item.slug neq ''}
                                <a class="btn btn-default"
                                   href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$blog_item.stamp|date_format:"%Y"}/{$blog_item.month_slug}/{$blog_item.stamp|date_format:"%d"}/{$blog_item.slug}">
                                    Read more...
                                </a>
                            {else}
                                <a class="btn btn-default"
                                   href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/item/{$blog_item.blog_id}">
                                    Read more...
                                </a>
                            {/if}
                        </p>
                    {/if}
                </div>

                <p class="entry_meta">
                    {if $blog_item.slug neq ''}
                        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$blog_item.stamp|date_format:"%Y"}/{$blog_item.month_slug}/{$blog_item.stamp|date_format:"%d"}/{$blog_item.slug}{if $disqusUsername neq ''}#disqus_thread{/if}">Permalink</a>
                    {else}
                        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/item/{$blog_item.blog_id}{if $disqusUsername neq ''}#disqus_thread{/if}">Permalink</a>
                    {/if}

                    {if isset($blog_item.cats) && sizeof($blog_item.cats)}
                        <span class="sep">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                        Categories:
                        {foreach from=$blog_item.cats key=i item=c}
                            <span class="tag">
                                 <span class="badge badge-{if $i eq $blog_category}success{else}secondary{/if}">
                                    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/category/{$c|lower}">
                                        {$c}
                                    </a>
                            </span>
                        </span>
                        {/foreach}
                    {/if}

                    {if isset($blog_item.tags) and sizeof($blog_item.tags)}
                        <span class="sep">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                        Tags:
                        {foreach from=$blog_item.tags item=t}
                            <span class="tag">
                                <span class="badge badge-{if isset($active_tags) and sizeof($active_tags) and in_array($t, $active_tags)}info{else}secondary{/if}">
                                    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/tags/{$t}">
                                        {$t}
                                    </a>
                                </span>
                            </span>
                            {*
                            <a class="button{if isset($active_tags) and isset($$active_tgas) and sizeof($active_tags) and in_array($t, $active_tags)} active{/if}" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/tags/{$t}"><span class="label label-default">{$t}</span></a>
                            *}
                        {/foreach}
                    {/if}

                    {*
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    {$blog_item.comments} comment{if $blog_item.comments neq 1}s{/if}
                    *}

                </p>
            </div>
            {foreachelse}
            <p style="text-align: center;">No posts found.</p>
        {/foreach}



        {include file="elib:/blog/comp_blog_pagi.tpl"}


    </div><!-- /.blog-main -->

    {include file="elib:/blog/sidebar.tpl"}

</div><!-- /.row -->


{include file="footer.tpl"}