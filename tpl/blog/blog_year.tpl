{include file="header.tpl"}


<div id="content_inner">

<p id="breadcrumb"><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}">Blog</a> &raquo; {$year}</p>

<h2>Archive for {$year}</h2>


{if count($months) > 0}
<ul>
{foreach from=$months item=m key=slug}
<li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$year}/{$slug}">{$m.month}</a> - {$m.count} item{if $m.count > 1}s{/if}</li>
{/foreach}
</ul>
{/if}

</div>

{include file="footer.tpl"}