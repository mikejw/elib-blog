{include file="elib:admin/admin_header.tpl"}


<div class="form-group cms-actions">

    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/category" class="btn btn-sm btn-primary">
        Edit Categories
    </a>

    {if $status eq 1}
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/create" class="btn btn-sm btn-primary">
            Create New
        </a>
    {/if}
</div>


<p style="line-height: 0.5em;">&nbsp;</p>


<ul class="nav nav-tabs">
<li role="presentation"><a class="nav-link {if $status eq '1'}active{/if}" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/?status=1&amp;page=1">Drafts</a></li>
<li role="presentation"><a class="nav-link {if $status eq '2'}active{/if}" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/?status=2&amp;page=1">Published</a></li>
{if $super eq 1}
<li role="presentation"><a class="nav-link {if $status eq '3'}active{/if}" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/?status=3&amp;page=1">Deleted</a></li>{/if}
</ul>

<p style="line-height: 0.5em;">&nbsp;</p>



{if count($blogs) < 1}
<p>Nothing to display.</p>
{else}
<table class="table">
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
<tr class="{cycle values="alt," }">
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


{include file="elib:comp_pagination.tpl"}






{include file="elib:admin/admin_footer.tpl"}