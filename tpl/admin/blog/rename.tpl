
<form action="" method="post">
    <div class="form-group">
        <label for="label">Label</label>
        <input name="label" type="text" class="form-control" id="label" placeholder="Enter label" value="{$blog_category->label}">
    </div>
    <input type="hidden" name="id" value="{$blog_category->id}" />
    <button type="submit" name="save" class="btn btn-primary">Submit</button>
</form>

