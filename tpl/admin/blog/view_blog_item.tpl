{include file="elib:/admin/admin_header.tpl"}


<div class="entry">
<h2>{$blog->heading} <span>|</span> {$blog->stamp|date_format:"%A %e %B %Y"} <span>|</span> {$author}</h2>
{$blog->body|replace:"</p>":"</p>\n"}
</div>

{if $blog->status eq 1}
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/edit_blog/{$blog->id}" method="get">
    <p><button class="btn btn-sm btn-primary" type="submit" name="edit">Edit</button></p>
</form>

<form class="confirm" action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/publish/{$blog->id}" method="get">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="stamp" value="1" />
        <label class="form-check-label" for="stamp">
            Update Timestamp?
        </label>
    </div>
    <p>&nbsp;</p>
    <p>
        <button class="btn btn-sm btn-primary" type="submit" name="edit">Publish</button>
    </p>
</form>

{else}
{if $blog->status eq 2 || $blog->status eq 3}
<form class="confirm" action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/redraft/{$blog->id}" method="get">
    <p><button class="btn btn-sm btn-primary" type="submit" name="edit">Redraft</button></p>
</form>
{/if}
{if $blog->status eq 2}
<form class="confirm" action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/delete/{$blog->id}" method="get">
    <p><button class="btn btn-sm btn-primary" name="delete[]" type="submit">Delete</button></p>
</form>
{/if}
{/if}



<h2>Images</h2>

{if $blog->status eq 1}

    {if $error neq ''}
        <p>&nbsp;</p>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
                <p>{$error}</p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    {/if}


<form action="" method="post" enctype="multipart/form-data">
<p><label for="file">File</label>
<input class="form-control-file" type="file" id="file" name="file" /></p>
<p>
<input type="hidden" name="id" value="{$blog->id}" />
<button  class="btn btn-sm btn-primary" type="submit" name="upload_image" value="1">Upload</button>
</p>
</form>
{/if}

{if sizeof($images) > 0}
{foreach from=$images item=image}
<p><img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/uploads/tn_{$image.filename}" alt="" /></p>
<p><a class="confirm" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/remove_image/{$image.id}">Delete</a></p>
{/foreach}
{/if}



<p>&nbsp;</p>


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

{if sizeof($attachments) > 0}
<ul>
{foreach from=$attachments item=a}
<li><a target="_blank" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/episodes/{$a.filename}">{$a.filename}</a> - <a class="confirm" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/remove_attachment/{$a.id}">Delete</a></li>
{/foreach}
</ul>
{/if}



{include file="elib:/admin/admin_footer.tpl"}
