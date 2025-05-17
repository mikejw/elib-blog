<!DOCTYPE html>
<html lang="en" class="{if $centerpage}centerpage{/if}">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Empathy Admin - Blog Images</title>
    <link href="http://{$WEB_ROOT}{$PUBLIC_DIR}/vendor/css/style.min.css" rel="stylesheet">
  </head>
  <body>
    <div class="container">

      {if count($images) === 0}
      <p>No images associated with this blog post.</p>
      {else}
      <form method="post" action="">
        <div class="form-group">
          {foreach from=$images item=image}
          <div class="form-check">
            <input class="form-check-input" type="radio" name="image" id="image{$image.id}" value="{$image.id}" data-payload="{base64_encode(json_encode($image))}">
            <label class="form-check-label" for="image{$image.id}">
              <img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/uploads/{$image.filename}" class="img-thumbnail" alt="Image {$image.id}" />
            </label>
          </div>
          {/foreach}
        </div>
        <div class="form-group">
          <p>Size</p>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="size" id="size-original" value="" checked>
            <label class="form-check-label" for="size-original">
              Original
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="size" id="size-l" value="l_">
            <label class="form-check-label" for="size-l">
              Large
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="size" id="size-mid" value="mid_">
            <label class="form-check-label" for="size-mid">
              Mid Size
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="size" id="size-tn" value="tn_">
            <label class="form-check-label" for="size-tn">
              Thumbnail
            </label>
          </div>
        </div>
        <div class="form-group">
        <p>Properties</p>
          <div class="form-check">
            <input type="checkbox" name="fluid" class="form-check-input" value="img-fluid" id="fluid">
            <label class="form-check-label" for="fluid">Enable Fluid (Constrain width to container.)</label>
          </div>
          <div class="form-check">
            <input type="checkbox" name="centered" class="form-check-input" value="d-block mx-auto" id="centered">
            <label class="form-check-label" for="centered">Enable Centered</label>
          </div>
        </div>
        <div class="form-group">
          <label for="caption">Caption</label>
          <input name="caption" type="text" class="form-control" placeholder="Enter image caption" value="">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
      </form>
      {/if}
    </div>

    <script type="text/javascript" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/js/common.js"></script>
    <script type="text/javascript" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/vendor/js/main.min.js"></script>
    <script type="text/javascript">
      $(document).ready(function() {
        var submitted = false;
        $('form').submit(function(e) {
          if (!submitted) {
            processing = true;
            e.preventDefault();
            var payload = $('form input[name=image]:checked').data('payload');
            if (!payload) {
              alert('Please select an image!');
            } else {
              var data = JSON.parse(atob(payload));
              data.size = $('form input[name=size]:checked').val();
              data.fluid = $('form input[name=fluid]:checked').val() || '';
              data.centered = $('form input[name=centered]:checked').val() || '';
              data.caption = $('form input[name=caption]').val() || '';
              payload = btoa(JSON.stringify(data));

              var origin = "http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/edit_blog/{$blog_id}";
              window.parent.postMessage({
                mceAction: 'insertContent',
                content: '[blog-image:' + payload + ']'
              }, origin);
            }
            submitted = true;
          }
        });
      });
    </script>
</body>
</html>