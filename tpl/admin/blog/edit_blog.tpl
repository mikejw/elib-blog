{include file="elib:/admin/admin_header.tpl"}


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

{if count($revisions) > 1}
<div class="row revision-select">
    <div class="col-4">Load revision:</div>
    <div class="col-8">
        {html_options options=$revisions selected=$revision name="revision" class="custom-select"}
    </div>
</div>
{/if}

<form action="" method="post" data-id={$blog->id}>
    <div class="form-group">
        <label for="heading">Heading</label>
        <input name="heading" type="text" class="form-control" placeholder="Enter heading" value="{$blog->heading}">
    </div>
    <div class="form-group">
        <label for="body">Body</label>
        <textarea rows="30" cols="0" name="body">{$blog->body|blog_images:$WEB_ROOT:$PUBLIC_DIR|escape}</textarea>
    </div>
    <div class="form-group">
        <label for="category">Category</label>
        <select class="form-control" name="category[]" multiple="yes">
            {html_options options=$cats selected=$blog_cats}
        </select>
    </div>

    <div class="form-group">
        <label for="tags">Tags (Comma separated.)</label>
        <input name="tags" type="text" class="form-control" placeholder="Enter tags" value="{$blog_tags}">
    </div>
    <div class="form-group">
        <label for="slug">Friendly URL 'Slug'</label>
        <input name="slug" type="text" class="form-control" placeholder="Enter slug" value="{$blog->slug}">
    </div>

    <input type="hidden" name="id" value="{$blog->id}" />
    <button type="submit" name="save" class="btn btn-primary">Submit</button>
    <button type="submit" name="cancel" class="btn btn-primary">Cancel</button>
</form>


{include file="elib:/admin/admin_footer.tpl"}
