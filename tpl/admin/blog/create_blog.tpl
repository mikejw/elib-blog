{include file="elib:admin/admin_header.tpl"}

{if isset($errors)}
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Error</strong>
        {foreach from=$errors item=e}
            <p>{$e}</p>
        {/foreach}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}



<form class="mt-4" action="" method="post">
    <div class="mb-3">
        <label class="form-label" for="heading">Heading</label>
        <input name="heading" type="text" class="form-control" placeholder="Enter heading" value="{$blog->heading}">
    </div>
    <div class="mb-3">
        <label class="form-label" for="body">Body</label>
        {*
        <textarea rows="0" cols="0" name="body">{$blog->body|replace:'<br />':"\r\n"}</textarea>
        *}
	    <textarea rows="20" name="body">{$blog->body|replace:'</p><p>':"\r\n"|replace:'<p>':""|replace:'</p>':""}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label" for="category">Category</label>
        <select class="form-control" name="category[]" multiple="yes">
            {html_options options=$cats selected=$blog_cats}
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label" for="tags">Tags (Comma separated.)</label>
        <input name="tags" type="text" class="form-control" placeholder="Enter tags" value="{$blog_tags}">
    </div>
    <div class="mb-3">
        <label class="form-label" for="slug">Friendly URL 'Slug'</label>
        <input name="slug" type="text" class="form-control" placeholder="Enter slug" value="{$blog->slug}">
    </div>
    <div class="mb-3">
        <input type="hidden" name="id" value="{$blog->id}" />
        <button type="submit" name="save" class="btn btn-primary btn-sm">Submit</button>
        <button type="submit" name="cancel" class="btn btn-primary btn-sm">Cancel</button>
        <p><br /><br />New items will be automatically saved to drafts.</p>
    </div>
</form>







{include file="elib:admin/admin_footer.tpl"}