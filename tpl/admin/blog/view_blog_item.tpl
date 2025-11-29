{include file="elib:admin/admin_header.tpl"}

<div class="mb-4 mt-4 form-group cms-actions">

        {if $blog->status eq 2 || $blog->status eq 3}
            <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/redraft/{$blog->id}" class="confirm btn btn-sm btn-primary">
               Redraft
            </a>
        {/if}
        {if $blog->status eq 2}
            <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/delete/{$blog->id}" class="confirm btn btn-sm btn-primary">
                Delete
            </a>
        {/if}

        {if $blog->status eq 1}
            <a data-bs-toggle="lightbox" data-disable-external-check="true" data-type="url" data-remote="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/preview/{$blog->id}" class="btn btn-sm btn-primary">
                Preview
            </a>
        {/if}

        {if $blog->status eq 1}
            <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/edit_blog/{$blog->id}" class="btn btn-sm btn-primary">
                Edit
            </a>
            <form class="confirm d-inline" action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/publish/{$blog->id}" method="get">
                <button class="btn btn-sm btn-primary" type="submit" name="edit">Publish</button>
                <div class="form-check d-inline">
                    <label class="form-check-label" for="stamp">
                        <input class="form-check-input" type="checkbox" name="stamp" value="1" />
                        Update Timestamp?
                    </label>
                </div>
            </form>
        {/if}

</div>


<div class="entry">
    <h2 class="mb-4 mt-4">{$blog->heading} <span>|</span> {$blog->stamp|date_format:"%A %e %B %Y"} <span>|</span> {$author}</h2>
    {$blog->body|blog_images:$WEB_ROOT:$PUBLIC_DIR}

</div>



<h2 class="mb-4 mt-5">Images</h2>

{if $blog->status eq 1}

    {if $errors neq ''}
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Error</strong>
                <p>{$error}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    {/if}

    <form method="post" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="file" class="form-label">Choose file</label>
            <input
                type="file"
                class="form-control"
                id="file"
                name="file"
            >
        </div>
        <input type="hidden" name="id" value="{$blog->id}" />
        <button
            type="submit"
            class="btn btn-primary mb-4"
            name="upload_image"
            value="1"
        >
            Upload
        </button>
    </form>

{/if}

{if count($images) > 0}
    {foreach from=$images item=image}
        <p class="mt-4"><img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/uploads/tn_{$image.filename}" alt="" /></p>
        <p><a class="confirm" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/remove_image/{$image.id}">Delete</a></p>
    {/foreach}
{/if}

{*
<h2>Attachments</h2>

{if $blog->status eq 1}
{if $error neq ''}
<ul id="error">
<li>{$error}</li>
</ul>
{/if}

<div id="video_upload">
<form action="" method="post" enctype="multipart/form-data">
<p><label for="file">File</label>
<input class="form-control-file" type="file" name="file" /></p>
<p>
<input type="hidden" name="upload_attachment" value="true" />
<input type="hidden" name="id" value="{$blog->id}" />
<button class="btn btn-sm btn-primary" type="submit" name="upload_attachment" value="1">Upload</button>
</p>
</form>
</div>
{/if}

{if count($attachments) > 0}
<ul>
{foreach from=$attachments item=a}
<li><a target="_blank" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/episodes/{$a.filename}">{$a.filename}</a> - <a class="confirm" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/remove_attachment/{$a.id}">Delete</a></li>
{/foreach}
</ul>
{/if}

*}

{include file="elib:admin/admin_footer.tpl"}
