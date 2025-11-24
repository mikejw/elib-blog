

{*<p><a href="http://instagram.com/mikeyjw"><img id="me" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/img/mikewhiting.jpg" alt="" /></a></p>*}

<div id="categories" class="sidebar-module">
    <h4>Category</h4>

    <!-- @todo: put font-awesome icons in CMS -->

    <ul class="clear">
        {foreach from=$categories item=c}
            <li>
                {if $c.id eq $blog_category}
                    <i class="me-1 fa fa-{$c.label_icon}" aria-hidden="true"></i> {$c.label}
                {else}
                    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/category/{$c.label|lower}/">
                        <i class="me-1 fa fa-{$c.label_icon}" aria-hidden="true"></i> {$c.label}
                    </a>
                {/if}
            </li>
        {/foreach}
    </ul>
</div>

<div class="tags sidebar-module">
    <div id="tags_collapsible" class="clear">
        <h4>Tags</h4>
        {foreach from=$tags key=k item=tag}
            <a style="font-size:{$tags[$k].size}rem;"
               class="{if is_array($active_tags) && in_array($tags[$k].tag, $active_tags)}text-info active font-weight-bold{/if}"
               href="http://{$WEB_ROOT}{$PUBLIC_DIR}{if $active_tags_string eq ''}/tags/{$tags[$k].tag}{elseif in_array($tags[$k].tag, $active_tags) and count($active_tags) eq 1}/blog{elseif in_array($tags[$k].tag, $active_tags)}/tags/{$active_tags_string|regex_replace:$tags[$k].tag_esc_1:''|regex_replace:$tags[$k].tag_esc_2:''|replace:$tags[$k].tag:''}{else}/tags/{$active_tags_string}+{$tags[$k].tag}{/if}">{$tags[$k].tag}</a>
        {/foreach}

        {if $active_tags_string neq ''}
            <p>&nbsp;</p>
            <p style="text-align: center;">
                <a class="btn btn-default" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog">
                    [ Clear active tag{if $multi_tags}s{/if} ]
                </a>
            </p>
        {/if}
    </div>
</div>


{if isset($archive)}
    <div id="archive" class="sidebar-module">

        <h4>Archive</h4>

        {if count($archive) lt 1}
            <p>None found.</p>
        {else}
            <nav>
                <ul>
                    {foreach from=$archive item=y key=year}
                        <li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$year}">{$year}</a>
                            {*<ul{if $year eq $blog->stamp|date_format:"%Y"} class="current"{/if}>*}
                            <ul{if $year eq $current_year || $year eq $blog->stamp|date_format:"%Y"} class="current"{/if}>
                                {foreach from=$y item=m key=month}
                                    <li>
                                        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$year}/{substr($month, 0, 3)|lower}">{$month}</a>
                                        <ul{if ($month eq $blog->stamp|date_format:"%B" && $year eq $blog->stamp|date_format:"%Y") ||
                                        ($month eq $current_month && $year eq $current_year)} class="current"{/if}>
                                            {foreach from=$m item=b key=id}
                                                {if $id neq $blog->id}

                                                    {if $b.slug neq ''}
                                                        <li>
                                                            <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$year}/{$b.month_slug}/{$b.day}/{$b.slug}">{$b.heading}</a>
                                                        </li>
                                                    {else}
                                                        <li>
                                                            <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/item/{$id}">{$b.heading}</a>
                                                        </li>
                                                    {/if}

                                                {else}
                                                    <li>{$b.heading}</li>
                                                {/if}
                                            {/foreach}
                                        </ul>
                                    </li>
                                {/foreach}
                            </ul>
                        </li>
                    {/foreach}
                </ul>
            </nav>
        {/if}
    </div>
{/if}




