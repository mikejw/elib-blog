{include file="elib:/admin/admin_header.tpl"}


<div class="grey_top">
<div class="top_right">
<div class="top_left"></div>
</div>
</div>


<div class="grey">

{if sizeof($errors) > 0}
<ul id="error">
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}


<form action="" method="post">
<fieldset>
<legend>Create Blog Item</legend>
<p>
<label>Heading</label>
<input type="text" name="heading" value="{$blog->heading}" />
</p>
<p>
<label>Body</label>
{*
<textarea rows="0" cols="0" name="body">{$blog->body|replace:'<br />':"\r\n"}</textarea>
*}
<textarea rows="0" cols="0" name="body">{$blog->body|replace:'</p><p>':"\r\n"|replace:'<p>':""|replace:'</p>':""}</textarea>

</p>
<p>
<label>Tags</label>
<input type="text" name="tags" value="{$blog_tags}" />
</p>
<p>
<label>Category</label>
<select name="category[]" multiple="yes">
{html_options options=$cats selected=$blog_cats}
</select>
</p>
<p>
<label>Friendly URL 'Slug'</label>
<input type="text" name="slug" value="{$blog->slug}" />
</p>
<p>
<label>&nbsp;</label>
<input type="hidden" name="id" value="{$blog->id}" />
<!--<input type="submit" name="save" value="Save" />-->
<button type="submit" name="save">Save</button>
</p>
</fieldset>
</form>

<p><br /><br />New items will be automatically saved to drafts.</p>

</div>
<div class="grey_bottom">
<div class="bottom_right">
<div class="bottom_left"></div>
</div>
</div>



{include file="elib:/admin/admin_footer.tpl"}