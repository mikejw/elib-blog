{include file="elib:admin/admin_header.tpl"}



<div id="operations">
<div class="grey_top">
<div class="top_right">
<div class="top_left"></div>
</div>
</div>

<div class="grey" style="padding:0.5em;">

<form style="display: inline;" action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/add_cat/{$blog_cat_id}" method="get">
    <button class="btn btn-sm btn-primary" type="submit" name="add_cat" value="1"{if $class neq 'blog_cat'}disabled="disabled"{/if}>Add Category</button>
</form>
<form style="display: inline;" action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/rename_category/{$blog_cat_id}" method="get">
    <button class="btn btn-sm btn-primary" type="submit" name="rename" value="1"{if $class neq 'blog_cat' || $event eq 'rename'}disabled="disabled"{/if}>Rename</button>
</form>
<form style="display: inline;" class="confirm" action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/delete_category/{$blog_cat_id}" method="get">
    <button class="btn btn-sm btn-primary" type="submit" name="delete" value="1"{if $blog_cat_id eq 0} disabled="disabled"{/if}>Delete</button>
</form>

<form style="display: inline;" action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/edit_cat_meta/{$blog_cat_id}" method="get">
    <button class="btn btn-sm btn-primary" type="submit" name="delete" value="1"{if $blog_cat_id eq 0} disabled="disabled"{/if}>Edit Meta</button>
</form>





{*
{if $class eq 'section'}
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/section/add_section/{$section_id}" method="get">
<div><button type="submit" name="add_section" value="1">Add Section</button></div>
</form>
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/section/add_data/{$section_id}" method="get">
<div><button type="submit" name="add_data_item" value="1"{if $event eq 'add_data'} disabled="disabled"{/if}>Add Data</button></div>
</form>
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/section/delete/{$section_id}" method="get">
<div><button type="submit" name="delete_section" value="1"{if $section_id eq 0} disabled="disabled"{/if}>Delete</button></div>
</form>
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/section/rename/{$section_id}" method="get">
<div><button type="submit" name="rename" value="1"{if $section->id eq 0 || $event eq 'rename'} disabled="disabled"{/if}>Rename</button></div>
</form>
{elseif $class eq 'data_item'}
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/section/add_section/{$section_id}" method="get">
<div><button type="submit" name="add_section" value="1" disabled="disabled">Add Section</button></div>
</form>
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/data_item/add_data/{$data_item_id}" method="get">
<div><button type="submit" name="add_data_item" value="1"{if $event eq 'add_data'} disabled="disabled"{/if}>Add Data</button></div>
</form>
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/data_item/delete/{$data_item_id}" method="get">
<div><button type="submit" name="delete_data_item" value="1">Delete</button></div>
</form>
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/data_item/rename/{$data_item_id}" method="get">
<div><button type="submit" name="rename" value="1"{if $event eq 'rename'} disabled="disabled"{/if}>Rename</button></div>
</form>
{/if}
*}

</div>
<div class="grey_bottom">
<div class="bottom_right">
<div class="bottom_left"></div>
</div>
</div>
</div>


<p style="line-height: 0.5em;">&nbsp;</p>





{if $blog_cat_id != 0 || !isset($blog_cat_id)}
<p><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/category/0">Top Level</a></p>
{/if}


{$banners}



<div id="right">

    {if isset($errors)}
        <p>&nbsp;</p>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            {foreach from=$errors item=e}
                <p>{$e}</p>
            {/foreach}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    {/if}

{if $class eq 'blog_cat'}
{if $event eq 'rename'}
{include file="elib:admin/blog/rename.tpl"}
{/if}
{/if}

</div>



{include file="elib:admin/admin_footer.tpl"}
