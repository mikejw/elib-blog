{include file="header.tpl"}


<div id="content_inner">

<p id="breadcrumb"><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}">Blog</a> &raquo;
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$year}">{$year}</a> &raquo;
{$month}</p>

<h2>Archive for {$month} {$year}</h2>


{if sizeof($blogs) > 0}
<ul>
{foreach from=$blogs item=i}
<li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$year}/{$month_slug}/{$i.day}">{$i.day_str}{$i.suffix}</a>:
{if $i.slug neq ''}
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$i.stamp|date_format:"%Y"}/{$i.month_slug}/{$i.stamp|date_format:"%d"}/{$i.slug}">{$i.heading}</a>
{else}
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/blog/item/{$i.id}">{$i.heading}</a>
{/if}
</li>

{*<li><a href="http://$WEB_ROOT}{$PUBLIC_DIR}/{$year}/{$slug}">{$m.month}</a> - {$m.count}</li>*}
{/foreach}
</ul>
{/if}

</div>

{include file="footer.tpl"}