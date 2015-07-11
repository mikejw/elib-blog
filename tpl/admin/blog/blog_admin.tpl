{include file="elib:/admin/admin_header.tpl"}



<div id="operations">
<div class="grey_top">
<div class="top_right">
<div class="top_left"></div>
</div>
</div>

<div class="grey" style="padding:0.5em;">

<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/category" method="get">
<div><button type="submit" name="edit_categories" value="1">Edit Categories</button></div>
</form>


</div>
<div class="grey_bottom">
<div class="bottom_right">
<div class="bottom_left"></div>
</div>
</div>
</div>


<p style="line-height: 0.5em;">&nbsp;</p>




<div id="yuk_tabbed_nav">
<ul>
<li{if $status eq '1'} class="active"{/if}><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/?status=1&amp;page=1">Drafts</a></li>
<li{if $status eq '2'} class="active"{/if}><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/?status=2&amp;page=1">Published</a></li>
{if $super eq 1}<li{if $status eq '3'} class="active"{/if}><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/?status=3&amp;page=1">Deleted</a></li>{/if}
</ul>
</div>



<!--
<div class="grey_top">
<div class="top_right">
<div class="top_left"></div>
</div>
</div>-->

<div class="grey" style="border-top: 0; -moz-border-radius-topleft: 0; -webkit-border-top-left-radius: 0; -moz-border-radius-topright: 0; -webkit-border-top-right-radius: 0;">

{if sizeof($blogs) < 1}
<p>Nothing to display.</p>
{else}
<table>
<tr>
<th>id</th>
<th>Heading</th>
<th>Body</th>
<th>Stamp</th>
<th>Category</th>
{if $super eq 1}
<th>Author</th>
{/if}
<th>&nbsp;</th>
</tr>
{section name=blog_item loop=$blogs}
<tr class="{cycle values="alt, }">
<td class="id">{$blogs[blog_item].id}</td>
<td><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/view/{$blogs[blog_item].id}">{$blogs[blog_item].heading}</a></td>
<td>{$blogs[blog_item].body|strip_tags|truncate:30:"..."}</td>
<td>{$blogs[blog_item].stamp|date_format:"%d/%m/%y @ %T"}</td>
<td>{$blogs[blog_item].category}</td>
{if $super eq 1}
<td>{$blogs[blog_item].username}</td>
{/if}
<td>
&nbsp;
</td>
</tr>
{/section}
</table>
{/if}

{if sizeof($p_nav) > 1}
<div id="p_nav">
<p>
{foreach from=$p_nav key=k item=v}
{if $v eq 1}<span>{$k}</span>
{else}
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/?page={$k}">{$k}</a>
{/if}
{/foreach}
</p>
</div>
{else}
<p>&nbsp;</p>
{/if}



</div>

<div class="grey_bottom">
<div class="bottom_right">
<div class="bottom_left"></div>
</div>
</div>

{if $status eq 1}
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/create" method="get">
<p><button name="create" type="submit">Create New</button></p>
</form>
{/if}



{include file="elib:/admin/admin_footer.tpl"}