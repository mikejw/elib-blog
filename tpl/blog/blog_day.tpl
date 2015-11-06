
{include file="header.tpl"}

<div id="content_inner">

<p id="breadcrumb"><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}">Blog</a> &raquo;
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$year}">{$year}</a> &raquo;
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$year}/{$month_slug}">{$month}</a> &raquo;
{$day_name}, {$day}{$suffix}</p>

<h2>Archive for {$day_name}, {$day}{$suffix} {$month} {$year}</h2>

<div id="archive">

{section name=blog_item loop=$blogs}
<div class="entry">
<h2>
<span class="stamp">
<span class="pipe">|</span> {$blogs[blog_item].stamp|date_format:"%d/%m/%Y, %k:%M"}</span>
{if $blogs[blog_item].slug neq ''}
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$blogs[blog_item].stamp|date_format:"%Y"}/{$blogs[blog_item].month_slug}/{$blogs[blog_item].stamp|date_format:"%d"}/{$blogs[blog_item].slug}">{$blogs[blog_item].heading}</a>
{else}
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/item/{$blogs[blog_item].blog_id}">{$blogs[blog_item].heading}</a>
{/if}
</h2>
<div class="inner">
{$blogs[blog_item].body}
{if $blogs[blog_item].truncated eq 1}
<p>
{if $blogs[blog_item].slug neq ''}
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$blogs[blog_item].stamp|date_format:"%Y"}/{$blogs[blog_item].month_slug}/{$blogs[blog_item].stamp|date_format:"%d"}/{$blogs[blog_item].slug}">Read More...</a>
{else}
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/item/{$blogs[blog_item].blog_id}">Read More...</a>
{/if}
</p>
{/if}
</div>
</div>
{/section}

</div>
</div>








{include file="footer.tpl"}
