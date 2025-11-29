

<form action="" method="post">
    <fieldset>
        <legend>Edit Meta Text</legend>
        <div class="mb-3">
            <label class="form-label">Meta Text</label>
            <textarea class="raw form-control" name="meta" rows="10" cols="">{$cat_item->meta|escape}</textarea>
        </div>
        <div class="mb-3">
            <input type="hidden" name="id" value="{$cat_item->id}"/>
            <button class="btn btn-sm btn-primary" type="submit" name="save">Save</button>
            <button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
        </div>
    </fieldset>
</form>

