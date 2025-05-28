{include file="elib:admin/admin_header.tpl"}


<form action="" method="post">
<fieldset>
<legend>Edit Meta Text</legend>
<p>
<label>Meta Text</label>
<textarea class="raw form-control" name="meta" rows="10" cols="">{$cat_item->meta|escape}</textarea>
</p>
<p>
<label>&nbsp;</label>
<input type="hidden" name="id" value="{$cat_item->id}" />
<button class="btn btn-sm btn-primary" type="submit" name="save">Save</button>
<button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
</p>
</fieldset>
</form>

{include file="elib:admin/admin_footer.tpl"}
