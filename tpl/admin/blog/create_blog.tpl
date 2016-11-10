{include file="elib:/admin/admin_header.tpl"}



  {if isset($errors)}
	<div class="alert alert-danger alert-dismissible" role="alert">
  	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  	<strong>Error!</strong>
  		{foreach from=$errors item=e} 
  			<p>{$e}</p>
  		{/foreach}
	</div>

    {/if}


<form action="" method="post">
<fieldset>
<legend>Create Blog Item</legend>
<p>
<label>Heading</label>
<input class="form-control" type="text" name="heading" value="{$blog->heading}" />
</p>
<p>
<label>Body</label>
{*
<textarea rows="0" cols="0" name="body">{$blog->body|replace:'<br />':"\r\n"}</textarea>
*}
<textarea class="form-control" rows="0" cols="0" name="body">{$blog->body|replace:'</p><p>':"\r\n"|replace:'<p>':""|replace:'</p>':""}</textarea>

</p>
<p>
<label>Tags</label>
<input  class="form-control" type="text" name="tags" value="{$blog_tags}" />
</p>
<p>
<label>Category</label>
<select  class="form-control" name="category[]" multiple="yes">
{html_options options=$cats selected=$blog_cats}
</select>
</p>
<p>
<label>Friendly URL 'Slug'</label>
<input class="form-control" type="text" name="slug" value="{$blog->slug}" />
</p>
<p>
<label>&nbsp;</label>
<input type="hidden" name="id" value="{$blog->id}" />
<button type="submit" class="btn-small btn btn-primary" name="save">Save</button>
<button type="submit" class="btn-small btn btn-primary" name="cancel">Cancel</button>
</p>
</fieldset>
</form>

<p><br /><br />New items will be automatically saved to drafts.</p>




{include file="elib:/admin/admin_footer.tpl"}