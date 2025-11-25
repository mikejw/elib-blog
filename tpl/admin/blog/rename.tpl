
<form action="" method="post">
    <div class="mb-3">
        <label class="form-label" for="label">Label</label>
        <input name="label" type="text" class="form-control" id="label" placeholder="Enter label" value="{$blog_category->label}">
    </div>
    <div class="mb-3">
        <input type="hidden" name="id" value="{$blog_category->id}" />
        <button type="submit" name="save" class="btn btn-primary btn-sm">Submit</button>
        <button type="submit" name="cancel" class="btn btn-primary btn-sm">Cancel</button>
    </div>
</form>

